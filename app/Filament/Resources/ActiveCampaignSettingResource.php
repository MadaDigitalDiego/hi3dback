<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActiveCampaignSettingResource\Pages;
use App\Models\ActiveCampaignSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActiveCampaignSettingResource extends Resource
{
    protected static ?string $model = ActiveCampaignSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'ActiveCampaign';

    protected static ?string $modelLabel = 'ActiveCampaign Configuration';

    protected static ?string $pluralModelLabel = 'ActiveCampaign Configurations';

    protected static ?string $navigationGroup = 'Intégrations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Activer l\'intégration')
                            ->helperText('Une seule configuration active est utilisée par le service')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Paramètres ActiveCampaign')
                    ->schema([
                        Forms\Components\TextInput::make('api_url')
                            ->label('API URL')
                            ->required()
                            ->url()
                            ->helperText('Ex: https://youraccount.api-us1.com'),

                        // Display a masked preview of the stored API key (read-only)
                        Forms\Components\TextInput::make('api_key_display')
                            ->label('API Key (enregistrée)')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state) {
                                $record = $component->getRecord();
                                if ($record?->api_key) {
                                    $component->state('••••' . substr($record->api_key, -4));
                                }
                            })
                            ->helperText('La clé n\'est pas affichée par sécurité. Saisissez une nouvelle clé pour la remplacer.'),

                        // Editable password input left blank for security; enter to update
                        Forms\Components\TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->required()
                            ->helperText('Clé API ActiveCampaign'),

                        Forms\Components\Section::make('Mapping dynamique')
                            ->schema([
                                Forms\Components\KeyValue::make('mapping.tags')
                                    ->label('Tags')
                                    ->keyLabel('Key')
                                    ->valueLabel('Tag name')
                                    ->helperText('Ex: signup => TagName')
                                    ->default([]),

                                Forms\Components\KeyValue::make('mapping.lists')
                                    ->label('Lists')
                                    ->keyLabel('Key')
                                    ->valueLabel('List ID or string')
                                    ->helperText('Ex: purchase => 123 (list id)')
                                    ->default([]),

                                Forms\Components\KeyValue::make('mapping.automations')
                                    ->label('Automations')
                                    ->keyLabel('Key')
                                    ->valueLabel('Automation ID')
                                    ->helperText('Ex: welcome => 456 (automation id)')
                                    ->default([]),
                            ])->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('api_url')
                    ->label('API URL')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->api_url),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->filters([])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActiveCampaignSettings::route('/'),
            'create' => Pages\CreateActiveCampaignSetting::route('/create'),
            'edit' => Pages\EditActiveCampaignSetting::route('/{record}/edit'),
        ];
    }
}
