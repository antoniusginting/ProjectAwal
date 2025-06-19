<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
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
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LaporanLumbungResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\LaporanLumbungResource\RelationManagers;

class LaporanLumbungResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LaporanLumbung::class;
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
                Card::make('Warning')
                    ->icon('heroicon-o-information-circle')
                    ->description('Harus disimpan sebelum keluar pada halaman ini')
                    ->schema([
                        Grid::make(5)
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
                                TextInput::make('berat_dryer')
                                    ->label('Berat Dryer')->suffix('Kg')
                                    ->numeric()
                                    ->readOnly(), // Opsional: buat readonly karena dihitung otomatis
                                TextInput::make('berat_penjualan')
                                    ->label('Berat Penjualan')->suffix('Kg')
                                    ->numeric()
                                    ->readOnly(), // Opsional: buat readonly karena dihitung otomatis
                                TextInput::make('hasil')
                                    ->label('Hasil')->suffix('Kg')
                                    ->numeric()
                                    ->readOnly(),
                                Select::make('status_silo')
                                    ->native(false)
                                    ->label('Status silo')
                                    ->options([
                                        'SILO BESAR' => 'SILO BESAR',
                                        'SILO STAFFEL A' => 'SILO STAFFEL A',
                                        'SILO STAFFEL B' => 'SILO STAFFEL B',
                                    ])->live()->reactive(),
                            ]),
                        Card::make('Info Dryer')
                            ->schema([
                                Select::make('filter_lumbung_tujuan')
                                    ->native(false)
                                    ->label('Lumbung')
                                    ->options(function () {
                                        // Ambil daftar nama_lumbung unik dari tabel penjualan1 (relasi)
                                        return \App\Models\Dryer::query()
                                            ->whereNotNull('lumbung_tujuan')
                                            ->where('lumbung_tujuan', '!=', '')
                                            ->distinct()
                                            ->pluck('lumbung_tujuan', 'lumbung_tujuan')
                                            ->toArray();
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Simpan pilihan dryers yang sudah ada sebelum filter berubah
                                        $currentDryers = $get('dryers') ?? [];
                                        $set('dryers', $currentDryers);
                                    }),

                                Select::make('dryers')
                                    ->label('Dryer')
                                    ->multiple()
                                    ->relationship(
                                        name: 'dryers',
                                        titleAttribute: 'no_dryer',
                                        modifyQueryUsing: function (Builder $query, $get) {
                                            $selectedLumbung = $get('filter_lumbung_tujuan');
                                            $currentDryers = $get('dryers') ?? [];

                                            // Coba ambil record dari berbagai context
                                            $currentRecordId = null;

                                            // Untuk EditRecord page
                                            if (request()->route('record')) {
                                                $currentRecordId = request()->route('record');
                                            }

                                            // Atau dari Livewire component
                                            try {
                                                $livewire = \Livewire\Livewire::current();
                                                if ($livewire && method_exists($livewire, 'getRecord')) {
                                                    $record = $livewire->getRecord();
                                                    if ($record) {
                                                        $currentRecordId = $record->getKey();
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore error jika tidak dalam context Livewire
                                            }

                                            // Ambil semua ID yang sudah digunakan
                                            $usedLaporanIds = DB::table('laporan_lumbung_has_dryers')
                                                ->pluck('dryer_id')
                                                ->toArray();

                                            // Jika sedang edit, ambil ID yang sudah terkait dengan record ini
                                            if ($currentRecordId) {
                                                $currentlySelectedIds = DB::table('laporan_lumbung_has_dryers')
                                                    ->where('laporan_lumbung_id', $currentRecordId)
                                                    ->pluck('dryer_id')
                                                    ->toArray();

                                                // Exclude currently selected IDs from used IDs
                                                $usedLaporanIds = array_diff($usedLaporanIds, $currentlySelectedIds);
                                            }

                                            // Base query
                                            $query = $query
                                                ->whereNotNull('dryers.lumbung_tujuan')
                                                ->where('dryers.lumbung_tujuan', '!=', '');

                                            // Jika ada filter lumbung yang dipilih
                                            if ($selectedLumbung) {
                                                // Include dryers yang sudah dipilih sebelumnya ATAU yang sesuai dengan filter
                                                $query->where(function ($subQuery) use ($selectedLumbung, $currentDryers) {
                                                    $subQuery->where('dryers.lumbung_tujuan', $selectedLumbung);

                                                    // Jika ada dryers yang sudah dipilih, include mereka juga
                                                    if (!empty($currentDryers)) {
                                                        $subQuery->orWhereIn('dryers.id', $currentDryers);
                                                    }
                                                });
                                            }
                                            $query->orderBy('dryers.created_at', 'desc');
                                            return $query->whereNotIn('dryers.id', $usedLaporanIds);
                                        }
                                    )
                                    ->preload()
                                    ->reactive()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return $record->no_dryer . ' - Dryer : ' . $record->kapasitasdryer->nama_kapasitas_dryer . ' - Lumbung Kering : ' . $record->lumbung_tujuan;
                                    })
                                    ->afterStateUpdated(
                                        function ($state, callable $set, callable $get, $livewire, $old) {
                                            // app(DryerService::class)->updateStatusToCompleted(
                                            //     $state ?? [],
                                            //     $old ?? []
                                            // );
                                            // Hitung total netto dari dryer yang dipilih
                                            if (!empty($state)) {
                                                // Ambil model Dryer (sesuaikan dengan nama model Anda)
                                                $totalNetto = \App\Models\Dryer::whereIn('id', $state)
                                                    ->sum('total_netto');

                                                // Set nilai ke field berat_dryer
                                                $set('berat_dryer', $totalNetto);
                                            } else {
                                                // Jika tidak ada dryer yang dipilih, set ke 0
                                                $set('berat_dryer', 0);
                                            }
                                            // Hitung hasil setelah berat_dryer berubah
                                            $totalDryer = (float) ($get('berat_dryer') ?? 0);
                                            $beratPenjualan = (float) ($get('berat_penjualan') ?? 0);
                                            $set('hasil', $totalDryer - $beratPenjualan);
                                        }
                                    )->preload()
                                    ->searchable(),
                            ])->columnSpan(1),
                        Card::make('Info Laporan Penjualan')
                            ->schema([
                                Select::make('lumbung')
                                    ->native(false)
                                    ->label('Lumbung')
                                    ->options(function () {
                                        // Ambil daftar nama_lumbung unik dari tabel penjualan1 (relasi)
                                        return \App\Models\Penjualan::query()
                                            ->whereNotNull('nama_lumbung')
                                            ->where('nama_lumbung', '!=', '')
                                            ->distinct()
                                            ->pluck('nama_lumbung', 'nama_lumbung')
                                            ->toArray();
                                    })
                                    ->reactive(),
                                // ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                //     // Disable saat edit, misal jika $record ada berarti edit
                                //     return $record !== null;
                                // }),
                                Select::make('timbanganTrontons')
                                    ->label('Laporan Penjualan')
                                    ->multiple()
                                    ->relationship(
                                        name: 'timbanganTrontons',
                                        titleAttribute: 'kode',
                                        modifyQueryUsing: function (Builder $query, $get) {
                                            // Coba ambil record dari berbagai context
                                            $currentRecordId = null;

                                            // Untuk EditRecord page
                                            if (request()->route('record')) {
                                                $currentRecordId = request()->route('record');
                                            }

                                            // Atau dari Livewire component
                                            try {
                                                $livewire = \Livewire\Livewire::current();
                                                if ($livewire && method_exists($livewire, 'getRecord')) {
                                                    $record = $livewire->getRecord();
                                                    if ($record) {
                                                        $currentRecordId = $record->getKey();
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore error jika tidak dalam context Livewire
                                            }

                                            $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];
                                            $selectedNamaLumbung = $get('lumbung');

                                            $query = $query->where(function ($query) use ($relasiPenjualan, $selectedNamaLumbung) {
                                                foreach ($relasiPenjualan as $index => $relasi) {
                                                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';

                                                    $query->$method($relasi, function (Builder $q) use ($selectedNamaLumbung) {
                                                        $q->whereNotNull('nama_lumbung')
                                                            ->where('nama_lumbung', '!=', '');

                                                        if ($selectedNamaLumbung) {
                                                            $q->where('nama_lumbung', $selectedNamaLumbung);
                                                        }
                                                    });
                                                }
                                            });

                                            $query->where(function ($q) {
                                                $q->where('status', false)  // status = 0 / false
                                                    ->orWhereNull('status');  // atau status = null
                                            });
                                            $query->orderBy('timbangan_trontons.created_at', 'desc');
                                            $query->limit(20);
                                            return $query;
                                        }
                                    )
                                    ->preload()
                                    ->reactive()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : 'N/A';
                                        return $record->kode . ' - ' . $noBk . ' - ' . ($record->penjualan1->nama_supir ?? '') . ' - ' . $record->total_netto;
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (!empty($state)) {
                                            $selectedLumbung = $get('lumbung'); // Ambil lumbung yang dipilih
                                            $totalNetto = 0;

                                            // Loop melalui setiap timbangan yang dipilih
                                            foreach ($state as $timbanganId) {
                                                // Ambil record timbangan dengan relasi penjualan
                                                $timbangan = \App\Models\TimbanganTronton::with([
                                                    'penjualan1',
                                                    'penjualan2',
                                                    'penjualan3',
                                                    'penjualan4',
                                                    'penjualan5',
                                                    'penjualan6'
                                                ])->find($timbanganId);

                                                if ($timbangan) {
                                                    // Array relasi penjualan
                                                    $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];

                                                    // Loop melalui setiap relasi penjualan
                                                    foreach ($relasiPenjualan as $relasi) {
                                                        $penjualan = $timbangan->$relasi;

                                                        // Jika penjualan ada dan nama_lumbung sesuai dengan yang dipilih
                                                        if (
                                                            $penjualan &&
                                                            $penjualan->nama_lumbung &&
                                                            $penjualan->nama_lumbung === $selectedLumbung
                                                        ) {

                                                            // Tambahkan netto penjualan ke total
                                                            $totalNetto += $penjualan->netto ?? 0;
                                                        }
                                                    }
                                                }
                                            }

                                            // Set nilai ke field berat_penjualan
                                            $set('berat_penjualan', $totalNetto);
                                        } else {
                                            // Jika tidak ada timbangan yang dipilih, set ke 0
                                            $set('berat_penjualan', 0);
                                        }

                                        // Hitung hasil setelah berat_penjualan berubah
                                        $totalDryer = (float) ($get('berat_dryer') ?? 0);
                                        $beratPenjualan = (float) ($get('berat_penjualan') ?? 0);
                                        $set('hasil', $totalDryer - $beratPenjualan);
                                    }),
                            ])->columnSpan(1),

                        Hidden::make('user_id')
                            ->label('User ID')
                            ->default(Auth::id()) // Set nilai default user yang sedang login,
                    ])->columns(2),
                Card::make('Info Timbangan Langsir')
                    ->schema([

                        // TextInput::make('berat_penjualan')
                        //     ->label('Berat Penjualan')
                        //     ->numeric()
                        //     ->readOnly(), // Opsional: buat readonly karena dihitung otomatis
                        // TextInput::make('hasil')
                        //     ->label('Hasil')
                        //     ->numeric()
                        //     ->readOnly(),
                        Select::make('penjualan_ids')
                            ->label('Timbangan Langsir')
                            ->placeholder('Pilih ID timbangan langsir')
                            ->multiple()
                            ->relationship(
                                name: 'penjualans',
                                titleAttribute: 'no_spb',
                                modifyQueryUsing: function ($query, $get) {
                                    $currentLaporanId = $get('id');

                                    return $query
                                        ->where('status_timbangan', 'LANGSIR')
                                        ->whereNotNull('netto')
                                        ->where('netto', '>', 0)
                                        ->whereDoesntHave('laporanLumbungs', function ($subQuery) use ($currentLaporanId) {
                                            if ($currentLaporanId) {
                                                $subQuery->where('laporan_lumbung_id', '!=', $currentLaporanId);
                                            }
                                        });
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->no_spb} - {$record->nama_supir} - {$record->netto}")
                            ->searchable()
                            ->columnSpan(3)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (empty($state)) {
                                    $set('berat_langsir', 0);
                                    return;
                                }

                                // Ambil data penjualan dengan select hanya kolom yang dibutuhkan
                                $penjualans = \App\Models\Penjualan::select('netto')
                                    ->whereIn('id', $state)
                                    ->get();

                                $totalNetto = $penjualans->sum('netto');
                                // Simpan nilai asli (integer) tanpa format
                                $set('berat_langsir', $totalNetto);
                            })
                            ->preload(),

                        TextInput::make('berat_langsir')
                            ->label('Total Netto')
                            ->columnSpan(1)
                            ->numeric()
                            // ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '0') // Format hanya untuk display
                            ->readOnly()
                            ->suffix('Kg'), // Opsional: tambah satuan
                    ])->columns(4)
                // ->visible(fn(Get $get) => filled($get('status_silo'))) // Muncul live ketika ada pilihan,
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
                TextColumn::make('status_silo')
                    ->label('Silo')
                    ->default('-')
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'SILO BESAR' => 'primary',
                            'SILO STAFFEL A' => 'primary',
                            'SILO STAFFEL B' => 'primary',
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
                    ->label('Lumbung'),
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
                    ->label('No Langsir')
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
                TextColumn::make('timbanganTrontons_kode')
                    ->label('No Laporan Penjualan')
                    ->searchable()
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $kodes = $record->timbanganTrontons->pluck('kode');

                        if ($kodes->count() <= 3) {
                            return $kodes->implode(', ');
                        }

                        return $kodes->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $kodes = $record->timbanganTrontons->pluck('kode');
                        return $kodes->implode(', ');
                    }),
                TextColumn::make('user.name')
                    ->alignCenter()
                    ->label('PJ'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('kode', 'desc') // Megurutkan kode terakhir menjadi pertama pada tabel
            ->actions([
                Tables\Actions\Action::make('view-laporan-lumbung')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-laporan-lumbung", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
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
            'index' => Pages\ListLaporanLumbungs::route('/'),
            'create' => Pages\CreateLaporanLumbung::route('/create'),
            'edit' => Pages\EditLaporanLumbung::route('/{record}/edit'),
            'view-laporan-lumbung' => Pages\ViewLaporanLumbung::route('/{record}/view-laporan-lumbung'),
        ];
    }
}
