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
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanMasuksResource\Pages;
use App\Filament\Resources\KendaraanMasuksResource\RelationManagers;

class KendaraanMasuksResource extends Resource
{

    public static function getNavigationSort(): int
    {
        return 4; // Ini akan muncul di atas
    }
    protected static ?string $model = KendaraanMasuks::class;

    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-o-truck';

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

                                TextInput::make('nama_sup_per')
                                    ->placeholder('Masukkan nama supplier atau perusahaan')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),

                                TextInput::make('plat_polisi')
                                    ->placeholder('Masukkan plat polisi')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),

                                TextInput::make('nama_barang')
                                    ->placeholder('Masukkan nama barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),

                                TextInput::make('jam_masuk')
                                    ->label('Jam Masuk')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat pilih TIDAK MUAT')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        // Kalau jam_masuk kosong, tapi jam_keluar sudah ada
                                        if (empty($state) && $record?->jam_keluar) {
                                            $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('H:i'));
                                        }
                                    })
                                    ->columnSpan(1),

                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat pilih MUAT')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        // Kalau jam_keluar kosong, tapi jam_masuk sudah ada
                                        if (empty($state) && $record?->jam_masuk) {
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i'));
                                        }
                                    })
                                    ->columnSpan(1),
                                Select::make('status_muat')
                                    ->label('Status Muat')
                                    ->options([
                                        'MUAT' => 'MUAT',
                                        'TIDAK MUAT' => 'TIDAK MUAT',
                                    ])
                                    ->placeholder('Pilih Status')
                                    ->native(false)
                                    ->default(fn($livewire) => $livewire->getRecord()?->muat ?? null)
                                    ->dehydrated(true)
                                    ->required(fn($livewire) => ! $livewire->getRecord()?->exists)
                                    ->reactive()
                                    ->afterStateHydrated(function ($state) {
                                        logger('MUAT STATE SAAT EDIT: ' . ($state ?? 'NULL'));
                                    })
                                    ->afterStateUpdated(function (?string $state, Set $set) {
                                        $now = now()->setTimezone('Asia/Jakarta')->format('H:i');

                                        if ($state === 'MUAT') {
                                            $set('jam_keluar', $now);
                                            $set('jam_masuk', null);
                                        } elseif ($state === 'TIDAK MUAT') {
                                            $set('jam_masuk', $now);
                                            $set('jam_keluar', null);
                                        } else {
                                            $set('jam_masuk', null);
                                            $set('jam_keluar', null);
                                        }
                                    })->disabled(fn($livewire) => $livewire->getRecord()?->exists),

                                Textarea::make('keterangan')
                                    ->placeholder('Masukkan Keterangan'),
                                //->columnSpanFull(), // Tetap 1 kolom penuh di semua ukuran layar
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
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('status_muat')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('nama_sup_per')
                    ->label('Nama Sup/Per')
                    ->searchable(),
                TextColumn::make('plat_polisi')
                    ->label('Plat Mobil')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('jam_masuk')
                    ->searchable(),
                TextColumn::make('jam_keluar')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
