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
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanMasuksResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KendaraanMasuksResource\RelationManagers;

class KendaraanMasuksResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = KendaraanMasuks::class;

    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-c-truck';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Registrasi Kendaraan';
    public static ?string $label = 'Daftar Registrasi Kendaraan ';

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        Card::make()
            ->schema([
                Grid::make()
                    ->schema([
                        // First group - form fields
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
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        TextInput::make('nama_sup_per')
                            ->label('Nama Supplier/Perusahaan')
                            ->placeholder('Masukkan nama supplier atau perusahaan')
                            ->autocomplete('off')
                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        TextInput::make('plat_polisi')
                            ->label('Plat Polisi')
                            ->placeholder('Masukkan plat polisi')
                            ->autocomplete('off')
                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->placeholder('Masukkan nama barang')
                            ->autocomplete('off')
                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        TextInput::make('nomor_antrian')
                            ->numeric()
                            ->label('Nomor Antrian')
                            ->placeholder('Masukkan Nomor Antrian')
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        TextInput::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->readOnly()
                            ->placeholder('Akan terisi saat toggle diaktifkan')
                            ->suffixIcon('heroicon-o-clock')
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        TextInput::make('jam_keluar')
                            ->label('Jam Keluar')
                            ->readOnly()
                            ->placeholder('Akan terisi saat toggle diaktifkan')
                            ->suffixIcon('heroicon-o-clock')
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        // Second group - toggles
                        Grid::make()
                            ->schema([
                                Toggle::make('status_awal')
                                    ->label('Status Masuk')
                                    ->helperText('Klik jika sudah Masuk')
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-user')
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // Toggle aktif, isi jam_masuk
                                            $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } else {
                                            // Toggle nonaktif, kosongkan jam_masuk
                                            $set('jam_masuk', null);
                                        }
                                    })
                                    ->columnSpan(1),

                                Toggle::make('status_selesai')
                                    ->label('Status Keluar')
                                    ->helperText('Klik jika sudah Keluar')
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-user')
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // Toggle aktif, isi jam_keluar
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } else {
                                            // Toggle nonaktif, kosongkan jam_keluar
                                            $set('jam_keluar', null);
                                        }
                                    })
                                    ->columnSpan(1),
                            ])
                            ->columns([
                                'default' => 1, // On mobile: 2 columns for toggles side by side
                                'md' => 1,      // On larger screens: still 2 columns
                            ])
                            ->columnSpan(['default' => 'full', 'md' => 1]), // Changed to 1 column on desktop

                        // Last item - keterangan
                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Masukkan Keterangan')
                            ->columnSpan('full'), // Always full width

                        // Hidden field
                        Hidden::make('user_id')
                            ->default(Auth::id()),
                    ])
                    ->columns([
                        'default' => 1, // Mobile: 1 column (stacked)
                        'md' => 2,      // Tablet & Desktop: 2 columns
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
                BadgeColumn::make('created_at')
                    ->label('Tanggal | Jam')
                    ->alignCenter()
                    ->colors([
                        'success' => fn($state) => Carbon::parse($state)->isToday(),
                        'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                        'gray' => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                    ])
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d M Y | H:i:s')),
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
                // TextColumn::make('created_at_time')
                //     ->label('Jam Dibuat')
                //     ->state(fn($record) => \Carbon\Carbon::parse($record->created_at)->format('H:i:s'))
                //     ->alignCenter(),
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
