<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Facturation';
    protected static ?string $label = 'Facture';
    protected static ?string $pluralLabel = 'Factures';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la facture')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')->label('Numéro'),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Client'),
                        Forms\Components\TextInput::make('amount')->label('Montant HT')->numeric(),
                        Forms\Components\TextInput::make('total')->label('Total TTC')->numeric(),
                        Forms\Components\TextInput::make('currency')->label('Devise'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'paid' => 'Payée',
                                'open' => 'Ouverte',
                                'failed' => 'Échouée',
                                'void' => 'Annulée',
                            ])
                            ->label('Statut'),
                        Forms\Components\DateTimePicker::make('created_at')->label('Date de création'),
                        Forms\Components\TextInput::make('pdf_url')
                            ->label('Lien PDF interne')
                            ->url(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Détails Stripe')
                    ->schema([
                        Forms\Components\TextInput::make('stripe_invoice_id')->label('ID Facture Stripe'),
                        Forms\Components\Placeholder::make('stripe_link')
                            ->label('Lien Stripe')
                            ->content(fn (Invoice $record): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                                "<a href=\"https://dashboard.stripe.com/invoices/{$record->stripe_invoice_id}\" target=\"_blank\" class=\"text-primary-600 underline\">Voir sur Stripe</a>"
                            )),
                    ])->columns(2),

                Forms\Components\Section::make('Informations de facturation utilisées')
                    ->schema([
                        Forms\Components\ViewField::make('billing_settings')
                            ->label('Paramètres utilisés')
                            ->view('filament.forms.components.invoice-billing-settings'),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Données brutes / Metadata'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Montant')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'open',
                        'danger' => 'failed',
                        'secondary' => 'void',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Payée',
                        'open' => 'Ouverte',
                        'failed' => 'Échouée',
                        'void' => 'Annulée',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record): ?string => $record->pdf_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Invoice $record): bool => !empty($record->pdf_url)),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }    
}
