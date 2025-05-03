<?php

namespace App\Filament\Resources;
// namespace BezhanSalleh\FilamentShield\Resources;
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
use Filament\Forms\Components\Actions\Action as FormAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LumbungBasahResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\LumbungBasahResource\RelationManagers;
use Dom\Text;

class LumbungBasahResource extends Resource implements HasShieldPermissions
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
        return 2; // Ini akan muncul di atas
    }
    protected static ?string $model = LumbungBasah::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

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
                                    ->columnSpan(2)
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_lb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (LumbungBasah::max('id') ?? 0) + 1;
                                        return 'LB' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
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
                                    }),
                                TextInput::make('kapasitas_total')
                                    ->label('Kapasitas Total')
                                    ->placeholder('Pilih terlebih dahulu no lumbung basah')
                                    ->disabled(),
                                TextInput::make('tujuan')
                                    ->label('Tujuan')
                                    ->placeholder('Masukkan Tujuan'),
                                TextInput::make('total_netto')
                                    ->label('Total Netto')
                                    ->readOnly()
                                    ->reactive()
                                    ->formatStateUsing(function ($state) {
                                        // Format untuk tampilan (dari integer ke string terformat)
                                        if (is_numeric($state)) {
                                            return number_format((int)$state, 0, ',', '.');
                                        }
                                        return $state;
                                    })
                                    ->dehydrateStateUsing(function ($state) {
                                        // Konversi ke integer saat menyimpan ke database
                                        return (int)str_replace(['.', ','], ['', '.'], $state);
                                    })
                                    ->suffixAction(
                                        FormAction::make('cekTonase')
                                            ->icon('heroicon-o-calculator')
                                            ->tooltip('Hitung Kapasitas')
                                            ->action(function ($get, $set) {
                                                // Mendapatkan nilai kapasitas_sisa dan total_netto
                                                $kapasitasSisa = (int)str_replace(['.', ','], ['', '.'], $get('kapasitas_sisa'));
                                                $totalNetto = (int)str_replace(['.', ','], ['', '.'], $get('total_netto'));

                                                // Hitung kapasitas sisa baru
                                                $kapasitasSisaBaru = $kapasitasSisa - $totalNetto;

                                                // Format angka untuk tampilan
                                                $formattedKapasitasSisaBaru = number_format($kapasitasSisaBaru, 0, ',', '.');

                                                // Set nilai kapasitas_sisa yang baru
                                                $set('kapasitas_sisa', $formattedKapasitasSisaBaru);

                                                // Tampilkan notifikasi
                                                Notification::make()
                                                    ->title('Kapasitas dihitung ulang')
                                                    ->body("Kapasitas sisa baru: {$formattedKapasitasSisaBaru}")
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                                    ->disabled(), // Tidak bisa diedit
                                TextInput::make('kapasitas_sisa')
                                    ->label('Kapasitas Sisa')
                                    ->placeholder('Pilih terlebih dahulu no lumbung basah')
                                    ->disabled(),
                            ])->columns(2)
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Select::make('sortirans')
                            ->label('Sortiran')
                            ->multiple()
                            ->relationship('sortirans', 'no_sortiran')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $noBk = $record->pembelian ? $record->pembelian->plat_polisi : 'N/A';
                                return $record->no_sortiran . ' - ' . $noBk;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (empty($state)) {
                                    $set('total_netto', 0);
                                    return;
                                }

                                // Ambil data sortiran berdasarkan ID yang dipilih
                                $sortirans = \App\Models\Sortiran::whereIn('id', $state)->get();

                                // Hitung total dengan mengkonversi varchar ke integer
                                $total = 0;
                                foreach ($sortirans as $sortiran) {
                                    // Konversi varchar ke integer (hapus karakter non-numerik jika ada)
                                    $nettoValue = (int) preg_replace('/[^0-9]/', '', $sortiran->netto_bersih);
                                    $total += $nettoValue;
                                }

                                // Set nilai ke field total_netto dengan format yang diinginkan
                                $set('total_netto', number_format($total, 0, ',', '.'));
                            })
                            ->preload()
                            ->searchable(),
                    ]),
                // Card::make()
                //     ->schema([
                //         Grid::make(3)
                //             ->schema([
                //                 //Card No sortiran1
                //                 Card::make('Sortiran ke-1')
                //                     ->schema([
                //                         Select::make('id_sortiran_1')
                //                             ->label('No Sortiran')
                //                             ->placeholder('Pilih No Sortiran 1')
                //                             ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                //                             ->searchable()
                //                             ->required()
                //                             ->reactive()
                //                             //->disabled(fn($record) => $record !== null)
                //                             ->afterStateHydrated(function ($state, callable $set) {
                //                                 if ($state) {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran1 = Sortiran::with('pembelian')->find($state);

                //                                     // Ambil netto dan format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($sortiran1?->pembelian?->netto ?? 0, 0, ',', '.');

                //                                     // Set nilai ke TextInput yang hanya untuk tampilan
                //                                     $set('netto_1_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_1', $sortiran1?->no_lumbung ?? 'Tidak ada');
                //                                 }
                //                             })
                //                             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                                 if (empty($state)) {
                //                                     // Reset nilai jika Select dikosongkan
                //                                     $set('netto_1', null);
                //                                     $set('netto_1_display', null);
                //                                     $set('no_lumbung_1', null);
                //                                 } else {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran1 = Sortiran::with('pembelian')->find($state);

                //                                     // Pastikan netto diambil dari sortiran yang benar
                //                                     $netto = $sortiran1?->pembelian?->netto ?? 0;

                //                                     // Simpan netto agar bisa digunakan dalam perhitungan
                //                                     $set('netto_1', $netto);

                //                                     // Format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($netto, 0, ',', '.');

                //                                     // Set nilai ke TextInput tampilan
                //                                     $set('netto_1_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_1', $sortiran1?->no_lumbung ?? 'Tidak ada');

                //                                     // 🔥 Validasi Duplikasi
                //                                     $selectedSortiran = [
                //                                         $get('id_sortiran_1'),
                //                                         $get('id_sortiran_2'),
                //                                         $get('id_sortiran_3'),
                //                                         $get('id_sortiran_4'),
                //                                         $get('id_sortiran_5'),
                //                                         $get('id_sortiran_6'),
                //                                     ];

                //                                     // Jika nilai duplikat ditemukan
                //                                     if (count(array_filter($selectedSortiran)) !== count(array_unique(array_filter($selectedSortiran)))) {
                //                                         // Reset nilai yang baru diinputkan
                //                                         $set('id_sortiran_1', null);
                //                                         $set('netto_1_display', null);
                //                                         $set('no_lumbung_1', null);

                //                                         // Memunculkan notifikasi
                //                                         Notification::make()
                //                                             ->title('Gagal!')
                //                                             ->danger()
                //                                             ->body('Sortiran tidak boleh duplikat. Pilih nomor lain.')
                //                                             ->send();
                //                                     }
                //                                 }

                //                                 // 🔥 Hitung ulang total netto dengan mengambil semua nilai netto yang sudah tersimpan
                //                                 $totalNetto =
                //                                     intval($get('netto_1') ?? 0) +
                //                                     intval($get('netto_2') ?? 0) +
                //                                     intval($get('netto_3') ?? 0) +
                //                                     intval($get('netto_4') ?? 0) +
                //                                     intval($get('netto_5') ?? 0) +
                //                                     intval($get('netto_6') ?? 0);

                //                                 $set('total_netto', $totalNetto);
                //                             }),

                //                         TextInput::make('netto_1_display')
                //                             ->label('Netto Pembelian')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 1')
                //                             ->disabled(),

                //                         TextInput::make('no_lumbung_1')
                //                             ->label('No lumbung')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 1')
                //                             ->disabled(),
                //                     ])->columnSpan(1)
                //                     ->collapsible(),
                //                 //Card No sortiran 2
                //                 Card::make('Sortiran ke-2')
                //                     ->schema([
                //                         Select::make('id_sortiran_2')
                //                             ->label('No Sortiran')
                //                             ->placeholder('Pilih No Sortiran 2')
                //                             ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                //                             ->searchable()
                //                             ->reactive()
                //                             //->disabled(fn($record) => $record !== null)
                //                             ->afterStateHydrated(function ($state, callable $set) {
                //                                 if ($state) {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran2 = Sortiran::with('pembelian')->find($state);

                //                                     // Ambil netto dan format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($sortiran2?->pembelian?->netto ?? 0, 0, ',', '.');

                //                                     // Set nilai ke TextInput yang hanya untuk tampilan
                //                                     $set('netto_2_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_2', $sortiran2?->no_lumbung ?? 'Tidak ada');
                //                                 }
                //                             })
                //                             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                                 if (empty($state)) {
                //                                     // Reset nilai jika Select dikosongkan
                //                                     $set('netto_2', null);
                //                                     $set('netto_2_display', null);
                //                                     $set('no_lumbung_2', null);
                //                                 } else {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran2 = Sortiran::with('pembelian')->find($state);

                //                                     // Pastikan netto diambil dari sortiran yang benar
                //                                     $netto = $sortiran2?->pembelian?->netto ?? 0;

                //                                     // Simpan netto agar bisa digunakan dalam perhitungan
                //                                     $set('netto_2', $netto);

                //                                     // Format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($netto, 0, ',', '.');

                //                                     // Set nilai ke TextInput tampilan
                //                                     $set('netto_2_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_2', $sortiran2?->no_lumbung ?? 'Tidak ada');

                //                                     // 🔥 Validasi Duplikasi
                //                                     $selectedSortiran = [
                //                                         $get('id_sortiran_1'),
                //                                         $get('id_sortiran_2'),
                //                                         $get('id_sortiran_3'),
                //                                         $get('id_sortiran_4'),
                //                                         $get('id_sortiran_5'),
                //                                         $get('id_sortiran_6'),
                //                                     ];

                //                                     // Jika nilai duplikat ditemukan
                //                                     if (count(array_filter($selectedSortiran)) !== count(array_unique(array_filter($selectedSortiran)))) {
                //                                         // Reset nilai yang baru diinputkan
                //                                         $set('id_sortiran_2', null);
                //                                         $set('netto_2_display', null);
                //                                         $set('no_lumbung_2', null);

                //                                         // Memunculkan notifikasi
                //                                         Notification::make()
                //                                             ->title('Gagal!')
                //                                             ->danger()
                //                                             ->body('Sortiran tidak boleh duplikat. Pilih nomor lain.')
                //                                             ->send();
                //                                     }
                //                                 }

                //                                 // 🔥 Hitung ulang total netto dengan mengambil semua nilai netto yang sudah tersimpan
                //                                 $totalNetto =
                //                                     intval($get('netto_1') ?? 0) +
                //                                     intval($get('netto_2') ?? 0) +
                //                                     intval($get('netto_3') ?? 0) +
                //                                     intval($get('netto_4') ?? 0) +
                //                                     intval($get('netto_5') ?? 0) +
                //                                     intval($get('netto_6') ?? 0);

                //                                 $set('total_netto', $totalNetto);
                //                             }),

                //                         TextInput::make('netto_2_display')
                //                             ->label('Netto Pembelian')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 2')
                //                             ->disabled(),
                //                         TextInput::make('no_lumbung_2')
                //                             ->label('No lumbung')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 2')
                //                             ->disabled(),

                //                     ])->columnSpan(1)
                //                     ->collapsible(),
                //                 //Card No sortiran 3
                //                 Card::make('Sortiran ke-3')
                //                     ->schema([
                //                         Select::make('id_sortiran_3')
                //                             ->label('No Sortiran')
                //                             ->placeholder('Pilih No Sortiran 3')
                //                             ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                //                             ->searchable()
                //                             ->reactive()
                //                             //->disabled(fn($record) => $record !== null)
                //                             ->afterStateHydrated(function ($state, callable $set) {
                //                                 if ($state) {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran3 = Sortiran::with('pembelian')->find($state);

                //                                     // Ambil netto dan format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($sortiran3?->pembelian?->netto ?? 0, 0, ',', '.');

                //                                     // Set nilai ke TextInput yang hanya untuk tampilan
                //                                     $set('netto_3_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_3', $sortiran3?->no_lumbung ?? 'Tidak ada');
                //                                 }
                //                             })
                //                             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                                 if (empty($state)) {
                //                                     // Reset nilai jika Select dikosongkan
                //                                     $set('netto_3', 0);
                //                                     $set('netto_3_display', '');
                //                                     $set('no_lumbung_3', '');
                //                                 } else {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran3 = Sortiran::with('pembelian')->find($state);

                //                                     // Pastikan netto diambil dari sortiran yang benar
                //                                     $netto = $sortiran3?->pembelian?->netto ?? 0;

                //                                     // Simpan netto agar bisa digunakan dalam perhitungan
                //                                     $set('netto_3', $netto);

                //                                     // Format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($netto, 0, ',', '.');

                //                                     // Set nilai ke TextInput tampilan
                //                                     $set('netto_3_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_3', $sortiran3?->no_lumbung ?? 'Tidak ada');

                //                                     // 🔥 Validasi Duplikasi
                //                                     $selectedSortiran = [
                //                                         $get('id_sortiran_1'),
                //                                         $get('id_sortiran_2'),
                //                                         $get('id_sortiran_3'),
                //                                         $get('id_sortiran_4'),
                //                                         $get('id_sortiran_5'),
                //                                         $get('id_sortiran_6'),
                //                                     ];

                //                                     // Jika nilai duplikat ditemukan
                //                                     if (count(array_filter($selectedSortiran)) !== count(array_unique(array_filter($selectedSortiran)))) {
                //                                         // Reset nilai yang baru diinputkan
                //                                         $set('id_sortiran_3', null);
                //                                         $set('netto_3_display', null);
                //                                         $set('no_lumbung_3', null);

                //                                         // Memunculkan notifikasi
                //                                         Notification::make()
                //                                             ->title('Gagal!')
                //                                             ->danger()
                //                                             ->body('Sortiran tidak boleh duplikat. Pilih nomor lain.')
                //                                             ->send();
                //                                     }
                //                                 }

                //                                 // 🔥 Hitung ulang total netto dengan mengambil semua nilai netto yang sudah tersimpan
                //                                 $totalNetto =
                //                                     intval($get('netto_1') ?? 0) +
                //                                     intval($get('netto_2') ?? 0) +
                //                                     intval($get('netto_3') ?? 0) +
                //                                     intval($get('netto_4') ?? 0) +
                //                                     intval($get('netto_5') ?? 0) +
                //                                     intval($get('netto_6') ?? 0);

                //                                 $set('total_netto', $totalNetto);
                //                             }),
                //                         TextInput::make('netto_3_display')
                //                             ->label('Netto Pembelian')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 3')
                //                             ->disabled(),
                //                         TextInput::make('no_lumbung_3')
                //                             ->label('No lumbung')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 3')
                //                             ->disabled(),
                //                     ])->columnSpan(1)
                //                     ->collapsible(),
                //                 //Card No Sortiran 4
                //                 Card::make('Sortiran ke-4')
                //                     ->schema([
                //                         Select::make('id_sortiran_4')
                //                             ->label('No Sortiran')
                //                             ->placeholder('Pilih No Sortiran 4')
                //                             ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                //                             ->searchable()
                //                             ->reactive()
                //                             //->disabled(fn($record) => $record !== null)
                //                             ->afterStateHydrated(function ($state, callable $set) {
                //                                 if ($state) {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran4 = Sortiran::with('pembelian')->find($state);

                //                                     // Ambil netto dan format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($sortiran4?->pembelian?->netto ?? 0, 0, ',', '.');

                //                                     // Set nilai ke TextInput yang hanya untuk tampilan
                //                                     $set('netto_4_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_4', $sortiran4?->no_lumbung ?? 'Tidak ada');
                //                                 }
                //                             })
                //                             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                                 if (empty($state)) {
                //                                     // Reset nilai jika Select dikosongkan
                //                                     $set('netto_4', 0);
                //                                     $set('netto_4_display', '');
                //                                     $set('no_lumbung_4', '');
                //                                 } else {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran4 = Sortiran::with('pembelian')->find($state);

                //                                     // Pastikan netto diambil dari sortiran yang benar
                //                                     $netto = $sortiran4?->pembelian?->netto ?? 0;

                //                                     // Simpan netto agar bisa digunakan dalam perhitungan
                //                                     $set('netto_4', $netto);

                //                                     // Format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($netto, 0, ',', '.');

                //                                     // Set nilai ke TextInput tampilan
                //                                     $set('netto_4_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_4', $sortiran1?->no_lumbung ?? 'Tidak ada');

                //                                     // 🔥 Validasi Duplikasi
                //                                     $selectedSortiran = [
                //                                         $get('id_sortiran_1'),
                //                                         $get('id_sortiran_2'),
                //                                         $get('id_sortiran_3'),
                //                                         $get('id_sortiran_4'),
                //                                         $get('id_sortiran_5'),
                //                                         $get('id_sortiran_6'),
                //                                     ];

                //                                     // Jika nilai duplikat ditemukan
                //                                     if (count(array_filter($selectedSortiran)) !== count(array_unique(array_filter($selectedSortiran)))) {
                //                                         // Reset nilai yang baru diinputkan
                //                                         $set('id_sortiran_4', null);
                //                                         $set('netto_4_display', null);
                //                                         $set('no_lumbung_4', null);

                //                                         // Memunculkan notifikasi
                //                                         Notification::make()
                //                                             ->title('Gagal!')
                //                                             ->danger()
                //                                             ->body('Sortiran tidak boleh duplikat. Pilih nomor lain.')
                //                                             ->send();
                //                                     }
                //                                 }

                //                                 // 🔥 Hitung ulang total netto dengan mengambil semua nilai netto yang sudah tersimpan
                //                                 $totalNetto =
                //                                     intval($get('netto_1') ?? 0) +
                //                                     intval($get('netto_2') ?? 0) +
                //                                     intval($get('netto_3') ?? 0) +
                //                                     intval($get('netto_4') ?? 0) +
                //                                     intval($get('netto_5') ?? 0) +
                //                                     intval($get('netto_6') ?? 0);

                //                                 $set('total_netto', $totalNetto);
                //                             }),

                //                         TextInput::make('netto_4_display')
                //                             ->label('Netto Pembelian')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 4')
                //                             ->disabled(),
                //                         TextInput::make('no_lumbung_4')
                //                             ->label('No lumbung')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 4')
                //                             ->disabled(),

                //                     ])->columnSpan(1)
                //                     ->collapsible(),
                //                 //Card No sortiran 5
                //                 Card::make('Sortiran ke-5')
                //                     ->schema([
                //                         Select::make('id_sortiran_5')
                //                             ->label('No Sortiran')
                //                             ->placeholder('Pilih No Sortiran 5')
                //                             ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                //                             ->searchable()
                //                             ->reactive()
                //                             //->disabled(fn($record) => $record !== null)
                //                             ->afterStateHydrated(function ($state, callable $set) {
                //                                 if ($state) {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran5 = Sortiran::with('pembelian')->find($state);

                //                                     // Ambil netto dan format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($sortiran5?->pembelian?->netto ?? 0, 0, ',', '.');

                //                                     // Set nilai ke TextInput yang hanya untuk tampilan
                //                                     $set('netto_5_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_5', $sortiran5?->no_lumbung ?? 'Tidak ada');
                //                                 }
                //                             })
                //                             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                                 if (empty($state)) {
                //                                     // Reset nilai jika Select dikosongkan
                //                                     $set('netto_5', 0);
                //                                     $set('netto_5_display', '');
                //                                     $set('no_lumbung_5', '');
                //                                 } else {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran5 = Sortiran::with('pembelian')->find($state);

                //                                     // Pastikan netto diambil dari sortiran yang benar
                //                                     $netto = $sortiran5?->pembelian?->netto ?? 0;

                //                                     // Simpan netto agar bisa digunakan dalam perhitungan
                //                                     $set('netto_5', $netto);

                //                                     // Format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($netto, 0, ',', '.');

                //                                     // Set nilai ke TextInput tampilan
                //                                     $set('netto_5_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_5', $sortiran5?->no_lumbung ?? 'Tidak ada');

                //                                     // 🔥 Validasi Duplikasi
                //                                     $selectedSortiran = [
                //                                         $get('id_sortiran_1'),
                //                                         $get('id_sortiran_2'),
                //                                         $get('id_sortiran_3'),
                //                                         $get('id_sortiran_4'),
                //                                         $get('id_sortiran_5'),
                //                                         $get('id_sortiran_6'),
                //                                     ];

                //                                     // Jika nilai duplikat ditemukan
                //                                     if (count(array_filter($selectedSortiran)) !== count(array_unique(array_filter($selectedSortiran)))) {
                //                                         // Reset nilai yang baru diinputkan
                //                                         $set('id_sortiran_5', null);
                //                                         $set('netto_5_display', null);
                //                                         $set('no_lumbung_5', null);

                //                                         // Memunculkan notifikasi
                //                                         Notification::make()
                //                                             ->title('Gagal!')
                //                                             ->danger()
                //                                             ->body('Sortiran tidak boleh duplikat. Pilih nomor lain.')
                //                                             ->send();
                //                                     }
                //                                 }

                //                                 // 🔥 Hitung ulang total netto dengan mengambil semua nilai netto yang sudah tersimpan
                //                                 $totalNetto =
                //                                     intval($get('netto_1') ?? 0) +
                //                                     intval($get('netto_2') ?? 0) +
                //                                     intval($get('netto_3') ?? 0) +
                //                                     intval($get('netto_4') ?? 0) +
                //                                     intval($get('netto_5') ?? 0) +
                //                                     intval($get('netto_6') ?? 0);

                //                                 $set('total_netto', $totalNetto);
                //                             }),

                //                         TextInput::make('netto_5_display')
                //                             ->label('Netto Pembelian')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 5')
                //                             ->disabled(),

                //                         TextInput::make('no_lumbung_5')
                //                             ->label('No lumbung')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 5')
                //                             ->disabled(),
                //                     ])->columnSpan(1)
                //                     ->collapsible(),
                //                 //Card No Sortiran 6
                //                 Card::make('Sortiran ke-6')
                //                     ->schema([
                //                         Select::make('id_sortiran_6')
                //                             ->label('No Sortiran')
                //                             ->placeholder('Pilih No Sortiran 6')
                //                             ->options(Sortiran::latest()->pluck('no_sortiran', 'id')->toArray())
                //                             ->searchable()
                //                             ->reactive()
                //                             //->disabled(fn($record) => $record !== null)
                //                             ->afterStateHydrated(function ($state, callable $set) {
                //                                 if ($state) {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran6 = Sortiran::with('pembelian')->find($state);

                //                                     // Ambil netto dan format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($sortiran6?->pembelian?->netto ?? 0, 0, ',', '.');

                //                                     // Set nilai ke TextInput yang hanya untuk tampilan
                //                                     $set('netto_6_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_6', $sortiran6?->no_lumbung ?? 'Tidak ada');
                //                                 }
                //                             })
                //                             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                                 if (empty($state)) {
                //                                     // Reset nilai jika Select dikosongkan
                //                                     $set('netto_6', 0);
                //                                     $set('netto_6_display', '');
                //                                     $set('no_lumbung_6', '');
                //                                 } else {
                //                                     // Ambil data sortiran berdasarkan ID yang dipilih
                //                                     $sortiran6 = Sortiran::with('pembelian')->find($state);

                //                                     // Pastikan netto diambil dari sortiran yang benar
                //                                     $netto = $sortiran6?->pembelian?->netto ?? 0;

                //                                     // Simpan netto agar bisa digunakan dalam perhitungan
                //                                     $set('netto_6', $netto);

                //                                     // Format angka dengan titik ribuan
                //                                     $nettoFormatted = number_format($netto, 0, ',', '.');

                //                                     // Set nilai ke TextInput tampilan
                //                                     $set('netto_6_display', $nettoFormatted);

                //                                     // Ambil nomor lumbung
                //                                     $set('no_lumbung_6', $sortiran6?->no_lumbung ?? 'Tidak ada');

                //                                     // 🔥 Validasi Duplikasi
                //                                     $selectedSortiran = [
                //                                         $get('id_sortiran_1'),
                //                                         $get('id_sortiran_2'),
                //                                         $get('id_sortiran_3'),
                //                                         $get('id_sortiran_4'),
                //                                         $get('id_sortiran_5'),
                //                                         $get('id_sortiran_6'),
                //                                     ];

                //                                     // Jika nilai duplikat ditemukan
                //                                     if (count(array_filter($selectedSortiran)) !== count(array_unique(array_filter($selectedSortiran)))) {
                //                                         // Reset nilai yang baru diinputkan
                //                                         $set('id_sortiran_6', null);
                //                                         $set('netto_6_display', null);
                //                                         $set('no_lumbung_6', null);

                //                                         // Memunculkan notifikasi
                //                                         Notification::make()
                //                                             ->title('Gagal!')
                //                                             ->danger()
                //                                             ->body('Sortiran tidak boleh duplikat. Pilih nomor lain.')
                //                                             ->send();
                //                                     }
                //                                 }

                //                                 // 🔥 Hitung ulang total netto dengan mengambil semua nilai netto yang sudah tersimpan
                //                                 $totalNetto =
                //                                     intval($get('netto_1') ?? 0) +
                //                                     intval($get('netto_2') ?? 0) +
                //                                     intval($get('netto_3') ?? 0) +
                //                                     intval($get('netto_4') ?? 0) +
                //                                     intval($get('netto_5') ?? 0) +
                //                                     intval($get('netto_6') ?? 0);

                //                                 $set('total_netto', $totalNetto);
                //                             }),

                //                         TextInput::make('netto_6_display')
                //                             ->label('Netto Pembelian')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 6')
                //                             ->disabled(),
                //                         TextInput::make('no_lumbung_6')
                //                             ->label('No lumbung')
                //                             ->placeholder('Pilih terlebih dahulu no sortiran 6')
                //                             ->disabled(),
                //                     ])->columnSpan(1)
                //                     ->collapsible(),
                //             ])
                //     ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_lb')->label('No LB'),
                TextColumn::make('no_lumbung_basah')->label('No Lumbung Basah')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('sortirans')
                    ->alignCenter()
                    ->label('No Sortiran')
                    ->formatStateUsing(function ($record) {
                        return $record->sortirans->map(function ($sortiran) {
                            return $sortiran->no_sortiran . ' - ' . $sortiran->netto_bersih . ' (' . number_format($sortiran->pivot->tonase, 0, ',', '.') . ' kg)';
                        })->implode(', ');
                    })
                    ->wrap()
                    ->limit(50),
                TextColumn::make('tujuan')
                    ->label('Tujuan'),
                TextColumn::make('total_netto')
                    ->alignCenter()
                    ->label('Total Netto'),
                TextColumn::make('status')
                    ->label('Status'),
            ])
            ->defaultSort('no_lb', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListLumbungBasahs::route('/'),
            'create' => Pages\CreateLumbungBasah::route('/create'),
            'edit' => Pages\EditLumbungBasah::route('/{record}/edit'),
        ];
    }
}
