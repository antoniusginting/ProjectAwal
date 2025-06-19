<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Silo;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;

use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\SiloResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SiloResource\RelationManagers;

class SiloResource extends Resource
{
    protected static ?string $model = Silo::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Kapasitas';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Silo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Card::make()
                    ->schema([
                        TextInput::make('stok')
                            ->numeric()
                            ->label('Stok Awal')
                            ->placeholder('Masukkan stok awal'),
                        Select::make('nama')
                            ->native(false)
                            ->required()
                            ->options([
                                'SILO BESAR' => 'SILO BESAR',
                                'SILO STAFFEL A' => 'SILO STAFFEL A',
                                'SILO STAFFEL B' => 'SILO STAFFEL B',
                            ])
                            ->label('SILO')
                            ->placeholder('Pilih silo')
                            ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                // Disable saat edit, misal jika $record ada berarti edit
                                return $record !== null;
                            }),
                        Card::make('STOK BESAR')
                            ->schema([
                            
                                Select::make('laporanLumbungs')
                                    ->label('Laporan Lumbung')
                                    ->multiple()
                                    ->relationship(
                                        name: 'laporanLumbungs',
                                        titleAttribute: 'nama',
                                        modifyQueryUsing: function (Builder $query) {
                                            return $query->orderBy('created_at', 'desc')
                                                ->whereNotNull('status_silo')
                                                ->where('status_silo', '!=', ''); // memastikan tidak kosong
                                        }
                                    )
                                    ->preload()
                                    ->searchable()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return $record->kode . ' - ' . $record->created_at->format('d/m/Y');
                                    }),
                            ])->columnSpan(1),
                        Card::make('PENJUALAN')
                            ->schema([

                                Select::make('timbanganTrontons')
                                    ->label('Laporan Penjualan')
                                    ->multiple()
                                    ->relationship(
                                        name: 'timbanganTrontons',
                                        titleAttribute: 'kode',
                                        modifyQueryUsing: function (Builder $query, $get) {
                                            $currentRecordId = null;

                                            if (request()->route('record')) {
                                                $currentRecordId = request()->route('record');
                                            }

                                            try {
                                                $livewire = \Livewire\Livewire::current();
                                                if ($livewire && method_exists($livewire, 'getRecord')) {
                                                    $record = $livewire->getRecord();
                                                    if ($record) {
                                                        $currentRecordId = $record->getKey();
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore error
                                            }

                                            $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];
                                            $selectedNamaLumbung = $get('nama');

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
                                                $q->where('status', false)->orWhereNull('status');
                                            });
                                            $query->orderBy('timbangan_trontons.created_at', 'desc');
                                            $query->limit(20);
                                            return $query;
                                        }
                                    )
                                    ->disabled(fn($get) => !$get('nama'))
                                    ->preload()
                                    ->reactive()
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $noBk = $record->penjualan1 ? $record->penjualan1->plat_polisi : 'N/A';
                                        return $record->kode . ' - ' . $noBk . ' - ' . ($record->penjualan1->nama_supir ?? '') . ' - ' . $record->total_netto;
                                    })
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Ambil data laporan penjualan sebelumnya
                                        $existingLaporan = $get('laporan_penjualan_sebelumnya') ?
                                            explode(',', $get('laporan_penjualan_sebelumnya')) : [];

                                        // Gabungkan dengan data baru
                                        if ($state) {
                                            // Ambil kode dari TimbanganTronton yang dipilih
                                            $newKodes = \App\Models\TimbanganTronton::whereIn('id', $state)
                                                ->pluck('kode')
                                                ->toArray();

                                            // Gabungkan dengan data existing, hapus duplikat
                                            $allKodes = array_unique(array_merge($existingLaporan, $newKodes));

                                            // Set kembali ke field laporan_penjualan_sebelumnya
                                            $set('laporan_penjualan_sebelumnya', implode(',', $allKodes));
                                        }
                                    }),
                            ])->columnSpan(1)
                    ])->columns(2)
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
                TextColumn::make('stok')->label('Stok Awal'),
                TextColumn::make('nama')->label('Nama Silo'),
                TextColumn::make('laporanLumbungs.kode')
                    ->alignCenter()
                    ->label('No IO'),
                TextColumn::make('timbanganTrontons.kode')
                    ->searchable()
                    ->alignCenter()
                    ->label('No Laporan Penjualan'),
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
