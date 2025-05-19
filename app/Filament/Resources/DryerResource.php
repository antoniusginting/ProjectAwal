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
    public static function canAccess(): bool
    {
        return false; // Menyembunyikan resource dari sidebar
    }
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
                                            return $record->no_lb;
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
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    // ->required()
                                    ->placeholder('Masukkan jenis jagung'),
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
                            ->visible(fn($livewire) => $livewire instanceof CreateRecord),

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
                            ->visible(fn($livewire) => $livewire instanceof EditRecord),
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Select Lumbung 1
                                Select::make('id_lumbung_1')
                                    ->label('No Lumbung 1')
                                    ->placeholder('Pilih No Lumbung 1')
                                    ->options(function (callable $get) {
                                        $currentId = $get('id_lumbung_1'); // nilai yang dipilih (jika ada)

                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                        $usedSpbIds = Dryer::query()
                                            ->get()
                                            ->flatMap(function ($record) {
                                                return [
                                                    $record->id_lumbung_1,
                                                    $record->id_lumbung_2,
                                                    $record->id_lumbung_3,
                                                    $record->id_lumbung_4,
                                                ];
                                            })
                                            ->filter()   // Hilangkan nilai null
                                            ->unique()   // Pastikan tidak ada duplikasi
                                            ->toArray();

                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                        $lumbungQuery = LumbungBasah::query();
                                        if ($currentId) {
                                            $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                $query->where('id', $currentId)
                                                    ->orWhereNotIn('id', $usedSpbIds);
                                            });
                                        } else {
                                            $lumbungQuery->whereNotIn('id', $usedSpbIds);
                                        }

                                        return $lumbungQuery
                                            ->latest()
                                            ->with('kapasitaslumbungbasah')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->no_lb .
                                                        ' - ' . $item->total_netto .
                                                        ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                                            $set('total_netto_1', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_1', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_1', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (empty($state)) {
                                            $set('total_netto_1', null);
                                            $set('no_lumbung_1', null);
                                            $set('jenis_jagung_1', null);
                                        } else {
                                            $selectedLumbungs = [
                                                $get('id_lumbung_1'),
                                                $get('id_lumbung_2'),
                                                $get('id_lumbung_3'),
                                                $get('id_lumbung_4'),
                                            ];
                                            $occurrences = array_count_values(array_filter($selectedLumbungs));
                                            if ($occurrences[$state] > 1) {
                                                Notification::make()
                                                    ->title('Peringatan!')
                                                    ->body('No lumbung tidak boleh sama.')
                                                    ->danger()
                                                    ->send();

                                                $set('id_lumbung_1', null);
                                                return;
                                            }

                                            $lumbung = LumbungBasah::find($state);
                                            $set('total_netto_1', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_1', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_1', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }

                                        // Hitung total netto dari semua lumbung
                                        $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                            + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                        $set('total_netto', $totalNetto);

                                        // Hitung kapasitas sisa setelah dikurangi total netto
                                        $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                                        $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                                        $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                                        $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                                    }),

                                // Select Lumbung 2
                                Select::make('id_lumbung_2')
                                    ->label('No Lumbung 2')
                                    ->placeholder('Pilih No Lumbung 2')
                                    ->options(function (callable $get) {
                                        $currentId = $get('id_lumbung_2'); // nilai yang dipilih (jika ada)

                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                        $usedSpbIds = Dryer::query()
                                            ->get()
                                            ->flatMap(function ($record) {
                                                return [
                                                    $record->id_lumbung_1,
                                                    $record->id_lumbung_2,
                                                    $record->id_lumbung_3,
                                                    $record->id_lumbung_4,
                                                ];
                                            })
                                            ->filter()   // Hilangkan nilai null
                                            ->unique()   // Pastikan tidak ada duplikasi
                                            ->toArray();

                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                        $lumbungQuery = LumbungBasah::query();
                                        if ($currentId) {
                                            $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                $query->where('id', $currentId)
                                                    ->orWhereNotIn('id', $usedSpbIds);
                                            });
                                        } else {
                                            $lumbungQuery->whereNotIn('id', $usedSpbIds);
                                        }

                                        return $lumbungQuery
                                            ->latest()
                                            ->with('kapasitaslumbungbasah')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->no_lb .
                                                        ' - ' . $item->total_netto .
                                                        ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                                            $set('total_netto_2', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_2', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_2', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (empty($state)) {
                                            $set('total_netto_2', null);
                                            $set('no_lumbung_2', null);
                                            $set('jenis_jagung_2', null);
                                        } else {
                                            $selectedLumbungs = [
                                                $get('id_lumbung_1'),
                                                $get('id_lumbung_2'),
                                                $get('id_lumbung_3'),
                                                $get('id_lumbung_4'),
                                            ];
                                            $occurrences = array_count_values(array_filter($selectedLumbungs));
                                            if ($occurrences[$state] > 1) {
                                                Notification::make()
                                                    ->title('Peringatan!')
                                                    ->body('No lumbung tidak boleh sama.')
                                                    ->danger()
                                                    ->send();

                                                $set('id_lumbung_2', null);
                                                return;
                                            }

                                            $lumbung = LumbungBasah::find($state);
                                            $set('total_netto_2', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_2', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_2', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }

                                        // Hitung total netto dari semua lumbung
                                        $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                            + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                        $set('total_netto', $totalNetto);

                                        // Hitung kapasitas sisa setelah dikurangi total netto
                                        $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                                        $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                                        $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                                        $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                                    }),

                                // Select Lumbung 3
                                Select::make('id_lumbung_3')
                                    ->label('No Lumbung 3')
                                    ->placeholder('Pilih No Lumbung 3')
                                    ->options(function (callable $get) {
                                        $currentId = $get('id_lumbung_3'); // nilai yang dipilih (jika ada)

                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                        $usedSpbIds = Dryer::query()
                                            ->get()
                                            ->flatMap(function ($record) {
                                                return [
                                                    $record->id_lumbung_1,
                                                    $record->id_lumbung_2,
                                                    $record->id_lumbung_3,
                                                    $record->id_lumbung_4,
                                                ];
                                            })
                                            ->filter()   // Hilangkan nilai null
                                            ->unique()   // Pastikan tidak ada duplikasi
                                            ->toArray();

                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                        $lumbungQuery = LumbungBasah::query();
                                        if ($currentId) {
                                            $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                $query->where('id', $currentId)
                                                    ->orWhereNotIn('id', $usedSpbIds);
                                            });
                                        } else {
                                            $lumbungQuery->whereNotIn('id', $usedSpbIds);
                                        }

                                        return $lumbungQuery
                                            ->latest()
                                            ->with('kapasitaslumbungbasah')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->no_lb .
                                                        ' - ' . $item->total_netto .
                                                        ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                                            $set('total_netto_3', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_3', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_3', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (empty($state)) {
                                            $set('total_netto_3', null);
                                            $set('no_lumbung_3', null);
                                            $set('jenis_jagung_3', null);
                                        } else {
                                            $selectedLumbungs = [
                                                $get('id_lumbung_1'),
                                                $get('id_lumbung_2'),
                                                $get('id_lumbung_3'),
                                                $get('id_lumbung_4'),
                                            ];
                                            $occurrences = array_count_values(array_filter($selectedLumbungs));
                                            if ($occurrences[$state] > 1) {
                                                Notification::make()
                                                    ->title('Peringatan!')
                                                    ->body('No lumbung tidak boleh sama.')
                                                    ->danger()
                                                    ->send();

                                                $set('id_lumbung_3', null);
                                                return;
                                            }

                                            $lumbung = LumbungBasah::find($state);
                                            $set('total_netto_3', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_3', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_3', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }

                                        // Hitung total netto dari semua lumbung
                                        $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                            + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                        $set('total_netto', $totalNetto);

                                        // Hitung kapasitas sisa setelah dikurangi total netto
                                        $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                                        $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                                        $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                                        $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                                    }),

                                // Select Lumbung 4
                                Select::make('id_lumbung_4')
                                    ->label('No Lumbung 4')
                                    ->placeholder('Pilih No Lumbung 4')
                                    ->options(function (callable $get) {
                                        $currentId = $get('id_lumbung_4'); // nilai yang dipilih (jika ada)

                                        // Ambil semua field timbangan jual (dari 1 sampai 6)
                                        $usedSpbIds = Dryer::query()
                                            ->get()
                                            ->flatMap(function ($record) {
                                                return [
                                                    $record->id_lumbung_1,
                                                    $record->id_lumbung_2,
                                                    $record->id_lumbung_3,
                                                    $record->id_lumbung_4,
                                                ];
                                            })
                                            ->filter()   // Hilangkan nilai null
                                            ->unique()   // Pastikan tidak ada duplikasi
                                            ->toArray();

                                        // Jika ada nilai yang tersimpan, kita ingin menyertakannya walaupun termasuk dalam usedSpbIds.
                                        $lumbungQuery = LumbungBasah::query();
                                        if ($currentId) {
                                            $lumbungQuery->where(function ($query) use ($currentId, $usedSpbIds) {
                                                $query->where('id', $currentId)
                                                    ->orWhereNotIn('id', $usedSpbIds);
                                            });
                                        } else {
                                            $lumbungQuery->whereNotIn('id', $usedSpbIds);
                                        }

                                        return $lumbungQuery
                                            ->latest()
                                            ->with('kapasitaslumbungbasah')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->no_lb .
                                                        ' - ' . $item->total_netto .
                                                        ' - ' . $item->kapasitaslumbungbasah->no_kapasitas_lumbung
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $lumbung = LumbungBasah::with('kapasitaslumbungbasah')->find($state);
                                            $set('total_netto_4', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_4', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_4', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (empty($state)) {
                                            $set('total_netto_4', null);
                                            $set('no_lumbung_4', null);
                                            $set('jenis_jagung_4', null);
                                        } else {
                                            $selectedLumbungs = [
                                                $get('id_lumbung_1'),
                                                $get('id_lumbung_2'),
                                                $get('id_lumbung_3'),
                                                $get('id_lumbung_4'),
                                            ];
                                            $occurrences = array_count_values(array_filter($selectedLumbungs));
                                            if ($occurrences[$state] > 1) {
                                                Notification::make()
                                                    ->title('Peringatan!')
                                                    ->body('No lumbung tidak boleh sama.')
                                                    ->danger()
                                                    ->send();

                                                $set('id_lumbung_4', null);
                                                return;
                                            }

                                            $lumbung = LumbungBasah::find($state);
                                            $set('total_netto_4', $lumbung?->total_netto ?? 0);
                                            $set('no_lumbung_4', $lumbung?->kapasitaslumbungbasah?->no_kapasitas_lumbung ?? 'Tidak ada');
                                            $set('jenis_jagung_4', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                        }

                                        // Hitung total netto dari semua lumbung
                                        $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                            + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                        $set('total_netto', $totalNetto);

                                        // Hitung kapasitas sisa setelah dikurangi total netto
                                        $kapasitasSisaOriginal = (float) ($get('kapasitas_sisa_original') ?? 0);
                                        $sisaSetelahDikurangi = $kapasitasSisaOriginal - $totalNetto;
                                        $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                                        $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                                    }),

                            ])
                    ]),
                Card::make()
                    ->schema([
                        Select::make('timbanganTrontons')
                            ->label('Laporan Penjualan')
                            ->multiple()
                            ->relationship('timbanganTrontons', 'kode') // ganti dengan field yang ingin ditampilkan
                            ->preload()
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : 'N/A';
                                return $record->kode . ' - ' . $noBk . ' - ' . $record->penjualan1->nama_supir . ' - ' . $record->total_netto;
                            })
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
                    ->label('Lumbung Tujuan')
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

                //Jagung 1
                TextColumn::make('lumbung1.no_lb')
                    ->alignCenter()
                    ->label('No Lumbung 1'),
                //Jagung 2
                TextColumn::make('lumbung2.no_lb')
                    ->alignCenter()
                    ->label('No Lumbung 2'),
                //Jagung 2
                TextColumn::make('lumbung3.no_lb')
                    ->alignCenter()
                    ->label('No Lumbung 3'),
                //Jagung 2
                TextColumn::make('lumbung4.no_lb')
                    ->alignCenter()
                    ->label('No Lumbung 4'),
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
