<?php

namespace App\Filament\Resources;

use DateTime;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KendaraanMasuks;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanMasuksResource\Pages;
use App\Filament\Resources\KendaraanMasuksResource\RelationManagers;

class KendaraanMasuksResource extends Resource
{


    protected static ?string $model = KendaraanMasuks::class;

    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-c-truck';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Kendaraan Masuk';
    public static ?string $label = 'Daftar Kendaraan Masuk ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'TAMU' => 'TAMU',
                                        'SUPPLIER' => 'SUPPLIER',
                                        'BONAR JAYA' => 'BONAR JAYA',
                                    ])
                                    ->placeholder('Pilih Status')
                                    ->native(false)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('jam_masuk')
                                    ->label('Jam Masuk')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock'),
                                // ->afterStateHydrated(function ($state, callable $set, $record) {
                                //     // Kalau sedang create (tidak ada record) dan jam_masuk masih kosong
                                //     if (empty($state) && !$record) {
                                //         $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                //     }
                                // }),
                                TextInput::make('nama_sup_per')
                                    ->placeholder('Masukkan nama supplier atau perusahaan')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),

                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock'),
                                TextInput::make('plat_polisi')
                                    ->placeholder('Masukkan plat polisi')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),
                                TextInput::make('nomor_antrian')
                                    ->numeric()
                                    ->label('Nomor Antrian')
                                    ->placeholder('Masukkan Nomor Antrian'),
                                // ->afterStateHydrated(function ($state, callable $set, $record) {
                                //     // Kalau create (record belum ada), generate nomor otomatis
                                //     if (!$record) {
                                //         $lastNomor = KendaraanMasuks::max('nomor_antrian'); // Ganti dengan model sesuai tabel kamu
                                //         $set('nomor_antrian', $lastNomor ? $lastNomor + 1 : 1);
                                //     }
                                // }),
                                TextInput::make('nama_barang')
                                    ->placeholder('Masukkan nama barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('status_awal')
                                            ->helperText('Klik jika sudah Masuk')
                                            ->onIcon('heroicon-m-bolt')
                                            ->offIcon('heroicon-m-user')
                                            ->dehydrated(true)
                                            ->columns(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Toggle aktif, isi jam_masuk
                                                    $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                                } else {
                                                    // Toggle nonaktif, kosongkan jam_masuk
                                                    $set('jam_masuk', null);
                                                }
                                            }),
                                        Toggle::make('status_selesai')
                                            ->helperText('Klik jika sudah Keluar')
                                            ->onIcon('heroicon-m-bolt')
                                            ->offIcon('heroicon-m-user')
                                            ->dehydrated(true)
                                            ->columns(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Toggle aktif, isi jam_keluar
                                                    $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                                } else {
                                                    // Toggle nonaktif, kosongkan jam_keluar
                                                    $set('jam_keluar', null);
                                                }
                                            }),
                                    ])->columnSpan(1),
                                // Select::make('status_muat')
                                //     ->label('Status Muat')
                                //     ->options([
                                //         'MUAT' => 'MUAT',
                                //         'TIDAK MUAT' => 'TIDAK MUAT',
                                //     ])
                                //     ->placeholder('Pilih Status')
                                //     ->native(false)
                                //     ->default(fn($livewire) => $livewire->getRecord()?->muat ?? null)
                                //     ->dehydrated(true)
                                //     ->required(fn($livewire) => ! $livewire->getRecord()?->exists)
                                //     ->reactive()
                                //     ->afterStateHydrated(function ($state) {
                                //         logger('MUAT STATE SAAT EDIT: ' . ($state ?? 'NULL'));
                                //     })
                                //     ->afterStateUpdated(function (?string $state, Set $set) {
                                //         $now = now()->setTimezone('Asia/Jakarta')->format('H:i');

                                //         if ($state === 'MUAT') {
                                //             $set('jam_keluar', $now);
                                //             $set('jam_masuk', null);
                                //         } elseif ($state === 'TIDAK MUAT') {
                                //             $set('jam_masuk', $now);
                                //             $set('jam_keluar', null);
                                //         } else {
                                //             $set('jam_masuk', null);
                                //             $set('jam_keluar', null);
                                //         }
                                //     })->disabled(fn($livewire) => $livewire->getRecord()?->exists),

                                Textarea::make('keterangan')
                                    ->placeholder('Masukkan Keterangan')
                                    ->columnSpanFull(), // Tetap 1 kolom penuh di semua ukuran layar
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])
                            ->columns([
                                'sm' => 1,  // Mobile: 1 kolom
                                'md' => 2,  // Tablet & Desktop: 2 kolom
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->columns([
                IconColumn::make('status_selesai')
                    ->label('')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('created_at_date')
                    ->label('Tanggal')
                    ->state(fn($record) => \Carbon\Carbon::parse($record->created_at)->format('d-m-Y'))
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('nama_sup_per')
                    ->label('Nama Sup/Per')
                    ->searchable(),
                TextColumn::make('plat_polisi')
                    ->label('Plat Mobil')
                    ->searchable(),
                TextColumn::make('nomor_antrian')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('created_at_time')
                    ->label('Jam Dibuat')
                    ->state(fn($record) => \Carbon\Carbon::parse($record->created_at)->format('H:i:s'))
                    ->alignCenter(),
                TextColumn::make('jam_masuk')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('jam_keluar')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            // ->bulkActions([
            //     // Tables\Actions\BulkActionGroup::make([
            //     Tables\Actions\DeleteBulkAction::make(),
            //     // ]),
            // ])
            ->filters([
                Filter::make('Hari Ini')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDate('created_at', Carbon::today())
                    ),
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
            'index' => Pages\ListKendaraanMasuks::route('/'),
            'create' => Pages\CreateKendaraanMasuks::route('/create'),
            'edit' => Pages\EditKendaraanMasuks::route('/{record}/edit'),
        ];
    }
}
