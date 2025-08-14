<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Gestion des utilisateurs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])->columns(2),

                Forms\Components\Section::make('Authentification')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->maxLength(255)
                            ->helperText('Laissez vide pour conserver le mot de passe actuel lors de la modification'),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email vérifié le')
                            ->helperText('Laissez vide si l\'email n\'est pas encore vérifié'),
                    ])->columns(2),

                Forms\Components\Section::make('Paramètres du compte')
                    ->schema([
                        Forms\Components\Toggle::make('is_professional')
                            ->label('Compte professionnel')
                            ->helperText('Indique si l\'utilisateur est un professionnel'),
                        Forms\Components\Toggle::make('profile_completed')
                            ->label('Profil complété')
                            ->helperText('Indique si l\'utilisateur a complété son profil'),
                        Forms\Components\Select::make('role')
                            ->label('Rôle administratif')
                            ->options([
                                'user' => 'Utilisateur',
                                'moderator' => 'Modérateur',
                                'admin' => 'Administrateur',
                                'super_admin' => 'Super Administrateur',
                            ])
                            ->default('user')
                            ->required()
                            ->helperText('Définit les permissions d\'accès au back-office'),
                        Forms\Components\TextInput::make('stripe_customer_id')
                            ->label('ID Client Stripe')
                            ->maxLength(255)
                            ->helperText('ID du client dans Stripe (généré automatiquement)'),
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
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email vérifié')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at)),
                Tables\Columns\IconColumn::make('is_professional')
                    ->label('Professionnel')
                    ->boolean()
                    ->trueIcon('heroicon-o-briefcase')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('info')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('profile_completed')
                    ->label('Profil complété')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'user' => 'gray',
                        'moderator' => 'warning',
                        'admin' => 'success',
                        'super_admin' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'user' => 'Utilisateur',
                        'moderator' => 'Modérateur',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                    }),
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
                SelectFilter::make('is_professional')
                    ->label('Type de compte')
                    ->options([
                        '1' => 'Professionnel',
                        '0' => 'Client',
                    ]),
                SelectFilter::make('profile_completed')
                    ->label('Statut du profil')
                    ->options([
                        '1' => 'Complété',
                        '0' => 'Incomplet',
                    ]),
                SelectFilter::make('role')
                    ->label('Rôle administratif')
                    ->options([
                        'user' => 'Utilisateur',
                        'moderator' => 'Modérateur',
                        'admin' => 'Administrateur',
                        'super_admin' => 'Super Administrateur',
                    ]),
                Filter::make('email_verified')
                    ->label('Email vérifié')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Filter::make('email_not_verified')
                    ->label('Email non vérifié')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
