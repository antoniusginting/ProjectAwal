<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use App\Models\KapasitasKontrakBeli;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KapasitasKontrakBeliResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KapasitasKontrakBeliResource\RelationManagers;

class KapasitasKontrakBeliResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = KapasitasKontrakBeli::class;
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
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Kapasitas Kontrak Beli';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('stok')
                            ->label('Stok Awal')
                            ->placeholder('Masukkan stok awal')
                            ->live() // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                        Select::make('nama')
                            ->native(false)
                            ->searchable()
                            ->required()
                            ->options([
                                'MAKASSAR' => 'MAKASSAR',
                                'GORONTALO' => 'GORONTALO',
                            ])
                            ->label('Kontrak')
                            ->placeholder('Pilih Kontrak')
                            ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                // Disable saat edit, misal jika $record ada berarti edit
                                return $record !== null;
                            })
                            ->live(),
                        TextInput::make('harga')
                            ->label('Harga')
                            ->placeholder('Masukkan harga')
                            ->live() // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                        Toggle::make('status')
                            ->label('Status')
                            ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                            ->default(false) // Default false (buka)
                            ->onColor('danger') // Warna merah saat true (tutup)
                            ->offColor('success'), // Warna hijau saat false (buka)
                        TextInput::make('supplier')
                            ->label('Supplier'),
                    ])->columns(4)
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
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('harga')->label('Harga')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama')
                    ->label('Jagung')
                    ->searchable(),
                TextColumn::make('supplier')
                    ->label('Supplier')
                    ->searchable(),
                // TextColumn::make('penjualanLuar.kode')
                //     ->alignCenter()
                //     ->searchable()
                //     ->placeholder('-----')
                //     ->label('Kode Penjualan')
                //     ->getStateUsing(function ($record) {
                //         $penjualanluar = $record->penjualanLuar->pluck('kode');

                //         if ($penjualanluar->count() <= 3) {
                //             return $penjualanluar->implode(', ');
                //         }

                //         return $penjualanluar->take(3)->implode(', ') . '...';
                //     })
                //     ->tooltip(function ($record) {
                //         $penjualanluar = $record->penjualanLuar->pluck('kode');
                //         return $penjualanluar->implode(', ');
                //     }),
                // TextColumn::make('pembelianLuar.kode')
                //     ->alignCenter()
                //     ->searchable()
                //     ->placeholder('-----')
                //     ->label('Kode Pembelian')
                //     ->getStateUsing(function ($record) {
                //         $pembelianluar = $record->pembelianLuar->pluck('kode');

                //         if ($pembelianluar->count() <= 3) {
                //             return $pembelianluar->implode(', ');
                //         }

                //         return $pembelianluar->take(3)->implode(', ') . '...';
                //     })
                //     ->tooltip(function ($record) {
                //         $pembelianluar = $record->pembelianLuar->pluck('kode');
                //         return $pembelianluar->implode(', ');
                //     }),
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-kapasitas-kontrak-beli')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-kapasitas-kontrak-beli", ['record' => $record->id])),
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
            'index' => Pages\ListKapasitasKontrakBelis::route('/'),
            'create' => Pages\CreateKapasitasKontrakBeli::route('/create'),
            'edit' => Pages\EditKapasitasKontrakBeli::route('/{record}/edit'),
            'view-kapasitas-kontrak-beli' => Pages\ViewKapasitasKontrakBeli::route('/{record}/view-kapasitas-kontrak-beli'),

            // Tambah 2 route baru
            'gorontalo' => Pages\GorontaloGabungan::route('/gorontalo'),
            'makassar' => Pages\MakassarGabungan::route('/makassar'),
        ];
    }
}
