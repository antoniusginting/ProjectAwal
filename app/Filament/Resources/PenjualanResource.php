<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Silo;
use Filament\Tables;
use App\Models\Mobil;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use App\Models\LaporanLumbung;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Exports\PenjualanExporter;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\PenjualanResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\PenjualanResource\Pages\EditPenjualan;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;

class PenjualanResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Timbangan';
    public static ?string $label = 'Daftar Penjualan ';

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
                                        $nextId = (Penjualan::max('id') ?? 0) + 1;
                                        return 'J' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
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
                            ])->columns(4)->collapsed(),
                        Card::make()
                            ->schema([
                                Select::make('id_penjualan')
                                    ->label('Ambil dari Penjualan Sebelumnya')
                                    ->options(function () {
                                        $timbanganTrontonIds = \App\Models\SuratJalan::whereNotNull('id_timbangan_tronton')
                                            ->pluck('id_timbangan_tronton');

                                        $existingPenjualans = \App\Models\TimbanganTronton::whereIn('id', $timbanganTrontonIds)
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

                                        return \App\Models\Penjualan::latest()
                                            ->whereNotIn('id', $existingPenjualans)
                                            ->take(50)
                                            ->get()
                                            ->mapWithKeys(function ($penjualan) {
                                                return [
                                                    $penjualan->id => "{$penjualan->plat_polisi} - {$penjualan->nama_supir} - (Timbangan ke-{$penjualan->keterangan}) - {$penjualan->created_at->format('d:m:Y')}"
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        $penjualan = \App\Models\Penjualan::find($state);
                                        if ($state === null) {
                                            $set('plat_polisi', null);
                                            $set('bruto', null);
                                            $set('tara', null);
                                            $set('netto', null);
                                            $set('nama_supir', null);
                                            $set('nama_barang', 'JAGUNG KERING SUPER');
                                            $set('keterangan', null);
                                            $set('brondolan', null);
                                            return;
                                        }
                                        if ($penjualan) {
                                            $set('plat_polisi', $penjualan->plat_polisi);
                                            $set('tara', self::formatNumber($penjualan->bruto));
                                            $set('nama_supir', $penjualan->nama_supir);
                                            $set('nama_barang', $penjualan->nama_barang);
                                            $keteranganBaru = in_array(intval($penjualan->keterangan), [1, 2, 3, 4, 5])
                                                ? intval($penjualan->keterangan) + 1
                                                : $penjualan->keterangan;
                                            $set('keterangan', $keteranganBaru);
                                            $set('brondolan', $penjualan->brondolan);
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
                                    ->placeholder('Masukkan Nilai Bruto')
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
                                    ->columnSpan(2)
                                    ->placeholder('Masukkan Nama Supir')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->required(),

                                Hidden::make('is_calculated')
                                    ->default(false)
                                    ->dehydrated(false), // Tidak disimpan ke database

                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->columnSpan(2)
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->hint(fn(callable $get) => $get('is_calculated')
                                        ? 'Weight sudah dikonfirmasi ✅'
                                        : 'Confirm Weight ⚠️')
                                    ->hintColor(fn(callable $get) => $get('is_calculated') ? 'success' : 'danger')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Reset status kalkulasi ketika tara berubah
                                        $set('is_calculated', false);

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
                                            ->icon(fn(callable $get) => $get('is_calculated') ? 'heroicon-o-check-circle' : 'heroicon-o-calculator')
                                            ->tooltip(fn(callable $get) => $get('is_calculated') ? 'Weight sudah dikonfirmasi' : 'Confirm Weight')
                                            ->color(fn(callable $get) => $get('is_calculated') ? 'success' : 'primary')
                                            ->action(function ($state, callable $set, callable $get) {
                                                $bruto = self::parseNumber($get('bruto')) ?? 0;
                                                $tara = self::parseNumber($state) ?? 0;
                                                $netto = max(0, $bruto - $tara);
                                                $set('netto', self::formatNumber($netto));

                                                // Set status kalkulasi menjadi true
                                                $set('is_calculated', true);

                                                // Set jam keluar jika tara diisi dan belum ada jam keluar
                                                if (!empty($state) && empty($get('jam_keluar'))) {
                                                    $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                                } elseif (empty($state)) {
                                                    $set('jam_keluar', null);
                                                }
                                            })
                                    ),


                                TextInput::make('nama_barang')
                                    ->default('JAGUNG KERING SUPER')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->columnSpan(2)
                                    ->placeholder('Klik kalkulator pada field Tara untuk menghitung')
                                    ->dehydrateStateUsing(fn($state) => self::parseNumber($state)),

                                Select::make('keterangan')
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
                                    ->native(false)
                                    ->required(),

                                // UPDATED: Filter laporan lumbung yang tidak memiliki nama lumbung kosong
                                Select::make('laporan_lumbung_id')
                                    ->disabled(fn($get) => !empty($get('silo_id')))
                                    ->label('No Lumbung')
                                    ->options(function () {
                                        return LaporanLumbung::whereNull('status_silo')
                                            ->where('status', false)
                                            ->whereNotNull('lumbung')
                                            ->where('lumbung', '!=', '')
                                            ->where('lumbung', '!=', ' ')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                $keterangan = !empty($item->keterangan) && trim($item->keterangan) !== ''
                                                    ? ' - Ket : ' . $item->keterangan
                                                    : '';

                                                return [
                                                    $item->id => $item->kode . ' - ' . $item->lumbung . $keterangan
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
                                            $laporanLumbung = LaporanLumbung::find($state);
                                            if ($laporanLumbung) {
                                                $set('nama_lumbung', strtoupper($laporanLumbung->lumbung));
                                            }
                                        } else {
                                            $set('nama_lumbung', null);
                                        }
                                    }),

                                TextInput::make('nama_lumbung')
                                    ->readOnly()
                                    ->disabled(fn($get) => !empty($get('silo_id')))
                                    ->placeholder('Masukkan Nama Lumbung')
                                    ->autocomplete('off')
                                    ->columnSpan(1)
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state)),

                                TextInput::make('jumlah_karung')
                                    ->numeric()
                                    ->columnSpan(2)
                                    ->label('Jumlah Karung')
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan Jumlah Karung')
                                    ->disabled(fn(Get $get) => $get('brondolan') === 'CURAH')
                                    ->dehydrated(fn(Get $get) => $get('brondolan') !== 'CURAH'),

                                Select::make('brondolan')
                                    ->label('Satuan Muatan')
                                    ->columnSpan(1)
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

                                Select::make('silo_id')
                                    ->label('Silo')
                                    ->disabled(fn($get) => !empty($get('laporan_lumbung_id')))
                                    ->options(function () {
                                        return Silo::whereIn('nama', [
                                            'SILO STAFFEL A',
                                            'SILO STAFFEL B',
                                            'SILO 2500',
                                            'SILO 1800'
                                        ])
                                            ->where('status', '!=', true)
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->nama
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Pilih')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $silo = Silo::find($state);
                                            if ($silo) {
                                                $set('status_silo', $silo->nama);
                                            }
                                        } else {
                                            $set('status_silo', null);
                                        }
                                    }),

                                TextInput::make('no_container')
                                    ->label('No Container')
                                    ->placeholder('Masukkan no container')
                                    ->hidden(),

                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id())
                            ])->columns(4),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Penjualan $record): ?string {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                if ($user && $user->hasRole('super_admin')) {
                    return EditPenjualan::getUrl(['record' => $record]);
                }

                if ($user && $user->hasRole('timbangan')) {
                    if (!$record->bruto) {
                        return EditPenjualan::getUrl(['record' => $record]);
                    }
                    return null;
                }

                return null;
            })
            ->query(Penjualan::query())
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
                        return Carbon::parse($state)
                            ->locale('id')
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),

                TextColumn::make('no_spb')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),

                TextColumn::make('plat_polisi')->label('Plat Polisi')
                    ->searchable(),

                TextColumn::make('nama_supir')
                    ->searchable(),

                TextColumn::make('keterangan')
                    ->prefix('Timbangan ke-')
                    ->searchable(),

                TextColumn::make('satuan_muatan')
                    ->label('Satuan Muatan')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $karung = $record->jumlah_karung ?? '-';
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

                TextColumn::make('no_lumbung')
                    ->label('No Lumbung')
                    ->state(function ($record) {
                        if ($record->laporanLumbung && $record->laporanLumbung->kode) {
                            return $record->laporanLumbung->kode;
                        }

                        if ($record->silos && $record->silos->nama) {
                            return $record->silos->nama;
                        }

                        return null;
                    })
                    ->alignCenter()
                    ->placeholder('-'),

                TextColumn::make('nama_lumbung')
                    ->label('Nama Lumbung')
                    ->state(function ($record) {
                        if ($record->nama_lumbung) {
                            return $record->nama_lumbung;
                        }

                        if ($record->silos && $record->silos->nama) {
                            return $record->silos->nama;
                        }

                        return null;
                    })
                    ->searchable()
                    ->alignCenter()
                    ->placeholder('-'),

                TextColumn::make('nama_barang')
                    ->searchable(),

                TextColumn::make('jam_masuk')
                    ->alignCenter(),

                TextColumn::make('jam_keluar')
                    ->alignCenter(),

                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('no_spb', 'desc')
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

                Filter::make('Bruto Kosong')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('bruto')
                    )
                    ->toggle()
                    ->default(function () {
                        return !optional(Auth::user())->hasAnyRole(['super_admin']);
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('view-penjualan')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-penjualan", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->headerActions([
                ExportAction::make()->exporter(PenjualanExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(PenjualanExporter::class)->label('Export to Excel'),
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
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
            'view-penjualan' => Pages\ViewPenjualan::route('/{record}/view-penjualan'),
        ];
    }
}
