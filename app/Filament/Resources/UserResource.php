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
use Filament\Forms\Components\Card;

class UserResource extends Resource
{
    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pelindung';
    protected static ?string $navigationLabel = 'User';


    public static ?string $label = 'Daftar User ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->autocomplete('off')
                            ->placeholder('Masukkan nama lengkap')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->autocomplete('off')
                            ->placeholder('Masukkan alamat email')
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->placeholder(fn($record) => $record ? 'Biarkan kosong jika tidak diubah' : 'Masukkan kata sandi')
                            ->dehydrated(fn($state) => filled($state)) // Hanya dikirim kalau diisi
                            ->required(fn($operation) => $operation === 'create')
                            ->rule(fn($state) => filled($state) ? ['min:5'] : []),


                        Select::make('roles')
                            ->relationship('roles', 'name')
                            // ->multiple() // bisa dua role
                            ->placeholder('Pilih salah satu role')
                            ->preload()
                            ->searchable()
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('No')
                    ->label('No')
                    ->alignCenter()
                    ->rowIndex(), // auto generate number sesuai urutan tampilan
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
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
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
}
