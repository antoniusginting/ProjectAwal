<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms\Components\Select;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'User';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static ?string $label = 'Daftar User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->placeholder('Masukkan nama lengkap')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->placeholder('Masukkan alamat email')
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->placeholder(fn($record) => $record ? 'Biarkan kosong jika tidak diubah' : 'Masukkan kata sandi')
                    ->minLength(8)
                    ->dehydrated(fn($state) => filled($state)) // Hanya kirim jika diisi
                    ->required(fn($operation) => $operation === 'create'),

                Select::make('roles')
                    ->relationship('roles', 'name')
                    // ->multiple() // bisa dua role
                    ->preload()
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('No'),
                TextColumn::make('name')->label('Nama'),
                TextColumn::make('email'),
                TextColumn::make('roles.name')
                    ->label('Peran'),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }
}
