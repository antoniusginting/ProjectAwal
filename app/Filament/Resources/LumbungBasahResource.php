<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Sortiran;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungBasah;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use App\Models\KapasitasLumbungBasah;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LumbungBasahResource\Pages;
use App\Filament\Resources\LumbungBasahResource\RelationManagers;

class LumbungBasahResource extends Resource
{

    public static function getNavigationSort(): int
    {
        return 2; // Ini akan muncul di atas
    }
    protected static ?string $model = LumbungBasah::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    public static ?string $label = 'Daftar Lumbung Basah ';

    protected static ?string $navigationLabel = 'Lumbung Basah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Lumbung')
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No LB')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_lb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (LumbungBasah::max('id') ?? 0) + 1;
                                        return 'LB' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('total_netto')
                                    ->label('Total netto')
                                    ->readOnly()
                                    ->placeholder('Otomatis Terhitung')
                                    ->reactive()
                                    ->numeric(),
                                Select::make('no_lumbung_basah')
                                    ->label('No Lumbung Basah')
                                    ->placeholder('Pilih No Lumbung')
                                    ->options(KapasitasLumbungBasah::pluck('no_kapasitas_lumbung', 'id'))
                                    ->searchable() // Biar bisa cari
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn($record) => $record !== null)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                            $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                            $formattedSisa = number_format($kapasitaslumbungbasah?->kapasitas_sisa ?? 0, 0, ',', '.');
                                            $set('kapasitas_sisa', $formattedSisa);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                        $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                        $formattedSisa = number_format($kapasitaslumbungbasah?->kapasitas_sisa ?? 0, 0, ',', '.');
                                        $set('kapasitas_sisa', $formattedSisa);
                                    }),
                                TextInput::make('jenis_jagung')
                                    ->label('Jenis Jagung')
                                    ->placeholder('Masukkan Jenis Jagung'),

                                TextInput::make('kapasitas_sisa')
                                    ->label('Kapasitas Sisa')
                                    ->placeholder('Pilih terlebih dahulu no lumbung basah')
                                    ->disabled(),

                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                                    ->disabled(), // Tidak bisa diedit
                            ])->columns(2)
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                //Card No sortiran1
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_1')
                                            ->label('No Sortiran 1')
                                            ->placeholder('Pilih No Sortiran 1')
                                            ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran1 = Sortiran::with('pembelian')->find($state);

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran1?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_1_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_1', $sortiran1?->no_lumbung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    // Reset semua nilai terkait jika Select dibatalkan
                                                    $set('netto_1', null);
                                                    $set('netto_1_display', null);
                                                    $set('no_lumbung_1', null);
                                                } else {

                                                    // Cek apakah sortiran sudah dipilih sebelumnya di field lain
                                                    $selectedSortirans = [
                                                        $get('id_sortiran_1'),
                                                        $get('id_sortiran_2'),
                                                        $get('id_sortiran_3'),
                                                        $get('id_sortiran_4'),
                                                        $get('id_sortiran_5'),
                                                        $get('id_sortiran_6'),
                                                    ];
                                                    // Hitung jumlah kemunculan dari ID yang dipilih
                                                    $occurrences = array_count_values(array_filter($selectedSortirans));

                                                    if ($occurrences[$state] > 1) {
                                                        // Tampilkan notifikasi jika ada duplikasi
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No Sortiran tidak boleh sama.')
                                                            ->danger()
                                                            ->send();
                                        
                                                        // Reset kembali jika terdeteksi duplikasi
                                                        $set('id_sortiran_1', null);
                                                        return;
                                                    }

                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran1 = Sortiran::with('pembelian')->find($state);

                                                    // Set netto hanya untuk sortiran pertama
                                                    $set('netto_1', $sortiran1?->pembelian?->netto ?? 'Tidak ada');

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran1?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_1_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_1', $sortiran1?->no_lumbung ?? 'Tidak ada');
                                                }

                                                // Langsung hitung total netto setelah perubahan
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),

                                        TextInput::make('netto_1_display')
                                            ->label('Netto Pembelian 1')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 1')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_1')
                                            ->label('No lumbung 1')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 1')
                                            ->disabled(),
                                    ])->columnSpan(1),
                                //Card No sortiran 2
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_2')
                                            ->label('No Sortiran 2')
                                            ->placeholder('Pilih No Sortiran 2')
                                            ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran2 = Sortiran::with('pembelian')->find($state);

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran2?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_2_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_2', $sortiran2?->no_lumbung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    // Reset semua nilai terkait jika Select dibatalkan
                                                    $set('netto_2', null);
                                                    $set('netto_2_display', null);
                                                    $set('no_lumbung_2', null);
                                                } else {

                                                     // Cek apakah sortiran sudah dipilih sebelumnya di field lain
                                                     $selectedSortirans = [
                                                        $get('id_sortiran_1'),
                                                        $get('id_sortiran_2'),
                                                        $get('id_sortiran_3'),
                                                        $get('id_sortiran_4'),
                                                        $get('id_sortiran_5'),
                                                        $get('id_sortiran_6'),
                                                    ];
                                                    // Hitung jumlah kemunculan dari ID yang dipilih
                                                    $occurrences = array_count_values(array_filter($selectedSortirans));

                                                    if ($occurrences[$state] > 1) {
                                                        // Tampilkan notifikasi jika ada duplikasi
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No Sortiran tidak boleh sama.')
                                                            ->danger()
                                                            ->send();
                                        
                                                        // Reset kembali jika terdeteksi duplikasi
                                                        $set('id_sortiran_2', null);
                                                        return;
                                                    }
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran2 = Sortiran::with('pembelian')->find($state);

                                                    // Set netto hanya untuk sortiran pertama
                                                    $set('netto_2', $sortiran2?->pembelian?->netto ?? 'Tidak ada');

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran2?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_2_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_2', $sortiran2?->no_lumbung ?? 'Tidak ada');
                                                }

                                                // Langsung hitung total netto setelah perubahan
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),

                                        TextInput::make('netto_2_display')
                                            ->label('Netto Pembelian 2')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 2')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_2')
                                            ->label('No lumbung 2')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 2')
                                            ->disabled(),

                                    ])->columnSpan(1),
                                //Card No sortiran 3
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_3')
                                            ->label('No Sortiran 3')
                                            ->placeholder('Pilih No Sortiran 3')
                                            ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran3 = Sortiran::with('pembelian')->find($state);

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran3?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_3_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_3', $sortiran3?->no_lumbung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    // Reset semua nilai terkait jika Select dibatalkan
                                                    $set('netto_3', null);
                                                    $set('netto_3_display', null);
                                                    $set('no_lumbung_3', null);
                                                } else {

                                                     // Cek apakah sortiran sudah dipilih sebelumnya di field lain
                                                     $selectedSortirans = [
                                                        $get('id_sortiran_1'),
                                                        $get('id_sortiran_2'),
                                                        $get('id_sortiran_3'),
                                                        $get('id_sortiran_4'),
                                                        $get('id_sortiran_5'),
                                                        $get('id_sortiran_6'),
                                                    ];
                                                    // Hitung jumlah kemunculan dari ID yang dipilih
                                                    $occurrences = array_count_values(array_filter($selectedSortirans));

                                                    if ($occurrences[$state] > 1) {
                                                        // Tampilkan notifikasi jika ada duplikasi
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No Sortiran tidak boleh sama.')
                                                            ->danger()
                                                            ->send();
                                        
                                                        // Reset kembali jika terdeteksi duplikasi
                                                        $set('id_sortiran_3', null);
                                                        return;
                                                    }
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran3 = Sortiran::with('pembelian')->find($state);

                                                    // Set netto hanya untuk sortiran pertama
                                                    $set('netto_3', $sortiran3?->pembelian?->netto ?? 'Tidak ada');

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran3?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_3_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_3', $sortiran3?->no_lumbung ?? 'Tidak ada');
                                                }

                                                // Langsung hitung total netto setelah perubahan
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),
                                        TextInput::make('netto_3_display')
                                            ->label('Netto Pembelian 3')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 3')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_3')
                                            ->label('No lumbung 3')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 3')
                                            ->disabled(),
                                    ])->columnSpan(1),
                                //Card No Sortiran 4
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_4')
                                            ->label('No Sortiran 4')
                                            ->placeholder('Pilih No Sortiran 4')
                                            ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran4 = Sortiran::with('pembelian')->find($state);

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran4?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_4_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_4', $sortiran4?->no_lumbung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    // Reset semua nilai terkait jika Select dibatalkan
                                                    $set('netto_4', null);
                                                    $set('netto_4_display', null);
                                                    $set('no_lumbung_4', null);
                                                } else {

                                                     // Cek apakah sortiran sudah dipilih sebelumnya di field lain
                                                     $selectedSortirans = [
                                                        $get('id_sortiran_1'),
                                                        $get('id_sortiran_2'),
                                                        $get('id_sortiran_3'),
                                                        $get('id_sortiran_4'),
                                                        $get('id_sortiran_5'),
                                                        $get('id_sortiran_6'),
                                                    ];
                                                    // Hitung jumlah kemunculan dari ID yang dipilih
                                                    $occurrences = array_count_values(array_filter($selectedSortirans));

                                                    if ($occurrences[$state] > 1) {
                                                        // Tampilkan notifikasi jika ada duplikasi
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No Sortiran tidak boleh sama.')
                                                            ->danger()
                                                            ->send();
                                        
                                                        // Reset kembali jika terdeteksi duplikasi
                                                        $set('id_sortiran_4', null);
                                                        return;
                                                    }
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran4 = Sortiran::with('pembelian')->find($state);

                                                    // Set netto hanya untuk sortiran pertama
                                                    $set('netto_4', $sortiran4?->pembelian?->netto ?? 'Tidak ada');

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran4?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_4_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_4', $sortiran4?->no_lumbung ?? 'Tidak ada');
                                                }

                                                // Langsung hitung total netto setelah perubahan
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),

                                        TextInput::make('netto_4_display')
                                            ->label('Netto Pembelian 4')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 4')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_4')
                                            ->label('No lumbung 4')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 4')
                                            ->disabled(),

                                    ])->columnSpan(1),
                                //Card No sortiran 5
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_5')
                                            ->label('No Sortiran 5')
                                            ->placeholder('Pilih No Sortiran 5')
                                            ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran5 = Sortiran::with('pembelian')->find($state);

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran5?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_5_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_5', $sortiran5?->no_lumbung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    // Reset semua nilai terkait jika Select dibatalkan
                                                    $set('netto_5', null);
                                                    $set('netto_5_display', null);
                                                    $set('no_lumbung_5', null);
                                                } else {

                                                     // Cek apakah sortiran sudah dipilih sebelumnya di field lain
                                                     $selectedSortirans = [
                                                        $get('id_sortiran_1'),
                                                        $get('id_sortiran_2'),
                                                        $get('id_sortiran_3'),
                                                        $get('id_sortiran_4'),
                                                        $get('id_sortiran_5'),
                                                        $get('id_sortiran_6'),
                                                    ];
                                                    // Hitung jumlah kemunculan dari ID yang dipilih
                                                    $occurrences = array_count_values(array_filter($selectedSortirans));

                                                    if ($occurrences[$state] > 1) {
                                                        // Tampilkan notifikasi jika ada duplikasi
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No Sortiran tidak boleh sama.')
                                                            ->danger()
                                                            ->send();
                                        
                                                        // Reset kembali jika terdeteksi duplikasi
                                                        $set('id_sortiran_5', null);
                                                        return;
                                                    }
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran5 = Sortiran::with('pembelian')->find($state);

                                                    // Set netto hanya untuk sortiran pertama
                                                    $set('netto_5', $sortiran5?->pembelian?->netto ?? 'Tidak ada');

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran5?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_5_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_5', $sortiran5?->no_lumbung ?? 'Tidak ada');
                                                }

                                                // Langsung hitung total netto setelah perubahan
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),

                                        TextInput::make('netto_5_display')
                                            ->label('Netto Pembelian 5')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 5')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_5')
                                            ->label('No lumbung 5')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 5')
                                            ->disabled(),
                                    ])->columnSpan(1),
                                //Card No Sortiran 6
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_6')
                                            ->label('No Sortiran 6')
                                            ->placeholder('Pilih No Sortiran 6')
                                            ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran6 = Sortiran::with('pembelian')->find($state);

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran6?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_6_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_6', $sortiran6?->no_lumbung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    // Reset semua nilai terkait jika Select dibatalkan
                                                    $set('netto_6', null);
                                                    $set('netto_6_display', null);
                                                    $set('no_lumbung_6', null);
                                                } else {

                                                     // Cek apakah sortiran sudah dipilih sebelumnya di field lain
                                                     $selectedSortirans = [
                                                        $get('id_sortiran_1'),
                                                        $get('id_sortiran_2'),
                                                        $get('id_sortiran_3'),
                                                        $get('id_sortiran_4'),
                                                        $get('id_sortiran_5'),
                                                        $get('id_sortiran_6'),
                                                    ];
                                                    // Hitung jumlah kemunculan dari ID yang dipilih
                                                    $occurrences = array_count_values(array_filter($selectedSortirans));

                                                    if ($occurrences[$state] > 1) {
                                                        // Tampilkan notifikasi jika ada duplikasi
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No Sortiran tidak boleh sama.')
                                                            ->danger()
                                                            ->send();
                                        
                                                        // Reset kembali jika terdeteksi duplikasi
                                                        $set('id_sortiran_6', null);
                                                        return;
                                                    }
                                                    // Ambil data sortiran berdasarkan ID yang dipilih
                                                    $sortiran6 = Sortiran::with('pembelian')->find($state);

                                                    // Set netto hanya untuk sortiran pertama
                                                    $set('netto_6', $sortiran6?->pembelian?->netto ?? 'Tidak ada');

                                                    // Ambil netto dan format angka dengan titik ribuan
                                                    $nettoFormatted = number_format($sortiran6?->pembelian?->netto ?? 0, 0, ',', '.');

                                                    // Set nilai ke TextInput yang hanya untuk tampilan
                                                    $set('netto_6_display', $nettoFormatted);

                                                    // Ambil nomor lumbung
                                                    $set('no_lumbung_6', $sortiran6?->no_lumbung ?? 'Tidak ada');
                                                }

                                                // Langsung hitung total netto setelah perubahan
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),

                                        TextInput::make('netto_6_display')
                                            ->label('Netto Pembelian 6')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 6')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_6')
                                            ->label('No lumbung 6')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 6')
                                            ->disabled(),
                                    ])->columnSpan(1),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_lb')->label('No LB'),
                TextColumn::make('no_lumbung_basah')->label('No Lumbung Basah')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('jenis_jagung')->label('Jenis Jagung')
                    ->searchable()
                    ->alignCenter(),

                //Jagung 1
                TextColumn::make('sortiran1.no_sortiran')
                    ->label('Sortiran 1'),

                //Jagung 2
                TextColumn::make('sortiran2.no_sortiran')
                    ->label('Sortiran 2'),

                //Jagung 3
                TextColumn::make('sortiran3.no_sortiran')
                    ->label('Sortiran 3'),

                //Jagung 4
                TextColumn::make('sortiran4.no_sortiran')
                    ->label('Sortiran 4'),

                //Jagung 5
                TextColumn::make('sortiran5.no_sortiran')
                    ->label('Sortiran 5'),

                //Jagung 6
                TextColumn::make('sortiran6.no_sortiran')
                    ->label('Sortiran 6'),

                TextColumn::make('total_netto')
                    ->label('Total Netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('status')
                    ->label('Status'),
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
            'index' => Pages\ListLumbungBasahs::route('/'),
            'create' => Pages\CreateLumbungBasah::route('/create'),
            'edit' => Pages\EditLumbungBasah::route('/{record}/edit'),
        ];
    }
}
