<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KendaraanMasuksResource\Pages;
use App\Filament\Resources\KendaraanMasuksResource\RelationManagers;
use App\Models\KendaraanMasuks;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KendaraanMasuksResource extends Resource
{
    protected static ?string $model = KendaraanMasuks::class;

    protected static ?string $navigationIcon = 'heroicon-o-forward';

    protected static ?string $navigationLabel = 'Kendaraan Masuk';

    public static ?string $label = 'Daftar Kendaraan Masuk ';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Card::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'Tamu' => 'Tamu',
                                    'Supplier' => 'Supplier',
                                ])
                                ->placeholder('Pilih Status')
                                ->native(false) 
                                ->required(),
                            
                            TextInput::make('nama_sup_per')
                                ->placeholder('Masukkan nama supplier atau perusahaan'),
        
                            TextInput::make('plat_polisi')
                                ->placeholder('Masukkan plat polisi'),
        
                            TextInput::make('nama_barang')
                                ->placeholder('Masukkan nama barang'),
        
                                
                                TextInput::make('jam_masuk')
                                ->readOnly()
                                ->suffixIcon('heroicon-o-clock')
                                ->default(now()->format('H:i')),
                                
                                TextInput::make('jam_keluar')
                                ->label('Jam Keluar')
                                ->readOnly()
                                ->placeholder('Kosongkan jika belum keluar')
                                ->suffixIcon('heroicon-o-clock')
                                ->required(false)
                                ->afterStateHydrated(function ($state, callable $set, $record) {
                                    if ($record && empty($state)) {
                                        $set('jam_keluar', now()->format('H:i:s'));
                                    }
                                }),
                                
                                TextInput::make('keterangan')
                                    ->placeholder('Masukkan Keterangan'),
                        ])
                        ->columns([
                            'sm' => 1,       // Mobile: 1 kolom
                        ]),
                ]),
            ]);        
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('nama_sup_per')
                    ->label('Nama Sup/Per')
                    ->searchable(),
                TextColumn::make('plat_polisi')
                    ->label('Plat Mobil')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('jam_masuk')
                    ->searchable(),
                TextColumn::make('jam_keluar')
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => Pages\ListKendaraanMasuks::route('/'),
            'create' => Pages\CreateKendaraanMasuks::route('/create'),
            'edit' => Pages\EditKendaraanMasuks::route('/{record}/edit'),
        ];
    }
}
