<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Kendaraan;
use Filament\Tables\Table;
use App\Models\KendaraanMuat;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanMuatResource\Pages;
use App\Filament\Resources\KendaraanMuatResource\RelationManagers;

class KendaraanMuatResource extends Resource
{
    protected static ?string $model = KendaraanMuat::class;
    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Kendaraan Muat';
    public static ?string $label = 'Daftar Kendaraan Muat ';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('nama_supir')
                                    ->label('Nama Supir')
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan Nama Supir'),
                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Akan terisi otomatis saat tambah data')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        // Kalau sedang create (tidak ada record) dan jam_masuk masih kosong
                                        if (empty($state) && !$record) {
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        }
                                    }),
                                Select::make('kendaraan_id')
                                    ->label('Plat Polisi')
                                    ->placeholder('Pilih kendaraan')
                                    ->options(
                                        fn() => \App\Models\Kendaraan::all()
                                            ->mapWithKeys(function ($kendaraan) {
                                                return [
                                                    $kendaraan->id => "{$kendaraan->plat_polisi_terbaru} -  {$kendaraan->nama_supir} - {$kendaraan->nama_kernet}",
                                                ];
                                            })
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $kendaraan = Kendaraan::find($state);
                                        if ($kendaraan) {
                                            $set('jenis_mobil', $kendaraan->jenis_mobil); // sesuaikan nama kolom
                                        } else {
                                            $set('jenis_mobil', null);
                                        }
                                    }),
                                TextInput::make('jam_masuk')
                                    ->label('Jam Masuk')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock'),

                                TextInput::make('jenis_mobil')
                                    ->placeholder('Otomatis')
                                    ->readOnly()
                                    ->columnSpan(1)
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        if ($record?->kendaraan_id) {
                                            $kendaraan = Kendaraan::find($record->kendaraan_id);
                                            if ($kendaraan) {
                                                $set('jenis_mobil', $kendaraan->jenis_mobil);
                                            }
                                        }
                                    }),
                                TextInput::make('tonase')
                                    ->numeric()
                                    ->autocomplete('off')
                                    ->label('Tonase')
                                    ->placeholder('Masukkan Tonase'),

                                FileUpload::make('foto')
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->imagePreviewHeight(200)
                                    ->label('Foto'),
                                TextInput::make('tujuan')
                                    ->placeholder('Masukkan Tujuan')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(1),
                                Toggle::make('status')
                                    ->helperText('Klik jika sudah Masuk')
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-user')
                                    ->dehydrated(true)
                                    ->columns(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // Toggle aktif, isi jam_keluar
                                            $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } else {
                                            // Toggle nonaktif, kosongkan jam_keluar
                                            $set('jam_masuk', null);
                                        }
                                    }),

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
            ->columns([
                IconColumn::make('status')
                    ->label('')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
                TextColumn::make('kendaraan.plat_polisi_terbaru')
                    ->label('Plat Polisi')
                    ->searchable(),
                TextColumn::make('kendaraan.jenis_mobil')
                    ->label('Jenis Mobil')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('tonase')
                    ->label('Tonase')
                    ->searchable(),
                TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->searchable(),
                ImageColumn::make('foto')
                    ->label('Foto 1')
                    ->getStateUsing(fn($record) => $record->foto[0] ?? null)
                    ->url(fn($record) => asset('storage/' . ($record->foto[0] ?? '')))
                    ->openUrlInNewTab(),
                TextColumn::make('jam_masuk')
                    ->searchable(),
                TextColumn::make('jam_keluar')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->filters([
                Filter::make('Hari Ini')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDate('created_at', Carbon::today())
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
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
            'index' => Pages\ListKendaraanMuats::route('/'),
            'create' => Pages\CreateKendaraanMuat::route('/create'),
            'edit' => Pages\EditKendaraanMuat::route('/{record}/edit'),
        ];
    }
}
