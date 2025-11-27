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
                Forms\Components\Section::make('Plan Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Plan Title')
                            ->helperText('Display title for the plan (e.g., "Professional Plan")'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Plan Name')
                            ->helperText('Internal name/slug (e.g., "pro", "enterprise")'),
                        Forms\Components\Select::make('user_type')
                            ->required()
                            ->options([
                                'professional' => 'Professional',
                                'client' => 'Client',
                            ])
                            ->label('User Type')
                            ->helperText('Select the type of user this plan is for'),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->label('Monthly Price'),
                        Forms\Components\Select::make('interval')
                            ->required()
                            ->options([
                                'month' => 'Monthly',
                                'year' => 'Yearly',
                                'week' => 'Weekly',
                                'day' => 'Daily',
                            ])
                            ->default('month')
                            ->label('Billing Interval'),
                        Forms\Components\TextInput::make('interval_count')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->label('Interval Count')
                            ->helperText('Number of intervals between billings (e.g., 1 for monthly, 3 for quarterly)'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Display Order'),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->label('Active'),
                    ])->columns(2),

                Forms\Components\Section::make('Stripe Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('stripe_product_id')
                            ->maxLength(255)
                            ->label('Stripe Product ID')
                            ->helperText('Laisser vide et utiliser le bouton "Créer sur Stripe" pour créer automatiquement le produit.'),
                        Forms\Components\TextInput::make('stripe_price_id')
                            ->maxLength(255)
                            ->label('Stripe Price ID (par défaut)')
                            ->helperText('ID du prix utilisé pour les abonnements. Peut être rempli automatiquement via le back-office.'),
                        Forms\Components\TextInput::make('stripe_price_id_monthly')
                            ->maxLength(255)
                            ->label('Stripe Price ID (Monthly)'),
                        Forms\Components\TextInput::make('stripe_price_id_yearly')
                            ->maxLength(255)
                            ->label('Stripe Price ID (Yearly)'),
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
                            ->label('Max Applications')
                            ->helperText('Maximum number of applications (leave empty for unlimited)'),
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
                    ->label('Max Apps'),
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
