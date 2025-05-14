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
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanMuatResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KendaraanMuatResource\RelationManagers;
use App\Filament\Resources\KendaraanMuatResource\Pages\EditKendaraanMuat;

class KendaraanMuatResource extends Resource implements HasShieldPermissions
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
    protected static ?string $model = KendaraanMuat::class;
    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-s-truck';

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
                                // 1. Nama Supir
                                TextInput::make('nama_supir')
                                    ->label('Nama Supir')
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan Nama Supir'),

                                // 2. Plat Polisi (kendaraan_id)
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
                                            $set('jenis_mobil', $kendaraan->jenis_mobil);
                                        } else {
                                            $set('jenis_mobil', null);
                                        }
                                    }),

                                // 3. Jenis Mobil
                                TextInput::make('jenis_mobil')
                                    ->placeholder('Otomatis')
                                    ->readOnly()
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        if ($record?->kendaraan_id) {
                                            $kendaraan = Kendaraan::find($record->kendaraan_id);
                                            if ($kendaraan) {
                                                $set('jenis_mobil', $kendaraan->jenis_mobil);
                                            }
                                        }
                                    }),

                                // 4. Foto
                                FileUpload::make('foto')
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->imagePreviewHeight(200)
                                    ->label('Foto'),

                                // 5. Tonase
                                TextInput::make('tonase')
                                    ->numeric()
                                    ->autocomplete('off')
                                    ->label('Tonase')
                                    ->placeholder('Masukkan Tonase'),

                                // 6. Tujuan
                                TextInput::make('tujuan')
                                    ->placeholder('Masukkan Tujuan')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state)),

                                // 7. Jam Berangkat (jam_keluar)
                                TextInput::make('jam_keluar')
                                    ->label('Jam Berangkat')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock'),

                                // 8. Jam Masuk
                                TextInput::make('jam_masuk')
                                    ->label('Jam Masuk')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock'),

                                // 9 & 10. Toggles (menggunakan grid terpisah)
                                Grid::make()
                                    ->schema([
                                        Toggle::make('status_awal')
                                            ->label('Tombol Berangkat')
                                            ->helperText('Klik jika sudah Berangkat')
                                            ->onIcon('heroicon-m-bolt')
                                            ->offIcon('heroicon-m-user')
                                            ->dehydrated(true)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'));
                                                } else {
                                                    $set('jam_keluar', null);
                                                }
                                            }),
                                        Toggle::make('status')
                                            ->label('Tombol Masuk')
                                            ->helperText('Klik jika sudah Masuk')
                                            ->onIcon('heroicon-m-bolt')
                                            ->offIcon('heroicon-m-user')
                                            ->dehydrated(true)
                                            ->reactive()
                                            ->disabled(fn($get) => !$get('status_awal'))
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'));
                                                } else {
                                                    $set('jam_masuk', null);
                                                }
                                            }),
                                    ])
                                    ->columns([
                                        'lg' => 2,     // Desktop: 2 kolom
                                        'md' => 2,     // Tablet: 2 kolom
                                        'default' => 1 // Mobile: 1 kolom
                                    ]),

                                // 11. Keterangan (di bawah sebagai terakhir)
                                Textarea::make('keterangan')
                                    ->placeholder('Masukkan Keterangan')
                                    ->columnSpanFull(),

                                // Hidden user_id
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()),
                            ])
                            ->columns([
                                'lg' => 2,     // Desktop: 2 kolom
                                'md' => 2,     // Tablet: 2 kolom
                                'default' => 1 // Mobile: 1 kolom (default jika tidak ada)
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (KendaraanMuat $record): ?string {
                $user = Auth::user();

                // 1) Super admin bisa edit semua kondisi
                if ($user && $user->hasRole('super_admin')) {
                    return EditKendaraanMuat::getUrl(['record' => $record]);
                }

                if ($user && $user->hasRole('satpam')) {
                    if (!$record->jam_masuk) {
                        return EditKendaraanMuat::getUrl(['record' => $record]);
                    }
                    return null;
                }
                return null;
            })
            ->columns([

                IconColumn::make('status')
                    ->label('')
                    ->boolean()  // Menandakan kolom adalah boolean (0 atau 1)
                    ->icon(fn($record) => $record->status ? 'heroicon-o-check-circle' : 'heroicon-o-check-circle')  // Tentukan ikon berdasarkan nilai status
                    ->alignCenter(),  // Rata tengah untuk ikon
                TextColumn::make('nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
                TextColumn::make('kendaraan.plat_polisi_terbaru')
                    ->label('Plat Polisi')
                    ->searchable(),
                TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->searchable(),
                TextColumn::make('kendaraan.jenis_mobil')
                    ->label('Jenis Mobil')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('tonase')
                    ->label('Tonase')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->searchable(),

                ImageColumn::make('foto')
                    ->label('Foto')
                    ->getStateUsing(fn($record) => $record->foto[0] ?? null)
                    ->url(fn($record) => asset('storage/' . ($record->foto[0] ?? '')))
                    ->openUrlInNewTab(),
                // TextColumn::make('created_at_time')
                //     ->label('Jam Dibuat')
                //     ->state(fn($record) => \Carbon\Carbon::parse($record->created_at)->format('H:i:s'))
                //     ->alignCenter(),
                TextColumn::make('jam_keluar')
                    ->label('Berangkat')
                    ->alignCenter()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        // Mengatur lokalitas ke Bahasa Indonesia
                        Carbon::setLocale('id');

                        return Carbon::parse($state)
                            ->locale('id') // Memastikan locale di-set ke bahasa Indonesia
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('jam_masuk')
                    ->label('Masuk')
                    ->alignCenter()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        // Mengatur lokalitas ke Bahasa Indonesia
                        Carbon::setLocale('id');

                        return Carbon::parse($state)
                            ->locale('id') // Memastikan locale di-set ke bahasa Indonesia
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal | Jam')
                    ->alignCenter()
                    // ->colors([
                    //     'success' => fn($state) => Carbon::parse($state)->isToday(),
                    //     'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                    //     'gray' => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                    // ])
                    ->formatStateUsing(function ($state) {
                        // Mengatur lokalitas ke Bahasa Indonesia
                        Carbon::setLocale('id');

                        return Carbon::parse($state)
                            ->locale('id') // Memastikan locale di-set ke bahasa Indonesia
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('user.name')
                    ->label('User')
            ])->defaultSort('id', 'desc')
            ->filters([
                Filter::make('Hari Ini')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDate('created_at', Carbon::today())
                    ),
                Filter::make('Jam Masuk Kosong')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('jam_masuk')
                    )
                    ->toggle() // Filter ini dapat diaktifkan/nonaktifkan oleh pengguna
                    ->default(function () {
                        // Filter aktif secara default hanya jika pengguna BUKAN super_admin ,'admin'
                        return !optional(Auth::user())->hasAnyRole(['super_admin']);
                    })
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
