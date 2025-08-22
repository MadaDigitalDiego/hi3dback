<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroImageResource\Pages;
use App\Filament\Resources\HeroImageResource\RelationManagers;
use App\Models\HeroImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\ReorderAction;
use Filament\Tables\Filters\Filter;

class HeroImageResource extends Resource
{
    protected static ?string $model = HeroImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Images Hero';

    protected static ?string $modelLabel = 'Image Hero';

    protected static ?string $pluralModelLabel = 'Images Hero';

    protected static ?string $navigationGroup = 'Gestion du contenu';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->maxLength(255)
                            ->helperText('Titre optionnel pour identifier l\'image'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->helperText('Description optionnelle de l\'image'),

                        Forms\Components\TextInput::make('alt_text')
                            ->label('Texte alternatif')
                            ->maxLength(255)
                            ->helperText('Texte alternatif pour l\'accessibilité (recommandé)'),
                    ])->columns(1),

                Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Image principale')
                            ->image()
                            ->directory('hero-images')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '21:9',
                                null, // Libre
                            ])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->helperText('Image principale qui sera affichée dans le Hero (max 5MB)'),

                        Forms\Components\FileUpload::make('thumbnail_path')
                            ->label('Miniature (optionnel)')
                            ->image()
                            ->directory('hero-images/thumbnails')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048) // 2MB
                            ->helperText('Miniature pour l\'aperçu (optionnel, max 2MB)'),
                    ])->columns(1),

                Section::make('Paramètres d\'affichage')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activer')
                            ->default(false)
                            ->helperText('Activer pour afficher cette image dans le Hero'),

                        Forms\Components\TextInput::make('position')
                            ->label('Position')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Position pour l\'ordre d\'affichage (0 = premier)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('Aperçu')
                    ->size(80)
                    ->defaultImageUrl(fn ($record) => $record->image_url)
                    ->circular(false),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sans titre'),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->alignCenter(),

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
                Filter::make('active')
                    ->label('Images actives')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),

                Filter::make('inactive')
                    ->label('Images inactives')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false)),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer sélectionnées')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => true]));
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver sélectionnées')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false]));
                        }),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position')
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
            'index' => Pages\ListHeroImages::route('/'),
            'create' => Pages\CreateHeroImage::route('/create'),
            'edit' => Pages\EditHeroImage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = static::getModel()::where('is_active', true)->count();
        return $activeCount > 0 ? 'success' : 'gray';
    }
}
