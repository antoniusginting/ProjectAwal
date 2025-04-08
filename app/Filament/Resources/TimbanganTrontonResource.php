<?php

namespace App\Filament\Resources;

use Dom\Text;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use App\Models\TimbanganTronton;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
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
    protected static ?int $navigationSort = 3;
    public static ?string $label = 'Daftar Tronton ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([

                                Card::make('Timbangan Jual')
                                    ->schema([
                                        //Timbangan Jual 1
                                        Card::make('Timbangan jual 1')
                                            ->schema([
                                                Select::make('id_timbangan_jual_1')
                                                    ->label('No SPB (Timbangan 1)')
                                                    ->placeholder('Pilih No SPB Penjualan')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_timbangan_jual_1'); // nilai yang dipilih (jika ada)

                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_timbangan_jual_1,
                                                                    $record->id_timbangan_jual_2,
                                                                    $record->id_timbangan_jual_3,
                                                                    $record->id_timbangan_jual_4,
                                                                    $record->id_timbangan_jual_5,
                                                                    $record->id_timbangan_jual_6,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $penjualanQuery = Penjualan::query();
                                                        if ($currentId) {
                                                            $penjualanQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $penjualanQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $penjualanQuery
                                                            ->latest()
                                                            ->with(['mobil', 'supplier'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->no_spb .
                                                                        ' - Timbangan ke-' . $item->keterangan .
                                                                        ' - ' . $item->nama_supir .
                                                                        ' - ' . ($item->plat_polisi ?? $item->no_container)
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->reactive()
                                                    // Callback lain tetap sama
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('plat_polisi1', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                            $set('bruto1', $penjualan?->bruto);
                                                            $set('tara1', $penjualan?->tara);
                                                            $set('netto1', $penjualan?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi1', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto1', $penjualan?->bruto);
                                                        $set('tara1', $penjualan?->tara);
                                                        $set('netto1', $penjualan?->netto);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_netto
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                        $set('tara_awal', self::getTaraAwal($get));
                                                    }),
                                                TextInput::make('plat_polisi1')
                                                    ->label('Plat Polisi')
                                                    ->reactive()
                                                    ->disabled(),

                                                TextInput::make('bruto1')
                                                    ->placeholder('Otomatis terisi')
                                                    ->label('Bruto')
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->reactive()
                                                    ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                                TextInput::make('tara1')
                                                    ->label('Tara')
                                                    ->reactive()
                                                    ->afterStateHydrated(fn($state, $set) => $set('tara1', number_format($state, 0, ',', '.')))
                                                    ->disabled(),

                                                TextInput::make('netto1')
                                                    ->label('Netto')
                                                    ->reactive()
                                                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
                                                    ->disabled(),
                                            ])->columnSpan(1)->collapsible(),
                                        //Timbangan Jual 2
                                        Card::make('Timbangan jual 2')
                                            ->schema([
                                                Select::make('id_timbangan_jual_2')
                                                    ->label('No SPB (Timbangan 2)')
                                                    ->placeholder('Pilih No SPB Penjualan')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_timbangan_jual_2'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_timbangan_jual_1,
                                                                    $record->id_timbangan_jual_2,
                                                                    $record->id_timbangan_jual_3,
                                                                    $record->id_timbangan_jual_4,
                                                                    $record->id_timbangan_jual_5,
                                                                    $record->id_timbangan_jual_6,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $penjualanQuery = Penjualan::query();
                                                        if ($currentId) {
                                                            $penjualanQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $penjualanQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $penjualanQuery
                                                            ->latest()
                                                            ->with(['mobil', 'supplier'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->no_spb .
                                                                        ' - Timbangan ke-' . $item->keterangan .
                                                                        ' - ' . $item->nama_supir .
                                                                        ' - ' . ($item->plat_polisi ?? $item->no_container)
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('plat_polisi2', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                            $set('bruto2', $penjualan?->bruto);
                                                            $set('tara2', $penjualan?->tara);
                                                            $set('netto2', $penjualan?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi2', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto2', $penjualan?->bruto);
                                                        $set('tara2', $penjualan?->tara);
                                                        $set('netto2', $penjualan?->netto);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_total_netto
                                                        // Update bruto_final berdasarkan helper
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
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
                                                    ->label('No SPB (Timbangan 3)')
                                                    ->placeholder('Pilih No SPB Penjualan')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_timbangan_jual_3'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_timbangan_jual_1,
                                                                    $record->id_timbangan_jual_2,
                                                                    $record->id_timbangan_jual_3,
                                                                    $record->id_timbangan_jual_4,
                                                                    $record->id_timbangan_jual_5,
                                                                    $record->id_timbangan_jual_6,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $penjualanQuery = Penjualan::query();
                                                        if ($currentId) {
                                                            $penjualanQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $penjualanQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $penjualanQuery
                                                            ->latest()
                                                            ->with(['mobil', 'supplier'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->no_spb .
                                                                        ' - Timbangan ke-' . $item->keterangan .
                                                                        ' - ' . $item->nama_supir .
                                                                        ' - ' . ($item->plat_polisi ?? $item->no_container)
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('plat_polisi3', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                            $set('bruto3', $penjualan?->bruto);
                                                            $set('tara3', $penjualan?->tara);
                                                            $set('netto3', $penjualan?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi3', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto3', $penjualan?->bruto);
                                                        $set('tara3', $penjualan?->tara);
                                                        $set('netto3', $penjualan?->netto);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_total_netto
                                                        // Update bruto_final berdasarkan helper
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
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
                                                    ->label('No SPB (Timbangan 4)')
                                                    ->placeholder('Pilih No SPB Penjualan')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_timbangan_jual_4'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_timbangan_jual_1,
                                                                    $record->id_timbangan_jual_2,
                                                                    $record->id_timbangan_jual_3,
                                                                    $record->id_timbangan_jual_4,
                                                                    $record->id_timbangan_jual_5,
                                                                    $record->id_timbangan_jual_6,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $penjualanQuery = Penjualan::query();
                                                        if ($currentId) {
                                                            $penjualanQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $penjualanQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $penjualanQuery
                                                            ->latest()
                                                            ->with(['mobil', 'supplier'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->no_spb .
                                                                        ' - Timbangan ke-' . $item->keterangan .
                                                                        ' - ' . $item->nama_supir .
                                                                        ' - ' . ($item->plat_polisi ?? $item->no_container)
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()

                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('plat_polisi4', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                            $set('bruto4', $penjualan?->bruto);
                                                            $set('tara4', $penjualan?->tara);
                                                            $set('netto4', $penjualan?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi4', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto4', $penjualan?->bruto);
                                                        $set('tara4', $penjualan?->tara);
                                                        $set('netto4', $penjualan?->netto);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_total_netto
                                                        // Update bruto_final berdasarkan helper
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
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
                                            ])->columnSpan(1)->collapsed(),
                                        //Timbangan Jual 5
                                        Card::make('Timbangan jual 5')
                                            ->schema([
                                                Select::make('id_timbangan_jual_5')
                                                    ->label('No SPB (Timbangan 5)')
                                                    ->placeholder('Pilih No SPB Penjualan')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_timbangan_jual_5'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_timbangan_jual_1,
                                                                    $record->id_timbangan_jual_2,
                                                                    $record->id_timbangan_jual_3,
                                                                    $record->id_timbangan_jual_4,
                                                                    $record->id_timbangan_jual_5,
                                                                    $record->id_timbangan_jual_6,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $penjualanQuery = Penjualan::query();
                                                        if ($currentId) {
                                                            $penjualanQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $penjualanQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $penjualanQuery
                                                            ->latest()
                                                            ->with(['mobil', 'supplier'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->no_spb .
                                                                        ' - Timbangan ke-' . $item->keterangan .
                                                                        ' - ' . $item->nama_supir .
                                                                        ' - ' . ($item->plat_polisi ?? $item->no_container)
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()

                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('plat_polisi5', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                            $set('bruto5', $penjualan?->bruto);
                                                            $set('tara5', $penjualan?->tara);
                                                            $set('netto5', $penjualan?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi5', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto5', $penjualan?->bruto);
                                                        $set('tara5', $penjualan?->tara);
                                                        $set('netto5', $penjualan?->netto);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_total_netto
                                                        // Update bruto_final berdasarkan helper
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
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
                                            ])->columnSpan(1)->collapsed(),
                                        //Timbangan Jual 6
                                        Card::make('Timbangan jual 6')
                                            ->schema([
                                                Select::make('id_timbangan_jual_6')
                                                    ->label('No SPB (Timbangan 6)')
                                                    ->placeholder('Pilih No SPB Penjualan')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_timbangan_jual_6'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_timbangan_jual_1,
                                                                    $record->id_timbangan_jual_2,
                                                                    $record->id_timbangan_jual_3,
                                                                    $record->id_timbangan_jual_4,
                                                                    $record->id_timbangan_jual_5,
                                                                    $record->id_timbangan_jual_6,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $penjualanQuery = Penjualan::query();
                                                        if ($currentId) {
                                                            $penjualanQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $penjualanQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $penjualanQuery
                                                            ->latest()
                                                            ->with(['mobil', 'supplier'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->no_spb .
                                                                        ' - Timbangan ke-' . $item->keterangan .
                                                                        ' - ' . $item->nama_supir .
                                                                        ' - ' . ($item->plat_polisi ?? $item->no_container)
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('plat_polisi6', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                            $set('bruto6', $penjualan?->bruto);
                                                            $set('tara6', $penjualan?->tara);
                                                            $set('netto6', $penjualan?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi6', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto6', $penjualan?->bruto);
                                                        $set('tara6', $penjualan?->tara);
                                                        $set('netto6', $penjualan?->netto);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_total_netto
                                                        // Update bruto_final berdasarkan helper
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
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
                                            ])->columnSpan(1)->collapsed(),
                                    ])->columns(3)->collapsible(),
                                Card::make('Informasi Berat')
                                    ->schema([
                                        // Card::make()
                                        //     ->schema([
                                        //         TextInput::make('bruto_akhir')
                                        //             ->placeholder('Otomatis terisi')
                                        //             ->label('Bruto Akhir')
                                        //             ->visible(false),
                                        //         TextInput::make('total_netto')
                                        //             ->placeholder('Otomatis terisi')
                                        //             ->label('Total Netto')
                                        //             ->readOnly()
                                        //             ->visible(false),

                                        //     ])->columns(3),
                                        Card::make()
                                            ->schema([
                                                TextInput::make('tambah_berat')
                                                    ->label('Tambah Berat')
                                                    ->numeric()
                                                    ->placeholder('Masukkan tambah berat')
                                                    ->live(debounce: 600)
                                                    ->reactive() // Menjadikan field ini responsif
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $set('bruto_final', ($get('bruto_akhir') ?? 0) + ($state ?? 0));
                                                        $set('netto_final', ($get('total_netto') ?? 0) + ($state ?? 0));
                                                    }),
                                                TextInput::make('bruto_final')
                                                    ->label('Bruto Final')
                                                    ->readOnly()
                                                    ->placeholder('Otomatis terjumlahkan'),
                                                TextInput::make('tara_awal')
                                                    ->label('Tara Final')
                                                    ->readOnly()
                                                    ->placeholder('Otomatis terjumlahkan'),
                                                TextInput::make('netto_final')
                                                    ->label('Netto Final')
                                                    ->readOnly()
                                                    ->placeholder('Otomatis terjumlahkan'),
                                            ])->columns(4)
                                    ])->collapsible(),
                                Textarea::make('keterangan')
                                    ->placeholder('Masukkan Keterangan')
                                    ->columnSpanFull(), // Tetap 1 kolom penuh di semua ukuran layar
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])
                    ])
            ]);
    }
    protected static function getTaraAwal($get)
    {
        return $get('tara1');
    }

    protected static function getBrutoAkhir($get)
    {
        return $get('bruto6')
            ?? $get('bruto5')
            ?? $get('bruto4')
            ?? $get('bruto3')
            ?? $get('bruto2')
            ?? $get('bruto1');
    }
    public static function hitungTotalNetto($get)
    {
        return (int) ($get('netto1') ?? 0) +
            (int) ($get('netto2') ?? 0) +
            (int) ($get('netto3') ?? 0) +
            (int) ($get('netto4') ?? 0) +
            (int) ($get('netto5') ?? 0) +
            (int) ($get('netto6') ?? 0);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('penjualan1.nama_supir')
                    ->label('Nama Supir'),

                TextColumn::make('bruto_final')
                    ->label('Bruto Final')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tara_awal')
                    ->label('Tara Final')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('netto_final')
                    ->label('Netto Final')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 1
                TextColumn::make('penjualan1.no_spb')
                    ->label('No SPB 1')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan1.nama_lumbung')
                    ->label('Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan1.no_lumbung')
                    ->label('No Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan1.netto')
                    ->label('Netto 1')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 2
                TextColumn::make('penjualan2.no_spb')
                    ->label('No SPB 2')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan2.nama_lumbung')
                    ->label('Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan2.no_lumbung')
                    ->label('No Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan2.netto')
                    ->label('Netto 2')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 3
                TextColumn::make('penjualan3.no_spb')
                    ->label('No SPB 3')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan3.nama_lumbung')
                    ->label('Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan3.no_lumbung')
                    ->label('No Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan3.netto')
                    ->label('Netto 3')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 4
                TextColumn::make('penjualan4.no_spb')
                    ->label('No SPB 4')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan4.nama_lumbung')
                    ->label('Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan4.no_lumbung')
                    ->label('No Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan4.netto')
                    ->label('Netto 4')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 5
                TextColumn::make('penjualan5.no_spb')
                    ->label('No SPB 5')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan5.nama_lumbung')
                    ->label('Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan5.no_lumbung')
                    ->label('No Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan5.netto')
                    ->label('Netto 5')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                //Penjualan 6
                TextColumn::make('penjualan6.no_spb')
                    ->label('No SPB 6')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('penjualan6.nama_lumbung')
                    ->label('Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan6.no_lumbung')
                    ->label('No Lumbung')
                    ->alignCenter(),
                TextColumn::make('penjualan6.netto')
                    ->label('Netto 6')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama_barang'),
                TextColumn::make('keterangan'),
                TextColumn::make('tambah_berat')
                    ->label('Berat Tambah')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('user.name')
                    ->label('User')
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
