<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KapasitasDryer;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KapasitasDryerResource\Pages;
use App\Filament\Resources\KapasitasDryerResource\RelationManagers;

class KapasitasDryerResource extends Resource
{
    protected static ?string $model = KapasitasDryer::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?string $navigationGroup = 'Kapasitas';
    protected static ?string $navigationLabel = 'Kapasitas Dryer';

    public static ?string $label = 'Daftar Kapasitas Dryer ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_kapasitas_dryer')
                    ->label('Nama Dryer')
                    ->placeholder('Masukkan Nama Kapasitas Dryer'),
                TextInput::make('kapasitas_total')
                    ->label('Kapasitas Total')
                    ->placeholder('Masukkan Jumlah Kapasitas Total')
                    ->live() // Memastikan perubahan langsung terjadi di Livewire
                    ->extraAttributes([
                        'x-data' => '{}',
                        'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                    ])
                    ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kapasitas_dryer')
                    ->label('No Lumbung'),
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
            'index' => Pages\ListKapasitasDryers::route('/'),
            'create' => Pages\CreateKapasitasDryer::route('/create'),
            'edit' => Pages\EditKapasitasDryer::route('/{record}/edit'),
        ];
    }
}
