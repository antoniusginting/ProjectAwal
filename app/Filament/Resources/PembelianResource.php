<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Mobil;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
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
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;

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

    /**
     * Helper method untuk format angka dengan titik sebagai pemisah ribuan
     */
    private static function formatNumber($number): string
    {
        if (!$number) return '0';
        return number_format((float)$number, 0, ',', '.');
    }

    /**
     * Helper method untuk convert formatted number ke integer
     */
    private static function parseNumber($formattedNumber): ?int
    {
        if (!$formattedNumber) return null;
        return (int) str_replace('.', '', $formattedNumber);
    }

    /**
     * Helper method untuk reset fields retur
     */
    private static function resetReturFields(callable $set): void
    {
        $set('bruto', null);
        $set('netto', null);
        $set('nama_barang', null);
        $set('no_container', null);
        $set('plat_polisi', null);
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

                                            // Reset semua field retur ketika ambil dari pembelian sebelumnya
                                            $set('tipe_retur', null);
                                            $set('no_container_antar_pulau', null);
                                            $set('surat_jalan_id', null);
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
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Format angka saat user selesai mengetik
                                        if ($state) {
                                            $cleanNumber = preg_replace('/[^\d]/', '', $state);
                                            $formatted = self::formatNumber($cleanNumber);
                                            $set('bruto', $formatted);
                                        }
                                    })
                                    ->dehydrateStateUsing(fn($state) => self::parseNumber($state)),

                                TextInput::make('nama_supir')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Supir'),

                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->hint('Jangan lupa confirm Weight')
                                    ->hintColor('danger')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Format angka saat user selesai mengetik
                                        if ($state) {
                                            $cleanNumber = preg_replace('/[^\d]/', '', $state);
                                            $formatted = self::formatNumber($cleanNumber);
                                            $set('tara', $formatted);
                                        }
                                    })
                                    ->dehydrateStateUsing(fn($state) => self::parseNumber($state))
                                    ->suffixAction(
                                        Action::make('hitungNetto')
                                            ->icon('heroicon-o-calculator')
                                            ->tooltip('Confirm Weight')
                                            ->color('primary')
                                            ->action(function ($state, callable $set, callable $get) {
                                                $bruto = self::parseNumber($get('bruto')) ?? 0;
                                                $tara = self::parseNumber($state) ?? 0;
                                                $netto = max(0, $bruto - $tara);
                                                $set('netto', self::formatNumber($netto));

                                                // Set jam keluar jika tara diisi dan belum ada jam keluar
                                                if (!empty($state) && empty($get('jam_keluar'))) {
                                                    $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                                } elseif (empty($state)) {
                                                    $set('jam_keluar', null);
                                                }

                                                // Notifikasi berhasil
                                                Notification::make()
                                                    ->title('Netto berhasil dihitung!')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),

                                TextInput::make('nama_barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Barang'),

                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->placeholder('Klik kalkulator pada field Tara untuk menghitung')
                                    ->dehydrateStateUsing(fn($state) => self::parseNumber($state)),

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
                                            ->live(debounce: 200)
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
                                // Pilihan tipe retur
                                Select::make('tipe_retur')
                                    ->label('Pilih Tipe Retur')
                                    ->options([
                                        'container_antar_pulau' => 'Container Antar Pulau',
                                        'surat_jalan' => 'Surat Jalan',
                                    ])
                                    ->placeholder('Pilih tipe retur (opsional)')
                                    ->native(false)
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('no_container_antar_pulau', null);
                                        $set('surat_jalan_id', null);

                                        if (empty($state)) {
                                            self::resetReturFields($set);
                                        }
                                    })
                                    ->columnSpan(fn(Get $get) => $get('tipe_retur') ? 1 : 2),

                                // Container Antar Pulau
                                Select::make('no_container_antar_pulau')
                                    ->label('No Container Antar Pulau')
                                    ->visible(fn(Get $get) => $get('tipe_retur') === 'container_antar_pulau')
                                    ->options(function () {
                                        try {
                                            $usedContainers = \App\Models\Pembelian::whereNotNull('no_container_antar_pulau')
                                                ->pluck('no_container_antar_pulau')
                                                ->toArray();

                                            return \App\Models\PembelianAntarPulau::query()
                                                ->whereNotIn('no_container', $usedContainers)
                                                ->get()
                                                ->pluck('no_container', 'no_container');
                                        } catch (\Exception $e) {
                                            Log::error('Error loading container options: ' . $e->getMessage());
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        try {
                                            if (empty($state)) {
                                                self::resetReturFields($set);
                                                return;
                                            }

                                            $containerExists = \App\Models\PembelianAntarPulau::where('no_container', $state)->exists();
                                            if (!$containerExists) {
                                                Log::warning("Container {$state} not found in PembelianAntarPulau");
                                                self::resetReturFields($set);
                                                return;
                                            }

                                            $penjualan = \App\Models\PenjualanAntarPulau::with('pembelianAntarPulau')
                                                ->whereHas('pembelianAntarPulau', function ($q) use ($state) {
                                                    $q->where('no_container', $state);
                                                })
                                                ->whereIn('status', ['TERIMA', 'SETENGAH', 'RETUR'])
                                                ->latest()
                                                ->first();

                                            if ($penjualan) {
                                                $bruto = (int) $penjualan->netto_diterima;
                                                $tara = self::parseNumber($get('tara')) ?? 0;
                                                $netto = max(0, $bruto - $tara);

                                                $set('bruto', self::formatNumber($bruto));
                                                $set('netto', self::formatNumber($netto));
                                                $set('nama_barang', strtoupper($penjualan->nama_barang) . ' (RETUR)');
                                                $set('no_container', strtoupper(optional($penjualan->pembelianAntarPulau)->no_container));
                                            } else {
                                                self::resetReturFields($set);
                                            }
                                        } catch (\Exception $e) {
                                            Log::error('Error in container afterStateUpdated: ' . $e->getMessage());
                                            self::resetReturFields($set);
                                        }
                                    }),

                                // Surat Jalan
                                Select::make('no_surat_jalan')
                                    ->label('Pilih Surat Jalan')
                                    ->visible(fn(Get $get) => $get('tipe_retur') === 'surat_jalan')
                                    ->options(function () {
                                        try {
                                            return \App\Models\SuratJalan::query()
                                                ->where('status', 'retur')
                                                ->with(['tronton', 'tronton.penjualan1'])
                                                ->get()
                                                ->mapWithKeys(function ($item) {
                                                    $kode = $item->tronton->kode ?? 'N/A';
                                                    $plat = $item->tronton->penjualan1->plat_polisi ?? 'N/A';
                                                    return [$item->id => "{$kode} - {$plat} - {$item->status}"];
                                                });
                                        } catch (\Exception $e) {
                                            Log::error('Error loading surat jalan options: ' . $e->getMessage());
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        try {
                                            if (empty($state)) {
                                                self::resetReturFields($set);
                                                return;
                                            }

                                            $suratJalan = \App\Models\SuratJalan::with(['tronton', 'tronton.penjualan1'])
                                                ->find($state);

                                            if ($suratJalan && $suratJalan->tronton && $suratJalan->tronton->penjualan1) {
                                                $penjualan1 = $suratJalan->tronton->penjualan1;

                                                $set('plat_polisi', $penjualan1->plat_polisi);
                                                $set('nama_barang', strtoupper($penjualan1->nama_barang) . ' (RETUR SURAT JALAN)');
                                                $set('nama_supir', $penjualan1->nama_supir ?? null);
                                            } else {
                                                self::resetReturFields($set);
                                            }
                                        } catch (\Exception $e) {
                                            Log::error('Error in surat jalan afterStateUpdated: ' . $e->getMessage());
                                            self::resetReturFields($set);
                                        }
                                    }),
                            ])
                            ->columns(2)
                            ->collapsed()
                            ->collapsible()

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

                TextColumn::make('bruto')
                    ->formatStateUsing(fn($state) => self::formatNumber($state)),

                TextColumn::make('tara')
                    ->formatStateUsing(fn($state) => self::formatNumber($state)),

                TextColumn::make('netto')
                    ->formatStateUsing(fn($state) => self::formatNumber($state)),

                TextColumn::make('no_container')->label('No Container Manual')->searchable(),
                TextColumn::make('no_container_antar_pulau')->label('No Container Antar Pulau')->searchable(),

                // Tambahan kolom untuk surat jalan
                TextColumn::make('no_surat_jalan')
                    ->label('Kode Surat Jalan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                // Filter tambahan untuk retur
                Filter::make('retur_container')
                    ->label('Retur Container')
                    ->query(fn(Builder $query) => $query->whereNotNull('no_container_antar_pulau'))
                    ->toggle(),

                Filter::make('retur_surat_jalan')
                    ->label('Retur Surat Jalan')
                    ->query(fn(Builder $query) => $query->whereNotNull('surat_jalan_id'))
                    ->toggle(),
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
