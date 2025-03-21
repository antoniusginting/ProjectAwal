<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use App\Models\TimbanganTronton;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use function Laravel\Prompts\textarea;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TimbanganTrontonResource\Pages;
use App\Filament\Resources\TimbanganTrontonResource\RelationManagers;

class TimbanganTrontonResource extends Resource
{
    protected static ?string $model = TimbanganTronton::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Tronton';
    protected static ?string $navigationGroup = 'Timbangan';
    public static ?string $label = 'Daftar Tronton ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('total_bruto')
                            ->label('Total Bruto')
                            ->readOnly()
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                            ->placeholder('Otomatis terjumlahkan berdasarkan bruto timbangan'),
                        TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->placeholder('Masukkan nama barang'),
                        Grid::make(3)
                            ->schema([
                                //Timbangan Jual 1
                                Card::make('Timbangan jual 1')
                                    ->schema([
                                        Select::make('id_timbangan_jual_1')
                                            ->label('No SPB')
                                            ->placeholder('Pilih No SPB Pembelian')
                                            ->options(Penjualan::latest()->with('supplier')->get()->mapWithKeys(function ($item) {
                                                return [$item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' . $item->supplier->nama_supplier . ' - ' . $item->plat_polisi];
                                            }))
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $penjualan = Penjualan::find($state);
                                                    $set('plat_polisi1', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                    $set('bruto1', $penjualan?->bruto ?? 0);
                                                    $set('tara1', $penjualan?->tara ?? 0);
                                                    $set('netto1', $penjualan?->netto ?? 0);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $penjualan = Penjualan::find($state);
                                                $set('plat_polisi1', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                $set('bruto1', $penjualan?->bruto ?? 0);
                                                $set('tara1', $penjualan?->tara ?? 0);
                                                $set('netto1', $penjualan?->netto ?? 0);
                                                $set('total_bruto', self::hitungTotalBruto($get)); // Update total_bruto
                                            }),

                                        TextInput::make('plat_polisi1')
                                            ->label('Plat Polisi')
                                            ->reactive()
                                            ->disabled(),

                                        TextInput::make('bruto1')
                                            ->placeholder('Otomatis terisi')
                                            ->label('Bruto 1')
                                            ->readOnly()
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                        TextInput::make('tara1')
                                            ->label('Tara 1')
                                            ->reactive()
                                            ->afterStateHydrated(fn($state, $set) => $set('tara1', number_format($state, 0, ',', '.')))
                                            ->disabled(),

                                        TextInput::make('netto1')
                                            ->label('Netto 1')
                                            ->reactive()
                                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                            ->disabled(),
                                    ])->columnSpan(1)->collapsible(),
                                //Timbangan Jual 2
                                Card::make('Timbangan jual 2')
                                    ->schema([
                                        Select::make('id_timbangan_jual_2')
                                            ->label('No SPB')
                                            ->placeholder('Pilih No SPB Pembelian')
                                            ->options(Penjualan::latest()->with('supplier')->get()->mapWithKeys(function ($item) {
                                                return [$item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' . $item->supplier->nama_supplier . ' - ' . $item->plat_polisi];
                                            }))
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $penjualan = Penjualan::find($state);
                                                    $set('plat_polisi2', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                    $set('bruto2', $penjualan?->bruto ?? 0);
                                                    $set('tara2', $penjualan?->tara ?? 0);
                                                    $set('netto2', $penjualan?->netto ?? 0);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $penjualan = Penjualan::find($state);
                                                $set('plat_polisi2', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                $set('bruto2', $penjualan?->bruto ?? 0);
                                                $set('tara2', $penjualan?->tara ?? 0);
                                                $set('netto2', $penjualan?->netto ?? 0);
                                                $set('total_bruto', self::hitungTotalBruto($get)); // Update total_bruto
                                            }),

                                        TextInput::make('plat_polisi2')
                                            ->label('Plat Polisi')
                                            ->reactive()
                                            ->disabled(),

                                        TextInput::make('bruto2')
                                            ->placeholder('Otomatis terisi')
                                            ->label('Bruto')
                                            ->numeric()
                                            ->readOnly()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                        TextInput::make('tara2')
                                            ->label('Tara')
                                            ->reactive()
                                            ->afterStateHydrated(fn($state, $set) => $set('tara2', number_format($state, 0, ',', '.')))
                                            ->disabled(),

                                        TextInput::make('netto2')
                                            ->label('Netto')
                                            ->reactive()
                                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                            ->disabled(),
                                    ])->columnSpan(1)->collapsible(),
                                //Timbangan Jual 3
                                Card::make('Timbangan jual 3')
                                    ->schema([
                                        Select::make('id_timbangan_jual_3')
                                            ->label('No SPB')
                                            ->placeholder('Pilih No SPB Pembelian')
                                            ->options(Penjualan::latest()->with('supplier')->get()->mapWithKeys(function ($item) {
                                                return [$item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' . $item->supplier->nama_supplier . ' - ' . $item->plat_polisi];
                                            }))
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $penjualan = Penjualan::find($state);
                                                    $set('plat_polisi3', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                    $set('bruto3', $penjualan?->bruto ?? 0);
                                                    $set('tara3', $penjualan?->tara ?? 0);
                                                    $set('netto3', $penjualan?->netto ?? 0);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $penjualan = Penjualan::find($state);
                                                $set('plat_polisi3', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                $set('bruto3', $penjualan?->bruto ?? 0);
                                                $set('tara3', $penjualan?->tara ?? 0);
                                                $set('netto3', $penjualan?->netto ?? 0);
                                                $set('total_bruto', self::hitungTotalBruto($get)); // Update total_bruto
                                            }),

                                        TextInput::make('plat_polisi3')
                                            ->label('Plat Polisi')
                                            ->reactive()
                                            ->disabled(),

                                        TextInput::make('bruto3')
                                            ->placeholder('Otomatis terisi')
                                            ->label('Bruto')
                                            ->numeric()
                                            ->readOnly()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                        TextInput::make('tara3')
                                            ->label('Tara')
                                            ->reactive()
                                            ->afterStateHydrated(fn($state, $set) => $set('tara3', number_format($state, 0, ',', '.')))
                                            ->disabled(),

                                        TextInput::make('netto3')
                                            ->label('Netto')
                                            ->reactive()
                                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                            ->disabled(),
                                    ])->columnSpan(1)->collapsible(),
                                //Timbangan Jual 4
                                Card::make('Timbangan jual 4')
                                    ->schema([
                                        Select::make('id_timbangan_jual_4')
                                            ->label('No SPB')
                                            ->placeholder('Pilih No SPB Pembelian')
                                            ->options(Penjualan::latest()->with('supplier')->get()->mapWithKeys(function ($item) {
                                                return [$item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' . $item->supplier->nama_supplier . ' - ' . $item->plat_polisi];
                                            }))
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $penjualan = Penjualan::find($state);
                                                    $set('plat_polisi4', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                    $set('bruto4', $penjualan?->bruto ?? 0);
                                                    $set('tara4', $penjualan?->tara ?? 0);
                                                    $set('netto4', $penjualan?->netto ?? 0);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $penjualan = Penjualan::find($state);
                                                $set('plat_polisi4', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                $set('bruto4', $penjualan?->bruto ?? 0);
                                                $set('tara4', $penjualan?->tara ?? 0);
                                                $set('netto4', $penjualan?->netto ?? 0);
                                                $set('total_bruto', self::hitungTotalBruto($get)); // Update total_bruto
                                            }),

                                        TextInput::make('plat_polisi4')
                                            ->label('Plat Polisi')
                                            ->reactive()
                                            ->disabled(),

                                        TextInput::make('bruto4')
                                            ->placeholder('Otomatis terisi')
                                            ->label('Bruto')
                                            ->numeric()
                                            ->readOnly()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                        TextInput::make('tara4')
                                            ->label('Tara')
                                            ->reactive()
                                            ->afterStateHydrated(fn($state, $set) => $set('tara4', number_format($state, 0, ',', '.')))
                                            ->disabled(),

                                        TextInput::make('netto4')
                                            ->label('Netto')
                                            ->reactive()
                                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                            ->disabled(),
                                    ])->columnSpan(1)->collapsible(),
                                //Timbangan Jual 5
                                Card::make('Timbangan jual 5')
                                    ->schema([
                                        Select::make('id_timbangan_jual_5')
                                            ->label('No SPB')
                                            ->placeholder('Pilih No SPB Pembelian')
                                            ->options(Penjualan::latest()->with('supplier')->get()->mapWithKeys(function ($item) {
                                                return [$item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' . $item->supplier->nama_supplier . ' - ' . $item->plat_polisi];
                                            }))
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $penjualan = Penjualan::find($state);
                                                    $set('plat_polisi5', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                    $set('bruto5', $penjualan?->bruto ?? 0);
                                                    $set('tara5', $penjualan?->tara ?? 0);
                                                    $set('netto5', $penjualan?->netto ?? 0);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $penjualan = Penjualan::find($state);
                                                $set('plat_polisi5', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                $set('bruto5', $penjualan?->bruto ?? 0);
                                                $set('tara5', $penjualan?->tara ?? 0);
                                                $set('netto5', $penjualan?->netto ?? 0);
                                                $set('total_bruto', self::hitungTotalBruto($get)); // Update total_bruto
                                            }),

                                        TextInput::make('plat_polisi5')
                                            ->label('Plat Polisi')
                                            ->reactive()
                                            ->disabled(),

                                        TextInput::make('bruto5')
                                            ->placeholder('Otomatis terisi')
                                            ->label('Bruto')
                                            ->numeric()
                                            ->readOnly()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                        TextInput::make('tara5')
                                            ->label('Tara')
                                            ->reactive()
                                            ->afterStateHydrated(fn($state, $set) => $set('tara5', number_format($state, 0, ',', '.')))
                                            ->disabled(),

                                        TextInput::make('netto5')
                                            ->label('Netto')
                                            ->reactive()
                                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                            ->disabled(),
                                    ])->columnSpan(1)->collapsible(),
                                //Timbangan Jual 6
                                Card::make('Timbangan jual 6')
                                    ->schema([
                                        Select::make('id_timbangan_jual_6')
                                            ->label('No SPB')
                                            ->placeholder('Pilih No SPB Pembelian')
                                            ->options(Penjualan::latest()->with('supplier')->get()->mapWithKeys(function ($item) {
                                                return [$item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' . $item->supplier->nama_supplier . ' - ' . $item->plat_polisi];
                                            }))
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $penjualan = Penjualan::find($state);
                                                    $set('plat_polisi6', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                    $set('bruto6', $penjualan?->bruto ?? 0);
                                                    $set('tara6', $penjualan?->tara ?? 0);
                                                    $set('netto6', $penjualan?->netto ?? 0);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $penjualan = Penjualan::find($state);
                                                $set('plat_polisi6', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                $set('bruto6', $penjualan?->bruto ?? 0);
                                                $set('tara6', $penjualan?->tara ?? 0);
                                                $set('netto6', $penjualan?->netto ?? 0);
                                                $set('total_bruto', self::hitungTotalBruto($get)); // Update total_bruto
                                            }),

                                        TextInput::make('plat_polisi6')
                                            ->label('Plat Polisi')
                                            ->reactive()
                                            ->disabled(),

                                        TextInput::make('bruto6')
                                            ->placeholder('Otomatis terisi')
                                            ->label('Bruto')
                                            ->numeric()
                                            ->reactive()
                                            ->readOnly()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                        TextInput::make('tara6')
                                            ->label('Tara')
                                            ->reactive()
                                            ->afterStateHydrated(fn($state, $set) => $set('tara6', number_format($state, 0, ',', '.')))
                                            ->disabled(),

                                        TextInput::make('netto6')
                                            ->label('Netto')
                                            ->reactive()
                                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                            ->disabled(),
                                    ])->columnSpan(1)->collapsible(),
                                Textarea::make('keterangan')
                                    ->placeholder('Masukkan Keterangan')
                                    ->columnSpanFull(), // Tetap 1 kolom penuh di semua ukuran layar
                            ])
                    ])->columns(2)
            ]);
    }

    public static function hitungTotalBruto($get)
    {
        return (int) ($get('bruto1') ?? 0) +
            (int) ($get('bruto2') ?? 0) +
            (int) ($get('bruto3') ?? 0) +
            (int) ($get('bruto4') ?? 0) +
            (int) ($get('bruto5') ?? 0) +
            (int) ($get('bruto6') ?? 0);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y')
                    ->color('success'),
                TextColumn::make('total_bruto')
                    ->label('Total Bruto')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 1
                TextColumn::make('penjualan1.no_spb')
                    ->label('No SPB 1')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan1.bruto')
                    ->label('Bruto 1')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan1.tara')
                    ->label('Tara 1')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan1.netto')
                    ->label('Netto 1')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 2
                TextColumn::make('penjualan2.no_spb')
                    ->label('No SPB 2')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan2.bruto')
                    ->label('Bruto 2')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan2.tara')
                    ->label('Tara 2')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan2.netto')
                    ->label('Netto 2')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 3
                TextColumn::make('penjualan3.no_spb')
                    ->label('No SPB 3')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan3.bruto')
                    ->label('Bruto 3')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan3.tara')
                    ->label('Tara 3')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan3.netto')
                    ->label('Netto 3')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 4
                TextColumn::make('penjualan4.no_spb')
                    ->label('No SPB 4')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan4.bruto')
                    ->label('Bruto 4')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan4.tara')
                    ->label('Tara 4')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan4.netto')
                    ->label('Netto 4')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 5
                TextColumn::make('penjualan5.no_spb')
                    ->label('No SPB 5')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan5.bruto')
                    ->label('Bruto 5')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan5.tara')
                    ->label('Tara 5')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan5.netto')
                    ->label('Netto 5')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 6
                TextColumn::make('penjualan6.no_spb')
                    ->label('No SPB 6')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan6.bruto')
                    ->label('Bruto 6')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan6.tara')
                    ->label('Tara 6')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('penjualan6.netto')
                    ->label('Netto 6')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama_barang'),
                TextColumn::make('keterangan'),
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTimbanganTrontons::route('/'),
            'create' => Pages\CreateTimbanganTronton::route('/create'),
            'edit' => Pages\EditTimbanganTronton::route('/{record}/edit'),
        ];
    }
}
