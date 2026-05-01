<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Gestion du contenu';
    protected static ?string $title = 'Paramètres du site';
    protected static ?string $navigationLabel = 'Contenu & Réseaux sociaux';

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::first();
        if ($settings) {
            $this->form->fill($settings->toArray());
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Page À propos - En-tête')
                    ->schema([
                        TextInput::make('about_title')
                            ->label('Titre')
                            ->maxLength(255),
                        TextInput::make('about_subtitle')
                            ->label('Sous-titre')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Page À propos - Notre histoire')
                    ->schema([
                        Textarea::make('about_story')
                            ->label('Notre histoire')
                            ->rows(5),
                    ]),

                Section::make('Page À propos - Notre mission')
                    ->schema([
                        Textarea::make('about_mission')
                            ->label('Notre mission')
                            ->rows(5),
                    ]),

                Section::make('Page À propos - Nos valeurs')
                    ->schema([
                        Repeater::make('about_values')
                            ->label('Valeurs')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('Description')
                                    ->required(),
                                TextInput::make('icon')
                                    ->label('Icône (nom Lucide React)')
                                    ->helperText('Ex: Star, Heart, Users, Lightbulb')
                                    ->default('Star'),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->defaultItems(4)
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    ]),

                Section::make('Page À propos - Notre équipe')
                    ->schema([
                        Repeater::make('about_team')
                            ->label('Membres de l\'équipe')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required(),
                                TextInput::make('role')
                                    ->label('Poste')
                                    ->required(),
                                TextInput::make('image')
                                    ->label('URL de l\'image')
                                    ->helperText('URL de l\'image du membre'),
                                Textarea::make('bio')
                                    ->label('Bio')
                                    ->rows(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->defaultItems(4)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),

                Section::make('Page À propos - Call to Action')
                    ->schema([
                        TextInput::make('about_cta_title')
                            ->label('Titre du CTA')
                            ->maxLength(255),
                        Textarea::make('about_cta_description')
                            ->label('Description du CTA')
                            ->rows(2),
                    ])->columns(2),

                Section::make('Liens Réseaux Sociaux')
                    ->schema([
                        TextInput::make('social_facebook')
                            ->label('Facebook')
                            ->url()
                            ->prefix('https://'),
                        TextInput::make('social_twitter')
                            ->label('Twitter / X')
                            ->url()
                            ->prefix('https://'),
                        TextInput::make('social_instagram')
                            ->label('Instagram')
                            ->url()
                            ->prefix('https://'),
                        TextInput::make('social_linkedin')
                            ->label('LinkedIn')
                            ->url()
                            ->prefix('https://'),
                        TextInput::make('social_youtube')
                            ->label('YouTube')
                            ->url()
                            ->prefix('https://'),
                        TextInput::make('social_tiktok')
                            ->label('TikTok')
                            ->url()
                            ->prefix('https://'),
                    ])->columns(2),
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
            
            $settings = SiteSetting::first();
            if ($settings) {
                $settings->update($data);
            } else {
                SiteSetting::create($data);
            }

            Notification::make()
                ->title('Paramètres enregistrés')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de l\'enregistrement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
