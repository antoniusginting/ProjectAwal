<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Dryer;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
use Filament\Forms\Components\Actions;
use App\Filament\Exports\DryerExporter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Forms\Components\Actions\Action;
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

    public static function getNavigationSort(): int
    {
        return 3;
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
                                if ($record) {
                                    return $record->no_dryer;
                                }
                                $nextId = (Dryer::max('id') ?? 0) + 1;
                                return 'D' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                            }),

                        Select::make('id_kapasitas_dryer')
                            ->label('Nama Dryer')
                            ->placeholder('Pilih nama Dryer')
                            ->options(KapasitasDryer::pluck('nama_kapasitas_dryer', 'id'))
                            ->searchable()
                            ->disabled(function (callable $get, $record) {
                                // Jika ini adalah form edit, cek status dari record yang sedang diedit
                                if ($record && $record->status) {
                                    if (in_array($record->status, ['completed'])) {
                                        return true;
                                    }
                                }

                                // REMOVED: Disable logic untuk sortiran yang masih ada
                                // Sekarang allow perpindahan dryer meskipun ada sortiran

                                return false;
                            })
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $kapasitasdryer = KapasitasDryer::find($state);
                                    $kapasitasSisaValue = $kapasitasdryer?->kapasitas_sisa ?? 0;
                                    $set('kapasitas_sisa_original', $kapasitasSisaValue);
                                    $formattedSisa = number_format($kapasitasSisaValue, 0, ',', '.');
                                    $set('kapasitas_sisa', $formattedSisa);
                                    $formattedtotal = number_format($kapasitasdryer?->kapasitas_total ?? 0, 0, ',', '.');
                                    $set('kapasitas_total', $formattedtotal);
                                    $totalNetto = (float) ($get('total_netto') ?? 0);
                                    if ($totalNetto > 0) {
                                        $sisaSetelahDikurangi = $kapasitasSisaValue - $totalNetto;
                                        $formattedSisaAkhir = number_format($sisaSetelahDikurangi, 0, ',', '.');
                                        $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                $kapasitasdryer = KapasitasDryer::find($state);
                                $kapasitasSisaValue = $kapasitasdryer?->kapasitas_sisa ?? 0;

                                // ENHANCED: Jika ada record yang sedang diedit, hitung dengan mengembalikan kapasitas lama
                                if ($record && $record->id_kapasitas_dryer && $record->id_kapasitas_dryer != $state) {
                                    // Tambahkan kembali kapasitas yang sedang digunakan oleh record ini
                                    $kapasitasSisaValue += $record->total_netto_integer;
                                }

                                $set('kapasitas_sisa_original', $kapasitasSisaValue);
                                $formattedSisa = number_format($kapasitasSisaValue, 0, ',', '.');
                                $set('kapasitas_sisa', $formattedSisa);
                                $formattedtotal = number_format($kapasitasdryer?->kapasitas_total ?? 0, 0, ',', '.');
                                $set('kapasitas_total', $formattedtotal);
                                $set('total_netto', $record ? $record->total_netto_integer : null);
                                $set('sortirans', $record ? $record->sortirans->pluck('id')->toArray() : null);

                                // Update kapasitas sisa akhir berdasarkan total netto yang ada
                                $currentTotalNetto = $record ? $record->total_netto_integer : 0;
                                $sisaSetelahDikurangi = $kapasitasSisaValue - $currentTotalNetto;
                                $formattedSisaAkhir = number_format(max(0, $sisaSetelahDikurangi), 0, ',', '.');
                                $set('kapasitas_sisa_akhir', $formattedSisaAkhir);
                            }),

                        Grid::make()
                            ->schema([
                                Select::make('laporan_lumbung_id')
                                    ->label('No IO')
                                    ->disabled(fn(string $operation): bool => $operation === 'create')
                                    ->options(function () {
                                        return LaporanLumbung::whereNull('status_silo')
                                            ->where('status', false)
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->kode . ' - ' . $item->lumbung .
                                                        (!empty($item->keterangan) && trim($item->keterangan) !== ''
                                                            ? ' - Ket : ' . $item->keterangan
                                                            : '')
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Pilih Laporan Lumbung')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // Ambil data laporan lumbung yang dipilih
                                            $laporanLumbung = LaporanLumbung::find($state);

                                            if ($laporanLumbung) {
                                                $kode = $laporanLumbung->kode;
                                                $lumbung = $laporanLumbung->lumbung;

                                                // Logika untuk menentukan status berdasarkan lumbung
                                                // Jika lumbung kosong/null atau hanya berisi spasi, set status pending
                                                if (empty(trim($lumbung))) {
                                                    $set('status', 'pending');

                                                    // Tampilkan notifikasi
                                                    // Notification::make()
                                                    //     ->title('Status Diubah ke Pending')
                                                    //     ->body('Lumbung tujuan belum ditentukan, status dryer diubah ke pending.')
                                                    //     ->warning()
                                                    //     ->duration(3000)
                                                    //     ->send();
                                                }
                                                // Jika lumbung sudah terisi, biarkan status default (processing)
                                                // Status lain tidak akan diubah secara otomatis
                                            }
                                        }
                                    }),
                                TextInput::make('tujuan')
                                    ->label('Lumbung Tujuan')
                                    ->placeholder('Masukkan Lumbung Tujuan'),
                            ])->columnSpan(1),


                        TextInput::make('pj')
                            ->label('PenanggungJawab')
                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                            ->placeholder('Masukkan PenanggungJawab'),

                        TextInput::make('operator')
                            ->label('Operator Dryer')
                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                            ->placeholder('Masukkan Operator Dryer'),

                        TextInput::make('rencana_kadar')
                            ->label('Rencana Kadar')
                            ->numeric()
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
                                'JG.K.Rendah' => 'JG.K.Rendah'
                            ])
                            ->placeholder('Pilih nama barang')
                            ->native(false),

                        TextInput::make('no_cc')
                            ->placeholder('Masukkan Nomor Pesanan')
                            ->label('Nomor Pesanan')
                            ->columnSpanFull()
                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                            ->maxLength(19)
                            ->visible(fn() => !optional(Auth::user())->hasAnyRole(['qc', 'mandor']))
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
                                return DB::table('kapasitas_lumbung_basahs')
                                    ->select('id', 'no_kapasitas_lumbung')
                                    ->orderBy('no_kapasitas_lumbung')
                                    ->pluck('no_kapasitas_lumbung', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $currentSortirans = $get('sortirans') ?? [];
                                $set('sortirans', $currentSortirans);
                            }),

                        Select::make('sortirans')
                            ->label('Sortiran')
                            ->multiple()
                            ->disabled(function ($record) {
                                return $record && $record->status === 'completed';
                            })
                            ->relationship(
                                name: 'sortirans',
                                titleAttribute: 'no_sortiran',
                                modifyQueryUsing: function (Builder $query, $get, $livewire) {
                                    $filterKapasitasLumbung = $get('filter_kapasitas_lumbung');
                                    $currentSortirans = $get('sortirans') ?? [];

                                    $currentRecordId = null;

                                    if (request()->route('record')) {
                                        $currentRecordId = request()->route('record');
                                    }

                                    try {
                                        if ($livewire && method_exists($livewire, 'getRecord')) {
                                            $record = $livewire->getRecord();
                                            if ($record) {
                                                $currentRecordId = $record->getKey();
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Ignore error
                                    }

                                    $usedSortiranIds = DB::table('dryer_has_sortiran')
                                        ->pluck('sortiran_id')
                                        ->toArray();

                                    if ($currentRecordId) {
                                        $currentlySelectedIds = DB::table('dryer_has_sortiran')
                                            ->where('dryer_id', $currentRecordId)
                                            ->pluck('sortiran_id')
                                            ->toArray();

                                        $usedSortiranIds = array_diff($usedSortiranIds, $currentlySelectedIds);
                                    }

                                    if ($filterKapasitasLumbung) {
                                        $query->where(function ($subQuery) use ($filterKapasitasLumbung, $currentSortirans) {
                                            $subQuery->whereHas('kapasitaslumbungbasah', function ($q) use ($filterKapasitasLumbung) {
                                                $q->where('id', $filterKapasitasLumbung);
                                            });

                                            if (!empty($currentSortirans)) {
                                                $subQuery->orWhereIn('sortirans.id', $currentSortirans);
                                            }
                                        });
                                    }

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

                                return $kapasitas . ' - ' . $noSpb . ' - ' . $noBk . ' - ' . $supplier . ' - ' . $record->netto_bersih . ' - ' . $record->created_at->format('d/m/Y');
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire, $old) {
                                $noDryer = $get('id_kapasitas_dryer');
                                $kapasitasAwal = 0;

                                $record = $livewire->getRecord();
                                $isEditMode = $record !== null;

                                if ($noDryer) {
                                    $kapasitasDryer = KapasitasDryer::find($noDryer);
                                    if ($kapasitasDryer) {
                                        $kapasitasAwal = $kapasitasDryer->kapasitas_sisa;
                                    }
                                }

                                if ($isEditMode) {
                                    $oldSortiranIds = $record->sortirans()
                                        ->select('sortirans.id')
                                        ->pluck('sortirans.id')
                                        ->toArray();

                                    $oldSortirans = \App\Models\Sortiran::whereIn('id', $oldSortiranIds)->get();

                                    $totalOldNetto = 0;
                                    foreach ($oldSortirans as $oldSortiran) {
                                        $oldNettoValue = (int) preg_replace('/[^0-9]/', '', $oldSortiran->netto_bersih);
                                        $totalOldNetto += $oldNettoValue;
                                    }

                                    $kapasitasAwal += $totalOldNetto;
                                }

                                if (empty($state)) {
                                    $set('total_netto', 0);
                                    $set('kapasitas_sisa_akhir', $kapasitasAwal);
                                    return;
                                }

                                $selectedSortirans = \App\Models\Sortiran::whereIn('id', $state)->get();

                                $totalNetto = 0;
                                foreach ($selectedSortirans as $sortiran) {
                                    $nettoValue = (int) preg_replace('/[^0-9]/', '', $sortiran->netto_bersih);
                                    $totalNetto += $nettoValue;
                                }

                                $set('total_netto', $totalNetto);
                                $kapasitasSisaBaru = $kapasitasAwal - $totalNetto;
                                $set('kapasitas_sisa_akhir', (int) $kapasitasSisaBaru);

                                $notificationMessage = $isEditMode ?
                                    "Kapasitas diperbarui (mode edit)" :
                                    "Kapasitas diperbarui";
                            })
                            ->preload()
                            ->columnSpan(3)
                            ->searchable(),
                    ])->columns(4),

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


                Card::make('Catatan')
                    ->schema([
                        Actions::make([
                            Action::make('toggle_keterangan')
                                ->label('Tambah Catatan')
                                ->icon('heroicon-o-plus')
                                ->color('primary')
                                ->action(function (Set $set) {
                                    $set('show_keterangan', true);
                                })
                                ->visible(fn(Get $get) => !$get('show_keterangan') && empty($get('keterangan'))),

                            Action::make('hide_keterangan')
                                ->label('Sembunyikan Catatan')
                                ->icon('heroicon-o-minus')
                                ->color('gray')
                                ->action(function (Set $set) {
                                    $set('show_keterangan', false);
                                    $set('keterangan', null);
                                })
                                ->visible(fn(Get $get) => $get('show_keterangan') && empty($get('keterangan')))
                        ]),

                        Textarea::make('keterangan')
                            ->label('Catatan')
                            ->placeholder('Masukkan catatan atau keterangan tambahan...')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => $get('show_keterangan') || !empty($get('keterangan')))
                            ->mutateDehydratedStateUsing(fn($state) => $state ? strtoupper($state) : $state)
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn(Get $get) => empty($get('keterangan'))),


                Hidden::make('show_keterangan')
                    ->default(fn(Get $get, $record) => !empty($get('keterangan')) || ($record && !empty($record->keterangan))),


                Hidden::make('status')
                    ->default('processing'),

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

                if ($user && $user->hasRole('super_admin')) {
                    return EditDryer::getUrl(['record' => $record]);
                }

                if ($user && ($user->hasRole('qc') || $user->hasRole('mandor'))) {
                    if (!$record->no_cc) {
                        return EditDryer::getUrl(['record' => $record]);
                    }
                    return null;
                }

                return null;
            })
            ->defaultPaginationPageOption(10)
            ->paginated([5, 10, 15, 50])
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
                        Carbon::setLocale('id');
                        return Carbon::parse($state)
                            ->locale('id')
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),

                // UPDATED: Status column dengan tambahan pending
                BadgeColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'processing' => 'Dalam Dryer',
                        'completed' => 'Selesai',
                        'pending' => 'Tertunda',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'completed',
                        'danger' => 'processing',
                        'warning' => 'pending', // TAMBAHAN BARU: warna untuk status pending
                    ]),

                TextColumn::make('no_dryer')
                    ->label('No Dryer')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('kapasitasdryer.nama_kapasitas_dryer')
                    ->label('Nama Dryer')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('laporanLumbung.kode')
                    ->label('No Lumbung')
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(function ($record) {
                        $laporan = $record->laporanLumbung;
                        if ($laporan) {
                            return $laporan->kode . ' - ' . $laporan->lumbung;
                        }
                        return '-';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->orWhereHas('laporanLumbung', function ($q) use ($search) {
                            $q->where('kode', 'like', "%{$search}%")
                                ->orWhere('lumbung', 'like', "%{$search}%");
                        });
                    }),
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
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('sortirans', function ($q) use ($search) {
                            $q->whereHas('pembelian', function ($q2) use ($search) {
                                $q2->where('no_spb', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->formatStateUsing(function ($record) {
                        $text = $record->sortirans->map(function ($sortiran) {
                            if (!empty($sortiran->pembelian?->no_spb)) {
                                return $sortiran->pembelian->no_spb;
                            }
                            return 'N/A';
                        })->implode(' | ');

                        return \Illuminate\Support\Str::limit($text, 30, '...');
                    })
                    ->extraAttributes(['class' => 'max-w-md truncate'])
                    ->tooltip(function ($record) {
                        return $record->sortirans->map(function ($sortiran) {
                            if (!empty($sortiran->pembelian?->no_spb)) {
                                return $sortiran->pembelian->no_spb;
                            }
                            return 'N/A';
                        })->implode(' | ');
                    }),
                TextColumn::make('total_netto')
                    ->alignCenter()
                    ->label('Total Netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                // TAMBAHAN: Column untuk keterangan
                TextColumn::make('keterangan')
                    ->label('Catatan')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->keterangan)
                    ->toggleable(isToggledHiddenByDefault: true),

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
