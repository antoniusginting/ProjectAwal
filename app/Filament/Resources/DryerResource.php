<?php

namespace App\Filament\Resources;
//supaya hilang dulu dari role
// namespace BezhanSalleh\FilamentShield\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Dryer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungBasah;
use App\Models\KapasitasDryer;
use App\Models\LaporanLumbung;
use App\Services\SortirService;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Filament\Exports\DryerExporter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\DryerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DryerResource\Pages\EditDryer;
use App\Filament\Resources\DryerResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class DryerResource extends Resource implements HasShieldPermissions
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
    public static function getNavigationSort(): int
    {
        return 3; // Ini akan muncul di atas
    }
    protected static ?string $model = Dryer::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Dryer';
    protected static ?string $navigationGroup = 'QC';
    protected static ?int $navigationSort = 2;
    public static ?string $label = 'Daftar Dryer ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Informasi Dryer')
                    ->schema([
                        Placeholder::make('next_id')
                            ->label('No Dryer')
                            ->content(function ($record) {
                                // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                if ($record) {
                                    return $record->no_dryer;
                                }

                                // Jika sedang membuat data baru, hitung kode berikutnya
                                $nextId = (Dryer::max('id') ?? 0) + 1;
                                return 'D' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                            }),
                        Select::make('id_kapasitas_dryer')
                            ->label('Nama Dryer')
                            ->placeholder('Pilih nama Dryer')
                            ->options(KapasitasDryer::pluck('nama_kapasitas_dryer', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $kapasitasdryer = KapasitasDryer::find($state);

                                    // Simpan kapasitas sisa asli (tanpa format) untuk perhitungan
                                    $kapasitasSisaValue = $kapasitasdryer?->kapasitas_sisa ?? 0;
                                    $set('kapasitas_sisa_original', $kapasitasSisaValue);

                                    // Format untuk tampilan
                                    $formattedSisa = number_format($kapasitasSisaValue, 0, ',', '.');
                                    $set('kapasitas_sisa', $formattedSisa);

                                    // Kapasitas total
                                    $formattedtotal = number_format($kapasitasdryer?->kapasitas_total ?? 0, 0, ',', '.');
                                    $set('kapasitas_total', $formattedtotal);

                                    // Hitung ulang kapasitas sisa berdasarkan total netto jika sudah ada
                                    $totalNetto = (float) ($get('total_netto') ?? 0);
                                    if ($totalNetto > 0) {
                                        $sisaSetelahDikurangi = $kapasitasSisaValue - $totalNetto;
                                        $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                                        $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $kapasitasdryer = KapasitasDryer::find($state);
                                // $set('lumbung_tujuan', null);
                                // Simpan kapasitas sisa asli untuk perhitungan
                                $kapasitasSisaValue = $kapasitasdryer?->kapasitas_sisa ?? 0;
                                $set('kapasitas_sisa_original', $kapasitasSisaValue);

                                // Format untuk tampilan
                                $formattedSisa = number_format($kapasitasSisaValue, 0, ',', '.');
                                $set('kapasitas_sisa', $formattedSisa);

                                // Kapasitas total
                                $formattedtotal = number_format($kapasitasdryer?->kapasitas_total ?? 0, 0, ',', '.');
                                $set('kapasitas_total', $formattedtotal);

                                // Reset nilai
                                $set('sortirans', null);
                                $set('total_netto', null);
                                $set('kapasitas_sisa_akhir', $formattedSisa); // Reset kapasitas sisa akhir ke nilai awal
                            }),


                        Select::make('laporan_lumbung_id')
                            ->label('Lumbung Tujuan')
                            ->disabled(fn(string $operation): bool => $operation === 'create')
                            ->options(function () {
                                return LaporanLumbung::whereNull('status_silo')
                                    ->where('status', false)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        return [
                                            $item->id => $item->kode . ' - ' . $item->lumbung
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Pilih Laporan Lumbung'),



                        // Select::make('lumbung_tujuan')
                        //     ->label('Lumbung Tujuan')
                        //     ->options(function (callable $get) {
                        //         $selectedId = $get('id_kapasitas_dryer');
                        //         if (!$selectedId) {
                        //             // Jika belum pilih kapasitas dryer, tampilkan semua opsi
                        //             return [];
                        //         }

                        //         // Cari nama kapasitas dryer berdasarkan ID yang dipilih
                        //         $namaKapasitas = KapasitasDryer::where('id', $selectedId)->value('nama_kapasitas_dryer');

                        //         if (in_array($namaKapasitas, ['A', 'B', 'D'])) {
                        //             return [
                        //                 'A' => 'A',
                        //                 'B' => 'B',
                        //                 'C' => 'C',
                        //                 'D' => 'D',
                        //                 'E' => 'E',
                        //             ];
                        //         }
                        //         if (in_array($namaKapasitas, ['A1', 'A2'])) {
                        //             return [
                        //                 'F' => 'F',
                        //                 'G' => 'G',
                        //                 'H' => 'H',
                        //                 'I' => 'I',
                        //             ];
                        //         }
                        //         if ($namaKapasitas === 'LSU') {
                        //             return [
                        //                 'S' => 'SILO BESAR',
                        //                 'F' => 'F',
                        //                 'G' => 'G',
                        //                 'H' => 'H',
                        //                 'I' => 'I',
                        //             ];
                        //         }

                        //         // Default opsi lengkap
                        //         return [
                        //             'A' => 'A',
                        //             'B' => 'B',
                        //             'C' => 'C',
                        //             'D' => 'D',
                        //             'E' => 'E',
                        //             'F' => 'F',
                        //             'G' => 'G',
                        //             'H' => 'H',
                        //             'I' => 'I',
                        //         ];
                        //     })
                        //     ->placeholder('Pilih lumbung kering')
                        //     ->native(false),

                        TextInput::make('pj')
                            ->label('PenanggungJawab')
                            ->placeholder('Masukkan PenanggungJawab'),
                        TextInput::make('operator')
                            ->label('Operator Dryer')
                            // ->required()
                            ->placeholder('Masukkan Operator Dryer'),

                        // TextInput::make('created_at')
                        //     ->label('Tanggal/Jam')
                        //     ->placeholder(now()->format('d-m-Y H:i:s')) // Tampilkan di input
                        //     ->disabled(), // Tidak bisa diedit

                        TextInput::make('rencana_kadar')
                            ->label('Rencana Kadar')
                            ->numeric()
                            // ->required()
                            ->placeholder('Masukkan rencana kadar'),
                        TextInput::make('hasil_kadar')
                            ->label('Hasil Kadar')
                            ->numeric()
                            ->placeholder('Masukkan hasil kadar'),
                        Select::make('nama_barang')
                            ->label('Nama Barang')
                            ->options([
                                'JGK' => 'JGK',
                                'JGB' => 'JGB',
                                'JMR' => 'JMR',
                            ])
                            ->placeholder('Pilih nama barang')
                            ->native(false),
                        TextInput::make('no_cc')
                            ->placeholder('Masukkan Nomor Pesanan')
                            ->label('Nomor Pesanan')
                            ->columnSpanFull()
                            // ->prefixIcon('heroicon-o-credit-card')
                            ->maxLength(19)
                            ->visible(fn() => !optional(Auth::user())->hasAnyRole(['timbangan'])) // diilangkan pada user timbangan
                            // ->extraInputAttributes([
                            //     'class' => 'font-mono tracking-wider text-center',
                            //     'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: 2px solid #4f46e5;'
                            // ])
                            ->hint('Setelah nomor pesanan disimpan, maka data tidak dapat diubah')
                            ->hintColor('warning'),
                    ])->columns(2),
                Card::make()
                    ->schema([
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->placeholder('Pilih terlebih dahulu nama Dryer')
                            ->disabled(),

                        TextInput::make('total_netto')
                            ->label('Kapasitas Terpakai')
                            ->placeholder('Otomatis terhitung')
                            ->readOnly(),

                        TextInput::make('kapasitas_sisa_akhir')
                            ->label('Kapasitas Sisa')
                            ->placeholder('Otomatis terhitung')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record === null),
                Card::make('Informasi Sortiran')
                    ->schema([
                        Select::make('filter_kapasitas_lumbung')
                            ->native(false)
                            ->label('Filter Kapasitas Lumbung')
                            ->columnSpan(1)
                            ->placeholder('Pilih Kapasitas Lumbung')
                            ->options(function () {
                                // Ambil semua kapasitas lumbung yang unik
                                return DB::table('kapasitas_lumbung_basahs')
                                    ->select('id', 'no_kapasitas_lumbung')
                                    ->orderBy('no_kapasitas_lumbung')
                                    ->where('id', '!=', 13) // Ganti 13 dengan ID yang ingin dikecualikan
                                    ->pluck('no_kapasitas_lumbung', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Simpan pilihan sortirans yang sudah ada sebelum filter berubah
                                $currentSortirans = $get('sortirans') ?? [];
                                $set('sortirans', $currentSortirans);
                            }),

                        Select::make('sortirans')
                            ->label('Sortiran')
                            ->multiple()
                            ->relationship(
                                name: 'sortirans',
                                titleAttribute: 'no_sortiran',
                                modifyQueryUsing: function (Builder $query, $get, $livewire) {
                                    $filterKapasitasLumbung = $get('filter_kapasitas_lumbung');
                                    $currentSortirans = $get('sortirans') ?? [];

                                    // Coba ambil record dari berbagai context
                                    $currentRecordId = null;

                                    // Untuk EditRecord page
                                    if (request()->route('record')) {
                                        $currentRecordId = request()->route('record');
                                    }

                                    // Atau dari Livewire component
                                    try {
                                        if ($livewire && method_exists($livewire, 'getRecord')) {
                                            $record = $livewire->getRecord();
                                            if ($record) {
                                                $currentRecordId = $record->getKey();
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Ignore error jika tidak dalam context Livewire
                                    }

                                    // Ambil semua ID sortiran yang sudah digunakan di dryer lain
                                    $usedSortiranIds = DB::table('dryer_has_sortiran')
                                        ->pluck('sortiran_id')
                                        ->toArray();

                                    // Jika sedang edit, ambil ID yang sudah terkait dengan record ini
                                    if ($currentRecordId) {
                                        $currentlySelectedIds = DB::table('dryer_has_sortiran')
                                            ->where('dryer_id', $currentRecordId)
                                            ->pluck('sortiran_id')
                                            ->toArray();

                                        // Exclude currently selected IDs from used IDs
                                        $usedSortiranIds = array_diff($usedSortiranIds, $currentlySelectedIds);
                                    }

                                    // Base query - exclude no_lumbung_basah = 13
                                    $query = $query->where('sortirans.no_lumbung_basah', '!=', 13);

                                    // Jika ada filter kapasitas lumbung yang dipilih
                                    if ($filterKapasitasLumbung) {
                                        // Include sortirans yang sudah dipilih sebelumnya ATAU yang sesuai dengan filter
                                        $query->where(function ($subQuery) use ($filterKapasitasLumbung, $currentSortirans) {
                                            // Filter berdasarkan kapasitas lumbung
                                            $subQuery->whereHas('kapasitaslumbungbasah', function ($q) use ($filterKapasitasLumbung) {
                                                $q->where('id', $filterKapasitasLumbung);
                                            });

                                            // Jika ada sortirans yang sudah dipilih, include mereka juga
                                            if (!empty($currentSortirans)) {
                                                $subQuery->orWhereIn('sortirans.id', $currentSortirans);
                                            }
                                        });
                                    }

                                    // Exclude sortiran yang sudah digunakan di dryer lain
                                    $query->whereNotIn('sortirans.id', $usedSortiranIds);

                                    return $query->latest('sortirans.created_at');
                                }
                            )
                            ->preload()
                            ->reactive()
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $noBk = $record->pembelian ? $record->pembelian->plat_polisi : '';
                                $supplier = $record->pembelian ? $record->pembelian->supplier->nama_supplier : '';
                                $kapasitas = $record->kapasitaslumbungbasah ? $record->kapasitaslumbungbasah->no_kapasitas_lumbung : 'N/A';
                                $noSpb = $record->pembelian ? $record->pembelian->no_spb : 'LANGSIR';

                                return $kapasitas . ' - ' . $noSpb . ' - ' . $noBk . ' - ' . $supplier . ' - ' . $record->netto_bersih;
                            })
                            // ->disabled(fn($get) => !$get('filter_kapasitas_lumbung'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire, $old) {
                                // Mendapatkan nilai kapasitas sisa awal
                                $noDryer = $get('id_kapasitas_dryer');
                                $kapasitasAwal = 0;
                                // app(SortirService::class)->updateStatusToDryer(
                                //     $state ?? [],
                                //     $old ?? []
                                // );


                                // Dapatkan record saat ini (untuk mode edit)
                                $record = $livewire->getRecord();
                                $isEditMode = $record !== null;

                                // Dapatkan kapasitas awal dari database
                                if ($noDryer) {
                                    $kapasitasDryer = KapasitasDryer::find($noDryer);
                                    if ($kapasitasDryer) {
                                        $kapasitasAwal = $kapasitasDryer->kapasitas_sisa;
                                    }
                                }

                                // Jika dalam mode edit, tambahkan kembali kapasitas yang sudah terpakai sebelumnya
                                if ($isEditMode) {
                                    // Mendapatkan sortiran yang sudah ada sebelumnya
                                    $oldSortiranIds = $record->sortirans()
                                        ->select('sortirans.id')
                                        ->pluck('sortirans.id')
                                        ->toArray();

                                    $oldSortirans = \App\Models\Sortiran::whereIn('id', $oldSortiranIds)->get();

                                    // Tambahkan kembali kapasitas dari sortiran yang sebelumnya terpakai
                                    $totalOldNetto = 0;
                                    foreach ($oldSortirans as $oldSortiran) {
                                        $oldNettoValue = (int) preg_replace('/[^0-9]/', '', $oldSortiran->netto_bersih);
                                        $totalOldNetto += $oldNettoValue;
                                    }

                                    // Tambahkan kapasitas yang sebelumnya terpakai
                                    $kapasitasAwal += $totalOldNetto;
                                }

                                // Jika tidak ada sortiran dipilih, reset total netto dan gunakan kapasitas awal
                                if (empty($state)) {
                                    $set('total_netto', 0); // Simpan sebagai integer
                                    // PERBAIKAN: Simpan sebagai integer, bukan string berformat
                                    $set('kapasitas_sisa_akhir', $kapasitasAwal);
                                    return;
                                }

                                // Ambil semua sortiran yang dipilih saat ini
                                $selectedSortirans = \App\Models\Sortiran::whereIn('id', $state)->get();

                                // Hitung total netto dari semua sortiran yang dipilih saat ini
                                $totalNetto = 0;
                                foreach ($selectedSortirans as $sortiran) {
                                    $nettoValue = (int) preg_replace('/[^0-9]/', '', $sortiran->netto_bersih);
                                    $totalNetto += $nettoValue;
                                }

                                // Set nilai total_netto sebagai integer untuk database
                                $set('total_netto', $totalNetto);

                                // Hitung kapasitas sisa baru dengan mengurangi kapasitas awal dengan total netto baru
                                $kapasitasSisaBaru = $kapasitasAwal - $totalNetto;

                                // PERBAIKAN: Pastikan benar-benar integer
                                $set('kapasitas_sisa_akhir', (int) $kapasitasSisaBaru);

                                // Tampilkan notifikasi
                                $notificationMessage = $isEditMode ?
                                    "Kapasitas diperbarui (mode edit)" :
                                    "Kapasitas diperbarui";
                            })
                            ->preload()
                            ->columnSpan(3)
                            ->searchable(),

                    ])->columns(4),
                // Card untuk Edit (record tidak null)
                Card::make()
                    ->schema([
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->placeholder('Pilih terlebih dahulu nama Dryer')
                            ->disabled(),

                        TextInput::make('total_netto')
                            ->label('Kapasitas Terpakai')
                            ->placeholder('Otomatis terhitung')
                            ->readOnly(),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null),
                Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Dryer $record): ?string {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                // 1) Super admin bisa edit semua kondisi
                if ($user && $user->hasRole('super_admin')) {
                    return EditDryer::getUrl(['record' => $record]);
                }

                // 2) Admin1 hanya bisa edit jika tara belum ada
                if ($user && $user->hasRole('qc_contoh')) {
                    if (!$record->no_cc) {
                        return EditDryer::getUrl(['record' => $record]);
                    }
                    return null;
                }

                // // 3) Admin2 hanya bisa edit jika no_spb belum ada
                // if ($user && $user->hasRole('admin')) {
                //     if (!$record->no_spb) {  // Sesuaikan dengan struktur data BK
                //         return EditPembelian::getUrl(['record' => $record]);
                //     }
                //     return null;
                // }

                // 4) Role lainnya tidak bisa edit
                return null;
            })
            ->defaultPaginationPageOption(10)
            ->defaultSort('no_dryer', 'desc')
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
                BadgeColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'processing' => 'Dalam Dryer',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'completed',
                        'danger' => 'processing',
                    ]),
                TextColumn::make('no_dryer')
                    ->label('No Dryer')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('kapasitasdryer.nama_kapasitas_dryer')
                    ->label('Nama Dryer')
                    ->alignCenter(),
                TextColumn::make('laporanLumbung.kode')
                    ->label('Kode Lumbung')
                    ->searchable(query: function ($query, $search) {
                        $query->orWhereHas('laporanLumbung', function ($q) use ($search) {
                            $q->where('kode', 'like', "%{$search}%")
                                ->orWhere('lumbung', 'like', "%{$search}%");
                        });
                    }),

                // TextColumn::make('lumbung_tujuan')
                //     ->label('Tujuan')
                //     ->searchable()
                //     ->alignCenter(),
                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('pj')
                    ->label('PJ')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('operator')
                    ->label('Operator')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('rencana_kadar')
                    ->label('Rencana Kadar')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('hasil_kadar')
                    ->label('Hasil Kadar')
                    ->searchable()
                    ->alignCenter(),
                // Versi yang menampilkan semua penjualan.no_spb jika pembelian.no_spb null
                TextColumn::make('sortirans')
                    ->alignCenter()
                    ->label('No SPB')
                    ->formatStateUsing(function ($record) {
                        $text = $record->sortirans->map(function ($sortiran) {
                            // Cek apakah pembelian.no_spb ada
                            if (!empty($sortiran->pembelian?->no_spb)) {
                                return $sortiran->pembelian->no_spb;
                            }

                            // Fallback ke semua penjualan.no_spb
                            $penjualanSpbs = $sortiran->penjualans->pluck('no_spb')->filter();

                            if ($penjualanSpbs->isEmpty()) {
                                return 'N/A';
                            }

                            // Jika ada banyak penjualan, tampilkan maksimal 3 dengan logic seperti sebelumnya
                            if ($penjualanSpbs->count() <= 3) {
                                return $penjualanSpbs->implode(', ');
                            }

                            return $penjualanSpbs->take(3)->implode(', ') . '...';
                        })->implode(' | '); // Gunakan separator berbeda untuk membedakan antar sortiran

                        // Batasi jumlah karakter total
                        return \Illuminate\Support\Str::limit($text, 30, '...');
                    })
                    ->extraAttributes(['class' => 'max-w-md truncate'])
                    ->tooltip(function ($record) {
                        return $record->sortirans->map(function ($sortiran) {
                            if (!empty($sortiran->pembelian?->no_spb)) {
                                return $sortiran->pembelian->no_spb;
                            }

                            // Untuk tooltip, tampilkan semua penjualan.no_spb tanpa batasan
                            $penjualanSpbs = $sortiran->penjualans->pluck('no_spb')->filter();
                            return $penjualanSpbs->isEmpty() ? 'N/A' : $penjualanSpbs->implode(', ');
                        })->implode(' | ');
                    }),
                TextColumn::make('total_netto')
                    ->alignCenter()
                    ->label('Total Netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->headerActions([
                ExportAction::make()->exporter(DryerExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(DryerExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view-dryer')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-dryer", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['dari_tanggal']) && !empty($data['sampai_tanggal'])) {
                            return $query->whereBetween('created_at', [
                                Carbon::parse($data['dari_tanggal'])->startOfDay(),
                                Carbon::parse($data['sampai_tanggal'])->endOfDay(),
                            ]);
                        }

                        if (!empty($data['dari_tanggal'])) {
                            return $query->where('created_at', '>=', Carbon::parse($data['dari_tanggal'])->startOfDay());
                        }

                        if (!empty($data['sampai_tanggal'])) {
                            return $query->where('created_at', '<=', Carbon::parse($data['sampai_tanggal'])->endOfDay());
                        }

                        return $query;
                    }),
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
            'index' => Pages\ListDryers::route('/'),
            'create' => Pages\CreateDryer::route('/create'),
            'edit' => Pages\EditDryer::route('/{record}/edit'),
            'view-dryer' => Pages\ViewDryer::route('/{record}/view-dryer'),
        ];
    }
}
