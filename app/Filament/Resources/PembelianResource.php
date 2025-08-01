<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Mobil;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Pembelian;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Exports\PembelianExporter;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\PembelianResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\PembelianResource\Pages\EditPembelian;
use App\Models\PembelianAntarPulau;

class PembelianResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 1;
    public static ?string $label = 'Daftar Pembelian';

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
                                    ->label('No SPB')
                                    ->content(function ($record) {
                                        if ($record) {
                                            return $record->no_spb;
                                        }
                                        $nextId = (Pembelian::max('id') ?? 0) + 1;
                                        return 'B' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
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
                                    ->required(false),

                                TextInput::make('created_at')
                                    ->label('Tanggal')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(),
                            ])->columns(4)->collapsed(),

                        Card::make()
                            ->schema([
                                Select::make('id_pembelian')
                                    ->label('Ambil dari Pembelian Sebelumnya')
                                    ->options(function () {
                                        return \App\Models\Pembelian::latest()->take(50)->get()->mapWithKeys(function ($pembelian) {
                                            return [
                                                $pembelian->id => "{$pembelian->plat_polisi} - {$pembelian->nama_supir} - (Timbangan ke-{$pembelian->keterangan}) - {$pembelian->created_at->format('d:m:Y')}"
                                            ];
                                        });
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state === null) {
                                            $set('plat_polisi', null);
                                            $set('bruto', null);
                                            $set('tara', null);
                                            $set('netto', null);
                                            $set('nama_supir', null);
                                            $set('nama_barang', null);
                                            $set('id_supplier', null);
                                            $set('keterangan', null);
                                            $set('brondolan', null);
                                            return;
                                        }

                                        $pembelian = \App\Models\Pembelian::find($state);
                                        if ($pembelian) {
                                            $set('plat_polisi', $pembelian->plat_polisi);
                                            $set('bruto', $pembelian->tara);
                                            $set('nama_supir', $pembelian->nama_supir);
                                            $set('nama_barang', $pembelian->nama_barang);
                                            $set('id_supplier', $pembelian->id_supplier);
                                            $keteranganBaru = in_array(intval($pembelian->keterangan), [1, 2, 3, 4])
                                                ? intval($pembelian->keterangan) + 1
                                                : $pembelian->keterangan;
                                            $set('keterangan', $keteranganBaru);
                                            $set('brondolan', $pembelian->brondolan);
                                        }
                                    })
                                    ->columnSpan(2),

                                TextInput::make('plat_polisi')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan plat polisi'),

                                TextInput::make('bruto')
                                    ->placeholder('Masukkan nilai bruto')
                                    ->label('Bruto')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 600)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $tara = $get('tara') ?? 0;
                                        $set('netto', max(0, intval($state) - intval($tara)));
                                    }),

                                TextInput::make('nama_supir')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Supir'),

                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->numeric()
                                    ->live(debounce: 600)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $bruto = $get('bruto') ?? 0;
                                        $set('netto', max(0, intval($bruto) - intval($state)));

                                        if (!empty($state) && empty($get('jam_keluar'))) {
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } elseif (empty($state)) {
                                            $set('jam_keluar', null);
                                        }
                                    }),

                                TextInput::make('nama_barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Barang'),

                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric(),

                                Select::make('id_supplier')
                                    ->label('Supplier')
                                    ->placeholder('Pilih Supplier')
                                    ->options(Supplier::pluck('nama_supplier', 'id'))
                                    ->searchable(),

                                Select::make('keterangan')
                                    ->label('Timbangan ke-')
                                    ->options([
                                        '1' => 'Timbangan ke-1',
                                        '2' => 'Timbangan ke-2',
                                        '3' => 'Timbangan ke-3',
                                        '4' => 'Timbangan ke-4',
                                        '5' => 'Timbangan ke-5',
                                    ])
                                    ->default('1')
                                    ->placeholder('Pilih timbangan ke-')
                                    ->native(false)
                                    ->required(),

                                // No Container manual
                                TextInput::make('no_container')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan No Container'),




                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('jumlah_karung')
                                            ->numeric()
                                            ->label('Jumlah Karung')
                                            ->autocomplete('off')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->disabled(fn(Get $get) => $get('brondolan') === 'CURAH')
                                            ->dehydrated(fn(Get $get) => $get('brondolan') !== 'CURAH'),

                                        Select::make('brondolan')
                                            ->label('Satuan Muatan')
                                            ->options([
                                                'GONI' => 'GONI',
                                                'CURAH' => 'CURAH',
                                            ])
                                            ->placeholder('Pilih Satuan Timbangan')
                                            ->native(false)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state === 'CURAH') {
                                                    $set('jumlah_karung', null);
                                                }
                                            }),
                                    ])->columnSpan(1),

                                FileUpload::make('foto')
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->imagePreviewHeight(200)
                                    ->label('Foto')
                                    ->columnSpanFull(),

                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()),
                            ])->columns(2),
                        Card::make('Returan')
                            ->schema([
                                Select::make('no_container_antar_pulau')
                                    ->label('No Container Antar Pulau(Retur)')
                                    ->disabled(fn(Get $get) => !empty($get('surat_jalan_id')))
                                    ->options(function () {
                                        $usedContainers = \App\Models\Pembelian::whereNotNull('no_container_antar_pulau')
                                            ->pluck('no_container_antar_pulau')
                                            ->toArray();

                                        return \App\Models\PembelianAntarPulau::query()
                                            ->whereNotIn('no_container', $usedContainers)
                                            ->get()
                                            ->pluck('no_container', 'no_container');
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Reset field lain jika ini dipilih
                                        if (!empty($state)) {
                                            $set('surat_jalan_id', null);
                                        }

                                        // Helper function untuk reset fields
                                        $resetFields = function () use ($set) {
                                            $set('bruto', null);
                                            $set('netto', null);
                                            $set('nama_barang', null);
                                            $set('no_container', null);
                                        };

                                        // Jika deselect, reset dan return
                                        if (empty($state)) {
                                            $resetFields();
                                            return;
                                        }

                                        // Cari data penjualan antar pulau dari no_container
                                        $penjualan = \App\Models\PenjualanAntarPulau::whereHas('pembelianAntarPulau', function ($q) use ($state) {
                                            $q->where('no_container', $state);
                                        })
                                            ->whereIn('status', ['TERIMA', 'SETENGAH'])
                                            ->latest()
                                            ->first();

                                        if ($penjualan) {
                                            $bruto = (int) $penjualan->netto_diterima;
                                            $tara = (int) ($get('tara') ?? 0);
                                            $netto = max(0, $bruto - $tara);

                                            $set('bruto', $bruto); // Ambil dari netto_diterima penjualan
                                            $set('netto', $netto); // Hitung bruto - tara
                                            $set('nama_barang', strtoupper($penjualan->nama_barang) . ' (Retur)');
                                            $set('no_container', strtoupper($penjualan->pembelianAntarPulau->no_container));
                                        } else {
                                            $resetFields();
                                        }
                                    }),

                                Select::make('surat_jalan_id')
                                    ->label('Pilih Surat Jalan(Retur)')
                                    ->disabled(fn(Get $get) => !empty($get('no_container_antar_pulau')))
                                    ->options(function () {
                                        return \App\Models\SuratJalan::query()
                                            ->where('status', 'retur')
                                            ->with('tronton')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [$item->id => "{$item->tronton->kode} - {$item->tronton->penjualan1->plat_polisi} - {$item->status}"];
                                            });
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Reset field lain jika ini dipilih
                                        if (!empty($state)) {
                                            $set('no_container_antar_pulau', null);
                                        }

                                        // Helper function untuk reset fields
                                        $resetFields = function () use ($set) {
                                            $set('plat_polisi', null);
                                            $set('nama_barang', null);
                                            // tambahkan field lain yang perlu direset
                                        };

                                        // Jika deselect, reset dan return
                                        if (empty($state)) {
                                            $resetFields();
                                            return;
                                        }

                                        // Cari data surat jalan
                                        $suratJalan = \App\Models\SuratJalan::find($state);

                                        // Set data atau reset jika tidak ada
                                        if ($suratJalan && $suratJalan->tronton && $suratJalan->tronton->penjualan1) {
                                            $set('plat_polisi', $suratJalan->tronton->penjualan1->plat_polisi);
                                            $set('nama_barang', $suratJalan->tronton->penjualan1->nama_barang);
                                            // tambahkan field lain sesuai kebutuhan
                                        } else {
                                            $resetFields();
                                        }
                                    }),
                            ])->columns(2)->collapsed()
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Pembelian $record): ?string {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if ($user && $user->hasRole('super_admin')) {
                    return EditPembelian::getUrl(['record' => $record]);
                }
                if ($user && $user->hasRole('timbangan') && !$record->tara) {
                    return EditPembelian::getUrl(['record' => $record]);
                }
                return null;
            })
            ->query(Pembelian::query())
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
                        Carbon::setLocale('id');
                        return Carbon::parse($state)->locale('id')->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),

                TextColumn::make('no_spb')->searchable()->copyable(),
                TextColumn::make('plat_polisi')->label('Plat Polisi')->searchable(),
                TextColumn::make('nama_supir')->searchable(),
                TextColumn::make('supplier.nama_supplier')->label('Supplier')->searchable(),
                TextColumn::make('nama_barang')->searchable(),
                TextColumn::make('keterangan')->prefix('Timbangan-')->searchable(),
                TextColumn::make('satuan_muatan')
                    ->label('Satuan Muatan')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $karung = $record->jumlah_karung ?? '';
                        $brondolan = $record->brondolan ?? '-';
                        if (strtolower($brondolan) === 'curah') {
                            return $brondolan;
                        }
                        return "{$karung} - {$brondolan}";
                    }),
                TextColumn::make('bruto')->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tara')->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('netto')->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                TextColumn::make('no_container')->label('No Container Manual')->searchable(),
                TextColumn::make('no_container_antar_pulau')->label('No Container Antar Pulau')->searchable(),

                TextColumn::make('jam_masuk'),
                TextColumn::make('jam_keluar'),

                ImageColumn::make('foto')
                    ->label('Foto 1')
                    ->getStateUsing(fn($record) => $record->foto[0] ?? null)
                    ->url(fn($record) => asset('storage/' . ($record->foto[0] ?? '')))
                    ->openUrlInNewTab(),

                TextColumn::make('user.name')->label('User'),
            ])
            ->defaultSort('no_spb', 'desc')
            ->actions([
                Tables\Actions\Action::make('view-pembelian')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-pembelian", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->headerActions([
                ExportAction::make()->exporter(PembelianExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(PembelianExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
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

                Filter::make('Tara Kosong')
                    ->query(fn(Builder $query) => $query->whereNull('tara'))
                    ->toggle()
                    ->default(fn() => !optional(Auth::user())->hasAnyRole(['super_admin'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
            'view-pembelian' => Pages\ViewPembelian::route('/{record}/view-pembelian'),
        ];
    }
}
