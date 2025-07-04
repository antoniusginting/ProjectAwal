<?php

namespace App\Filament\Resources;

use Dom\Text;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Transfer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LaporanLumbung;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\TransferResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransferResource\RelationManagers;
use Filament\Forms\Components\Grid;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No Transfer')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->kode;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (Transfer::max('id') ?? 0) + 1;
                                        return 'T' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('jam_masuk')
                                    ->readOnly()
                                    ->suffixIcon('heroicon-o-clock')
                                    ->default(now()->setTimezone('Asia/Jakarta')->format('H:i:s')),
                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Kosong jika belum keluar')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->required(false)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        // Biarkan tetap kosong saat edit
                                    }),
                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(),
                            ])->columns(4),
                        Card::make()
                            ->schema([

                                Select::make('id_transfer')
                                    ->label('Ambil dari Transfer Sebelumnya')
                                    ->options(function () {
                                        // Ambil id_timbangan_tronton dari SuratJalan
                                        $timbanganTrontonIds = \App\Models\SuratJalan::whereNotNull('id_timbangan_tronton')
                                            ->pluck('id_timbangan_tronton');

                                        // Gunakan subquery untuk mendapatkan semua id Transfer
                                        $existingTransfers = \App\Models\TimbanganTronton::whereIn('id', $timbanganTrontonIds)
                                            ->select(
                                                'id_timbangan_jual_1',
                                                'id_timbangan_jual_2',
                                                'id_timbangan_jual_3',
                                                'id_timbangan_jual_4',
                                                'id_timbangan_jual_5',
                                                'id_timbangan_jual_6'
                                            )
                                            ->get()
                                            ->flatMap(function ($item) {
                                                $ids = [];
                                                for ($i = 1; $i <= 6; $i++) {
                                                    $field = "id_timbangan_jual_{$i}";
                                                    if (!is_null($item->$field)) {
                                                        $ids[] = $item->$field;
                                                    }
                                                }
                                                return $ids;
                                            })
                                            ->toArray();

                                        // Ambil ID Transfer yang belum ada di tabel timbangan_tronton
                                        return \App\Models\Transfer::latest()
                                            ->whereNotIn('id', $existingTransfers)  // Hanya ambil yang belum ada di tabel timbangan_tronton
                                            ->take(50)
                                            ->get()
                                            ->mapWithKeys(function ($transfer) {
                                                return [
                                                    $transfer->id => "{$transfer->plat_polisi} - {$transfer->nama_supir} - (Timbangan ke-{$transfer->keterangan}) - {$transfer->created_at->format('d:m:Y')}"
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->hidden(fn($livewire) => $livewire->getRecord()?->exists)
                                    ->dehydrated(false) // jangan disimpan ke DB
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        $transfer = \App\Models\Transfer::find($state);
                                        if ($state === null) {
                                            // Kosongkan semua data yang sebelumnya di-set
                                            $set('plat_polisi', null);
                                            $set('bruto', null);
                                            $set('tara', null);
                                            $set('netto', null);
                                            $set('nama_supir', null);
                                            $set('nama_barang', 'JAGUNG KERING SUPER');
                                            $set('keterangan', null);
                                            return;
                                        }
                                        if ($transfer) {
                                            $set('plat_polisi', $transfer->plat_polisi);
                                            $set('tara', $transfer->bruto);
                                            $set('nama_supir', $transfer->nama_supir);
                                            $set('nama_barang', $transfer->nama_barang);
                                            // Naikkan keterangan jika awalnya 1, 2, atau 3
                                            $keteranganBaru = in_array(intval($transfer->keterangan), [1, 2, 3, 4, 5])
                                                ? intval($transfer->keterangan) + 1
                                                : $transfer->keterangan;
                                            $set('keterangan', $keteranganBaru);
                                        }
                                    })
                                    ->columnSpan(4),

                                TextInput::make('plat_polisi')
                                    ->suffixIcon('heroicon-o-truck')
                                    ->autocomplete('off')
                                    ->columnSpan(2)
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan plat polisi'),
                                TextInput::make('bruto')
                                    ->label('Bruto')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->placeholder('Masukkan Nilai Bruto')
                                    ->live(debounce: 600) // Tunggu 500ms setelah user berhenti mengetik
                                    ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                        $tara = $get('tara') ?? 0;
                                        $set('netto', max(0, intval($state) - intval($tara))); // Hitung netto
                                        $record = $livewire->record ?? null;
                                        // Hanya isi jam_keluar jika sedang edit ($record tidak null) dan jam_keluar masih kosong
                                        if (!empty($state) && empty($get('jam_keluar'))) {
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } elseif (empty($state)) {
                                            // Jika tara dikosongkan, hapus juga jam_keluar
                                            $set('jam_keluar', null);
                                        }
                                    }),
                                TextInput::make('nama_supir')
                                    ->autocomplete('off')
                                    ->columnSpan(2)
                                    ->placeholder('Masukkan Nama Supir')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->required(),
                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->columnSpan(2)
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 600) // Tambahkan debounce juga di sini
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $bruto = $get('bruto') ?? 0;
                                        $set('netto', max(0, intval($bruto) - intval($state)));
                                    }),
                                TextInput::make('nama_barang')
                                    ->default('JAGUNG KERING SUPER')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->columnSpan(2)
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric(),

                                Grid::make()
                                    ->schema([
                                        Radio::make('tipe')
                                            ->label('Tipe Transfer')
                                            ->options([
                                                'masuk' => 'Masuk',
                                                'keluar' => 'Keluar'
                                            ])
                                            ->default('masuk')
                                            ->inline()
                                            ->reactive(),

                                        // Select untuk Lumbung Masuk
                                        Select::make('laporan_lumbung_masuk_id')
                                            ->label('No Lumbung Masuk')
                                            ->options(function () {
                                                return LaporanLumbung::where('status', '!=', true)
                                                    ->get()
                                                    ->mapWithKeys(function ($item) {
                                                        // Jika status_silo ada, tampilkan format "kode - status_silo"
                                                        // Jika tidak ada, tampilkan hanya kode
                                                        $label = $item->status_silo
                                                            ? $item->kode . ' - ' . $item->status_silo
                                                            : $item->kode . ' - ' . $item->lumbung;

                                                        return [
                                                            $item->id => $label
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->placeholder('Pilih Laporan Lumbung Masuk')
                                            ->visible(fn(Get $get) => $get('tipe') === 'masuk'),

                                        // Select untuk Lumbung Keluar
                                        Select::make('laporan_lumbung_keluar_id')
                                            ->label('No Lumbung Keluar')
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
                                            ->placeholder('Pilih Laporan Lumbung Keluar')
                                            ->visible(fn(Get $get) => $get('tipe') === 'keluar'),
                                    ])->columnSpan(2),
                                // TextInput::make('nama_lumbung')
                                //     ->readOnly()
                                //     ->placeholder('Otomatis terisi')
                                //     ->autocomplete('off')
                                //     ->columnSpan(1)
                                //     ->mutateDehydratedStateUsing(fn($state) => strtoupper($state)),

                                // Select::make('status_transfer')
                                //     ->label('Status Transfer')
                                //     ->columnSpan(fn(Get $get) => $get('status_transfer') === 'MASUK' ? 1 : 2) // Dynamic column span
                                //     ->options([
                                //         'MASUK' => 'MASUK',
                                //         'KELUAR' => 'KELUAR',
                                //     ])
                                //     ->placeholder('-- Pilih Status Transfer --')
                                //     ->native(false)
                                //     ->reactive()
                                //     ->required(),
                                // Select::make('silo')
                                //     ->label('KE')
                                //     ->columnSpan(1)
                                //     ->options([
                                //         'SILO STAFFEL A' => 'SILO STAFFEL A',
                                //         'SILO STAFFEL B' => 'SILO STAFFEL B',
                                //         'SILO 2500' => 'SILO 2500',
                                //         'SILO 1800' => 'SILO 1800',
                                //         '1' => 'LUMBUNG BASAH 1',
                                //         '2' => 'LUMBUNG BASAH 2',
                                //         '3' => 'LUMBUNG BASAH 3',
                                //         '4' => 'LUMBUNG BASAH 4',
                                //         '5' => 'LUMBUNG BASAH 5',
                                //         '6' => 'LUMBUNG BASAH 6',
                                //         '7' => 'LUMBUNG BASAH 7',
                                //         '8' => 'LUMBUNG BASAH 8',
                                //         '9' => 'LUMBUNG BASAH 9',
                                //     ])
                                //     ->placeholder('-- Pilih salah satu opsi --')
                                //     ->native(true)
                                //     ->extraAttributes(['class' => 'top-dropdown'])
                                //     ->visible(fn(Get $get) => $get('status_transfer') === 'MASUK'),

                                Select::make('keterangan') // Gantilah 'tipe' dengan nama field di database
                                    ->label('Timbangan ke-')
                                    ->columnSpan(2)
                                    ->options([
                                        '1' => 'Timbangan ke-1',
                                        '2' => 'Timbangan ke-2',
                                        '3' => 'Timbangan ke-3',
                                        '4' => 'Timbangan ke-4',
                                        '5' => 'Timbangan ke-5',
                                        '6' => 'Timbangan ke-6',
                                    ])
                                    ->default(1)
                                    ->placeholder('Pilih timbangan ke-')
                                    // ->inlineLabel() // Membuat label sebelah kiri
                                    ->native(false) // Mengunakan dropdown modern
                                    ->required(), // Opsional: Atur default value
                            ])->columns(4)
                    ]),
                Hidden::make('user_id')
                    ->label('User ID')
                    ->default(Auth::id()) // Set nilai default user yang sedang login,
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

                TextColumn::make('laporanLumbungKeluar.kode')
                    ->alignCenter()
                    ->label('No IO Keluar')
                    ->formatStateUsing(function ($record) {
                        if ($record->laporanLumbungKeluar) {
                            return $record->laporanLumbungKeluar->kode . ' - ' . $record->laporanLumbungKeluar->lumbung;
                        }
                        return '-';
                    }),
                TextColumn::make('laporanLumbungMasuk.kode')
                    ->alignCenter()
                    ->label('No IO Masuk')
                    ->formatStateUsing(function ($record) {
                        if ($record->laporanLumbungMasuk) {
                            $kode = $record->laporanLumbungMasuk->kode;
                            $lumbung = $record->laporanLumbungMasuk->lumbung
                                ? $record->laporanLumbungMasuk->lumbung
                                : $record->laporanLumbungMasuk->status_silo;
                            return $kode . ' - ' . $lumbung;
                        }
                        return '-';
                    }),
                TextColumn::make('kode')
                    ->searchable()
                    ->alignCenter()
                    ->label('No Transfer')
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),
                TextColumn::make('plat_polisi')->label('Plat Polisi')
                    ->searchable(),
                TextColumn::make('nama_supir')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->prefix('Timbangan ke-')
                    ->searchable(),

                TextColumn::make('bruto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tara')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                // TextColumn::make('laporanLumbung.kode')
                //     ->label('No Lumbung')
                //     ->alignCenter(),
                // TextColumn::make('nama_lumbung')
                //     ->searchable()
                //     ->alignCenter(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('jam_masuk')
                    ->alignCenter(),
                TextColumn::make('jam_keluar')
                    ->alignCenter(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
            'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
