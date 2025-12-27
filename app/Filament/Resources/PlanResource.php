<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Filament\Resources\PlanResource\RelationManagers;
use App\Models\Plan;
use App\Services\StripeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
	            ->schema([
	                // Informations générales du plan
		                Forms\Components\Section::make('Informations générales')
	                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Titre du plan')
	                            ->helperText('Titre affiché pour ce plan (ex. "Offre Professionnelle").'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nom technique')
                            ->helperText('Identifiant interne/slug (ex. \"pro\", \"enterprise\").'),
                        Forms\Components\Select::make('user_type')
                            ->required()
                            ->options([
                                'professional' => 'Professionnel',
                                'client' => 'Client',
                            ])
	                            ->label('Type d\'utilisateur')
	                            ->helperText('Type d\'utilisateur ciblé par ce plan.'),
	                        Forms\Components\Textarea::make('description')
	                            ->label('Description')
	                            ->helperText('Description marketing ou fonctionnelle du plan.')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->label('Plan actif'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Ordre d\'affichage')
                            ->helperText('Permet de trier les plans dans l\'interface.'),
                    ])->columns(2),

		                // Tarifications & facturations (référence pour Stripe)
		                Forms\Components\Section::make('Tarifications & facturations')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0.00)
                            ->step(0.01)
	                            ->prefix(config('subscription.currency', 'EUR') . ' ')
		                            ->label('Prix mensuel de référence')
		                            ->helperText('Montant mensuel (> 0) utilisé pour générer les prix Stripe mensuels et annuels.'),
	                        Forms\Components\TextInput::make('yearly_price')
	                            ->numeric()
	                            ->minValue(0.00)
	                            ->step(0.01)
	                            ->prefix(config('subscription.currency', 'EUR') . ' ')
		                            ->label('Prix annuel (optionnel)')
		                            ->helperText('Si vide, le prix annuel Stripe sera calculé automatiquement (12 x prix mensuel).'),
	                        Forms\Components\Select::make('interval')
	                            ->required()
	                            ->options([
	                                'month' => 'Mensuel',
	                                'year' => 'Annuel',
	                            ])
		                            ->default('month')
		                            ->label('Période de facturation')
		                            ->helperText('Actuellement seuls les plans mensuels et annuels sont supportés pour Stripe.'),
	                        Forms\Components\TextInput::make('interval_count')
	                            ->required()
	                            ->numeric()
		                            ->default(1)
		                            ->minValue(1)
		                            ->label('Nombre d\'intervalles')
		                            ->helperText('Pour l\'instant, Stripe utilise toujours 1 (mensuel ou annuel). Laissez la valeur par défaut.'),
                    ])->columns(2),

		                // Configuration Stripe (avancée / générée automatiquement)
		                Forms\Components\Section::make('Stripe (avancé)')
	                    ->description('Ces champs sont remplis automatiquement lors de la synchronisation avec Stripe.')
                    ->schema([
	                        Forms\Components\TextInput::make('stripe_product_id')
	                            ->maxLength(255)
	                            ->label('Stripe Product ID')
	                            ->readOnly()
	                            ->helperText('Créé automatiquement via le bouton "Créer sur Stripe" ou la synchronisation automatique.'),
	                        Forms\Components\TextInput::make('stripe_price_id')
	                            ->maxLength(255)
	                            ->label('Stripe Price ID (par défaut)')
	                            ->readOnly()
	                            ->helperText('Pointe en général vers le prix mensuel ou annuel selon la configuration du plan.'),
                        Forms\Components\TextInput::make('stripe_price_id_monthly')
                            ->maxLength(255)
                            ->label('Stripe Price ID (mensuel)')
                            ->readOnly(),
                        Forms\Components\TextInput::make('stripe_price_id_yearly')
                            ->maxLength(255)
                            ->label('Stripe Price ID (annuel)')
                            ->readOnly(),
		                        Forms\Components\Toggle::make('auto_sync_to_stripe')
	                            ->label('Créer / mettre à jour sur Stripe après sauvegarde')
	                            ->helperText('Si activé, le produit et les prix Stripe seront créés ou mis à jour automatiquement après l\'enregistrement du plan.')
	                            ->default(false),
                    ])->columns(1),

                Forms\Components\Section::make('Plan Limits')
                    ->description('Define the limits for each feature in this plan')
                    ->schema([
                        Forms\Components\TextInput::make('max_services')
                            ->numeric()
                            ->nullable()
                            ->label('Max Services')
                            ->helperText('Maximum number of services (leave empty for unlimited)'),
                        Forms\Components\TextInput::make('max_open_offers')
                            ->numeric()
                            ->nullable()
                            ->label('Max Open Offers')
                            ->helperText('Maximum number of open offers (leave empty for unlimited)'),
                        Forms\Components\TextInput::make('max_applications')
                            ->numeric()
                            ->nullable()
	                            ->label('Max candidates')
	                            ->helperText('Maximum number of candidates (leave empty for unlimited)'),
                        Forms\Components\TextInput::make('max_messages')
                            ->numeric()
                            ->nullable()
                            ->label('Max Messages')
                            ->helperText('Maximum number of messages (leave empty for unlimited)'),
                        Forms\Components\KeyValue::make('limits')
                            ->keyLabel('Feature')
                            ->valueLabel('Limit')
                            ->columnSpanFull()
                            ->helperText('Additional custom limits (optional)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('user_type')
                    ->colors([
                        'primary' => 'professional',
                        'success' => 'client',
                    ])
                    ->label('User Type'),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_services')
                    ->numeric()
                    ->label('Max Services'),
                Tables\Columns\TextColumn::make('max_open_offers')
                    ->numeric()
                    ->label('Max Offers'),
                Tables\Columns\TextColumn::make('max_applications')
                    ->numeric()
	                    ->label('Max candidates'),
                Tables\Columns\TextColumn::make('max_messages')
                    ->numeric()
                    ->label('Max Messages'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('syncStripe')
                    ->label('Créer sur Stripe')
                    ->icon('heroicon-o-credit-card')
                    ->requiresConfirmation()
                    ->action(function (Plan $record) {
                        try {
                            app(StripeService::class)->syncPlanWithStripe($record);

                            Notification::make()
                                ->title('Plan synchronisé avec Stripe')
                                ->body('Le produit et le prix Stripe ont été créés ou mis à jour.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erreur lors de la synchronisation Stripe')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }    
}
