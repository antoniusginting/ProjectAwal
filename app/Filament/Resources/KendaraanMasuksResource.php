<?php

namespace App\Filament\Resources;

use DateTime;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
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
    protected static ?string $navigationIcon = 'heroicon-o-forward';

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
                                        'Tamu' => 'Tamu',
                                        'Supplier' => 'Supplier',
                                    ])
                                    ->placeholder('Pilih Status')
                                    ->native(false)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('nama_sup_per')
                                    ->placeholder('Masukkan nama supplier atau perusahaan')
                                    ->columnSpan(1),

                                TextInput::make('plat_polisi')
                                    ->placeholder('Masukkan plat polisi')
                                    ->columnSpan(1),

                                TextInput::make('nama_barang')
                                    ->placeholder('Masukkan nama barang')
                                    ->columnSpan(1),

                                TextInput::make('jam_masuk')
                                    ->readOnly()
                                    ->suffixIcon('heroicon-o-clock')
                                    ->default(now()->format('H:i'))
                                    ->columnSpan(1),

                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Kosongkan jika belum keluar')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->required(false)
                                    ->hidden(fn($livewire, $state) => $livewire instanceof \Filament\Resources\Pages\ViewRecord && empty($state))
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        if ($record && empty($state)) {
                                            $set('jam_keluar', now()->format('H:i:s'));
                                        }
                                    })
                                    ->columnSpan(1),

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
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('status')
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
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
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
