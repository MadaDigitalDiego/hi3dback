<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OpenOffer;
use App\Models\ProfessionalProfile;
use App\Notifications\NewOpenOfferNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class DiagnoseEmailIssues extends Command
{
    protected $signature = 'diagnose:emails';
    protected $description = 'Diagnose email sending issues for professional notifications';

    public function handle()
    {
        $this->info('=== Diagnostic des problèmes d\'envoi d\'emails ===');
        $this->newLine();

        // 1. Vérifier la configuration email
        $this->checkEmailConfiguration();

        // 2. Vérifier les utilisateurs professionnels
        $this->checkProfessionalUsers();

        // 3. Test d'envoi d'email simple
        $this->testSimpleEmail();

        // 4. Test de notification
        $this->testNotification();

        // 5. Vérifier les logs
        $this->checkLogs();

        $this->info('=== Fin du diagnostic ===');
    }

    private function checkEmailConfiguration()
    {
        $this->info('1. Vérification de la configuration email:');
        
        $mailer = config('mail.default');
        $this->line("  - Mailer par défaut: {$mailer}");
        
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $encryption = config('mail.mailers.smtp.encryption');
        $username = config('mail.mailers.smtp.username');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
        
        $this->line("  - Host SMTP: {$host}");
        $this->line("  - Port: {$port}");
        $this->line("  - Encryption: {$encryption}");
        $this->line("  - Username: {$username}");
        $this->line("  - From Address: {$fromAddress}");
        $this->line("  - From Name: {$fromName}");
        
        // Vérifier les variables d'environnement
        $envMailer = env('MAIL_MAILER');
        $this->line("  - ENV MAIL_MAILER: {$envMailer}");
        
        if ($envMailer === 'log') {
            $this->warn("  ⚠️  MAIL_MAILER est configuré sur 'log' - les emails seront écrits dans les logs");
        }
        
        $this->newLine();
    }

    private function checkProfessionalUsers()
    {
        $this->info('2. Vérification des utilisateurs professionnels:');
        
        $totalUsers = User::count();
        $professionalUsers = User::where('is_professional', true)->count();
        $usersWithProfiles = User::where('is_professional', true)
            ->whereHas('professionalProfile')
            ->count();
        
        $this->line("  - Total utilisateurs: {$totalUsers}");
        $this->line("  - Utilisateurs professionnels: {$professionalUsers}");
        $this->line("  - Professionnels avec profil: {$usersWithProfiles}");
        
        // Vérifier les emails des professionnels
        $professionalsWithEmail = User::where('is_professional', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        
        $this->line("  - Professionnels avec email: {$professionalsWithEmail}");
        
        // Exemples d'emails
        $sampleEmails = User::where('is_professional', true)
            ->whereNotNull('email')
            ->limit(3)
            ->pluck('email');
        
        $this->line("  - Exemples d'emails: " . $sampleEmails->implode(', '));
        $this->newLine();
    }

    private function testSimpleEmail()
    {
        $this->info('3. Test d\'envoi d\'email simple:');
        
        try {
            // Trouver un utilisateur professionnel pour le test
            $testUser = User::where('is_professional', true)
                ->whereNotNull('email')
                ->first();
            
            if (!$testUser) {
                $this->error("  ❌ Aucun utilisateur professionnel avec email trouvé");
                return;
            }
            
            $this->line("  - Test avec utilisateur: {$testUser->email}");
            
            // Test d'envoi simple
            Mail::raw('Test email from Laravel', function ($message) use ($testUser) {
                $message->to($testUser->email)
                        ->subject('Test Email - Diagnostic');
            });
            
            $this->info("  ✅ Email de test envoyé avec succès");
            
        } catch (\Exception $e) {
            $this->error("  ❌ Erreur lors de l'envoi: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testNotification()
    {
        $this->info('4. Test de notification:');
        
        try {
            // Trouver un utilisateur professionnel et une offre pour le test
            $testUser = User::where('is_professional', true)
                ->whereNotNull('email')
                ->first();
            
            $testOffer = OpenOffer::first();
            
            if (!$testUser) {
                $this->error("  ❌ Aucun utilisateur professionnel trouvé");
                return;
            }
            
            if (!$testOffer) {
                $this->error("  ❌ Aucune offre trouvée");
                return;
            }
            
            $this->line("  - Test notification pour: {$testUser->email}");
            $this->line("  - Offre: {$testOffer->title}");
            
            // Vérifier les propriétés nécessaires
            if (!$testUser->first_name || !$testUser->last_name) {
                $this->warn("  ⚠️  L'utilisateur n'a pas de first_name/last_name");
            }
            
            // Test de notification
            Notification::send($testUser, new NewOpenOfferNotification($testOffer));
            
            $this->info("  ✅ Notification envoyée avec succès");
            
        } catch (\Exception $e) {
            $this->error("  ❌ Erreur lors de l'envoi de notification: " . $e->getMessage());
            $this->error("  Stack trace: " . $e->getTraceAsString());
        }
        
        $this->newLine();
    }

    private function checkLogs()
    {
        $this->info('5. Vérification des logs récents:');
        
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            $this->error("  ❌ Fichier de log non trouvé: {$logFile}");
            return;
        }
        
        // Lire les dernières lignes du log
        $lines = [];
        $file = new \SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        // Lire les 50 dernières lignes
        $startLine = max(0, $totalLines - 50);
        $file->seek($startLine);
        
        $emailRelatedLines = [];
        while (!$file->eof()) {
            $line = $file->current();
            if (stripos($line, 'mail') !== false || 
                stripos($line, 'notification') !== false || 
                stripos($line, 'email') !== false ||
                stripos($line, 'smtp') !== false) {
                $emailRelatedLines[] = trim($line);
            }
            $file->next();
        }
        
        if (empty($emailRelatedLines)) {
            $this->line("  - Aucun log lié aux emails trouvé récemment");
        } else {
            $this->line("  - Logs récents liés aux emails:");
            foreach (array_slice($emailRelatedLines, -10) as $line) {
                $this->line("    " . substr($line, 0, 100) . "...");
            }
        }
        
        $this->newLine();
    }
}
