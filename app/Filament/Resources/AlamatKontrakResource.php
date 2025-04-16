<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\AlamatKontrak;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AlamatKontrakResource\Pages;
use App\Filament\Resources\AlamatKontrakResource\RelationManagers;
use App\Models\Kontrak;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class AlamatKontrakResource extends Resource
{
    protected static ?string $model = AlamatKontrak::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-asia-australia';
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
                TextColumn::make('id')
                    ->label('No')
                    ->alignCenter(),
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
