<?php

namespace App\Filament\Resources;

use Dom\Text;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use App\Models\TimbanganTronton;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;

use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\Toggle;
use function Laravel\Prompts\textarea;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Columns\CheckboxColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TimbanganTrontonResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\TimbanganTrontonResource\RelationManagers;
use App\Filament\Resources\TimbanganTrontonResource\Pages\EditTimbanganTronton;
use App\Models\Luar;
use App\Models\Pembelian;

class TimbanganTrontonResource extends Resource implements HasShieldPermissions
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
    protected static ?string $model = TimbanganTronton::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 3;
    public static ?string $label = 'Daftar Laporan Penjualan ';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Select::make('plat_polisi')
                    ->label('Ambil dari Timbangan Penjualan (No BK)')
                    ->searchable()
                    ->options(function () {
                        $usedIds = Collection::make(range(1, 6))
                            ->flatMap(function (int $i) {
                                return TimbanganTronton::query()
                                    ->pluck("id_timbangan_jual_{$i}")
                                    ->map(fn($val) => intval($val));
                            })
                            ->filter(fn($id) => !is_null($id) && $id !== '')
                            ->unique()
                            ->values()
                            ->all();

                        return Penjualan::query()
                            ->whereNotIn('id', $usedIds)
                            ->distinct()
                            ->whereNotNull('bruto')         // bruto tidak boleh null
                            ->where('bruto', '!=', '')      // bruto tidak boleh kosong string (jika perlu)
                            ->orderByDesc('created_at')
                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
                            ->get()
                            ->mapWithKeys(function ($penjualan) {
                                // Menggabungkan plat polisi dan nama supir sebagai label
                                $label = $penjualan->plat_polisi . ' - ' . $penjualan->nama_supir;
                                // Tetap menggunakan plat_polisi sebagai value
                                return [$penjualan->plat_polisi => $label];
                            })
                            ->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                        if (! $state) {
                            return;
                        }

                        // Get used IDs to prevent them from being populated
                        $usedIds = Collection::make(range(1, 6))
                            ->flatMap(function (int $i) {
                                return TimbanganTronton::query()
                                    ->pluck("id_timbangan_jual_{$i}")
                                    ->map(fn($val) => intval($val));
                            })
                            ->filter(fn($id) => !is_null($id) && $id !== '')
                            ->unique()
                            ->values()
                            ->all();

                        // Also get the current form's IDs to avoid overwriting them
                        $currentFormIds = Collection::make(range(1, 6))
                            ->map(function (int $i) use ($get) {
                                return $get("id_timbangan_jual_{$i}");
                            })
                            ->filter(fn($id) => !is_null($id) && $id !== '')
                            ->unique()
                            ->values()
                            ->all();

                        // Combine both sets of IDs to exclude
                        $allUsedIds = array_merge($usedIds, $currentFormIds);
                        $excludedBarang = ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'];
                        // Prefill data dari Penjualan berdasarkan plat_polisi yang dipilih, but exclude used IDs
                        $records = Penjualan::where('plat_polisi', $state)
                            ->whereNotIn('id', $allUsedIds)
                            ->whereNotIn('nama_barang', $excludedBarang)
                            ->whereNotNull('bruto')                      // bruto tidak boleh null
                            ->where('bruto', '!=', '')                   // bruto tidak boleh kosong string
                            ->get();

                        // Find the first empty slot to start filling from
                        $startIndex = 1;
                        while ($startIndex <= 6 && $get("id_timbangan_jual_{$startIndex}") !== null) {
                            $startIndex++;
                        }

                        // Fill only empty slots
                        foreach ($records as $idx => $pj) {
                            $i = $startIndex + $idx;
                            if ($i > 6) break;
                            $set("id_timbangan_jual_{$i}", $pj->id);
                            $set("nama_lumbung_{$i}", $pj->nama_lumbung);
                            $set("no_lumbung_{$i}", $pj->no_lumbung);
                            $set("bruto{$i}", $pj->bruto);
                            $set("tara{$i}", $pj->tara);
                            $set("netto{$i}", $pj->netto);
                        }

                        // Update ringkasan akhir
                        $set('total_netto', self::hitungTotalNetto($get));
                        $set('bruto_akhir', self::getBrutoAkhir($get));
                        $set('tara_awal', self::getTaraAwal($get));
                    }),

                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Card::make('Informasi Berat')
                                    ->schema([
                                        Card::make()
                                            ->schema([
                                                TextInput::make('bruto_akhir')
                                                    ->label('Bruto Akhir')
                                                    ->readOnly()
                                                    ->placeholder('Otomatis terjumlahkan'),
                                                TextInput::make('tara_awal')
                                                    ->label('Tara Awal')
                                                    ->readOnly()
                                                    ->placeholder('Otomatis terjumlahkan'),
                                                TextInput::make('total_netto')
                                                    ->placeholder('Otomatis terisi')
                                                    ->label('Total Netto')
                                                    ->readOnly()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        // Saat edit, simpan nilai total netto yang tersimpan sebagai initial_total_netto
                                                        $set('initial_total_netto', $state);
                                                    }),
                                            ])->columns(3)
                                    ])->collapsed(),
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
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                                                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
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
                                                    // Callback lain tetap sama
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $penjualan = Penjualan::find($state);
                                                            $set('no_lumbung_1', $penjualan?->no_lumbung);
                                                            $set('nama_lumbung_1', $penjualan?->nama_lumbung);
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
                                                        $set('no_lumbung_1', $penjualan?->no_lumbung);
                                                        $set('nama_lumbung_1', $penjualan?->nama_lumbung);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_netto
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                        $set('tara_awal', self::getTaraAwal($get));
                                                    }),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('no_lumbung_1')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('No Lumbung'),
                                                        TextInput::make('nama_lumbung_1')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('Nama Lumbung'),
                                                    ]),
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
                                                    // ->afterStateHydrated(fn($state, $set) => $set('tara1', number_format($state, 0, ',', '.')))
                                                    ->disabled(),

                                                TextInput::make('netto1')
                                                    ->label('Netto')
                                                    ->reactive()
                                                    // ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 0, ',', '.') : '')
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
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                                                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
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
                                                            $set('no_lumbung_2', $penjualan?->no_lumbung);
                                                            $set('nama_lumbung_2', $penjualan?->nama_lumbung);
                                                            $set('bruto2', $penjualan?->bruto);
                                                            $set('tara2', $penjualan?->tara);
                                                            $netto2 = $penjualan?->netto ?? 0;
                                                            $set('netto2', $netto2);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto2', $netto2);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi2', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto2', $penjualan?->bruto);
                                                        $set('tara2', $penjualan?->tara);
                                                        $set('no_lumbung_2', $penjualan?->no_lumbung);
                                                        $set('nama_lumbung_2', $penjualan?->nama_lumbung);
                                                        $newNetto = $penjualan?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto2') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto2', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto2 agar tampil (misalnya untuk format)
                                                        $set('netto2', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('no_lumbung_2')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('No Lumbung'),
                                                        TextInput::make('nama_lumbung_2')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('Nama Lumbung'),
                                                    ]),

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

                                                    ->disabled(),

                                                TextInput::make('netto2')
                                                    ->label('Netto')
                                                    ->reactive()

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
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                                                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
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
                                                            $set('no_lumbung_3', $penjualan?->no_lumbung);
                                                            $set('nama_lumbung_3', $penjualan?->nama_lumbung);
                                                            $set('bruto3', $penjualan?->bruto);
                                                            $set('tara3', $penjualan?->tara);
                                                            $netto3 = $penjualan?->netto ?? 0;
                                                            $set('netto3', $netto3);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto3', $netto3);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi3', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto3', $penjualan?->bruto);
                                                        $set('tara3', $penjualan?->tara);
                                                        $set('no_lumbung_3', $penjualan?->no_lumbung);
                                                        $set('nama_lumbung_3', $penjualan?->nama_lumbung);
                                                        $newNetto = $penjualan?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto3') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto3', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto3 agar tampil (misalnya untuk format)
                                                        $set('netto3', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('no_lumbung_3')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('No Lumbung'),
                                                        TextInput::make('nama_lumbung_3')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('Nama Lumbung'),
                                                    ]),

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

                                                    ->disabled(),

                                                TextInput::make('netto3')
                                                    ->label('Netto')
                                                    ->reactive()

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
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                                                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
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
                                                            $set('no_lumbung_4', $penjualan?->no_lumbung);
                                                            $set('nama_lumbung_4', $penjualan?->nama_lumbung);
                                                            $set('bruto4', $penjualan?->bruto);
                                                            $set('tara4', $penjualan?->tara);
                                                            $netto4 = $penjualan?->netto ?? 0;
                                                            $set('netto4', $netto4);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto4', $netto4);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi4', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto4', $penjualan?->bruto);
                                                        $set('tara4', $penjualan?->tara);
                                                        $set('no_lumbung_4', $penjualan?->no_lumbung);
                                                        $set('nama_lumbung_4', $penjualan?->nama_lumbung);
                                                        $newNetto = $penjualan?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto4') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto4', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto4 agar tampil (misalnya untuk format)
                                                        $set('netto4', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('no_lumbung_4')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('No Lumbung'),
                                                        TextInput::make('nama_lumbung_4')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('Nama Lumbung'),
                                                    ]),

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

                                                    ->disabled(),

                                                TextInput::make('netto4')
                                                    ->label('Netto')
                                                    ->reactive()

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
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                                                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
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
                                                            $set('no_lumbung_5', $penjualan?->no_lumbung);
                                                            $set('nama_lumbung_5', $penjualan?->nama_lumbung);
                                                            $set('bruto5', $penjualan?->bruto);
                                                            $set('tara5', $penjualan?->tara);
                                                            $netto5 = $penjualan?->netto ?? 0;
                                                            $set('netto5', $netto5);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto5', $netto5);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi5', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto5', $penjualan?->bruto);
                                                        $set('tara5', $penjualan?->tara);
                                                        $set('no_lumbung_5', $penjualan?->no_lumbung);
                                                        $set('nama_lumbung_5', $penjualan?->nama_lumbung);
                                                        $newNetto = $penjualan?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto5') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto5', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto5 agar tampil (misalnya untuk format)
                                                        $set('netto5', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('no_lumbung_5')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('No Lumbung'),
                                                        TextInput::make('nama_lumbung_5')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('Nama Lumbung'),
                                                    ]),

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

                                                    ->disabled(),

                                                TextInput::make('netto5')
                                                    ->label('Netto')
                                                    ->reactive()

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
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT', 'TAMPUNGAN SILO BESAR', 'LANGSIR', 'ISI MINYAK'])
                                                            ->whereNotNull('status_timbangan')
                                                            ->where('status_timbangan', '!=', 'langsir')
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
                                                            $set('no_lumbung_6', $penjualan?->no_lumbung);
                                                            $set('nama_lumbung_6', $penjualan?->nama_lumbung);
                                                            $set('bruto6', $penjualan?->bruto);
                                                            $set('tara6', $penjualan?->tara);
                                                            $netto6 = $penjualan?->netto ?? 0;
                                                            $set('netto6', $netto6);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto6', $netto6);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $penjualan = Penjualan::find($state);
                                                        $set('plat_polisi6', $penjualan?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('bruto6', $penjualan?->bruto);
                                                        $set('tara6', $penjualan?->tara);
                                                        $set('no_lumbung_6', $penjualan?->no_lumbung);
                                                        $set('nama_lumbung_6', $penjualan?->nama_lumbung);
                                                        $newNetto = $penjualan?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto6') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto6', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto6 agar tampil (misalnya untuk format)
                                                        $set('netto6', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('no_lumbung_6')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('No Lumbung'),
                                                        TextInput::make('nama_lumbung_6')
                                                            ->placeholder('Otomatis terisi')
                                                            ->reactive()
                                                            ->disabled()
                                                            ->label('Nama Lumbung'),
                                                    ]),

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

                                                    ->disabled(),

                                                TextInput::make('netto6')
                                                    ->label('Netto')
                                                    ->reactive()

                                                    ->disabled(),
                                            ])->columnSpan(1)->collapsed(),

                                    ])->columns(3)->collapsed()->visible(fn() => !optional(Auth::user())->hasAnyRole(['admin', 'adminaudit'])),

                                Card::make('Timbangan Antar Pulau')
                                    ->schema([
                                        //Timbangan luar 1
                                        Card::make('Timbangan luar 1')
                                            ->schema([
                                                Select::make('id_luar_1')
                                                    ->label('No SPB (Timbangan 1)')
                                                    ->placeholder('Pilih No SPB Luar')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_luar_1'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan beli (dari 1 sampai 3)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_luar_1,
                                                                    $record->id_luar_2,
                                                                    $record->id_luar_3,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $luarQuery = Luar::query();
                                                        if ($currentId) {
                                                            $luarQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $luarQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $luarQuery
                                                            ->latest()
                                                            ->with(['supplier'])
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->kode .
                                                                        ' - ' . $item->supplier->nama_supplier .
                                                                        ' - ' . $item->kode_segel .
                                                                        ' - ' . $item->no_container
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $luar = Luar::find($state);
                                                            $set('nama_barang_7', $luar?->nama_barang);
                                                            $set('netto7', $luar?->netto);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $luar = Luar::find($state);
                                                        $set('kode_segel_7', $luar?->plat_polisi ?? 'Plat tidak ditemukan');
                                                        $set('netto7', $luar?->netto);
                                                        $set('nama_barang_7', $luar?->nama_barang);
                                                        $set('total_netto', self::hitungTotalNetto($get)); // Update total_netto
                                                    }),



                                                TextInput::make('nama_barang_7')
                                                    ->placeholder('Otomatis terisi')
                                                    ->reactive()
                                                    ->disabled()
                                                    ->label('Nama Barang'),
                                                // TextInput::make('bruto7')
                                                //     ->placeholder('Otomatis terisi')
                                                //     ->label('Bruto')
                                                //     ->numeric()
                                                //     ->reactive()
                                                //     ->readOnly()
                                                //     ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                                // TextInput::make('tara7')
                                                //     ->label('Tara')
                                                //     ->reactive()

                                                //     ->disabled(),

                                                TextInput::make('netto7')
                                                    ->label('Netto')
                                                    ->reactive()

                                                    ->disabled(),
                                            ])->columnSpan(1)->collapsible(),
                                        //Timbangan Luar 2
                                        Card::make('Timbangan luar 2')
                                            ->schema([
                                                Select::make('id_luar_2')
                                                    ->label('No SPB (Timbangan 2)')
                                                    ->placeholder('Pilih No SPB Luar')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_luar_2'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan beli (dari 1 sampai 3)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_luar_1,
                                                                    $record->id_luar_2,
                                                                    $record->id_luar_3,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $luarQuery = Luar::query();
                                                        if ($currentId) {
                                                            $luarQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $luarQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $luarQuery
                                                            ->latest()
                                                            ->with(['supplier'])
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->kode .
                                                                        ' - ' . $item->supplier->nama_supplier .
                                                                        ' - ' . $item->kode_segel .
                                                                        ' - ' . $item->no_container
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $luar = Luar::find($state);
                                                            $set('nama_barang_8', $luar?->nama_barang);
                                                            $netto8 = $luar?->netto ?? 0;
                                                            $set('netto8', $netto8);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto8', $netto8);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $luar = Luar::find($state);
                                                        $set('kode_segel8', $luar?->kode_segel ?? 'kode segel tidak ditemukan');
                                                        $set('nama_barang_8', $luar?->nama_barang);
                                                        $newNetto = $luar?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto8') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto8', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto8 agar tampil (misalnya untuk format)
                                                        $set('netto8', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),



                                                TextInput::make('nama_barang_8')
                                                    ->placeholder('Otomatis terisi')
                                                    ->reactive()
                                                    ->disabled()
                                                    ->label('Nama Barang'),
                                                // TextInput::make('bruto8')
                                                //     ->placeholder('Otomatis terisi')
                                                //     ->label('Bruto')
                                                //     ->numeric()
                                                //     ->reactive()
                                                //     ->readOnly()
                                                //     ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                                // TextInput::make('tara8')
                                                //     ->label('Tara')
                                                //     ->reactive()

                                                //     ->disabled(),

                                                TextInput::make('netto8')
                                                    ->label('Netto')
                                                    ->reactive()

                                                    ->disabled(),
                                            ])->columnSpan(1)->collapsible(),
                                        //Timbangan Beli 3
                                        Card::make('Timbangan luar 3')
                                            ->schema([
                                                Select::make('id_luar_3')
                                                    ->label('No SPB (Timbangan 3)')
                                                    ->placeholder('Pilih No SPB Luar')
                                                    ->options(function (callable $get) {
                                                        $currentId = $get('id_luar_3'); // nilai yang dipilih (jika ada)

                                                        // Ambil semua field timbangan beli (dari 1 sampai 3)
                                                        $usedSpbIds = TimbanganTronton::query()
                                                            ->get()
                                                            ->flatMap(function ($record) {
                                                                return [
                                                                    $record->id_luar_1,
                                                                    $record->id_luar_2,
                                                                    $record->id_luar_3,
                                                                ];
                                                            })
                                                            ->filter()   // Hilangkan nilai null
                                                            ->unique()   // Pastikan tidak ada duplikasi
                                                            ->toArray();

                                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                                        $luarQuery = Luar::query();
                                                        if ($currentId) {
                                                            $luarQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                                $query->where('id', $currentId)
                                                                    ->orWhereNotIn('id', $usedSpbIds);
                                                            });
                                                        } else {
                                                            $luarQuery->whereNotIn('id', $usedSpbIds);
                                                        }

                                                        return $luarQuery
                                                            ->latest()
                                                            ->with(['supplier'])
                                                            ->whereNotIn('nama_barang', ['SEKAM', 'ABU JAGUNG', 'CANGKANG', 'SALAH', 'RETUR', 'ABU JAGUNG/KAUL', 'BOTOT'])
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [
                                                                    $item->id => $item->kode .
                                                                        ' - ' . $item->supplier->nama_supplier .
                                                                        ' - ' . $item->kode_segel .
                                                                        ' - ' . $item->no_container
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateHydrated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $luar = Luar::find($state);
                                                            $set('nama_barang_9', $luar?->nama_barang);
                                                            $netto9 = $luar?->netto ?? 0;
                                                            $set('netto9', $netto9);
                                                            // Simpan nilai awal untuk perhitungan selanjutnya
                                                            $set('prev_netto9', $netto9);
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $luar = Luar::find($state);
                                                        $set('kode_segel9', $luar?->kode_segel ?? 'kode segel tidak ditemukan');
                                                        $set('nama_barang_9', $luar?->nama_barang);
                                                        $newNetto = $luar?->netto ?? 0;
                                                        // Ambil nilai netto sebelumnya, jika belum ada asumsikan 0
                                                        $prevNetto = $get('prev_netto9') ?? 0;

                                                        // Hitung perbedaan antara nilai baru dan sebelumnya
                                                        $diff = $newNetto - $prevNetto;

                                                        // Update penyimpanan nilai netto sebelumnya
                                                        $set('prev_netto9', $newNetto);

                                                        // Ambil total netto saat ini. Jika belum ter-update, gunakan nilai awal (initial_total_netto)
                                                        $currentTotal = $get('total_netto') ?? ($get('initial_total_netto') ?? 0);

                                                        // Update total netto dengan menambahkan selisih
                                                        $set('total_netto', $currentTotal + $diff);

                                                        // Jika perlu, juga set field netto9 agar tampil (misalnya untuk format)
                                                        $set('netto9', $newNetto);
                                                        $set('bruto_akhir', self::getBrutoAkhir($get));
                                                    }),



                                                TextInput::make('nama_barang_9')
                                                    ->placeholder('Otomatis terisi')
                                                    ->reactive()
                                                    ->disabled()
                                                    ->label('Nama Barang'),
                                                // TextInput::make('bruto9')
                                                //     ->placeholder('Otomatis terisi')
                                                //     ->label('Bruto')
                                                //     ->numeric()
                                                //     ->reactive()
                                                //     ->readOnly()
                                                //     ->afterStateUpdated(fn($state, $set, $get) => $set('total_bruto', self::hitungTotalBruto($get))),

                                                // TextInput::make('tara9')
                                                //     ->label('Tara')
                                                //     ->reactive()

                                                //     ->disabled(),

                                                TextInput::make('netto9')
                                                    ->label('Netto')
                                                    ->reactive()

                                                    ->disabled(),
                                            ])->columnSpan(1)->collapsible(),
                                    ])->columns(3)->collapsed()->visible(fn() => !optional(Auth::user())->hasAnyRole(['timbangan'])),
                                // Toggle::make('status')
                                //     ->disabled(function ($state, $record) {
                                //         return $record && !in_array((int) $state, [0]);
                                //     })
                                //     ->helperText('Klik jika sudah diaudit')
                                //     ->onIcon('heroicon-m-bolt')
                                //     ->offIcon('heroicon-m-user')
                                //     ->dehydrated(true)
                                //     ->default(0)
                                //     ->hidden(fn() => !optional(Auth::user())->hasAnyRole(['adminaudit', 'super_admin']))
                                //     ->columns(1),
                                Grid::make()
                                    ->schema([
                                        Textarea::make('keterangan')
                                            ->placeholder('Masukkan Keterangan'),
                                        FileUpload::make('foto')
                                            ->image()
                                            ->multiple()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto'),
                                    ]),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])
                    ])
            ]);
    }
    protected static function getTaraAwal($get)
    {
        $tara1 = $get('tara1');
        return (is_null($tara1) || trim($tara1) === '') ? $get('tara7') : $tara1;
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
            (int) ($get('netto6') ?? 0) +
            (int) ($get('netto7') ?? 0) +
            (int) ($get('netto8') ?? 0) +
            (int) ($get('netto9') ?? 0);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (TimbanganTronton $record): ?string {
                $user = Auth::user();

                // 1) Super admin bisa edit semua kondisi
                if ($user && $user->hasRole('super_admin')) {
                    return EditTimbanganTronton::getUrl(['record' => $record]);
                }

                // 2) Admin1 hanya bisa edit jika status belum ada
                if ($user && $user->hasRole('timbangan')) {
                    if (!$record->status == 1) {
                        return EditTimbanganTronton::getUrl(['record' => $record]);
                    }
                    return null;
                }

                // 3) Admin2 hanya bisa edit jika BK belum ada
                if ($user && $user->hasRole('admin')) {
                    if (!$record->kode) {  // Sesuaikan dengan struktur data BK
                        return EditTimbanganTronton::getUrl(['record' => $record]);
                    }
                    return null;
                }

                // 4) Role lainnya tidak bisa edit
                return null;
            })

            ->columns([
                ToggleColumn::make('status')
                    ->label('Status Audit')
                    ->alignCenter()
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->disabled(fn() => !optional(Auth::user())->hasAnyRole(['admin', 'super_admin', 'adminaudit'])),
                BadgeColumn::make('created_at')
                    ->label('Tanggal')
                    ->alignCenter()
                    ->colors([
                        'success' => fn($state) => Carbon::parse($state)->isToday(),
                        'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                        'gray' => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                    ])
                    ->formatStateUsing(function ($state) {
                        // Mengatur lokalitas ke Bahasa Indonesia
                        Carbon::setLocale('id');

                        return Carbon::parse($state)
                            ->locale('id') // Memastikan locale di-set ke bahasa Indonesia
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('kode')
                    ->label('No Penjualan')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('penjualan1.plat_polisi')
                    ->label('Plat Polisi')
                    ->alignCenter()
                    ->searchable()
                    ->placeholder('LUAR'),
                TextColumn::make('penjualan1.nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
                TextColumn::make('bruto_akhir')
                    ->label('Bruto Akhir')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tara_awal')
                    ->label('Tara Awal')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('total_netto')
                    ->label('Total Netto')
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
                //Pembelian Luar 1
                TextColumn::make('luar1.kode')
                    ->label('No SPB Luar 1')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('luar1.nama_barang')
                    ->label('Nama Barang')
                    ->alignCenter(),
                TextColumn::make('luar1.no_container')
                    ->label('No Container')
                    ->alignCenter(),
                TextColumn::make('luar1.netto')
                    ->label('Netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama_barang'),
                TextColumn::make('keterangan'),
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->getStateUsing(fn($record) => $record->foto[0] ?? null)
                    ->url(fn($record) => asset('storage/' . ($record->foto[0] ?? '')))
                    ->openUrlInNewTab(),
                TextColumn::make('user.name')
                    ->label('User')
            ])->defaultSort('id', 'desc')
            ->filters([
                Filter::make('Hari Ini')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDate('created_at', Carbon::today())
                    )->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('View')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-laporan-penjualan", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListTimbanganTrontons::route('/'),
            'create' => Pages\CreateTimbanganTronton::route('/create'),
            'edit' => Pages\EditTimbanganTronton::route('/{record}/edit'),
            'view-laporan-penjualan' => Pages\ViewLaporanPenjualan::route('/{record}/view-laporan-penjualan'),
        ];
    }
}
