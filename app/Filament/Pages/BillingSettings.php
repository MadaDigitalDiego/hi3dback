<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use App\Models\BillingSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;

class BillingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Facturation';
    protected static ?string $title = 'Paramètres de facturation';
    protected static ?string $navigationLabel = 'Paramètres';

    protected static string $view = 'filament.pages.billing-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = BillingSetting::first();
        if ($settings) {
            $this->form->fill($settings->toArray());
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de l\'entreprise')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('billing'),
                        TextInput::make('company_name')
                            ->label('Nom de l\'entreprise')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email de facturation')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->label('Téléphone'),
                        TextInput::make('vat_number')
                            ->label('Numéro de TVA / NIF / VAT'),
                        TextInput::make('address')
                            ->label('Adresse de facturation')
                            ->required(),
                    ])->columns(2),

                Section::make('Mentions et Pied de page')
                    ->schema([
                        Textarea::make('legal_mentions')
                            ->label('Mentions légales')
                            ->rows(3),
                        Textarea::make('footer_text')
                            ->label('Pied de page de facture')
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            
            $settings = BillingSetting::first();
            if ($settings) {
                $settings->update($data);
            } else {
                BillingSetting::create($data);
            }

            Notification::make()
                ->title('Paramètres enregistrés')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de l\'enregistrement')
                ->danger()
                ->send();
        }
    }
}
