<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Silo;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LaporanLumbung;
use App\Services\DryerService;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\LaporanLumbungExporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LaporanLumbungResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\LaporanLumbungResource\RelationManagers;

class LaporanLumbungResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LaporanLumbung::class;
    protected static ?string $navigationLabel = 'Lumbung Kering';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'QC';
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
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    // public static function canAccess(): bool
    // {
    //     return false; // Menyembunyikan resource dari sidebar
    // }
    public static function getNavigationSort(): int
    {
        return 4; // Ini akan muncul di atas
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('next_id')
                                            ->label('No Laporan Lumbung')
                                            ->content(function ($record) {
                                                // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                                if ($record) {
                                                    return $record->kode;
                                                }

                                                // Jika sedang membuat data baru, hitung kode berikutnya
                                                $nextId = (LaporanLumbung::max('id') ?? 0) + 1;
                                                return 'IO' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                            }),
                                        Toggle::make('status')
                                            ->label('Status')
                                            ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                                            ->default(false) // Default false (buka)
                                            ->onColor('danger') // Warna merah saat true (tutup)
                                            ->offColor('success'), // Warna hijau saat false (buka)
                                    ])->columnSpan(1),
                                // TextInput::make('berat_dryer')
                                //     ->label('Berat Dryer')->suffix('Kg')
                                //     ->placeholder('Terhitung otomatis')
                                //     ->numeric()
                                //     ->readOnly(), // Opsional: buat readonly karena dihitung otomatis
                                // TextInput::make('berat_penjualan')
                                //     ->label('Berat Penjualan')->suffix('Kg')
                                //     ->placeholder('Terhitung otomatis')
                                //     ->numeric()
                                //     ->readOnly(), // Opsional: buat readonly karena dihitung otomatis
                                // TextInput::make('hasil')
                                //     ->label('Hasil')->suffix('Kg')
                                //     ->placeholder('Terhitung otomatis')
                                //     ->numeric()
                                //     ->readOnly(),
                                Grid::make()
                                    ->schema([

                                        Select::make('silo_id')
                                            ->label('Kode')
                                            ->options(function () {
                                                return Silo::whereIn('nama', [
                                                    'SILO STAFFEL A',
                                                    'SILO STAFFEL B',
                                                    'SILO 2500',
                                                    'SILO 1800'
                                                ])
                                                    ->where('status', '!=', true) // Tambahkan kondisi untuk mengecualikan status true
                                                    // Atau bisa juga menggunakan:
                                                    // ->where('status', false)
                                                    // ->whereNull('status')
                                                    // ->where(function($query) {
                                                    //     $query->where('status', false)->orWhereNull('status');
                                                    // })
                                                    ->get()
                                                    ->mapWithKeys(function ($item) {
                                                        return [
                                                            $item->id => $item->id . ' - ' . $item->nama
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->disabled(function (callable $get) {
                                                // Disabled jika field lumbung terisi
                                                return !empty($get('lumbung'));
                                            })
                                            ->placeholder('Pilih')
                                            ->reactive()
                                            ->default(function () {
                                                // Auto-select berdasarkan parameter URL dari tab aktif
                                                $statusSilo = request()->get('status_silo');

                                                if ($statusSilo) {
                                                    // Mapping dari parameter URL ke nama silo
                                                    $statusMapping = [
                                                        'silo staffel a' => 'SILO STAFFEL A',
                                                        'silo staffel b' => 'SILO STAFFEL B',
                                                        'silo 2500' => 'SILO 2500',
                                                        'silo 1800' => 'SILO 1800',
                                                    ];

                                                    $namaTarget = $statusMapping[$statusSilo] ?? null;

                                                    if ($namaTarget) {
                                                        // Cari silo berdasarkan nama dan pastikan status bukan true
                                                        $silo = Silo::where('nama', $namaTarget)
                                                            ->where('status', '!=', true)
                                                            ->first();
                                                        return $silo ? $silo->id : null;
                                                    }
                                                }

                                                return null;
                                            })
                                            ->afterStateHydrated(function (Select $component, $state, callable $set) {
                                                // Jika belum ada nilai terpilih, set berdasarkan parameter URL
                                                if (!$state) {
                                                    $statusSilo = request()->get('status_silo');

                                                    if ($statusSilo) {
                                                        $statusMapping = [
                                                            'silo staffel a' => 'SILO STAFFEL A',
                                                            'silo staffel b' => 'SILO STAFFEL B',
                                                            'silo 2500' => 'SILO 2500',
                                                            'silo 1800' => 'SILO 1800',
                                                        ];

                                                        $namaTarget = $statusMapping[$statusSilo] ?? null;

                                                        if ($namaTarget) {
                                                            $silo = Silo::where('nama', $namaTarget)
                                                                ->where('status', '!=', true)
                                                                ->first();
                                                            if ($silo) {
                                                                $component->state($silo->id);
                                                                $set('status_silo', $silo->nama); // Set status_silo juga
                                                            }
                                                        }
                                                    }
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data silo berdasarkan ID yang dipilih
                                                    $silo = Silo::find($state);
                                                    if ($silo) {
                                                        $set('status_silo', $silo->nama); // Set status sesuai nama silo
                                                    }
                                                } else {
                                                    $set('status_silo', null);
                                                }
                                            }),

                                        Select::make('status_silo')
                                            ->native(false)
                                            ->placeholder('Otomatis')
                                            ->label('Status silo')
                                            ->disabled()
                                            ->dehydrated() // Memastikan field tetap terkirim meskipun disabled
                                            ->options([
                                                'SILO STAFFEL A' => 'SILO STAFFEL A',
                                                'SILO STAFFEL B' => 'SILO STAFFEL B',
                                                'SILO 2500' => 'SILO 2500',
                                                'SILO 1800' => 'SILO 1800',
                                            ])
                                            ->default(function () {
                                                // Ambil dari parameter URL yang sudah dikirim dari ListLaporanLumbungs
                                                $statusSilo = request()->get('status_silo');

                                                if ($statusSilo) {
                                                    // Mapping dari database value ke display value
                                                    $statusMapping = [
                                                        'silo staffel a' => 'SILO STAFFEL A',
                                                        'silo staffel b' => 'SILO STAFFEL B',
                                                        'silo 2500' => 'SILO 2500',
                                                        'silo 1800' => 'SILO 1800',
                                                    ];

                                                    return $statusMapping[strtolower($statusSilo)] ?? strtoupper($statusSilo);
                                                }

                                                return null;
                                            })
                                            ->live()
                                            ->reactive(),


                                    ])->columnSpan(1),
                                // ->afterStateUpdated(function (Set $set, $state) {
                                //     // Jika status_silo dipilih (tidak kosong), set tipe_penjualan ke 'masuk'
                                //     if (!empty($state)) {
                                //         $set('tipe_penjualan', 'MASUK');
                                //     }
                                // }),
                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('Masukkan keterangan...')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->visible(fn(Get $get) => $get('show_keterangan') || !empty($get('keterangan')))
                                    ->suffixAction(
                                        Action::make('hide_keterangan')
                                            ->icon('heroicon-o-x-mark')
                                            ->color('gray')
                                            ->action(function (Set $set) {
                                                $set('show_keterangan', false);
                                                $set('keterangan', null);
                                            })
                                            ->visible(fn(Get $get) => empty($get('keterangan'))) // Hanya tampil jika field kosong
                                    ),
                            ]),
                        // Card::make('Info Dryer')
                        //     // ->visible(fn(Get $get) => $get('tipe_penjualan') === 'keluar')
                        //     ->schema([
                        //         Group::make([
                        // Field select yang bisa berubah menjadi readonly saat edit
                        Hidden::make('lumbung')
                            ->default(function () {
                                // Set default dari request parameter atau record
                                if (request()->get('lumbung')) {
                                    return request()->get('lumbung');
                                }

                                try {
                                    $livewire = \Livewire\Livewire::current();
                                    if ($livewire && method_exists($livewire, 'getRecord')) {
                                        $record = $livewire->getRecord();
                                        if ($record && isset($record->lumbung)) {
                                            return $record->lumbung;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Ignore error
                                }

                                return null;
                            }),
                        // ->afterStateUpdated(function ($state, $set, $get) {
                        //     // Reset dryer selection ketika lumbung berubah
                        //     $set('dryers', []);
                        // }),
                    ])
                    ->columns(1),

                // Select::make('dryers')
                //     ->label('Dryer')
                //     ->multiple()
                //     ->disabled(fn(Get $get) => $get('tipe_penjualan') === 'masuk') // Disable jika 'masuk'
                //     ->relationship(
                //         name: 'dryers',
                //         titleAttribute: 'no_dryer',
                //         modifyQueryUsing: function (Builder $query, $get) {
                //             $selectedLumbung = $get('lumbung_display') ?: $get('lumbung');
                //             $currentDryers = $get('dryers') ?? [];

                //             // Coba ambil record dari berbagai context
                //             $currentRecordId = null;

                //             // Untuk EditRecord page
                //             if (request()->route('record')) {
                //                 $currentRecordId = request()->route('record');
                //             }

                //             // Atau dari Livewire component
                //             try {
                //                 $livewire = \Livewire\Livewire::current();
                //                 if ($livewire && method_exists($livewire, 'getRecord')) {
                //                     $record = $livewire->getRecord();
                //                     if ($record) {
                //                         $currentRecordId = $record->getKey();
                //                     }
                //                 }
                //             } catch (\Exception $e) {
                //                 // Ignore error jika tidak dalam context Livewire
                //             }

                //             // Ambil semua ID yang sudah digunakan
                //             $usedLaporanIds = DB::table('laporan_lumbung_has_dryers')
                //                 ->pluck('dryer_id')
                //                 ->toArray();

                //             // Jika sedang edit, ambil ID yang sudah terkait dengan record ini
                //             if ($currentRecordId) {
                //                 $currentlySelectedIds = DB::table('laporan_lumbung_has_dryers')
                //                     ->where('laporan_lumbung_id', $currentRecordId)
                //                     ->pluck('dryer_id')
                //                     ->toArray();

                //                 // Exclude currently selected IDs from used IDs
                //                 $usedLaporanIds = array_diff($usedLaporanIds, $currentlySelectedIds);
                //             }

                //             // Base query
                //             $query = $query
                //                 ->whereNotNull('dryers.lumbung_tujuan')
                //                 ->where('dryers.lumbung_tujuan', '!=', '');

                //             // Jika ada filter lumbung yang dipilih
                //             if ($selectedLumbung) {
                //                 // Include dryers yang sudah dipilih sebelumnya ATAU yang sesuai dengan filter
                //                 $query->where(function ($subQuery) use ($selectedLumbung, $currentDryers) {
                //                     $subQuery->where('dryers.lumbung_tujuan', $selectedLumbung);

                //                     // Jika ada dryers yang sudah dipilih, include mereka juga
                //                     if (!empty($currentDryers)) {
                //                         $subQuery->orWhereIn('dryers.id', $currentDryers);
                //                     }
                //                 });
                //             }
                //             $query->orderBy('dryers.created_at', 'desc');
                //             return $query->whereNotIn('dryers.id', $usedLaporanIds);
                //         }
                //     )
                //     ->preload()
                //     ->reactive()
                //     ->getOptionLabelFromRecordUsing(function ($record) {
                //         return $record->no_dryer . ' - Dryer : ' . $record->kapasitasdryer->nama_kapasitas_dryer . ' - Lumbung Kering : ' . $record->lumbung_tujuan;
                //     })
                //     ->afterStateUpdated(
                //         function ($state, callable $set, callable $get, $livewire, $old) {
                //             // app(DryerService::class)->updateStatusToCompleted(
                //             //     $state ?? [],
                //             //     $old ?? []
                //             // );
                //             // Hitung total netto dari dryer yang dipilih
                //             if (!empty($state)) {
                //                 // Ambil model Dryer (sesuaikan dengan nama model Anda)
                //                 $totalNetto = \App\Models\Dryer::whereIn('id', $state)
                //                     ->sum('total_netto');

                //                 // Set nilai ke field berat_dryer
                //                 $set('berat_dryer', $totalNetto);
                //             } else {
                //                 // Jika tidak ada dryer yang dipilih, set ke 0
                //                 $set('berat_dryer', 0);
                //             }
                //             // Hitung hasil setelah berat_dryer berubah
                //             $totalDryer = (float) ($get('berat_dryer') ?? 0);
                //             $beratPenjualan = (float) ($get('berat_penjualan') ?? 0);
                //             $set('hasil', $totalDryer - $beratPenjualan);
                //         }
                //     )->preload()
                //     ->searchable(),
                // ])->columnSpan(2),
                // Card::make('Info Laporan Penjualan')
                //     ->schema([
                //         // Select::make('lumbung')
                //         //     ->native(false)
                //         //     ->disabled(fn(Get $get) => $get('tipe_penjualan') === 'masuk')
                //         //     ->label('Lumbung')
                //         //     ->options(function () {
                //         //         return \App\Models\Penjualan::query()
                //         //             ->whereNotNull('nama_lumbung')
                //         //             ->where('nama_lumbung', '!=', '')
                //         //             ->whereNotIn('nama_lumbung', [
                //         //                 'SILO STAFFEL A',
                //         //                 'SILO STAFFEL B',
                //         //                 'SILO BESAR',
                //         //                 'SILO 2500',
                //         //                 'SILO 1800'
                //         //             ])
                //         //             ->distinct()
                //         //             ->pluck('nama_lumbung', 'nama_lumbung')
                //         //             ->toArray();
                //         //     })
                //         //     ->reactive()
                //         //     ->afterStateHydrated(function (Select $component, $state) {
                //         //         // Jika state kosong dan ada nilai default dari tab, gunakan nilai default
                //         //         if (empty($state) && request()->has('activeTab')) {
                //         //             $activeTab = request()->get('activeTab');
                //         //             if (str_starts_with($activeTab, 'lumbung_')) {
                //         //                 $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));
                //         //                 $lumbungMapping = [
                //         //                     'A' => 'A', // Sesuaikan dengan nama yang ada di database
                //         //                     'B' => 'B',
                //         //                     'C' => 'C',
                //         //                     'D' => 'D',
                //         //                     'E' => 'E',
                //         //                     'F' => 'F',
                //         //                     'G' => 'G',
                //         //                     'H' => 'H',
                //         //                     'I' => 'I',
                //         //                 ];

                //         //                 if (isset($lumbungMapping[$lumbungCode])) {
                //         //                     $component->state($lumbungMapping[$lumbungCode]);
                //         //                 }
                //         //             }
                //         //         }
                //         //     }),
                //         // ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                //         //     // Disable saat edit, misal jika $record ada berarti edit
                //         //     return $record !== null;
                //         // }),


                //         Select::make('timbanganTrontons')
                //             ->label('Laporan Penjualan')
                //             ->disabled(fn(Get $get) => $get('tipe_penjualan') === 'masuk') // Disable jika 'masuk'
                //             ->multiple()
                //             ->relationship(
                //                 name: 'timbanganTrontons',
                //                 titleAttribute: 'kode',
                //                 modifyQueryUsing: function (Builder $query, $get) {
                //                     // Coba ambil record dari berbagai context
                //                     $currentRecordId = null;

                //                     // Untuk EditRecord page
                //                     if (request()->route('record')) {
                //                         $currentRecordId = request()->route('record');
                //                     }

                //                     // Atau dari Livewire component
                //                     try {
                //                         $livewire = \Livewire\Livewire::current();
                //                         if ($livewire && method_exists($livewire, 'getRecord')) {
                //                             $record = $livewire->getRecord();
                //                             if ($record) {
                //                                 $currentRecordId = $record->getKey();
                //                             }
                //                         }
                //                     } catch (\Exception $e) {
                //                         // Ignore error jika tidak dalam context Livewire
                //                     }

                //                     $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];
                //                     $selectedNamaLumbung = $get('lumbung');

                //                     $query = $query->where(function ($query) use ($relasiPenjualan, $selectedNamaLumbung) {
                //                         foreach ($relasiPenjualan as $index => $relasi) {
                //                             $method = $index === 0 ? 'whereHas' : 'orWhereHas';

                //                             $query->$method($relasi, function (Builder $q) use ($selectedNamaLumbung) {
                //                                 $q->whereNotNull('nama_lumbung')
                //                                     ->where('nama_lumbung', '!=', '');

                //                                 if ($selectedNamaLumbung) {
                //                                     $q->where('nama_lumbung', $selectedNamaLumbung);
                //                                 }
                //                             });
                //                         }
                //                     });

                //                     $query->where(function ($q) {
                //                         $q->where('status', false)  // status = 0 / false
                //                             ->orWhereNull('status');  // atau status = null
                //                     });
                //                     $query->orderBy('timbangan_trontons.created_at', 'desc');
                //                     $query->limit(20);
                //                     return $query;
                //                 }
                //             )
                //             ->preload()
                //             ->reactive()
                //             ->getOptionLabelFromRecordUsing(function ($record) {
                //                 $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : 'N/A';
                //                 return $record->kode . ' - ' . $noBk . ' - ' . ($record->penjualan1->nama_supir ?? '') . ' - ' . $record->total_netto;
                //             })
                //             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                 if (!empty($state)) {
                //                     $selectedLumbung = $get('lumbung'); // Ambil lumbung yang dipilih
                //                     $totalNetto = 0;

                //                     // Loop melalui setiap timbangan yang dipilih
                //                     foreach ($state as $timbanganId) {
                //                         // Ambil record timbangan dengan relasi penjualan
                //                         $timbangan = \App\Models\TimbanganTronton::with([
                //                             'penjualan1',
                //                             'penjualan2',
                //                             'penjualan3',
                //                             'penjualan4',
                //                             'penjualan5',
                //                             'penjualan6'
                //                         ])->find($timbanganId);

                //                         if ($timbangan) {
                //                             // Array relasi penjualan
                //                             $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];

                //                             // Loop melalui setiap relasi penjualan
                //                             foreach ($relasiPenjualan as $relasi) {
                //                                 $penjualan = $timbangan->$relasi;

                //                                 // Jika penjualan ada dan nama_lumbung sesuai dengan yang dipilih
                //                                 if (
                //                                     $penjualan &&
                //                                     $penjualan->nama_lumbung &&
                //                                     $penjualan->nama_lumbung === $selectedLumbung
                //                                 ) {

                //                                     // Tambahkan netto penjualan ke total
                //                                     $totalNetto += $penjualan->netto ?? 0;
                //                                 }
                //                             }
                //                         }
                //                     }

                //                     // Set nilai ke field berat_penjualan
                //                     $set('berat_penjualan', $totalNetto);
                //                 } else {
                //                     // Jika tidak ada timbangan yang dipilih, set ke 0
                //                     $set('berat_penjualan', 0);
                //                 }

                //                 // Hitung hasil setelah berat_penjualan berubah
                //                 $totalDryer = (float) ($get('berat_dryer') ?? 0);
                //                 $beratPenjualan = (float) ($get('berat_penjualan') ?? 0);
                //                 $set('hasil', $totalDryer - $beratPenjualan);
                //             }),
                //     ])->columnSpan(1),

                Actions::make([
                    Action::make('toggle_keterangan')
                        ->label('Tambah Catatan')
                        ->icon('heroicon-o-plus')
                        ->color('primary')
                        ->action(function (Set $set) {
                            $set('show_keterangan', true);
                        })
                        ->visible(fn(Get $get) => !$get('show_keterangan') && empty($get('keterangan')))
                ])
                    ->columnSpanFull(),
                // Hidden field untuk state
                TextInput::make('show_keterangan')
                    ->hidden()
                    ->default(fn(Get $get) => !empty($get('keterangan'))), // Auto true jika ada data keterangan
                // TextInput keterangan yang bisa di-toggle
                // TextInput::make('keterangan')
                //     ->label('Catatan')
                //     ->placeholder('Masukkan catatan...')
                //     ->maxLength(255)
                //     ->columnSpanFull()
                //     ->visible(fn(Get $get) => $get('show_keterangan'))
                //     ->suffixAction(
                //         Action::make('hide_keterangan')
                //             ->icon('heroicon-o-x-mark')
                //             ->color('gray')
                //             ->action(function (Set $set) {
                //                 $set('show_keterangan', false);
                //                 $set('keterangan', null);
                //             })
                //     ),
                Hidden::make('user_id')
                    ->label('User ID')
                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                // ])->columns(2),
                // Card::make('Info Timbangan Langsir')
                //     ->schema([

                // TextInput::make('berat_penjualan')
                //     ->label('Berat Penjualan')
                //     ->numeric()
                //     ->readOnly(), // Opsional: buat readonly karena dihitung otomatis
                // TextInput::make('hasil')
                //     ->label('Hasil')
                //     ->numeric()
                //     ->readOnly(),
                // Select::make('penjualan_ids')
                //     ->label('Timbangan Langsir')
                //     ->placeholder('Pilih ID timbangan langsir')
                //     ->multiple()
                //     ->relationship(
                //         name: 'penjualans',
                //         titleAttribute: 'no_spb',
                //         modifyQueryUsing: function ($query, $get) {
                //             $statusSilo = $get('status_silo');

                //             // Base query
                //             $query = $query
                //                 ->where('status_timbangan', 'LANGSIR')
                //                 ->whereNotNull('netto')
                //                 ->where('netto', '>', 0)
                //                 ->orderBy('id', 'desc'); // Urutkan berdasarkan ID terbaru

                //             // Filter berdasarkan status_silo jika dipilih
                //             if ($statusSilo) {
                //                 $query->where('silo', $statusSilo);
                //             }

                //             return $query;
                //         }
                //     )
                //     ->getOptionLabelFromRecordUsing(fn($record) => "{$record->no_spb} - {$record->nama_supir} - {$record->no_lumbung} - {$record->nama_lumbung} - {$record->silo} - {$record->netto}")
                //     ->searchable()
                //     ->columnSpan(2)
                //     ->live()
                //     ->afterStateUpdated(function (Set $set, $state) {
                //         if (empty($state)) {
                //             $set('berat_langsir', 0);
                //             return;
                //         }

                //         // Ambil data penjualan dengan select hanya kolom yang dibutuhkan
                //         $penjualans = \App\Models\Penjualan::select('netto')
                //             ->whereIn('id', $state)
                //             ->get();

                //         $totalNetto = $penjualans->sum('netto');
                //         // Simpan nilai asli (integer) tanpa format
                //         $set('berat_langsir', $totalNetto);
                //     })
                //     ->preload(),

                // Select::make('tipe_penjualan')
                //     ->label('MASUK/KELUAR')
                //     ->native(false)
                //     ->options([
                //         'masuk' => 'MASUK',
                //         'keluar' => 'KELUAR',
                //     ])
                //     ->live()
                //     ->columnSpan(1)
                //     ->default(function () {
                //         // Default untuk create mode
                //         return request()->get('lumbung') !== null ? 'keluar' : 'masuk';
                //     })
                //     ->afterStateHydrated(function ($component, $state, $context, Get $get) {
                //         // Untuk edit mode - set berdasarkan nilai lumbung di database
                //         if ($context === 'edit') {
                //             $lumbungValue = $get('lumbung');
                //             $newValue = !empty($lumbungValue) ? 'keluar' : 'masuk';
                //             $component->state($newValue);
                //         }
                //     })
                //     ->disabled()
                //     ->dehydrated()
                //     ->afterStateUpdated(function (Set $set, $state) {
                //         // Reset status_silo ketika bukan masuk
                //         if ($state !== 'masuk') {
                //             $set('status_silo', null);
                //         }
                //     }),

                // TextInput::make('berat_langsir')
                //     ->label('Total berat')
                //     ->columnSpan(1)
                //     ->numeric()
                //     // ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '0') // Format hanya untuk display
                //     ->readOnly()
                //     ->suffix('Kg'), // Opsional: tambah satuan
                // ])->columns(4)->collapsed()
                //->visible(fn(Get $get) => filled($get('status_silo'))) // Muncul live ketika ada pilihan,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Tutup' : 'Buka';
                    })
                    ->color(function ($state) {
                        return $state ? 'danger' : 'success';
                    }),
                TextColumn::make('status_silo')
                    ->label('Silo')
                    ->default('-')
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'SILO STAFFEL A' => 'primary',
                            'SILO STAFFEL B' => 'primary',
                            'SILO 2500' => 'primary',
                            'SILO 1800' => 'primary',
                            '-' => 'primary',
                            default => 'primary'
                        };
                    }),
                TextColumn::make('kode')
                    ->label('No Laporan')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('lumbung')
                    ->alignCenter()
                    ->searchable()
                    ->label('Lumbung')
                    ->placeholder('- TRANSFER -'),
                TextColumn::make('dryers.no_dryer')
                    ->alignCenter()
                    ->searchable()
                    ->label('Dryer')
                    ->getStateUsing(function ($record) {
                        $dryer = $record->dryers->pluck('no_dryer');

                        if ($dryer->count() <= 3) {
                            return $dryer->implode(', ');
                        }

                        return $dryer->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $dryer = $record->dryers->pluck('no_dryer');
                        return $dryer->implode(', ');
                    }),
                TextColumn::make('penjualans.no_spb')
                    ->searchable()
                    ->alignCenter()
                    ->label('No Penjualan')
                    ->getStateUsing(function ($record) {
                        $nospb = $record->penjualans->pluck('no_spb');

                        if ($nospb->count() <= 3) {
                            return $nospb->implode(', ');
                        }

                        return $nospb->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $nospb = $record->penjualans->pluck('no_spb');
                        return $nospb->implode(', ');
                    }),
                TextColumn::make('transferKeluar.kode')
                    ->label('No Transfer Keluar')
                    ->searchable()
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $kodes = $record->transferKeluar->pluck('kode');

                        if ($kodes->count() <= 3) {
                            return $kodes->implode(', ');
                        }

                        return $kodes->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $kodes = $record->transferKeluar->pluck('kode');
                        return $kodes->implode(', ');
                    }),
                TextColumn::make('transferMasuk.kode')
                    ->label('No Transfer Masuk')
                    ->searchable()
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $kodes = $record->transferMasuk->pluck('kode');

                        if ($kodes->count() <= 3) {
                            return $kodes->implode(', ');
                        }

                        return $kodes->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $kodes = $record->transferMasuk->pluck('kode');
                        return $kodes->implode(', ');
                    }),
                TextColumn::make('user.name')
                    ->alignCenter()
                    ->label('PJ'),
            ])
            ->filters([
                Filter::make('pilih_tanggal')
                    ->form([
                        DatePicker::make('tanggal')
                            ->label('Pilih Tanggal')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['tanggal'] ?? null,
                            fn($query, $date) => $query->whereDate('created_at', $date)
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return ($data['tanggal'] ?? null)
                            ? 'Tanggal: ' . Carbon::parse($data['tanggal'])->format('d M Y')
                            : null;
                    }),
            ])
            ->headerActions([
                ExportAction::make()->exporter(LaporanLumbungExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(LaporanLumbungExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->defaultSort('kode', 'desc') // Megurutkan kode terakhir menjadi pertama pada tabel
            ->actions([
                Tables\Actions\Action::make('view-laporan-lumbung')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-laporan-lumbung", ['record' => $record->id])),
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
            'index' => Pages\ListLaporanLumbungs::route('/'),
            'create' => Pages\CreateLaporanLumbung::route('/create'),
            'edit' => Pages\EditLaporanLumbung::route('/{record}/edit'),
            'view-laporan-lumbung' => Pages\ViewLaporanLumbung::route('/{record}/view-laporan-lumbung'),
        ];
    }
}
