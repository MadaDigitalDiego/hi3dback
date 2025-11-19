<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StripeConfigurationResource\Pages;
use App\Models\StripeConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StripeConfigurationResource extends Resource
{
    protected static ?string $model = StripeConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Configuration Stripe';

    protected static ?string $modelLabel = 'Configuration Stripe';

    protected static ?string $pluralModelLabel = 'Configurations Stripe';

    protected static ?string $navigationGroup = 'Paiements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Ex: Configuration de production')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('mode')
                            ->label('Mode Stripe')
                            ->options([
                                'test' => 'Test',
                                'live' => 'Production',
                            ])
                            ->required()
                            ->default('test'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Configuration active')
                            ->helperText('Une seule configuration peut être active à la fois')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Clés Stripe')
                    ->description('Obtenez ces clés depuis votre tableau de bord Stripe')
                    ->schema([
                        Forms\Components\TextInput::make('public_key')
                            ->label('Clé publique (pk_...)')
                            ->required()
                            ->placeholder('pk_test_...')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('secret_key')
                            ->label('Clé secrète (sk_...)')
                            ->required()
                            ->password()
                            ->placeholder('sk_test_...')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('webhook_secret')
                            ->label('Secret Webhook (whsec_...)')
                            ->required()
                            ->password()
                            ->placeholder('whsec_...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'test' => 'info',
                        'live' => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripeConfigurations::route('/'),
            'create' => Pages\CreateStripeConfiguration::route('/create'),
            'edit' => Pages\EditStripeConfiguration::route('/{record}/edit'),
        ];
    }
}

