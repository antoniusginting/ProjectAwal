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
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\TransferExporter;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\TransferResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransferResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class TransferResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 5;
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

                                Select::make('penjualan_id')
                                    ->required()
                                    ->label('Ambil dari Timbangan Jual (LANGSIR)')
                                    ->options(function ($livewire) {
                                        // Ambil ID penjualan yang sudah digunakan di model ini
                                        $query = \App\Models\Transfer::whereNotNull('penjualan_id');

                                        // Jika sedang edit, exclude record yang sedang diedit
                                        if ($livewire->getRecord()?->exists) {
                                            $query->where('id', '!=', $livewire->getRecord()->id);
                                        }

                                        $usedPenjualanIds = $query->pluck('penjualan_id')->toArray();

                                        // Ambil penjualan yang memenuhi kriteria:
                                        return \App\Models\Penjualan::latest()
                                            ->where('nama_barang', 'LIKE', '%langsir%')
                                            ->whereNotIn('id', $usedPenjualanIds) // Exclude yang sudah digunakan
                                            ->whereNotNull('bruto') // Bruto tidak boleh null
                                            ->whereNotNull('netto') // Netto tidak boleh null
                                            ->where('bruto', '>', 0) // Bruto harus lebih dari 0
                                            ->where('netto', '>', 0) // Netto harus lebih dari 0
                                            ->whereNull('silo_id') // Silo ID harus null
                                            ->take(50)
                                            ->get()
                                            ->mapWithKeys(function ($penjualan) {
                                                return [
                                                    $penjualan->id => "{$penjualan->no_spb} - {$penjualan->nama_barang} - {$penjualan->nama_supir} - (Timbangan ke-{$penjualan->keterangan}) - {$penjualan->created_at->format('d:m:Y')}"
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->reactive()
                                    // ->hidden(fn($livewire) => $livewire->getRecord()?->exists)
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        $penjualan = \App\Models\Penjualan::find($state);
                                        if ($state === null) {
                                            // Kosongkan semua data yang sebelumnya di-set
                                            $set('id_transfer', null);
                                            $set('plat_polisi', null);
                                            $set('bruto', null);
                                            $set('tara', null);
                                            $set('netto', null);
                                            $set('nama_supir', null);
                                            $set('nama_barang', null);
                                            $set('keterangan', null);
                                            $set('silo_id', null);
                                            return;
                                        }
                                        if ($penjualan) {
                                            // Ambil data dari pembelian yang berelasi
                                            $set('id_transfer', null);
                                            $set('plat_polisi', $penjualan->plat_polisi);
                                            $set('tara', $penjualan->tara);
                                            $set('bruto', $penjualan->bruto);
                                            $set('netto', $penjualan->netto);
                                            $set('nama_supir', $penjualan->nama_supir);
                                            $set('nama_barang', $penjualan->nama_barang);
                                            $set('keterangan', $penjualan->keterangan);
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
                                    ->live(debounce: 200) // Tunggu 500ms setelah user berhenti mengetik
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
                                    ->live(debounce: 200) // Tambahkan debounce juga di sini
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
                                        // Select untuk Lumbung Masuk
                                        Select::make('laporan_lumbung_masuk_id')
                                            ->label('No Lumbung Masuk')
                                            ->options(function () {
                                                return LaporanLumbung::where('status', '!=', true)
                                                    ->get()
                                                    ->mapWithKeys(function ($item) {
                                                        return [
                                                            $item->id => $item->kode . ' - ' . $item->lumbung . ' - ' . $item->keterangan
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull()
                                            ->nullable()
                                            ->placeholder('Pilih Laporan Lumbung Masuk')
                                            ->reactive()
                                            ->disabled(
                                                fn(callable $get) =>
                                                filled($get('silo_id')) ||
                                                    filled($get('silo_keluar_id')) ||
                                                    filled($get('silo_masuk_id'))
                                            )
                                            ->dehydrated()  // PENTING: Ini memaksa field disabled tetap terkirim
                                            ->afterStateUpdated(function (callable $set, $state) {
                                                if (filled($state)) {
                                                    $set('silo_id', null);
                                                    $set('silo_keluar_id', null);
                                                    $set('silo_masuk_id', null);
                                                }
                                            }),

                                    ])->columnSpan(2),
                                Select::make('keterangan')
                                    ->label('Timbangan ke-')
                                    ->columnSpan(1)
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
                                    ->native(false)
                                    ->required(),
                                Select::make('silo_id')
                                    ->label('Silo')
                                    ->options(function (callable $get) {
                                        return \App\Models\Silo::where('status', 0)
                                            ->orderBy('id')
                                            ->get()
                                            ->mapWithKeys(function ($silo) {
                                                return [
                                                    $silo->id => "{$silo->nama}"
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1)
                                    ->nullable()
                                    ->placeholder('Pilih Kode Silo')
                                    ->reactive()
                                    ->disabled(
                                        fn(callable $get) =>
                                        filled($get('laporan_lumbung_masuk_id')) ||
                                            filled($get('silo_keluar_id')) ||
                                            filled($get('silo_masuk_id'))
                                    )
                                    ->dehydrated()  // PENTING: Ini memaksa field disabled tetap terkirim
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if (filled($state)) {
                                            $set('laporan_lumbung_masuk_id', null);
                                            $set('silo_keluar_id', null);
                                            $set('silo_masuk_id', null);
                                        }
                                    }),


                            ])->columns(4),
                        Card::make('Langsir Silo ke Silo')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('silo_keluar_id')
                                            ->label('Silo Keluar')
                                            ->options(function (callable $get) {
                                                return \App\Models\Silo::where('status', 0)
                                                    ->orderBy('id')
                                                    ->get()
                                                    ->mapWithKeys(function ($silo) {
                                                        return [
                                                            $silo->id => "{$silo->nama}"
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->placeholder('Pilih Silo Asal')
                                            ->reactive()
                                            ->disabled(function (callable $get, ?string $operation) {
                                                // Hanya disable pada create mode, tidak pada edit
                                                if ($operation === 'edit') {
                                                    return false;
                                                }

                                                return filled($get('laporan_lumbung_masuk_id')) ||
                                                    filled($get('silo_id'));
                                            })
                                            ->dehydrated()
                                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                                if (filled($state)) {
                                                    $set('laporan_lumbung_masuk_id', null);
                                                    $set('silo_id', null);

                                                    $siloMasukId = $get('silo_masuk_id');
                                                    if ($state == $siloMasukId) {
                                                        $set('silo_masuk_id', null);
                                                    }
                                                }
                                            }),

                                        Select::make('silo_masuk_id')
                                            ->label('Silo Masuk')
                                            ->options(function (callable $get) {
                                                $siloKeluarId = $get('silo_keluar_id');

                                                return \App\Models\Silo::where('status', 0)
                                                    ->when($siloKeluarId, function ($query) use ($siloKeluarId) {
                                                        return $query->where('id', '!=', $siloKeluarId);
                                                    })
                                                    ->orderBy('id')
                                                    ->get()
                                                    ->mapWithKeys(function ($silo) {
                                                        return [
                                                            $silo->id => "{$silo->nama}"
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->placeholder('Pilih Silo Tujuan')
                                            ->reactive()
                                            ->disabled(function (callable $get, ?string $operation) {
                                                // Hanya disable pada create mode, tidak pada edit
                                                if ($operation === 'edit') {
                                                    return false;
                                                }

                                                return filled($get('laporan_lumbung_masuk_id')) ||
                                                    filled($get('silo_id'));
                                            })
                                            ->dehydrated()
                                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                                if (filled($state)) {
                                                    $set('laporan_lumbung_masuk_id', null);
                                                    $set('silo_id', null);

                                                    $siloKeluarId = $get('silo_keluar_id');
                                                    if ($state == $siloKeluarId) {
                                                        $set('silo_keluar_id', null);
                                                    }
                                                }
                                            }),
                                    ])->columnSpan(2),
                            ])->collapsed(),
                    ]),
                Hidden::make('user_id')
                    ->label('User ID')
                    ->default(Auth::id()) // Set nilai default user yang sedang login,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
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
                    ->label('Status Transfer')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        // Cek kondisi berdasarkan field yang terisi
                        if ($record->laporan_lumbung_masuk_id) {
                            return 'Langsir Gonian';
                        } elseif ($record->silo_id) {
                            return 'Langsir LK ke Silo';
                        } elseif ($record->silo_keluar_id && $record->silo_masuk_id) {
                            return 'Langsir Silo ke Silo';
                        } else {
                            return 'Status Tidak Diketahui';
                        }
                    })
                    ->badge()
                    ->color(function ($record): string {
                        // Warna badge berdasarkan status
                        if ($record->laporan_lumbung_masuk_id) {
                            return 'supplier'; // Hijau untuk Langsir Goni
                        } elseif ($record->silo_id) {
                            return 'tamu'; // Kuning untuk Langsir LK ke Silo
                        } elseif ($record->silo_keluar_id && $record->silo_masuk_id) {
                            return 'ekspedisi'; // Biru untuk Langsir Silo ke Silo
                        } else {
                            return 'danger'; // Merah untuk Tidak Diketahui
                        }
                    })
                    ->searchable(false)
                    ->sortable(false),

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
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('jam_masuk')
                    ->alignCenter(),
                TextColumn::make('jam_keluar')
                    ->alignCenter(),
                TextColumn::make('laporanLumbungMasuk.kode')
                    ->default('-')
                    ->searchable()
                    ->alignCenter()
                    ->label('No IO Masuk')
                    ->formatStateUsing(function ($record) {
                        if (!$record->laporanLumbungMasuk) {
                            return '-';
                        }

                        $kode = $record->laporanLumbungMasuk->kode ?? '';
                        $lumbung = $record->laporanLumbungMasuk->lumbung ?? 'Tidak Ada';

                        return trim($kode . ' - ' . $lumbung);
                    }),
                TextColumn::make('silo.nama')
                    ->alignCenter()
                    ->searchable()
                    ->label('Langsir LK ke Silo')
                    ->default('-'),
                TextColumn::make('siloMasuk.nama')
                    ->alignCenter()
                    ->searchable()
                    ->label('Langsir Silo Masuk')
                    ->default('-'),
                TextColumn::make('siloKeluar.nama')
                    ->searchable()
                    ->alignCenter()
                    ->label('Langsir Silo Keluar')
                    ->default('-'),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('kode', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()->exporter(TransferExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(TransferExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view-transfer')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-transfer", ['record' => $record->id])),
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
            'view-transfer' => Pages\ViewTransfer::route('/{record}/view-transfer'),
        ];
    }
}
