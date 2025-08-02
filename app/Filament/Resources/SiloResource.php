<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Silo;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\SiloResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\SiloResource\RelationManagers;

class SiloResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Silo::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Kapasitas Lumbung';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Silo';
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
                        TextInput::make('stok')
                            ->label('Stok Awal')
                            ->placeholder('Masukkan stok awal')
                            ->live(debounce: 200) // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                        Select::make('nama')
                            ->native(false)
                            ->required()
                            ->options([
                                'SILO STAFFEL A' => 'SILO STAFFEL A',
                                'SILO STAFFEL B' => 'SILO STAFFEL B',
                                'SILO 2500' => 'SILO 2500',
                                'SILO 1800' => 'SILO 1800',
                            ])
                            ->label('STOK')
                            ->placeholder('Pilih Stok')
                            ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                // Disable saat edit, misal jika $record ada berarti edit
                                return $record !== null;
                            })
                            ->live(),
                        Toggle::make('status')
                            ->label('Status')
                            ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                            ->default(false) // Default false (buka)
                            ->onColor('danger') // Warna merah saat true (tutup)
                            ->offColor('success'), // Warna hijau saat false (buka)
                        // Card::make('STOK BESAR')
                        //     ->schema([

                        // Select::make('laporanLumbungs')
                        //     ->label('Laporan Lumbung')
                        //     ->multiple()
                        //     ->disabled(function (callable $get) {
                        //         $selectedNama = $get('nama');

                        //         // Disable jika user memilih salah satu dari opsi silo
                        //         return !in_array($selectedNama, [
                        //             'SILO STAFFEL A',
                        //             'SILO STAFFEL B',
                        //             'SILO 2500',
                        //             'SILO 1800'
                        //         ]);
                        //     })
                        //     ->relationship(
                        //         name: 'laporanLumbungs',
                        //         titleAttribute: 'nama',
                        //         modifyQueryUsing: function (Builder $query, Get $get) {
                        //             $selectedSilo = $get('nama'); // Ambil nilai dari select nama

                        //             return $query->orderBy('created_at', 'desc')
                        //                 ->whereNotNull('status_silo')
                        //                 ->where('status_silo', '!=', '')
                        //                 ->when($selectedSilo, function ($query) use ($selectedSilo) {
                        //                     return $query->where('status_silo', $selectedSilo);
                        //                 });
                        //         }
                        //     )
                        //     ->preload()
                        //     ->searchable()
                        //     ->getOptionLabelFromRecordUsing(function ($record) {
                        //         return $record->kode . ' - ' . $record->status_silo . ' - BERAT : ' . $record->berat_langsir . ' - ' . $record->created_at->format('d/m/Y');
                        //     }),
                        // ])->columnSpan(1),
                        // Card::make('PENJUALAN')
                        //     ->schema([

                        // Select::make('timbanganTrontons')
                        //     ->label('Laporan Penjualan')
                        //     ->multiple()
                        //     ->relationship(
                        //         name: 'timbanganTrontons',
                        //         titleAttribute: 'kode',
                        //         modifyQueryUsing: function (Builder $query, $get) {
                        //             $currentRecordId = null;

                        //             if (request()->route('record')) {
                        //                 $currentRecordId = request()->route('record');
                        //             }

                        //             try {
                        //                 $livewire = \Livewire\Livewire::current();
                        //                 if ($livewire && method_exists($livewire, 'getRecord')) {
                        //                     $record = $livewire->getRecord();
                        //                     if ($record) {
                        //                         $currentRecordId = $record->getKey();
                        //                     }
                        //                 }
                        //             } catch (\Exception $e) {
                        //                 // Ignore error
                        //             }

                        //             $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];
                        //             $relasiLuar = ['luar1', 'luar2', 'luar3'];
                        //             $selectedNama = $get('nama');

                        //             $query = $query->where(function ($query) use ($relasiPenjualan, $relasiLuar, $selectedNama) {
                        //                 // Logic 1: Cek nama_lumbung dari relasi penjualan
                        //                 $query->where(function ($subQuery) use ($relasiPenjualan, $selectedNama) {
                        //                     foreach ($relasiPenjualan as $index => $relasi) {
                        //                         $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                        //                         $subQuery->$method($relasi, function (Builder $q) use ($selectedNama) {
                        //                             $q->whereNotNull('nama_lumbung')
                        //                                 ->where('nama_lumbung', '!=', '');
                        //                             if ($selectedNama) {
                        //                                 $q->where('nama_lumbung', $selectedNama);
                        //                             }
                        //                         });
                        //                     }
                        //                 });

                        //                 // Logic 2: Atau cek nama_supplier dari relasi luar->supplier
                        //                 $query->orWhere(function ($subQuery) use ($relasiLuar, $selectedNama) {
                        //                     foreach ($relasiLuar as $index => $relasi) {
                        //                         $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                        //                         $subQuery->$method($relasi, function (Builder $q) use ($selectedNama) {
                        //                             // Cek apakah ada relasi supplier
                        //                             $q->whereHas('supplier', function (Builder $supplierQuery) use ($selectedNama) {
                        //                                 $supplierQuery->whereNotNull('nama_supplier')
                        //                                     ->where('nama_supplier', '!=', '');
                        //                                 if ($selectedNama) {
                        //                                     $supplierQuery->where('nama_supplier', $selectedNama);
                        //                                 }
                        //                             });
                        //                         });
                        //                     }
                        //                 });
                        //             });

                        //             $query->where(function ($q) {
                        //                 $q->where('status', false)->orWhereNull('status');
                        //             });
                        //             $query->orderBy('timbangan_trontons.created_at', 'desc');
                        //             $query->limit(20);
                        //             return $query;
                        //         }
                        //     )
                        //     ->disabled(fn($get) => !$get('nama'))
                        //     ->preload()
                        //     ->reactive()
                        //     ->getOptionLabelFromRecordUsing(function ($record) {
                        //         $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : ($record->luar1 ? $record->luar1->no_container : 'N/A');
                        //         $supir = $record->penjualan1 ? $record->penjualan1->nama_supir : ($record->luar1 ? $record->luar1->kode_segel : 'N/A');
                        //         return $record->kode . ' - ' . $noBk . ' - ' . $supir . ' - ' . $record->total_netto;
                        //     })
                        //     ->afterStateUpdated(function ($state, $set, $get) {
                        //         // Ambil data laporan penjualan sebelumnya
                        //         $existingLaporan = $get('laporan_penjualan_sebelumnya') ?
                        //             explode(',', $get('laporan_penjualan_sebelumnya')) : [];

                        //         // Gabungkan dengan data baru
                        //         if ($state) {
                        //             // Ambil kode dari TimbanganTronton yang dipilih
                        //             $newKodes = \App\Models\TimbanganTronton::whereIn('id', $state)
                        //                 ->pluck('kode')
                        //                 ->toArray();

                        //             // Gabungkan dengan data existing, hapus duplikat
                        //             $allKodes = array_unique(array_merge($existingLaporan, $newKodes));

                        //             // Set kembali ke field laporan_penjualan_sebelumnya
                        //             $set('laporan_penjualan_sebelumnya', implode(',', $allKodes));
                        //         }
                        //     }),
                        // ])->columnSpan(1)
                    ])->columns(2),
                // Card::make('Penambahan Stock')
                //     ->schema([
                //         // Repeater untuk stock additions
                //         Repeater::make('stockLuar')
                //             ->label('')
                //             ->relationship()
                //             ->schema([
                //                 Grid::make(3)
                //                     ->schema([
                //                         TextInput::make('quantity')
                //                             ->label('Jumlah')
                //                             ->placeholder('Masukkan jumlah')
                //                             ->required()
                //                             ->live(),
                //                         // ->extraAttributes([
                //                         //     'x-data' => '{}',
                //                         //     'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                //                         // ])
                //                         // ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database

                //                         DatePicker::make('date_added')
                //                             ->label('Tanggal')
                //                             ->default(now())
                //                             ->required(),

                //                         TextInput::make('notes')
                //                             ->label('Catatan')
                //                             ->placeholder('Catatan penambahan'),
                //                     ])
                //             ])
                //             ->addActionLabel('Tambah Stok')
                //             ->collapsible()
                //             ->columns(2)
                //             ->collapsed()
                //             ->itemLabel(
                //                 fn(array $state): ?string =>
                //                 isset($state['quantity']) && isset($state['date_added'])
                //                     ? number_format($state['quantity'], 0, ',', '.') . ' - ' . date('d/m/Y', strtotime($state['date_added']))
                //                     : 'Penambahan Stok Baru'
                //             ),
                //     ])->collapsed()
                //     ->visible(
                //         fn(Get $get): bool =>
                //         in_array($get('nama'), [
                //             'SILO STAFFEL A',
                //             'SILO STAFFEL B',
                //             'SILO 2500',
                //             'SILO 1800'
                //         ])
                //     ),
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
                TextColumn::make('stok')->label('Stok Awal')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama')->label('Nama Silo'),
                TextColumn::make('laporanLumbungs.kode')
                    ->alignCenter()
                    ->searchable()
                    ->placeholder('-----')
                    ->label('NO IO')
                    ->getStateUsing(function ($record) {
                        $laporanlumbung = $record->laporanlumbungs->pluck('kode');

                        if ($laporanlumbung->count() <= 3) {
                            return $laporanlumbung->implode(', ');
                        }

                        return $laporanlumbung->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $laporanlumbung = $record->laporanlumbungs->pluck('kode');
                        return $laporanlumbung->implode(', ');
                    }),
                TextColumn::make('penjualans.no_spb')
                    ->alignCenter()
                    ->searchable()
                    ->placeholder('-----')
                    ->label('No Penjualan')
                    ->getStateUsing(function ($record) {
                        $penjualan = $record->penjualans->pluck('no_spb');

                        if ($penjualan->count() <= 3) {
                            return $penjualan->implode(', ');
                        }

                        return $penjualan->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $penjualan = $record->penjualans->pluck('no_spb');
                        return $penjualan->implode(', ');
                    }),
                // TextColumn::make('timbanganTrontons.kode')
                //     ->alignCenter()
                //     ->searchable()
                //     ->label('NO Laporan Penjualan')
                //     ->getStateUsing(function ($record) {
                //         $timbangantronton = $record->timbangantrontons->pluck('kode');

                //         if ($timbangantronton->count() <= 3) {
                //             return $timbangantronton->implode(', ');
                //         }

                //         return $timbangantronton->take(3)->implode(', ') . '...';
                //     })
                //     ->tooltip(function ($record) {
                //         $timbangantronton = $record->timbangantrontons->pluck('kode');
                //         return $timbangantronton->implode(', ');
                //     }),
                // TextColumn::make('laporanLumbungs.kode')
                //     ->alignCenter()
                //     ->label('No IO'),

                // TextColumn::make('timbanganTrontons.kode')
                //     ->searchable()
                //     ->alignCenter()
                //     ->label('No Laporan Penjualan'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc') // Megurutkan created_at terakhir menjadi pertama pada tabel
            ->actions([
                Tables\Actions\Action::make('view-penjualan')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-silo", ['record' => $record->id])),
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
            'index' => Pages\ListSilos::route('/'),
            'create' => Pages\CreateSilo::route('/create'),
            'edit' => Pages\EditSilo::route('/{record}/edit'),
            'view-silo' => Pages\ViewSilo::route('/{record}/view-silo'),
        ];
    }
}
