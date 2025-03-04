<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SortiranResource\Pages;
use App\Filament\Resources\SortiranResource\RelationManagers;
use App\Models\Pembelian;
use App\Models\Sortiran;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SortiranResource extends Resource
{
    protected static ?string $model = Sortiran::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pembelian') //Menambahkan Header
                    ->schema([
                        Card::make()
                            ->schema([
                                // TextInput::make('created_at')
                                //     ->label('Tanggal Sekarang')
                                //     ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                                //     ->disabled(), // Tidak bisa diedit
                                Select::make('pembelian_id')
                                    ->label('No SPB')
                                    ->placeholder('Pilih No SPB Pembelian')
                                    ->options(Pembelian::pluck('no_spb', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $pembelian = Pembelian::find($state);
                                        $set('netto_pembelian', $pembelian?->netto ?? 0);
                                        $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');

                                        // Mengambil data mobil berdasarkan mobil_id di tabel pembelian
                                        $set('plat_polisi', $pembelian?->mobil?->plat_polisi ?? 'Plat tidak ditemukan');

                                        $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                        $set('brondolan', $pembelian?->brondolan ?? 'Barang tidak ditemukan');
                                    }),

                                TextInput::make('plat_polisi')
                                    ->label('Plat Polisi')
                                    ->disabled(),

                                TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->disabled(),

                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->disabled(),

                                TextInput::make('netto_pembelian')
                                    ->label('Netto Pembelian')
                                    ->numeric()
                                    ->disabled(),

                                TextInput::make('brondolan')
                                    ->label('Brondolan')
                                    ->disabled(),

                            ])->columns(2),
                    ])
                    ->collapsible(),
                Card::make()
                    ->schema([
                        TextInput::make('lumbung')
                            ->label('Lumbung')
                            ->columnSpan(2),
                        // Grid untuk menyusun field ke kanan
                Grid::make(3) // 3 Kolom
                ->schema([
                    // Kualitas Jagung 1
                    Card::make()
                        ->schema([
                            TextInput::make('kualitas_jagung_1')
                                ->label('Kualitas Jagung 1'),
                            TextInput::make('foto_jagung_1')
                                ->label('Foto Jagung 1'),
                            TextInput::make('x1_x10_1')
                                ->label('X1-X10 1'),
                            TextInput::make('jumlah_karung_1')
                                ->label('Jumlah Karung 1'),
                            TextInput::make('kadar_air_1')
                                ->label('Kadar Air 1'),
                        ])
                        ->columnSpan(1), // Satu card per kolom

                    // Kualitas Jagung 2
                    Card::make()
                        ->schema([
                            TextInput::make('kualitas_jagung_2')
                                ->label('Kualitas Jagung 2'),
                            TextInput::make('foto_jagung_2')
                                ->label('Foto Jagung 2'),
                            TextInput::make('x1_x10_2')
                                ->label('X1-X10 2'),
                            TextInput::make('jumlah_karung_2')
                                ->label('Jumlah Karung 2'),
                            TextInput::make('kadar_air_2')
                                ->label('Kadar Air 2'),
                        ])
                        ->columnSpan(1), 

                    // Kualitas Jagung 3
                    Card::make()
                        ->schema([
                            TextInput::make('kualitas_jagung_3')
                                ->label('Kualitas Jagung 3'),
                            TextInput::make('foto_jagung_3')
                                ->label('Foto Jagung 3'),
                            TextInput::make('x1_x10_3')
                                ->label('X1-X10 3'),
                            TextInput::make('jumlah_karung_3')
                                ->label('Jumlah Karung 3'),
                            TextInput::make('kadar_air_3')
                                ->label('Kadar Air 3'),
                        ])
                        ->columnSpan(1), 
                ])
        ])
        ->columns(1), // Agar Grid tetap dalam satu Card
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('pembelian.no_spb')->label('No SPB')
                    ->searchable(),
                TextColumn::make('lumbung'),
                TextColumn::make('kualitas_jagung_1')
                    ->label('Kualitas Jagung 1'),
                TextColumn::make('foto_jagung_1')
                    ->label('Foto Jagung 1'),
                TextColumn::make('x1_x10_1')
                    ->label('X1 - X10'),
                TextColumn::make('jumlah_karung_2')
                    ->label('Jumlah Karung 2'),
                TextColumn::make('kadar_air_2')
                    ->label('Kadar Air 2'),
                TextColumn::make('kualitas_jagung_2')
                    ->label('Kualitas Jagung 2'),
                TextColumn::make('foto_jagung_2')
                    ->label('Foto Jagung 2'),
                TextColumn::make('x1_x10_2')
                    ->label('X1 - X10'),
                TextColumn::make('jumlah_karung_2')
                    ->label('Jumlah Karung 2'),
                TextColumn::make('kadar_air_2')
                    ->label('Kadar Air 2'),
                TextColumn::make('kualitas_jagung_3')
                    ->label('Kualitas Jagung 3'),
                TextColumn::make('foto_jagung_3')
                    ->label('Foto Jagung 3'),
                TextColumn::make('x1_x10_3')
                    ->label('X1 - X10'),
                TextColumn::make('jumlah_karung_3')
                    ->label('Jumlah Karung 3'),
                TextColumn::make('kadar_air_3')
                    ->label('Kadar Air 3'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSortirans::route('/'),
            'create' => Pages\CreateSortiran::route('/create'),
            'edit' => Pages\EditSortiran::route('/{record}/edit'),
        ];
    }
}
