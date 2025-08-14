<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-super-admin
                            {--email= : Email de l\'administrateur}
                            {--password= : Mot de passe de l\'administrateur}
                            {--first-name= : Prénom de l\'administrateur}
                            {--last-name= : Nom de l\'administrateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un super administrateur pour le back-office Filament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Création d\'un Super Administrateur ===');
        $this->newLine();

        // Récupérer les données soit depuis les options, soit en demandant à l'utilisateur
        $email = $this->option('email') ?: $this->ask('Email de l\'administrateur');
        $password = $this->option('password') ?: $this->secret('Mot de passe');
        $firstName = $this->option('first-name') ?: $this->ask('Prénom', 'Admin');
        $lastName = $this->option('last-name') ?: $this->ask('Nom', 'Hi3D');

        // Validation des données
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->error('Erreurs de validation :');
            foreach ($validator->errors()->all() as $error) {
                $this->error('- ' . $error);
            }
            return 1;
        }

        // Vérifier si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $this->error("Un utilisateur avec l'email {$email} existe déjà.");
            return 1;
        }

        try {
            // Créer l'utilisateur
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_professional' => false,
                'profile_completed' => true,
                'role' => 'super_admin',
            ]);

            $this->newLine();
            $this->info('✅ Super administrateur créé avec succès !');
            $this->newLine();
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['ID', $user->id],
                    ['Nom complet', $user->first_name . ' ' . $user->last_name],
                    ['Email', $user->email],
                    ['Rôle', $user->role],
                    ['Créé le', $user->created_at->format('d/m/Y H:i:s')],
                ]
            );
            $this->newLine();
            $this->info('Vous pouvez maintenant vous connecter au back-office :');
            $this->info('URL: ' . config('app.url') . '/admin');
            $this->info('Email: ' . $user->email);
            $this->newLine();

            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
            return 1;
        }
    }
}
