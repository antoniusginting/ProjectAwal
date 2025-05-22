<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kontrak;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\AlamatKontrak;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AlamatKontrakResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\AlamatKontrakResource\RelationManagers;

class AlamatKontrakResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
    protected static ?string $model = AlamatKontrak::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Alamat';
    protected static ?string $navigationGroup = 'Kontrak';
    public static ?string $label = 'Daftar Alamat Kontrak ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('id_kontrak')
                            ->label('Nama Kontrak')
                            ->options(Kontrak::pluck('nama', 'id')) // Ambil daftar mobil
                            ->searchable() // Biar bisa cari
                            ->required(), // Wajib diisi

                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->placeholder('Masukkan alamat')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->label('No')
                    ->alignCenter()
                    ->rowIndex(), // auto generate number sesuai urutan tampilan
                TextColumn::make('kontrak.nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
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
            'index' => Pages\ListAlamatKontraks::route('/'),
            'create' => Pages\CreateAlamatKontrak::route('/create'),
            'edit' => Pages\EditAlamatKontrak::route('/{record}/edit'),
        ];
    }
}
