<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?string $pluralModelLabel = 'Catégories';

    protected static ?string $navigationGroup = 'Gestion du contenu';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations principales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                if ($context === 'create') {
                                    $set('value', \Illuminate\Support\Str::slug($state, '_'));
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('value')
                            ->label('Valeur technique')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Identifiant technique unique (généré automatiquement)'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Slug pour les URLs (généré automatiquement)'),
                    ])->columns(2),

                Forms\Components\Section::make('Description et contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->helperText('Description détaillée de la catégorie'),
                    ]),

                Forms\Components\Section::make('Hiérarchie et organisation')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Catégorie parente')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Sélectionnez une catégorie parente pour créer une sous-catégorie'),

                        Forms\Components\TextInput::make('order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Ordre d\'affichage (0 = premier)'),
                    ])->columns(2),

                Forms\Components\Section::make('Média et statut')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Image')
                            ->image()
                            ->directory('categories')
                            ->visibility('public')
                            ->helperText('Image représentative de la catégorie'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->helperText('Désactiver pour masquer la catégorie'),

                        Forms\Components\Placeholder::make('count')
                            ->label('Nombre d\'éléments')
                            ->content(fn ($record) => $record?->count ?? 0)
                            ->helperText('Nombre d\'éléments dans cette catégorie (calculé automatiquement)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-category.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Catégorie principale')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('count')
                    ->label('Éléments')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Catégorie parente')
                    ->relationship('parent', 'name')
                    ->placeholder('Toutes les catégories'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous les statuts')
                    ->trueLabel('Actives seulement')
                    ->falseLabel('Inactives seulement'),

                Tables\Filters\Filter::make('main_categories')
                    ->label('Catégories principales')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),

                Tables\Filters\Filter::make('subcategories')
                    ->label('Sous-catégories')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
