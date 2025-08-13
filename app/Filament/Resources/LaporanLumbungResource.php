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

                                Grid::make()
                                    ->schema([

                                        Select::make('lumbung')
                                            ->label('Lumbung')
                                            ->native(false)
                                            ->columnSpan(1)
                                            ->options(function () {
                                                $lumbungList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'FIKTIF', 'LANTAI DALAM', 'SILO 2500'];
                                                $availableOptions = [];

                                                // Cek apakah sedang dalam mode edit
                                                $isEditing = false;
                                                $currentLumbung = null;

                                                try {
                                                    $livewire = \Livewire\Livewire::current();
                                                    if ($livewire && method_exists($livewire, 'getRecord')) {
                                                        $record = $livewire->getRecord();
                                                        if ($record && isset($record->lumbung)) {
                                                            $isEditing = true;
                                                            $currentLumbung = $record->lumbung;
                                                        }
                                                    }
                                                } catch (\Exception $e) {
                                                    // Ignore error
                                                }

                                                foreach ($lumbungList as $lumbungCode) {
                                                    // Cek apakah ada record dengan status 0 untuk lumbung ini
                                                    $hasStatusZero = \App\Models\LaporanLumbung::where('lumbung', $lumbungCode)
                                                        ->where('status', 0)
                                                        ->exists();

                                                    // Jika ada record dengan status 0, lumbung tidak bisa dipilih
                                                    if ($hasStatusZero) {
                                                        // Khusus untuk mode edit: tetap tampilkan lumbung yang sedang dipilih meski tidak bisa dipilih
                                                        if ($isEditing && $lumbungCode === $currentLumbung) {
                                                            if (str_starts_with($lumbungCode, 'SILO')) {
                                                                $availableOptions[$lumbungCode] = "{$lumbungCode} ";
                                                            } elseif ($lumbungCode === 'LANTAI DALAM') {
                                                                $availableOptions[$lumbungCode] = "Lantai Dalam ";
                                                            } else {
                                                                $availableOptions[$lumbungCode] = "Lumbung {$lumbungCode} ";
                                                            }
                                                        }
                                                        // Jika tidak dalam mode edit, skip lumbung ini
                                                        continue;
                                                    }

                                                    // Jika tidak ada record dengan status 0, lumbung bisa dipilih
                                                    // Format label berbeda untuk silo dan lantai dalam
                                                    if (str_starts_with($lumbungCode, 'SILO')) {
                                                        $availableOptions[$lumbungCode] = $lumbungCode;
                                                    } elseif ($lumbungCode === 'LANTAI DALAM') {
                                                        $availableOptions[$lumbungCode] = "Lantai Dalam";
                                                    } else {
                                                        $availableOptions[$lumbungCode] = "Lumbung {$lumbungCode}";
                                                    }
                                                }

                                                return $availableOptions;
                                            })
                                            ->nullable() // Memungkinkan nilai kosong/null
                                            ->default(function () {
                                                // Cek apakah default dari parameter/record masih valid (tidak tertutup)
                                                $defaultLumbung = null;

                                                if (request()->get('lumbung')) {
                                                    $defaultLumbung = request()->get('lumbung');
                                                } else {
                                                    try {
                                                        $livewire = \Livewire\Livewire::current();
                                                        if ($livewire && method_exists($livewire, 'getRecord')) {
                                                            $record = $livewire->getRecord();
                                                            if ($record && isset($record->lumbung)) {
                                                                $defaultLumbung = $record->lumbung;
                                                            }
                                                        }
                                                    } catch (\Exception $e) {
                                                        // Ignore error
                                                    }
                                                }

                                                // Validasi apakah default lumbung masih bisa dipilih
                                                if ($defaultLumbung) {
                                                    // Cek apakah ada record dengan status 0 untuk lumbung ini
                                                    $hasStatusZero = \App\Models\LaporanLumbung::where('lumbung', $defaultLumbung)
                                                        ->where('status', 0)
                                                        ->exists();

                                                    // Jika tidak ada record dengan status 0, lumbung bisa dipilih
                                                    if (!$hasStatusZero) {
                                                        return $defaultLumbung;
                                                    }
                                                }

                                                return null;
                                            })
                                            ->placeholder('Pilih Lumbung (opsional)')
                                            // ->helperText('Hanya lumbung yang semua recordnya berstatus terbuka (1) yang dapat dipilih')
                                            ->reactive(), // Agar bisa update otomatis jika ada perubahan
                                        Select::make('silo_id')
                                            ->label('Kode')
                                            ->options(function (callable $get) {
                                                $lumbung = $get('lumbung');

                                                // Hanya tampilkan options jika lumbung adalah salah satu dari silo
                                                if (!in_array($lumbung, ['SILO STAFFEL A', 'SILO STAFFEL B', 'SILO 1800', 'SILO 2500'])) {
                                                    return [];
                                                }

                                                return Silo::where('nama', $lumbung)
                                                    ->where('status', '!=', true)
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
                                            ->visible(function (callable $get) {
                                                $lumbung = $get('lumbung');
                                                // Hanya tampilkan field ini jika lumbung adalah salah satu dari silo
                                                return in_array($lumbung, ['SILO STAFFEL A', 'SILO STAFFEL B', 'SILO 1800', 'SILO 2500']);
                                            })
                                            ->placeholder('Pilih Kode Silo')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    // Set status_silo sesuai dengan lumbung yang dipilih
                                                    $lumbung = $get('lumbung');
                                                    $set('status_silo', $lumbung);
                                                } else {
                                                    $set('status_silo', null);
                                                }
                                            }),

                                        // Select::make('status_silo')
                                        //     ->native(false)
                                        //     ->placeholder('Otomatis')
                                        //     ->label('Status silo')
                                        //     ->disabled()
                                        //     ->dehydrated() // Memastikan field tetap terkirim meskipun disabled
                                        //     ->options([
                                        //         'SILO STAFFEL A' => 'SILO STAFFEL A',
                                        //         'SILO STAFFEL B' => 'SILO STAFFEL B',
                                        //         'SILO 2500' => 'SILO 2500',
                                        //         'SILO 1800' => 'SILO 1800',
                                        //     ])
                                        //     ->default(function () {
                                        //         // Ambil dari parameter URL yang sudah dikirim dari ListLaporanLumbungs
                                        //         $statusSilo = request()->get('status_silo');

                                        //         if ($statusSilo) {
                                        //             // Mapping dari database value ke display value
                                        //             $statusMapping = [
                                        //                 'silo staffel a' => 'SILO STAFFEL A',
                                        //                 'silo staffel b' => 'SILO STAFFEL B',
                                        //                 'silo 2500' => 'SILO 2500',
                                        //                 'silo 1800' => 'SILO 1800',
                                        //             ];

                                        //             return $statusMapping[strtolower($statusSilo)] ?? strtoupper($statusSilo);
                                        //         }

                                        //         return null;
                                        //     })
                                        //     ->live()
                                        //     ->reactive(),


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



                    ])
                    ->columns(1),

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
                // TextColumn::make('status_silo')
                //     ->label('Silo')
                //     ->default('-')
                //     ->alignCenter()
                //     ->badge()
                //     ->color(function ($state) {
                //         return match ($state) {
                //             'SILO STAFFEL A' => 'primary',
                //             'SILO STAFFEL B' => 'primary',
                //             'SILO 2500' => 'primary',
                //             'SILO 1800' => 'primary',
                //             '-' => 'primary',
                //             default => 'primary'
                //         };
                //     }),
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
            ->headerActions([
                ExportAction::make()->exporter(LaporanLumbungExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
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
