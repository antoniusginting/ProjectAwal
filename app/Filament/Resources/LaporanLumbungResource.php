<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LaporanLumbung;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
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
                Card::make()
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
                            })->columnSpanFull(),
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

                                            return $query->whereNotIn('dryers.id', $usedLaporanIds);
                                        }
                                    )
                                    ->preload()
                                    ->reactive()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return $record->no_dryer . ' - ' . $record->lumbung_tujuan;
                                    }),
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
                                        }),
                            ])->columnSpan(1),

                        // Select::make('timbanganTrontons')
                        //     ->label('Laporan Penjualan')
                        //     ->multiple()
                        //     ->relationship('timbanganTrontons', 'kode') // ganti dengan field yang ingin ditampilkan
                        //     ->preload()
                        //     ->getOptionLabelFromRecordUsing(function ($record) {
                        //         $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : 'N/A';
                        //         return $record->kode . ' - ' . $noBk . ' - ' . ($record->penjualan1->nama_supir ?? '') . ' - ' . $record->total_netto;
                        //     }),
                        Hidden::make('user_id')
                            ->label('User ID')
                            ->default(Auth::id()) // Set nilai default user yang sedang login,
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('No Laporan')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('dryers.no_dryer')
                    ->alignCenter()
                    ->searchable()
                    ->label('Dryer'),
                TextColumn::make('timbanganTrontons.kode')
                    ->searchable()
                    ->alignCenter()
                    ->label('No Laporan Penjualan'),
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
