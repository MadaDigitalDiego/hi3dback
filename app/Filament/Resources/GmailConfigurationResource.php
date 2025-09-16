<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GmailConfigurationResource\Pages;
use App\Filament\Resources\GmailConfigurationResource\RelationManagers;
use App\Models\GmailConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GmailConfigurationResource extends Resource
{
    protected static ?string $model = GmailConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-at-symbol';

    protected static ?string $navigationLabel = 'Configuration Gmail';

    protected static ?string $modelLabel = 'Configuration Gmail';

    protected static ?string $pluralModelLabel = 'Configurations Gmail';

    protected static ?string $navigationGroup = 'Authentification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la configuration')
                            ->required()
                            ->default('Gmail OAuth Configuration')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Configuration active')
                            ->helperText('Une seule configuration peut être active à la fois')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration OAuth Google')
                    ->schema([
                        Forms\Components\TextInput::make('client_id')
                            ->label('Client ID Google')
                            ->required()
                            ->helperText('Obtenez cette valeur depuis la Google Cloud Console')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('client_secret')
                            ->label('Client Secret Google')
                            ->required()
                            ->password()
                            ->revealable()
                            ->helperText('Obtenez cette valeur depuis la Google Cloud Console')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('redirect_uri')
                            ->label('URI de redirection')
                            ->required()
                            ->url()
                            ->default(fn() => url('/api/auth/gmail/callback'))
                            ->helperText('Cette URL doit être configurée dans Google Cloud Console')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Permissions (Scopes)')
                    ->schema([
                        Forms\Components\CheckboxList::make('scopes')
                            ->label('Permissions demandées')
                            ->options([
                                'openid' => 'OpenID Connect',
                                'profile' => 'Profil utilisateur',
                                'email' => 'Adresse email',
                            ])
                            ->default(['openid', 'profile', 'email'])
                            ->required()
                            ->helperText('Sélectionnez les permissions nécessaires pour votre application')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_id')
                    ->label('Client ID')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->client_id),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('redirect_uri')
                    ->label('URI de redirection')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->redirect_uri),

                Tables\Columns\BadgeColumn::make('scopes')
                    ->label('Permissions')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' permissions' : '0 permissions')
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Configuration active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test_connection')
                    ->label('Tester')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->action(function ($record) {
                        // Logique de test de connexion
                        if ($record->isComplete()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Configuration valide')
                                ->body('La configuration Gmail semble correcte.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Configuration incomplète')
                                ->body('Veuillez remplir tous les champs requis.')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGmailConfigurations::route('/'),
            'create' => Pages\CreateGmailConfiguration::route('/create'),
            'edit' => Pages\EditGmailConfiguration::route('/{record}/edit'),
        ];
    }    
}
