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

    protected static ?string $navigationIcon = 'heroicon-o-cloud-upload';

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

                        Forms\Components\TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->required()
                            ->helperText('Clé API ActiveCampaign'),

                        Forms\Components\Textarea::make('mapping')
                            ->label('Mapping (JSON)')
                            ->rows(6)
                            ->helperText('JSON optionnel pour mapper tags, lists et automations (ex: {"tags": {"signup":"TagName"}})'),
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
