<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Sortiran;
use Filament\Forms\Form;
use App\Models\Pembelian;

use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\KapasitasLumbungBasah;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\SortiranExporter;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\SortiranResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Resources\SortiranResource\RelationManagers;
use App\Filament\Resources\SortiranResource\Pages\ViewSortiran;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class SortiranResource extends Resource implements HasShieldPermissions
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
    public static function getNavigationSort(): int
    {
        return 1; // Ini akan muncul di atas
    }
    protected static ?string $model = Sortiran::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'QC';
    protected static ?string $navigationLabel = 'Sortiran';
    protected static ?int $navigationSort = 1;
    public static ?string $label = 'Daftar Sortiran ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Informasi Kapasitas')
                    ->schema([
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->placeholder('Pilih terlebih dahulu no lumbung basah')
                            ->disabled(),
                        TextInput::make('kapasitas_sisa')
                            ->label('Kapasitas Sisa')
                            ->placeholder('Pilih terlebih dahulu no lumbung basah')
                            ->disabled(),
                    ])->columns(2)->collapsible(),
                Section::make('Informasi Pembelian') //Menambahkan Header
                    ->schema([
                        Card::make()
                            ->schema([
                                // TextInput::make('created_at')
                                //     ->label('Tanggal Sekarang')
                                //     ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                                //     ->disabled(), // Tidak bisa diedit
                                Select::make('id_pembelian')
                                    ->label('No SPB')
                                    ->placeholder('Pilih No SPB Pembelian')
                                    ->options(function ($get) {
                                        $selectedId = $get('id_pembelian');

                                        $idSudahDisortir = Sortiran::pluck('id_pembelian')->toArray();

                                        // Hilangkan selected ID dari filter jika sedang edit
                                        if ($selectedId) {
                                            $idSudahDisortir = array_diff($idSudahDisortir, [$selectedId]);
                                        }
                                        $query = Pembelian::with(['mobil', 'supplier'])
                                            ->whereNotIn('id', $idSudahDisortir)
                                            ->whereNotNull('tara')
                                            ->whereNotIn('nama_barang', [
                                                'CANGKANG',
                                                'SEKAM',
                                                'SALAH',
                                                'RETUR',
                                                'SEKAM PADI',
                                                'BESI',
                                                'LANGSIR SILO',
                                                'PASIR',
                                                'JG TUNGKUL',
                                                'SAMPAH',
                                                'ABU JAGUNG',
                                                'DEDAK'
                                            ])
                                            ->whereNotBetween(DB::raw('DATE(created_at)'), ['2025-08-01', '2025-08-07'])
                                            ->latest();


                                        // Pastikan data yang sedang dipilih (saat edit) tetap ada
                                        if ($selectedId) {
                                            $query->orWhere('id', $selectedId);
                                        }

                                        return $query->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->no_spb . ' - TIMBANGAN ' . $item->keterangan . ' - ' .
                                                        ($item->supplier->nama_supplier ?? '-') . ' - ' .
                                                        ($item->plat_polisi ?? '-') . ' - ' . $item->created_at->format('d-m-y')
                                                ];
                                            });
                                    })

                                    ->searchable()
                                    ->columnSpan(2)
                                    // ->required()
                                    ->reactive()
                                    ->disabled(fn($record) => $record !== null)
                                    ->disabled(fn($get) => !empty($get('penjualan_ids'))) // Hide jika pembelian dipilih
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $pembelian = Pembelian::find($state);
                                            $set('netto_pembelian', $pembelian?->netto ?? 0);
                                            $set('netto_bersih', $pembelian?->netto ?? 0);
                                            $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');
                                            $set('plat_polisi', $pembelian?->plat_polisi ?? 'Plat tidak ditemukan');
                                            $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $pembelian = Pembelian::find($state);
                                        $set('netto_pembelian', $pembelian?->netto ?? 0);
                                        $set('netto_bersih', $pembelian?->netto ?? 0);
                                        $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');
                                        $set('plat_polisi', $pembelian?->plat_polisi ?? 'Plat tidak ditemukan');
                                        $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                    }),


                                // Select::make('penjualan_ids')
                                //     ->label('Timbangan Langsir')
                                //     ->placeholder('Pilih ID timbangan langsir')
                                //     ->multiple()
                                //     ->relationship(
                                //         name: 'penjualans',
                                //         titleAttribute: 'no_spb',
                                //         modifyQueryUsing: function ($query, $get) {
                                //             // Hapus filter existing data untuk mengizinkan input data yang sama berulang kali
                                //             return $query
                                //                 ->where('status_timbangan', 'LANGSIR')
                                //                 ->whereNotNull('netto')
                                //                 ->where('netto', '>', 0)
                                //                 ->whereNotIn('silo', [
                                //                     'SILO STAFFEL A',
                                //                     'SILO STAFFEL B',
                                //                     'SILO 2500',
                                //                     'SILO 1800'
                                //                 ])
                                //                 ->orderBy('id', 'desc'); // Urutkan berdasarkan ID terbaru
                                //         }
                                //     )
                                //     ->getOptionLabelFromRecordUsing(fn($record) => "{$record->no_spb} - {$record->nama_supir} - {$record->no_lumbung} - {$record->nama_lumbung} - LB {$record->silo} - {$record->netto}")
                                //     ->searchable()
                                //     ->columnSpan(2)
                                //     ->live()
                                //     ->preload()
                                //     ->disabled(fn($get) => !empty($get('id_pembelian'))) // Hide jika pembelian dipilih
                                //     ->afterStateUpdated(function ($state, $set) {
                                //         if (empty($state)) {
                                //             $set('netto_pembelian', 0);
                                //             $set('netto_bersih', 0);
                                //             return;
                                //         }

                                //         $totalNetto = \App\Models\Penjualan::whereIn('id', $state)
                                //             ->whereNotNull('netto')
                                //             ->where('netto', '>', 0)
                                //             ->sum('netto');

                                //         $set('netto_pembelian', $totalNetto ?: 0);
                                //         $set('netto_bersih', $totalNetto ?: 0);
                                //     }),
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->columnSpan(2)
                                    ->placeholder('Otomatis terisi saat memilih no SPB')
                                    ->disabled(),
                                TextInput::make('netto_pembelian')
                                    ->label('Netto Timbangan')
                                    ->reactive()
                                    ->columnSpan(2)
                                    ->disabled(),
                                // ->afterStateHydrated(fn($state, $set) => $set('netto_pembelian', number_format($state, 0, ',', '.')))
                                TextInput::make('netto_bersih')
                                    ->label('Netto Bersih')
                                    ->columnSpan(2)
                                    ->placeholder('Otomatis terisi')
                                    ->afterStateHydrated(function ($state, callable $set, callable $get, $record) {
                                        // Jika data sudah ada di database, gunakan nilai dari record
                                        if ($record && $record->netto_bersih !== null) {
                                            // Parsing nilai dari record dan pastikan format angka
                                            $value = (float) str_replace(['.', ','], ['', '.'], $record->netto_bersih);
                                        } else {
                                            // Jika tidak ada di database, ambil dari netto_pembelian
                                            $value = $get('netto_pembelian');
                                        }

                                        // // Format angka dengan ribuan menggunakan titik dan desimal menggunakan koma
                                        $set('netto_bersih', number_format($value, 0, ',', '.'));
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Hilangkan format sebelum melakukan operasi matematis
                                        $cleanState = (float) str_replace(['.', ','], ['', '.'], $state);
                                        $cleanTungkul = (float) str_replace(['.', ','], ['', '.'], $get('berat_tungkul') ?? 0);

                                        // Lakukan pengurangan
                                        $nettoBersih = max(0, ($cleanState - $cleanTungkul));

                                        // Format kembali hasilnya
                                        $set('netto_bersih', number_format($nettoBersih, 0, ',', '.'));
                                    })
                                    ->reactive()
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('berat_tungkul')
                                    ->label('Berat Tungkul')
                                    ->placeholder('Masukkan berat tungkul jika ada')
                                    ->numeric()
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Ambil nilai dasar dari netto_pembelian jika ada, agar tidak terjadi double subtraction
                                        $nettoPembelian = (float) str_replace(['.', ','], ['', '.'], $get('netto_pembelian') ?? 1);
                                        $beratTungkul = (float) str_replace(['.', ','], ['', '.'], $state);

                                        // Hitung updated netto bersih
                                        $updatedNettoBersih = max(0, $nettoPembelian - $beratTungkul);

                                        // Cek jika netto bersih kosong (nilai di bawah 0 sebelum max())
                                        if (($nettoPembelian - $beratTungkul) < 0) {
                                            // Tampilkan notifikasi
                                            Notification::make()
                                                ->title('Peringatan!')
                                                ->body('Berat tungkul melebihi netto pembelian. Netto bersih telah diset ke 0.')
                                                ->warning()
                                                ->send();
                                        }

                                        // Perbaharui tampilan atau nilai netto_bersih dengan format ribuan
                                        $set('netto_bersih', number_format($updatedNettoBersih, 0, ',', '.'));
                                    }),

                                TextInput::make('total_karung')
                                    ->readOnly()
                                    ->label('Total Karung')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->extraAttributes([
                                        'style' => '
                                            background-color: #f8f9fa;
                                            color: #212529;
                                            cursor: not-allowed;
                                        ',
                                        'onfocus' => 'this.blur()',
                                    ])
                                    ->autocomplete('off')
                                    ->helperText('Keterangan: Klik icon Calculator untuk mengetahui tonase')
                                    ->placeholder('Otomatis terhitung')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Ambil nilai dasar netto: gunakan 'netto_pembelian' sebagai basis
                                        $nettoPembelian = floatval(str_replace(['.', ','], ['', '.'], $get('netto_pembelian') ?? 1));
                                        // Ambil berat tungkul, default 0 jika tidak ada
                                        $beratTungkul = floatval(str_replace(['.', ','], ['', '.'], $get('berat_tungkul') ?? 0));

                                        // Hitung netto bersih yang up to date
                                        $updatedNettoBersih = max(0, $nettoPembelian - $beratTungkul);

                                        $totalKarung = floatval($state ?: 1); // Menghindari pembagian dengan nol

                                        // Jika field total_karung kosong, reset tonase
                                        if (empty($state)) {
                                            foreach (range(1, 6) as $i) {
                                                $set("tonase_$i", null);
                                            }
                                            return;
                                        }
                                        // Hitung nilai tonase untuk tiap unit karung
                                        foreach (range(1, 6) as $i) {
                                            $jumlahKarung = floatval($get("jumlah_karung_$i") ?? 0);
                                            $tonase = (($jumlahKarung * $updatedNettoBersih) / $totalKarung);

                                            // Tentukan presisi desimal, misalnya 0 (bulat)
                                            $precision = 0;
                                            $roundedTonase = round($tonase, $precision);
                                            $formattedTonase = number_format($roundedTonase, $precision, ',', '.');
                                            $set("tonase_$i", $formattedTonase);
                                        }

                                        // Opsi: Jika Anda ingin secara visual juga mengupdate field netto_bersih
                                        $set('netto_bersih', number_format($updatedNettoBersih, 0, ',', '.'));
                                    })
                                    ->suffixAction(
                                        FormAction::make('cekTonase')
                                            ->icon('heroicon-o-calculator')
                                            ->tooltip('Hitung Tonase')
                                            ->action(function ($state, callable $set, $get) {
                                                // Ambil kembali semua nilai yang dibutuhkan
                                                $nettoPembelian = floatval(str_replace(['.', ','], ['', '.'], $get('netto_pembelian') ?? 1));
                                                $beratTungkul   = floatval(str_replace(['.', ','], ['', '.'], $get('berat_tungkul') ?? 0));
                                                $updatedNetto   = max(0, $nettoPembelian - $beratTungkul);
                                                $totalKarung    = floatval($state ?: 0);

                                                // ❗ Cek dulu apakah total karung valid
                                                if ($totalKarung <= 0) {
                                                    Notification::make()
                                                        ->title('Total karung tidak boleh kosong atau nol!')
                                                        ->danger()
                                                        ->send();
                                                    return; // hentikan eksekusi
                                                }

                                                // Hitung dan set ulang tonase_1 … tonase_6
                                                foreach (range(1, 6) as $i) {
                                                    $jumlahKarung = floatval($get("jumlah_karung_$i") ?? 0);
                                                    $tonase       = ($jumlahKarung * $updatedNetto) / $totalKarung;
                                                    $rounded      = round($tonase, 0);
                                                    $set("tonase_$i", number_format($rounded, 0, ',', '.'));
                                                }

                                                // Update field netto_bersih juga kalau perlu
                                                $set('netto_bersih', number_format($updatedNetto, 0, ',', '.'));

                                                Notification::make()
                                                    ->title('Tonase berhasil dihitung!')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(4),
                    ])
                    ->collapsible(),
                Card::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                // Placeholder::make('next_idi')
                                //     ->label('No Sortiran')
                                //     ->content(function ($record) {
                                //         // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                //         if ($record) {
                                //             return $record->no_sortiran;
                                //         }

                                //         // Jika sedang membuat data baru, hitung kode berikutnya
                                //         $nextId = (Sortiran::max('id') ?? 0) + 1;
                                //         return 'S' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                //     }),
                                // // TextInput::make('no_lumbung')
                                // //     ->label('No Lumbung')
                                // //     ->placeholder('Masukkan No Lumbung')
                                // //     ->autocomplete('off')
                                // //     ->required(),
                                Select::make('no_lumbung_basah')
                                    ->label('No Lumbung Basah')
                                    ->placeholder('Pilih No Lumbung')
                                    ->options(
                                        KapasitasLumbungBasah::all()->mapWithKeys(function ($item) {
                                            return [$item->id => $item->no_kapasitas_lumbung . ' - ' . $item->jenis];
                                        })
                                    )
                                    ->searchable() // Biar bisa cari
                                    ->required()
                                    ->disabled(function (callable $get, $record) {
                                        // Jika ini adalah form edit, cek status dari record yang sedang diedit
                                        if ($record && $record->status) {
                                            return in_array($record->status, ['in_dryer', 'completed']);
                                        }

                                        return false;
                                    })
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                            $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                            $formattedSisa = number_format($kapasitaslumbungbasah?->kapasitas_sisa ?? 0, 0, ',', '.');
                                            $set('kapasitas_sisa', $formattedSisa);
                                            $set('kapasitas_total', $kapasitaslumbungbasah?->kapasitas_total ?? 'Tidak ada');
                                            $formattedtotal = number_format($kapasitaslumbungbasah?->kapasitas_total ?? 0, 0, ',', '.');
                                            $set('kapasitas_total', $formattedtotal);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                        $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                        $formattedSisa = number_format($kapasitaslumbungbasah?->kapasitas_sisa ?? 0, 0, ',', '.');
                                        $set('kapasitas_sisa', $formattedSisa);
                                        $set('kapasitas_total', $kapasitaslumbungbasah?->kapasitas_total ?? 'Tidak ada');
                                        $formattedtotal = number_format($kapasitaslumbungbasah?->kapasitas_total ?? 0, 0, ',', '.');
                                        $set('kapasitas_total', $formattedtotal);
                                        // Reset nilai sortirans ketika no_lumbung_basah berubah
                                        $set('sortirans', null);
                                        $set('total_netto', null);
                                    }),
                                TextInput::make('kadar_air')
                                    ->label('Kadar Air')
                                    ->numeric()
                                    // ->required()
                                    ->placeholder('Masukkan kadar air'),
                                Textarea::make('keterangan')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->label('Keterangan')
                                    ->columnSpanFull()
                                    ->placeholder('Masukkan keterangan'),
                            ]),

                        // Grid untuk menyusun field ke kanan
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                // Kualitas Jagung 1
                                Card::make('Jagung ke-1')
                                    ->schema([
                                        Select::make('kualitas_jagung_1') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 1')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false) // Mengunakan dropdown modern
                                            ->required(), // Opsional: Atur default value
                                        FileUpload::make('foto_jagung_1')
                                            ->image()
                                            ->multiple()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_1')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false) // Mengunakan dropdown modern
                                            ->required(), // Opsional: Atur default value
                                        TextInput::make('jumlah_karung_1')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),
                                        // TextInput::make('total_karung')
                                        //     ->label('Jumlah Karung Saat Ini')
                                        //     ->reactive()
                                        //     ->disabled()
                                        //     ->extraAttributes(['style' => 'color: #333; font-weight: bold;']),
                                        TextInput::make('tonase_1')
                                            ->placeholder('Otomatis tonase terisi')
                                            ->label('Tonase')
                                            ->required()
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(), // Satu card per kolom

                                // Kualitas Jagung 2
                                Card::make('Jagung ke-2')
                                    ->schema([
                                        Select::make('kualitas_jagung_2') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_2')
                                            ->image()
                                            ->openable()
                                            ->multiple()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_2')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_2')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),
                                        // TextInput::make('total_karung')
                                        //     ->label('Jumlah Karung Saat Ini')
                                        //     ->reactive()
                                        //     ->disabled()
                                        //     ->extraAttributes(['style' => 'color: #333; font-weight: bold;']),
                                        TextInput::make('tonase_2')
                                            ->placeholder('Otomatis tonase terisi')
                                            ->label('Tonase')
                                            ->required()
                                            ->readOnly(),

                                    ])
                                    ->columnSpan(1)->collapsible(),

                                // Kualitas Jagung 3
                                Card::make('Jagung ke-3')
                                    ->schema([
                                        Select::make('kualitas_jagung_3') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_3')
                                            ->image()
                                            ->openable()
                                            ->multiple()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_3')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_3')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),
                                        // TextInput::make('total_karung')
                                        //     ->label('Jumlah Karung Saat Ini')
                                        //     ->reactive()
                                        //     ->disabled()
                                        //     ->extraAttributes(['style' => 'color: #333; font-weight: bold;']),
                                        TextInput::make('tonase_3')
                                            ->placeholder('Otomatis tonase terisi')
                                            ->label('Tonase')
                                            ->required()
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(),

                                // Kualitas Jagung 4
                                // Card::make('Jagung ke-4')
                                //     ->schema([
                                //         Select::make('kualitas_jagung_4') // Gantilah 'tipe' dengan nama field di database
                                //             ->label('Kualitas Jagung')
                                //             ->options([
                                //                 'JG Kering' => 'Jagung Kering',
                                //                 'JG Basah' => 'Jagung Basah',
                                //                 'JG Kurang Kering' => 'Jagung Kurang Kering',
                                //             ])
                                //             ->placeholder('Pilih Kualitas Jagung')
                                //             ->native(false), // Mengunakan dropdown modern

                                //         FileUpload::make('foto_jagung_4')
                                //             ->image()
                                //             ->openable()
                                //             ->multiple()
                                //             ->imagePreviewHeight(200)
                                //             ->label('Foto Jagung'),
                                //         Select::make('x1_x10_4')
                                //             ->label('X1-X10')
                                //             ->options([
                                //                 'X0' => 'X0',
                                //                 'X1' => 'X1',
                                //                 'X2' => 'X2',
                                //                 'X3' => 'X3',
                                //                 'X4' => 'X4',
                                //                 'X5' => 'X5',
                                //                 'X6' => 'X6',
                                //                 'X7' => 'X7',
                                //                 'X8' => 'X8',
                                //                 'X9' => 'X9',
                                //                 'X10' => 'X10',
                                //             ])
                                //             ->placeholder('Pilih Silang Jagung')
                                //             ->native(false), // Mengunakan dropdown modern

                                //         TextInput::make('jumlah_karung_4')
                                //             ->placeholder('Masukkan Jumlah Karung')
                                //             ->label('Jumlah Karung')
                                //             ->numeric()
                                //             ->reactive()
                                //             ->afterStateUpdated(
                                //                 fn($state, $set, $get) =>
                                //                 $set(
                                //                     'total_karung',
                                //                     (int) ($get('jumlah_karung_1') ?? 0) +
                                //                         (int) ($get('jumlah_karung_2') ?? 0) +
                                //                         (int) ($get('jumlah_karung_3') ?? 0) +
                                //                         (int) ($get('jumlah_karung_4') ?? 0) +
                                //                         (int) ($get('jumlah_karung_5') ?? 0) +
                                //                         (int) ($get('jumlah_karung_6') ?? 0)
                                //                 )
                                //             ),
                                //         // TextInput::make('total_karung')
                                //         //     ->label('Jumlah Karung Saat Ini')
                                //         //     ->reactive()
                                //         //     ->disabled()
                                //         //     ->extraAttributes(['style' => 'color: #333; font-weight: bold;']),
                                //         TextInput::make('tonase_4')
                                //             ->placeholder('Otomatis tonase terisi')
                                //             ->label('Tonase')
                                //             ->required()
                                //             ->readOnly(),
                                //     ])
                                //     ->columnSpan(1)->collapsed(),

                                // // Kualitas Jagung 5
                                // Card::make('Jagung ke-5')
                                //     ->schema([
                                //         Select::make('kualitas_jagung_5') // Gantilah 'tipe' dengan nama field di database
                                //             ->label('Kualitas Jagung')
                                //             ->options([
                                //                 'JG Kering' => 'Jagung Kering',
                                //                 'JG Basah' => 'Jagung Basah',
                                //                 'JG Kurang Kering' => 'Jagung Kurang Kering',
                                //             ])
                                //             ->placeholder('Pilih Kualitas Jagung')
                                //             ->native(false), // Mengunakan dropdown modern

                                //         FileUpload::make('foto_jagung_5')
                                //             ->image()
                                //             ->openable()
                                //             ->multiple()
                                //             ->imagePreviewHeight(200)
                                //             ->label('Foto Jagung'),
                                //         Select::make('x1_x10_5')
                                //             ->label('X1-X10')
                                //             ->options([
                                //                 'X0' => 'X0',
                                //                 'X1' => 'X1',
                                //                 'X2' => 'X2',
                                //                 'X3' => 'X3',
                                //                 'X4' => 'X4',
                                //                 'X5' => 'X5',
                                //                 'X6' => 'X6',
                                //                 'X7' => 'X7',
                                //                 'X8' => 'X8',
                                //                 'X9' => 'X9',
                                //                 'X10' => 'X10',
                                //             ])
                                //             ->placeholder('Pilih Silang Jagung')
                                //             ->native(false), // Mengunakan dropdown modern

                                //         TextInput::make('jumlah_karung_5')
                                //             ->placeholder('Masukkan Jumlah Karung')
                                //             ->label('Jumlah Karung')
                                //             ->numeric()
                                //             ->reactive()
                                //             ->afterStateUpdated(
                                //                 fn($state, $set, $get) =>
                                //                 $set(
                                //                     'total_karung',
                                //                     (int) ($get('jumlah_karung_1') ?? 0) +
                                //                         (int) ($get('jumlah_karung_2') ?? 0) +
                                //                         (int) ($get('jumlah_karung_3') ?? 0) +
                                //                         (int) ($get('jumlah_karung_4') ?? 0) +
                                //                         (int) ($get('jumlah_karung_5') ?? 0) +
                                //                         (int) ($get('jumlah_karung_6') ?? 0)
                                //                 )
                                //             ),
                                //         // TextInput::make('total_karung')
                                //         //     ->label('Jumlah Karung Saat Ini')
                                //         //     ->reactive()
                                //         //     ->disabled()
                                //         //     ->extraAttributes(['style' => 'color: #333; font-weight: bold;']),
                                //         TextInput::make('tonase_5')
                                //             ->placeholder('Otomatis tonase terisi')
                                //             ->label('Tonase')
                                //             ->required()
                                //             ->readOnly(),
                                //     ])
                                //     ->columnSpan(1)->collapsed(),

                                // // Kualitas Jagung 6
                                // Card::make('Jagung ke-6')
                                //     ->schema([
                                //         Select::make('kualitas_jagung_6') // Gantilah 'tipe' dengan nama field di database
                                //             ->label('Kualitas Jagung')
                                //             ->options([
                                //                 'JG Kering' => 'Jagung Kering',
                                //                 'JG Basah' => 'Jagung Basah',
                                //                 'JG Kurang Kering' => 'Jagung Kurang Kering',
                                //             ])
                                //             ->placeholder('Pilih Kualitas Jagung')
                                //             ->native(false), // Mengunakan dropdown modern

                                //         FileUpload::make('foto_jagung_6')
                                //             ->image()
                                //             ->openable()
                                //             ->multiple()
                                //             ->imagePreviewHeight(200)
                                //             ->label('Foto Jagung'),
                                //         Select::make('x1_x10_6')
                                //             ->label('X1-X10')
                                //             ->options([
                                //                 'X0' => 'X0',
                                //                 'X1' => 'X1',
                                //                 'X2' => 'X2',
                                //                 'X3' => 'X3',
                                //                 'X4' => 'X4',
                                //                 'X5' => 'X5',
                                //                 'X6' => 'X6',
                                //                 'X7' => 'X7',
                                //                 'X8' => 'X8',
                                //                 'X9' => 'X9',
                                //                 'X10' => 'X10',
                                //             ])
                                //             ->placeholder('Pilih Silang Jagung')
                                //             ->native(false), // Mengunakan dropdown modern

                                //         TextInput::make('jumlah_karung_6')
                                //             ->placeholder('Masukkan Jumlah Karung')
                                //             ->label('Jumlah Karung')
                                //             ->numeric()
                                //             ->reactive()
                                //             ->afterStateUpdated(
                                //                 fn($state, $set, $get) =>
                                //                 $set(
                                //                     'total_karung',
                                //                     (int) ($get('jumlah_karung_1') ?? 0) +
                                //                         (int) ($get('jumlah_karung_2') ?? 0) +
                                //                         (int) ($get('jumlah_karung_3') ?? 0) +
                                //                         (int) ($get('jumlah_karung_4') ?? 0) +
                                //                         (int) ($get('jumlah_karung_5') ?? 0) +
                                //                         (int) ($get('jumlah_karung_6') ?? 0)
                                //                 )
                                //             ),
                                //         // TextInput::make('total_karung')
                                //         //     ->label('Jumlah Karung Saat Ini')
                                //         //     ->reactive()
                                //         //     ->disabled()
                                //         //     ->extraAttributes(['style' => 'color: #333; font-weight: bold;']),
                                //         TextInput::make('tonase_6')
                                //             ->placeholder('Otomatis tonase terisi')
                                //             ->label('Tonase')
                                //             ->required()
                                //             ->readOnly(),
                                //     ])
                                //     ->columnSpan(1)->collapsed(),
                            ]),
                        // TextInput::make('kadar_air')
                        //     ->label('Kadar Air')
                        //     ->numeric()
                        //     ->placeholder('Masukkan kadar air')
                        //     ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s') // polling ulang setiap 5 detik
            ->defaultPaginationPageOption(5)
            // ->paginated([5, 10, 15])
            ->columns([
                // ToggleColumn::make('statuss')
                //     ->label('Status')
                //     ->extraCellAttributes(fn($record) => $record->cek === 1 ? ['style' => 'opacity: 10;'] : [])
                //     ->alignCenter()
                //     ->onIcon('heroicon-m-check')
                //     ->offIcon('heroicon-m-x-mark')
                //     ->disabled(fn() => !optional(Auth::user())->hasAnyRole(['admin', 'super_admin', 'adminaudit'])),
                // ToggleColumn::make('cek')
                //     ->label('Check')
                //     ->alignCenter()
                //     ->onIcon('heroicon-m-check')
                //     ->offIcon('heroicon-m-x-mark')
                //     ->disabled(fn() => !optional(Auth::user())->hasAnyRole(['admin', 'super_admin', 'adminaudit'])),
                ToggleColumn::make('verif')
                    ->label('Verifikasi')
                    ->alignCenter()
                    ->disabled(fn() => !optional(Auth::user())->hasAnyRole(['super_admin', 'mandor'])),
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
                        'available' => 'Tersedia',
                        'in_dryer' => 'Dalam Dryer',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'available',
                        'danger' => 'in_dryer',
                        'primary' => 'compeleted'
                    ]),
                TextColumn::make('no_sortiran')
                    ->searchable()
                    ->label('No Sortiran')
                    ->alignCenter(),
                // Versi ringkas menggunakan conditional operator
                TextColumn::make('pembelian.no_spb')
                    ->label('No SPB')
                    ->searchable()
                    ->alignCenter()
                    ->default('-'),
                TextColumn::make('pembelian.supplier.nama_supplier')->label('Nama Supplier')
                    ->placeholder('--LANGSIR--')
                    ->searchable(),
                TextColumn::make('netto_bersih')->label('Netto Bersih')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('kapasitaslumbungbasah.no_kapasitas_lumbung')->label('No Lumbung')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('total_karung')->label('Total Karung')
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('kadar_air')
                    ->alignCenter()
                    ->suffix('%'),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->alignCenter(),

                TextColumn::make('user.name')
                    ->label('User')
            ])->defaultSort('no_sortiran', 'desc')
            //buat mengurutkan soritran yang sudah dilihat jadi paling bawah
            // ->modifyQueryUsing(
            //     fn(Builder $query) =>
            //     $query->orderByRaw('cek IS NULL DESC, cek DESC')
            //         ->orderBy('created_at', 'desc')
            // )
            ->filters([
                //
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $user = optional(Auth::user());

                // Jika user adalah admin atau mandor, hanya tampilkan data dengan verif = true
                if ($user && $user->hasAnyRole(['timbangan', 'admin'])) {
                    $query->where('verif', true);
                }

                return $query;
            })
            ->actions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->visible(function ($record) {
                        // Versi lebih aman - cek relasi pembelian ada dan no_spb tidak kosong
                        return isset($record->pembelian) &&
                            !empty($record->pembelian->no_spb);
                    })
                    ->action(function ($record, $livewire) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        // Cek apakah user memiliki role 'admin' atau 'adminaudit'
                        if ($user->hasAnyRole(['admin', 'adminaudit'])) {
                            $record->update(['cek' => true]);
                        }
                        return redirect(self::getUrl('view-sortiran', ['record' => $record->id]));
                    }),
            ], position: ActionsPosition::BeforeColumns)
            // ->bulkActions([
            //     // Tables\Actions\BulkActionGroup::make([
            //     Tables\Actions\DeleteBulkAction::make(),
            //     // ]),
            // ])
            ->headerActions([
                ExportAction::make()->exporter(SortiranExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(SortiranExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->defaultSort('id', 'desc')
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
            ])
            ->recordClasses(function ($record) {
                return match ($record->cek) {
                    1 => 'opacity-70',     // status = null → transparansi 50%
                    default => '',
                };
            });
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
            'view-sortiran' => Pages\ViewSortiran::route('/{record}/view-sortiran'),
        ];
    }
}
