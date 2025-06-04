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
use Illuminate\Support\Facades\DB;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\DryerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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

    public static ?string $label = 'Daftar Dryer ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dryer')
                    ->schema([
                        Card::make()
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
                                        $set('lumbung_tujuan', null);
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

                                Select::make('lumbung_tujuan')
                                    ->label('Lumbung Tujuan')
                                    ->options(function (callable $get) {
                                        $selectedId = $get('id_kapasitas_dryer');
                                        if (!$selectedId) {
                                            // Jika belum pilih kapasitas dryer, tampilkan semua opsi
                                            return [];
                                        }

                                        // Cari nama kapasitas dryer berdasarkan ID yang dipilih
                                        $namaKapasitas = KapasitasDryer::where('id', $selectedId)->value('nama_kapasitas_dryer');

                                        if (in_array($namaKapasitas, ['A', 'B', 'D'])) {
                                            return [
                                                'A' => 'A',
                                                'B' => 'B',
                                                'C' => 'C',
                                                'D' => 'D',
                                                'E' => 'E',
                                            ];
                                        }
                                        if (in_array($namaKapasitas, ['A1', 'A2'])) {
                                            return [
                                                'F' => 'F',
                                                'G' => 'G',
                                                'H' => 'H',
                                                'I' => 'I',
                                            ];
                                        }
                                        if ($namaKapasitas === 'LSU') {
                                            return [
                                                'S' => 'SILO BESAR',
                                                'F' => 'F',
                                                'G' => 'G',
                                                'H' => 'H',
                                                'I' => 'I',
                                            ];
                                        }

                                        // Default opsi lengkap
                                        return [
                                            'A' => 'A',
                                            'B' => 'B',
                                            'C' => 'C',
                                            'D' => 'D',
                                            'E' => 'E',
                                            'F' => 'F',
                                            'G' => 'G',
                                            'H' => 'H',
                                            'I' => 'I',
                                        ];
                                    })
                                    ->placeholder('Pilih lumbung kering')
                                    ->native(false),

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
                    ])->collapsible(),
                // Card::make()
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 // Select Lumbung 1
                //                 Select::make('id_lumbung_1')
                //                     ->label('No Lumbung 1')
                //                     ->placeholder('Pilih No Lumbung 1')
                //                     ->options(function (callable $get) {
                //                         $currentId = $get('id_lumbung_1'); // nilai yang dipilih (jika ada)

                //                         // Ambil semua field timbangan jual (dari 1 sampai 6)
                //                         $usedSpbIds = Dryer::query()
                //                             ->get()
                //                             ->flatMap(function ($record) {
                //                                 return [
                //                                     $record->id_lumbung_1,
                //                                     $record->id_lumbung_2,
                //                                     $record->id_lumbung_3,
                //                                     $record->id_lumbung_4,
                //                                 ];
                //                             })
                //                             ->filter()   // Hilangkan nilai null
                //                             ->unique()   // Pastikan tidak ada duplikasi
                //                             ->toArray();

                //                         // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                //                         $lumbungQuery = LumbungBasah::query();
                //                         if ($currentId) {
                //                             $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                //                                 $query->where('id', $currentId)
                //                                     ->orWhereNotIn('id', $usedSpbIds);
                //                             });
                //                         } else {
                //                             $lumbungQuery->whereNotIn('id', $usedSpbIds);
                //                         }

                //                         return $lumbungQuery
                //                             ->latest()
                //                             ->with('kapasitaslumbungbasah')
                //                             ->get()
                //                             ->mapWithKeys(function ($item) {
                //                                 return [
                //                                     $item->id => $item->no_lb .
                //                                         ' - ' . $item->total_netto .
                //                                         ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                //                                 ];
                //                             })
                //                             ->toArray();
                //                     })
                //                     ->searchable()
                //                     ->required()
                //                     ->reactive()
                //                     ->afterStateHydrated(function ($state, callable $set) {
                //                         if ($state) {
                //                             $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                //                             $set('total_netto_1', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_1', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_1', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }
                //                     })
                //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                         if (empty($state)) {
                //                             $set('total_netto_1', null);
                //                             $set('no_lumbung_1', null);
                //                             $set('jenis_jagung_1', null);
                //                         } else {
                //                             $selectedLumbungs = [
                //                                 $get('id_lumbung_1'),
                //                                 $get('id_lumbung_2'),
                //                                 $get('id_lumbung_3'),
                //                                 $get('id_lumbung_4'),
                //                             ];
                //                             $occurrences = array_count_values(array_filter($selectedLumbungs));
                //                             if ($occurrences[$state] > 1) {
                //                                 Notification::make()
                //                                     ->title('Peringatan!')
                //                                     ->body('No lumbung tidak boleh sama.')
                //                                     ->danger()
                //                                     ->send();

                //                                 $set('id_lumbung_1', null);
                //                                 return;
                //                             }

                //                             $lumbung = LumbungBasah::find($state);
                //                             $set('total_netto_1', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_1', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_1', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }

                //                         // Hitung total netto dari semua lumbung
                //                         $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                //                             + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                //                         $set('total_netto', $totalNetto);

                //                         // Hitung kapasitas sisa setelah dikurangi total netto
                //                         $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                //                         $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                //                         $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                //                         $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                //                     }),

                //                 // Select Lumbung 2
                //                 Select::make('id_lumbung_2')
                //                     ->label('No Lumbung 2')
                //                     ->placeholder('Pilih No Lumbung 2')
                //                     ->options(function (callable $get) {
                //                         $currentId = $get('id_lumbung_2'); // nilai yang dipilih (jika ada)

                //                         // Ambil semua field timbangan jual (dari 1 sampai 6)
                //                         $usedSpbIds = Dryer::query()
                //                             ->get()
                //                             ->flatMap(function ($record) {
                //                                 return [
                //                                     $record->id_lumbung_1,
                //                                     $record->id_lumbung_2,
                //                                     $record->id_lumbung_3,
                //                                     $record->id_lumbung_4,
                //                                 ];
                //                             })
                //                             ->filter()   // Hilangkan nilai null
                //                             ->unique()   // Pastikan tidak ada duplikasi
                //                             ->toArray();

                //                         // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                //                         $lumbungQuery = LumbungBasah::query();
                //                         if ($currentId) {
                //                             $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                //                                 $query->where('id', $currentId)
                //                                     ->orWhereNotIn('id', $usedSpbIds);
                //                             });
                //                         } else {
                //                             $lumbungQuery->whereNotIn('id', $usedSpbIds);
                //                         }

                //                         return $lumbungQuery
                //                             ->latest()
                //                             ->with('kapasitaslumbungbasah')
                //                             ->get()
                //                             ->mapWithKeys(function ($item) {
                //                                 return [
                //                                     $item->id => $item->no_lb .
                //                                         ' - ' . $item->total_netto .
                //                                         ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                //                                 ];
                //                             })
                //                             ->toArray();
                //                     })
                //                     ->searchable()
                //                     ->reactive()
                //                     ->afterStateHydrated(function ($state, callable $set) {
                //                         if ($state) {
                //                             $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                //                             $set('total_netto_2', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_2', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_2', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }
                //                     })
                //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                         if (empty($state)) {
                //                             $set('total_netto_2', null);
                //                             $set('no_lumbung_2', null);
                //                             $set('jenis_jagung_2', null);
                //                         } else {
                //                             $selectedLumbungs = [
                //                                 $get('id_lumbung_1'),
                //                                 $get('id_lumbung_2'),
                //                                 $get('id_lumbung_3'),
                //                                 $get('id_lumbung_4'),
                //                             ];
                //                             $occurrences = array_count_values(array_filter($selectedLumbungs));
                //                             if ($occurrences[$state] > 1) {
                //                                 Notification::make()
                //                                     ->title('Peringatan!')
                //                                     ->body('No lumbung tidak boleh sama.')
                //                                     ->danger()
                //                                     ->send();

                //                                 $set('id_lumbung_2', null);
                //                                 return;
                //                             }

                //                             $lumbung = LumbungBasah::find($state);
                //                             $set('total_netto_2', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_2', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_2', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }

                //                         // Hitung total netto dari semua lumbung
                //                         $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                //                             + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                //                         $set('total_netto', $totalNetto);

                //                         // Hitung kapasitas sisa setelah dikurangi total netto
                //                         $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                //                         $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                //                         $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                //                         $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                //                     }),

                //                 // Select Lumbung 3
                //                 Select::make('id_lumbung_3')
                //                     ->label('No Lumbung 3')
                //                     ->placeholder('Pilih No Lumbung 3')
                //                     ->options(function (callable $get) {
                //                         $currentId = $get('id_lumbung_3'); // nilai yang dipilih (jika ada)

                //                         // Ambil semua field timbangan jual (dari 1 sampai 6)
                //                         $usedSpbIds = Dryer::query()
                //                             ->get()
                //                             ->flatMap(function ($record) {
                //                                 return [
                //                                     $record->id_lumbung_1,
                //                                     $record->id_lumbung_2,
                //                                     $record->id_lumbung_3,
                //                                     $record->id_lumbung_4,
                //                                 ];
                //                             })
                //                             ->filter()   // Hilangkan nilai null
                //                             ->unique()   // Pastikan tidak ada duplikasi
                //                             ->toArray();

                //                         // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                //                         $lumbungQuery = LumbungBasah::query();
                //                         if ($currentId) {
                //                             $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                //                                 $query->where('id', $currentId)
                //                                     ->orWhereNotIn('id', $usedSpbIds);
                //                             });
                //                         } else {
                //                             $lumbungQuery->whereNotIn('id', $usedSpbIds);
                //                         }

                //                         return $lumbungQuery
                //                             ->latest()
                //                             ->with('kapasitaslumbungbasah')
                //                             ->get()
                //                             ->mapWithKeys(function ($item) {
                //                                 return [
                //                                     $item->id => $item->no_lb .
                //                                         ' - ' . $item->total_netto .
                //                                         ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                //                                 ];
                //                             })
                //                             ->toArray();
                //                     })
                //                     ->searchable()
                //                     ->reactive()
                //                     ->afterStateHydrated(function ($state, callable $set) {
                //                         if ($state) {
                //                             $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                //                             $set('total_netto_3', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_3', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_3', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }
                //                     })
                //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                         if (empty($state)) {
                //                             $set('total_netto_3', null);
                //                             $set('no_lumbung_3', null);
                //                             $set('jenis_jagung_3', null);
                //                         } else {
                //                             $selectedLumbungs = [
                //                                 $get('id_lumbung_1'),
                //                                 $get('id_lumbung_2'),
                //                                 $get('id_lumbung_3'),
                //                                 $get('id_lumbung_4'),
                //                             ];
                //                             $occurrences = array_count_values(array_filter($selectedLumbungs));
                //                             if ($occurrences[$state] > 1) {
                //                                 Notification::make()
                //                                     ->title('Peringatan!')
                //                                     ->body('No lumbung tidak boleh sama.')
                //                                     ->danger()
                //                                     ->send();

                //                                 $set('id_lumbung_3', null);
                //                                 return;
                //                             }

                //                             $lumbung = LumbungBasah::find($state);
                //                             $set('total_netto_3', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_3', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_3', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }

                //                         // Hitung total netto dari semua lumbung
                //                         $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                //                             + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                //                         $set('total_netto', $totalNetto);

                //                         // Hitung kapasitas sisa setelah dikurangi total netto
                //                         $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                //                         $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                //                         $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                //                         $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                //                     }),

                //                 // Select Lumbung 4
                //                 Select::make('id_lumbung_4')
                //                     ->label('No Lumbung 4')
                //                     ->placeholder('Pilih No Lumbung 4')
                //                     ->options(function (callable $get) {
                //                         $currentId = $get('id_lumbung_4'); // nilai yang dipilih (jika ada)

                //                         // Ambil semua field timbangan jual (dari 1 sampai 6)
                //                         $usedSpbIds = Dryer::query()
                //                             ->get()
                //                             ->flatMap(function ($record) {
                //                                 return [
                //                                     $record->id_lumbung_1,
                //                                     $record->id_lumbung_2,
                //                                     $record->id_lumbung_3,
                //                                     $record->id_lumbung_4,
                //                                 ];
                //                             })
                //                             ->filter()   // Hilangkan nilai null
                //                             ->unique()   // Pastikan tidak ada duplikasi
                //                             ->toArray();

                //                         // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                //                         $lumbungQuery = LumbungBasah::query();
                //                         if ($currentId) {
                //                             $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                //                                 $query->where('id', $currentId)
                //                                     ->orWhereNotIn('id', $usedSpbIds);
                //                             });
                //                         } else {
                //                             $lumbungQuery->whereNotIn('id', $usedSpbIds);
                //                         }

                //                         return $lumbungQuery
                //                             ->latest()
                //                             ->with('kapasitaslumbungbasah')
                //                             ->get()
                //                             ->mapWithKeys(function ($item) {
                //                                 return [
                //                                     $item->id => $item->no_lb .
                //                                         ' - ' . $item->total_netto .
                //                                         ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                //                                 ];
                //                             })
                //                             ->toArray();
                //                     })
                //                     ->searchable()
                //                     ->reactive()
                //                     ->afterStateHydrated(function ($state, callable $set) {
                //                         if ($state) {
                //                             $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                //                             $set('total_netto_4', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_4', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_4', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }
                //                     })
                //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                         if (empty($state)) {
                //                             $set('total_netto_4', null);
                //                             $set('no_lumbung_4', null);
                //                             $set('jenis_jagung_4', null);
                //                         } else {
                //                             $selectedLumbungs = [
                //                                 $get('id_lumbung_1'),
                //                                 $get('id_lumbung_2'),
                //                                 $get('id_lumbung_3'),
                //                                 $get('id_lumbung_4'),
                //                             ];
                //                             $occurrences = array_count_values(array_filter($selectedLumbungs));
                //                             if ($occurrences[$state] > 1) {
                //                                 Notification::make()
                //                                     ->title('Peringatan!')
                //                                     ->body('No lumbung tidak boleh sama.')
                //                                     ->danger()
                //                                     ->send();

                //                                 $set('id_lumbung_4', null);
                //                                 return;
                //                             }

                //                             $lumbung = LumbungBasah::find($state);
                //                             $set('total_netto_4', $lumbung?->total_netto ?? 0);
                //                             $set('no_lumbung_4', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                //                             $set('jenis_jagung_4', $lumbung?->jenis_jagung ?? 'Tidak ada');
                //                         }

                //                         // Hitung total netto dari semua lumbung
                //                         $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                //                             + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                //                         $set('total_netto', $totalNetto);

                //                         // Hitung kapasitas sisa setelah dikurangi total netto
                //                         $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                //                         $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                //                         $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                //                         $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                //                     }),

                //             ])
                //     ]),
                Card::make()
                    ->schema([
                        // Select::make('timbanganTrontons')
                        //     ->label('Laporan Penjualan')
                        //     ->multiple()
                        //     ->relationship('timbanganTrontons', 'kode') // ganti dengan field yang ingin ditampilkan
                        //     ->preload()
                        //     ->getOptionLabelFromRecordUsing(function ($record) {
                        //         $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : 'N/A';
                        //         return $record->kode . ' - ' . $noBk . ' - ' . ($record->penjualan1->nama_supir ?? '') . ' - ' . $record->total_netto;
                        //     }),
                        // // Select::make('sortirans')
                        //     ->label('Sortirans')
                        //     ->multiple()
                        //     ->relationship('sortirans', 'no_sortiran') // ganti dengan field yang ingin ditampilkan
                        //     ->preload()
                        //     ->getOptionLabelFromRecordUsing(function ($record) {
                        //         return $record->no_sortiran . ' - ' . ' - ' . ($record->pembelian->plat_polisi ?? '') . ' - ' . $record->pembelian->supplier->nama_supplier;
                        //     }),
                        // Tambahkan Select untuk filter kapasitas lumbung di atas Select sortiran
                        // SOLUSI 1: Preservasi data yang sudah dipilih dalam afterStateUpdated
                        Select::make('filter_kapasitas_lumbung')
                            ->native(false)
                            ->label('Filter Kapasitas Lumbung')
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
                                $noBk = $record->pembelian ? $record->pembelian->plat_polisi : 'N/A';
                                $supplier = $record->pembelian ? $record->pembelian->supplier->nama_supplier : 'N/A';
                                $kapasitas = $record->kapasitaslumbungbasah ? $record->kapasitaslumbungbasah->no_kapasitas_lumbung : 'N/A';
                                return $kapasitas . ' - ' .  $record->pembelian->no_spb . ' - ' . $noBk . ' - ' . $supplier . ' - ' . $record->netto_bersih;
                            })
                            ->disabled(fn($get) => !$get('filter_kapasitas_lumbung'))
                            ->helperText('Pilih kapasitas lumbung terlebih dahulu untuk menampilkan sortiran')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                // Mendapatkan nilai kapasitas sisa awal
                                $noDryer = $get('id_kapasitas_dryer');
                                $kapasitasAwal = 0;
                                $record = $livewire->getRecord();
                                $isEditMode = $record !== null;

                                if ($isEditMode) {
                                    // Dapatkan sortiran sebelumnya yang sudah terkait dengan record ini
                                    $oldSortiranIds = $record->sortirans()->pluck('sortirans.id')->toArray();
                                    $oldSortirans = \App\Models\Sortiran::whereIn('id', $oldSortiranIds)->get();

                                    foreach ($oldSortirans as $oldSortiran) {
                                        $oldKapasitas = \App\Models\KapasitasLumbungBasah::find($oldSortiran->no_lumbung_basah);
                                        if ($oldKapasitas) {
                                            $oldNettoValue = (int) preg_replace('/[^0-9]/', '', $oldSortiran->netto_bersih);
                                            // Rollback kapasitas lama sebelum perubahan
                                            $oldKapasitas->decrement('kapasitas_sisa', $oldNettoValue);
                                        }
                                    }
                                }

                                // Mendapatkan semua sortiran yang dipilih saat ini
                                $selectedSortirans = \App\Models\Sortiran::whereIn('id', $state)->get();

                                foreach ($selectedSortirans as $sortiran) {
                                    // Update status menjadi TRUE
                                    // $sortiran->update(['status' => 1]);

                                    // Mengembalikan kapasitas ke lumbung basah
                                    $kapasitas = \App\Models\KapasitasLumbungBasah::find($sortiran->no_lumbung_basah);
                                    if ($kapasitas) {
                                        $nettoValue = (int) preg_replace('/[^0-9]/', '', $sortiran->netto_bersih);
                                        $kapasitas->increment('kapasitas_sisa', $nettoValue);
                                    }
                                }

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

                                // DEBUG: Log nilai untuk memastikan
                                \Log::info('Debug Kapasitas:', [
                                    'kapasitas_awal' => $kapasitasAwal,
                                    'total_netto' => $totalNetto,
                                    'kapasitas_sisa_baru' => $kapasitasSisaBaru,
                                    'type_kapasitas_sisa' => gettype($kapasitasSisaBaru)
                                ]);

                                // PERBAIKAN: Pastikan benar-benar integer
                                $set('kapasitas_sisa_akhir', (int) $kapasitasSisaBaru);

                                // Tampilkan notifikasi
                                $notificationMessage = $isEditMode ?
                                    "Kapasitas diperbarui (mode edit)" :
                                    "Kapasitas diperbarui";
                            })
                            ->preload()
                            ->searchable(),

                        // PERBAIKAN: Field total_netto dengan format display tapi simpan sebagai integer
                        // TextInput::make('total_netto')
                        //     ->label('Kapasitas Terpakai')
                        //     ->placeholder('Otomatis terhitung')
                        //     ->readOnly()
                        //     ->formatStateUsing(function ($state) {
                        //         // Format untuk display dengan pemisah ribuan
                        //         return $state ? number_format($state, 0, ',', '.') : '0';
                        //     })
                        //     ->dehydrateStateUsing(function ($state) {
                        //         // Hapus format sebelum disimpan ke database
                        //         return (int) str_replace(['.', ','], '', $state ?? '0');
                        //     }),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                TextColumn::make('no_dryer')
                    ->label('No Dryer')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('kapasitasdryer.nama_kapasitas_dryer')
                    ->label('Nama Dryer')
                    ->alignCenter(),
                TextColumn::make('lumbung_tujuan')
                    ->label('Tujuan')
                    ->searchable()
                    ->alignCenter(),
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
                TextColumn::make('sortirans')
                    ->alignCenter()
                    ->label('No SPB')
                    ->formatStateUsing(function ($record) {
                        $text = $record->sortirans->map(function ($sortiran) {
                            return $sortiran->pembelian->no_spb;
                        })->implode(', ');

                        // Batasi jumlah karakter dan tambahkan ellipsis
                        return \Illuminate\Support\Str::limit($text, 30, '...');
                    })
                    // Tambahkan ini untuk batasi lebar kolom dengan CSS
                    ->extraAttributes(['class' => 'max-w-md truncate'])
                    // Tambahkan tooltip untuk melihat konten lengkap saat hover
                    ->tooltip(function ($record) {
                        return $record->sortirans->map(function ($sortiran) {
                            return $sortiran->pembelian->no_spb;
                        })->implode(', ');
                    }),
                TextColumn::make('total_netto')
                    ->alignCenter()
                    ->label('Total Netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
            ])
            ->actions([
                Tables\Actions\Action::make('view-dryer')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-dryer", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->filters([
                //
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
            'index' => Pages\ListDryers::route('/'),
            'create' => Pages\CreateDryer::route('/create'),
            'edit' => Pages\EditDryer::route('/{record}/edit'),
            'view-dryer' => Pages\ViewDryer::route('/{record}/view-dryer'),
        ];
    }
}
