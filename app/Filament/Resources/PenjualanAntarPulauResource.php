<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PenjualanAntarPulau;
use App\Models\PembelianAntarPulau;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\PenjualanAntarPulauExporter;
use App\Filament\Resources\PenjualanAntarPulauResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class PenjualanAntarPulauResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PenjualanAntarPulau::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 3;
    public static ?string $label = 'Daftar Penjualan Antar Pulau ';
    protected static ?string $navigationLabel = 'Penjualan Antar Pulau';

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
        return $form->schema([
            Card::make()->schema([
                Card::make()->schema([
                    Placeholder::make('next_id')
                        ->label('No SPB')
                        ->content(function ($record) {
                            if ($record) {
                                return $record->kode;
                            }
                            $nextId = (PenjualanAntarPulau::max('id') ?? 0) + 1;
                            return 'CJ' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                        }),

                    TextInput::make('created_at')
                        ->label('Tanggal')
                        ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d-m-Y'))
                        ->disabled(),
                ])->columns(2)->collapsed(),

                Card::make()->schema([
                    TextInput::make('nama_barang')
                        ->autocomplete('off')
                        ->columnSpan(2)
                        ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                        ->placeholder('Masukkan Nama Barang'),

                    Select::make('pembelian_antar_pulau_id')
                        ->label('No Container')
                        ->columnSpan(2)
                        ->native(false)
                        ->required()
                        ->options(function (Get $get) {
                            /**
                             * Exclude container jika:
                             * - Sudah ada status TERIMA
                             * - Sudah ada 2x SETENGAH
                             * - ATAU status RETUR
                             */
                            $excludedContainers = PembelianAntarPulau::whereHas('penjualanAntarPulau', function ($q) {
                                $q->where(function ($query) {
                                    $query->where('status', 'TERIMA')
                                        ->orWhere('status', 'RETUR')
                                        ->orWhere(function ($subQuery) {
                                            $subQuery->where('status', 'SETENGAH')
                                                ->havingRaw('COUNT(*) >= 2');
                                        });
                                });
                            })
                                ->withCount(['penjualanAntarPulau' => function ($query) {
                                    $query->where('status', 'SETENGAH');
                                }])
                                ->get()
                                ->filter(function ($item) {
                                    $hasTerimaOrRetur = $item->penjualanAntarPulau()
                                        ->whereIn('status', ['TERIMA', 'RETUR'])
                                        ->exists();

                                    $setengahCount = $item->penjualan_antar_pulau_count ?? $item->penjualanAntarPulau()
                                        ->where('status', 'SETENGAH')
                                        ->count();

                                    return $hasTerimaOrRetur || $setengahCount >= 2;
                                })
                                ->pluck('id')
                                ->toArray();

                            $available = PembelianAntarPulau::whereNotIn('id', $excludedContainers)
                                ->with('kapasitasKontrakBeli')
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    $supplier = $item->kapasitasKontrakBeli?->nama ?? 'TANPA SUPPLIER';
                                    $setengahCount = $item->penjualanAntarPulau()
                                        ->where('status', 'SETENGAH')
                                        ->count();

                                    $usageIndicator = '';
                                    if ($setengahCount > 0) {
                                        $usageIndicator = " (Sudah diterima setengah)";
                                    }

                                    return [
                                        $item->id => "{$item->no_container} - {$item->nama_barang} - {$supplier} - {$item->kode_segel} - " .
                                            Carbon::parse($item->created_at)->format('d-m-y') . $usageIndicator
                                    ];
                                });

                            $currentId = $get('pembelian_antar_pulau_id');
                            if ($currentId && !$available->has($currentId)) {
                                if ($current = PembelianAntarPulau::with('kapasitasKontrakBeli')->find($currentId)) {
                                    $supplier = $current->kapasitasKontrakBeli?->nama ?? 'TANPA SUPPLIER';
                                    $label = "{$current->no_container} - {$current->nama_barang} - {$supplier} - {$current->nama_ekspedisi} - {$current->kode_segel} - " .
                                        Carbon::parse($current->created_at)->format('d-m-y') . ' (dipakai di record ini)';
                                    $available->put($current->id, $label);
                                }
                            }

                            return $available->toArray();
                        })
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $pembelian = PembelianAntarPulau::with('kapasitasKontrakBeli')->find($state);
                            if ($pembelian) {
                                $set('no_container', $pembelian->no_container);
                                $set('nama_barang', strtoupper($pembelian->nama_barang));
                                $set('nama_ekspedisi', strtoupper($pembelian->nama_ekspedisi));
                                $set('kode_segel', strtoupper($pembelian->kode_segel));
                                $set('netto', $pembelian?->netto ?? '');
                            } else {
                                $set('no_container', null);
                            }
                        }),

                    TextInput::make('kode_segel')
                        ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                        ->columnSpan(2)
                        ->autocomplete('off')
                        ->placeholder('Masukkan kode Segel'),

                    Select::make('kapasitas_kontrak_jual_id')
                        ->label('Kontrak')
                        ->columnSpan(2)
                        ->native(false)
                        ->required()
                        ->options(function () {
                            return \App\Models\KapasitasKontrakJual::query()
                                ->where('status', false)
                                ->where('nama', 'like', '%kontainer%')
                                ->pluck('nama', 'id')
                                ->toArray();
                        })
                        ->placeholder('Pilih Supplier')
                        ->searchable()
                        ->live(),

                    TextInput::make('netto')
                        ->label('Netto')
                        ->placeholder('Masukkan netto')
                        ->numeric()
                        ->columnSpan(2)
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            $status = $get('status');
                            if ($status === 'TERIMA') {
                                $set('netto_diterima', $state);
                            }
                        }),

                    Select::make('status')
                        ->native(false)
                        ->options([
                            'TERIMA'   => 'TERIMA',
                            'RETUR'    => 'RETUR',
                            'TOLAK'    => 'TOLAK',
                            'SETENGAH' => 'SETENGAH',
                        ])
                        ->label('Status')
                        ->placeholder('Belum ada Status')
                        ->live()
                        ->columnSpan(fn(callable $get) => match ($get('status')) {
                            'TERIMA' => 1,
                            'SETENGAH' => 1,
                            default => 2
                        })
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if ($state === 'RETUR') {
                                $nama = $get('nama_barang');
                                if ($nama && ! str_contains($nama, '(RETUR)')) {
                                    $set('nama_barang', trim($nama . ' (RETUR)'));
                                }
                            }
                        }),
                    TextInput::make('netto_diterima')
                        ->label('Netto Diterima')
                        ->columnSpan(1)
                        ->placeholder('Masukkan netto diterima')
                        ->numeric()
                        ->reactive()
                        ->visible(fn(callable $get) => in_array($get('status'), ['TERIMA', 'SETENGAH'])),
                    Hidden::make('user_id')
                        ->label('User ID')
                        ->default(Auth::id()),
                ])->columns(4),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            BadgeColumn::make('created_at')
                ->label('Tanggal')
                ->alignCenter()
                ->colors([
                    'success' => fn($state) => Carbon::parse($state)->isToday(),
                    'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                    'gray'    => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                ])
                ->formatStateUsing(function ($state) {
                    Carbon::setLocale('id');
                    return Carbon::parse($state)
                        ->locale('id')
                        ->isoFormat('D MMMM YYYY | HH:mm:ss');
                }),

            BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'success' => 'TERIMA',
                    'warning' => 'SETENGAH',
                    'danger'  => 'TOLAK',
                    'gray'    => 'RETUR',
                ]),

            TextColumn::make('kode')
                ->label('No SPB')
                ->searchable()
                ->copyable()
                ->copyMessage('berhasil menyalin'),

            TextColumn::make('pembelianAntarPulau.no_container')
                ->label('No Container')
                ->searchable(),

            TextColumn::make('kode_segel')
                ->label('Kode Segel')
                ->searchable(),

            TextColumn::make('kapasitasKontrakJual.nama')
                ->label('Supplier')
                ->alignCenter()
                ->searchable(),

            TextColumn::make('nama_barang')->searchable(),

            TextColumn::make('netto')
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

            TextColumn::make('user.name')->label('User'),
        ])
            ->defaultSort('kode', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['dari_tanggal']) && ! empty($data['sampai_tanggal'])) {
                            return $query->whereBetween('created_at', [
                                Carbon::parse($data['dari_tanggal'])->startOfDay(),
                                Carbon::parse($data['sampai_tanggal'])->endOfDay(),
                            ]);
                        }

                        if (! empty($data['dari_tanggal'])) {
                            return $query->where('created_at', '>=', Carbon::parse($data['dari_tanggal'])->startOfDay());
                        }

                        if (! empty($data['sampai_tanggal'])) {
                            return $query->where('created_at', '<=', Carbon::parse($data['sampai_tanggal'])->endOfDay());
                        }

                        return $query;
                    }),
            ])
            ->headerActions([
                ExportAction::make()->exporter(PenjualanAntarPulauExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(PenjualanAntarPulauExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPenjualanAntarPulaus::route('/'),
            'create' => Pages\CreatePenjualanAntarPulau::route('/create'),
            'edit'   => Pages\EditPenjualanAntarPulau::route('/{record}/edit'),
        ];
    }
}
