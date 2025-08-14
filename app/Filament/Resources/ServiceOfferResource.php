<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceOfferResource\Pages;
use App\Filament\Resources\ServiceOfferResource\RelationManagers;
use App\Models\ServiceOffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOfferResource extends Resource
{
    protected static ?string $model = ServiceOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $modelLabel = 'Service';

    protected static ?string $pluralModelLabel = 'Services';

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
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('execution_time')
                    ->maxLength(255),
                Forms\Components\TextInput::make('concepts')
                    ->numeric(),
                Forms\Components\TextInput::make('revisions')
                    ->numeric(),
                Forms\Components\Toggle::make('is_private')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('active'),
                Forms\Components\TextInput::make('categories'),
                Forms\Components\TextInput::make('files'),
                Forms\Components\TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('likes')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('rating')
                    ->numeric(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
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
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('execution_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('concepts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revisions')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_private')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('likes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
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
            'index' => Pages\ListServiceOffers::route('/'),
            'create' => Pages\CreateServiceOffer::route('/create'),
            'edit' => Pages\EditServiceOffer::route('/{record}/edit'),
        ];
    }
}
