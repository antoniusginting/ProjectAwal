<?php

namespace App\Filament\Resources;

use App\Models\KapasitasKontrakJual;
use App\Filament\Resources\KapasitasKontrakJualResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Support\Facades\DB;

class KapasitasKontrakJualResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = KapasitasKontrakJual::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Kapasitas Kontrak Jual';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 4;

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()
                ->schema([
                    TextInput::make('stok')
                        ->label('Stok Awal')
                        ->placeholder('Masukkan stok awal')
                        ->live()
                        ->extraAttributes([
                            'x-data' => '{}',
                            'x-on:input' => "event.target.value = event.target.value.replace(/\\D/g, '').replace(/\\B(?=(\\d{3})+(?!\\d))/g, '.')",
                        ])
                        ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)),

                    TextInput::make('harga')
                        ->label('Harga')
                        ->placeholder('Masukkan harga')
                        ->live()
                        ->extraAttributes([
                            'x-data' => '{}',
                            'x-on:input' => "event.target.value = event.target.value.replace(/\\D/g, '').replace(/\\B(?=(\\d{3})+(?!\\d))/g, '.')",
                        ])
                        ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)),

                    Select::make('nama')
                        ->label('Kontrak')
                        ->placeholder('Pilih Kontrak')
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->options(fn() => \App\Models\Kontrak::query()->pluck('nama', 'nama')->toArray())
                        ->disabled(fn(callable $get, ?\Illuminate\Database\Eloquent\Model $record) => $record !== null)
                        ->live(),

                    TextInput::make('no_po')
                        ->label('No PO')
                        ->placeholder('Masukkan nomor PO')
                        ->numeric()
                        ->required(),

                    Toggle::make('status')
                        ->label('Status')
                        ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                        ->default(false)
                        ->onColor('danger')
                        ->offColor('success'),
                ])->columns(4),
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
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->locale('id')->isoFormat('D MMMM YYYY | HH:mm:ss')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state ? 'Tutup' : 'Buka')
                    ->color(fn($state) => $state ? 'danger' : 'success'),

                TextColumn::make('stok')
                    ->label('Stok Awal')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                TextColumn::make('harga')
                    ->label('Harga')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('no_po')
                    ->label('No PO')
                    ->alignCenter()
                    ->searchable(),

                // === Total Keluar (TERIMA/SETENGAH)
                TextColumn::make('total_keluar')
                    ->label('Keluar (TERIMA/SETENGAH)')
                    ->alignCenter()
                    ->getStateUsing(
                        fn($record) =>
                        number_format(
                            $record->penjualanLuar()
                                ->whereIn('status', ['TERIMA', 'SETENGAH'])
                                ->sum(DB::raw('COALESCE(netto_diterima, netto)')),
                            0,
                            ',',
                            '.'
                        )
                    ),

                // === Masuk Kembali (RETUR/TOLAK)
                TextColumn::make('total_masuk_kembali')
                    ->label('Masuk Kembali (RETUR/TOLAK)')
                    ->alignCenter()
                    ->getStateUsing(
                        fn($record) =>
                        number_format(
                            $record->penjualanLuar()
                                ->whereIn('status', ['RETUR', 'TOLAK'])
                                ->sum(DB::raw('COALESCE(netto_diterima, netto)')),
                            0,
                            ',',
                            '.'
                        )
                    ),

                // === Stok Sisa
                TextColumn::make('stok_sisa')
                    ->label('Stok Sisa')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $totalKeluar = $record->penjualanLuar()
                            ->whereIn('status', ['TERIMA', 'SETENGAH'])
                            ->sum(DB::raw('COALESCE(netto_diterima, netto)'));

                        $totalMasuk = $record->penjualanLuar()
                            ->whereIn('status', ['RETUR', 'TOLAK'])
                            ->sum(DB::raw('COALESCE(netto_diterima, netto)'));

                        return number_format(($record->stok - $totalKeluar + $totalMasuk), 0, ',', '.');
                    })
                    ->color(fn($record) => (
                        $record->stok -
                        $record->penjualanLuar()->whereIn('status', ['TERIMA', 'SETENGAH'])->sum(DB::raw('COALESCE(netto_diterima, netto)')) +
                        $record->penjualanLuar()->whereIn('status', ['RETUR', 'TOLAK'])->sum(DB::raw('COALESCE(netto_diterima, netto)'))
                    ) < 0 ? 'danger' : 'success'),

                // === Kode Penjualan
                TextColumn::make('penjualanLuar.kode')
                    ->label('Kode Penjualan')
                    ->alignCenter()
                    ->searchable()
                    ->placeholder('-----')
                    ->getStateUsing(function ($record) {
                        $penjualanCodes = $record->penjualanLuar->pluck('kode');
                        $suratJalanCodes = $record->suratJalan()
                            ->with('tronton')
                            ->get()
                            ->pluck('tronton.kode')
                            ->filter();
                        $allCodes = $penjualanCodes->merge($suratJalanCodes)->unique();
                        return $allCodes->count() <= 3
                            ? $allCodes->implode(', ')
                            : $allCodes->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        $penjualanCodes = $record->penjualanLuar->pluck('kode');
                        $suratJalanCodes = $record->suratJalan()
                            ->with('tronton')
                            ->get()
                            ->pluck('tronton.kode')
                            ->filter();
                        return $penjualanCodes->merge($suratJalanCodes)->unique()->implode(', ');
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\Action::make('view-kapasitas-kontrak-jual')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl('view-kapasitas-kontrak-jual', ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKapasitasKontrakJuals::route('/'),
            'create' => Pages\CreateKapasitasKontrakJual::route('/create'),
            'edit' => Pages\EditKapasitasKontrakJual::route('/{record}/edit'),
            'view-kapasitas-kontrak-jual' => Pages\ViewKapasitasKontrakJual::route('/{record}/view-kapasitas-kontrak-jual'),
        ];
    }
}
