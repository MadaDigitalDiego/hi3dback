<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpenOfferResource\Pages;
use App\Filament\Resources\OpenOfferResource\RelationManagers;
use App\Models\OpenOffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OpenOfferResource extends Resource
{
    protected static ?string $model = OpenOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Offres Ouvertes';

    protected static ?string $modelLabel = 'Offre Ouverte';

    protected static ?string $pluralModelLabel = 'Offres Ouvertes';

    protected static ?string $navigationGroup = 'Gestion des offres';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('categories'),
                Forms\Components\TextInput::make('filters'),
                Forms\Components\TextInput::make('budget')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('deadline'),
                Forms\Components\TextInput::make('company')
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('files'),
                Forms\Components\TextInput::make('recruitment_type')
                    ->maxLength(255),
                Forms\Components\Toggle::make('open_to_applications')
                    ->required(),
                Forms\Components\Toggle::make('auto_invite')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('active'),
                Forms\Components\TextInput::make('views_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('budget')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recruitment_type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('open_to_applications')
                    ->boolean(),
                Tables\Columns\IconColumn::make('auto_invite')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListOpenOffers::route('/'),
            'create' => Pages\CreateOpenOffer::route('/create'),
            'edit' => Pages\EditOpenOffer::route('/{record}/edit'),
        ];
    }
}
