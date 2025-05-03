<?php

namespace App\Filament\Resources;
namespace BezhanSalleh\FilamentShield\Resources;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use App\Models\KapasitasLumbungKering;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KapasitasLumbungKeringResource\Pages;
use App\Filament\Resources\KapasitasLumbungKeringResource\RelationManagers;

class KapasitasLumbungKeringResource extends Resource implements HasShieldPermissions
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
    // public static function canAccess(): bool
    // {
    //     return false; // Menyembunyikan resource dari sidebar
    // }
    protected static ?string $model = KapasitasLumbungKering::class;
    protected static ?string $navigationGroup = 'Kapasitas';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationLabel = 'Kapasitas Lumbung Kering';

    public static ?string $label = 'Daftar Kapasitas Lumbung Kering ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('nama_kapasitas_lumbung')
                            ->label('Nama Lumbung')
                            ->placeholder('Masukkan Nama Kapasitas Lumbung'),
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->placeholder('Masukkan Jumlah Kapasitas Total')
                            ->live() // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                    ])->columns(2)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kapasitas_lumbung')
                    ->label('Nama Lumbung'),
                TextColumn::make('kapasitas_total')
                    ->label('Kapasitas Total')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Tambah pemisah ribuan,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListKapasitasLumbungKerings::route('/'),
            'create' => Pages\CreateKapasitasLumbungKering::route('/create'),
            'edit' => Pages\EditKapasitasLumbungKering::route('/{record}/edit'),
        ];
    }
}
