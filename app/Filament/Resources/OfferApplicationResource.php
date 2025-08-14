<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferApplicationResource\Pages;
use App\Filament\Resources\OfferApplicationResource\RelationManagers;
use App\Models\OfferApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfferApplicationResource extends Resource
{
    protected static ?string $model = OfferApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Candidatures';

    protected static ?string $modelLabel = 'Candidature';

    protected static ?string $pluralModelLabel = 'Candidatures';

    protected static ?string $navigationGroup = 'Gestion des offres';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('open_offer_id')
                    ->relationship('openOffer', 'title')
                    ->required(),
                Forms\Components\TextInput::make('professional_profile_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('proposal')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('openOffer.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('professional_profile_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListOfferApplications::route('/'),
            'create' => Pages\CreateOfferApplication::route('/create'),
            'edit' => Pages\EditOfferApplication::route('/{record}/edit'),
        ];
    }
}
