<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use App\Models\KapasitasKontrakJual;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\KapasitasKontrakJualResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class KapasitasKontrakJualResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = KapasitasKontrakJual::class;

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
    protected static ?string $navigationLabel = 'Kapasitas Kontrak Jual';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 4;

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

                        TextInput::make('harga')
                            ->label('Harga')
                            ->placeholder('Masukkan harga')
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
                            ->options(function () {
                                return \App\Models\Kontrak::query()
                                    ->pluck('nama', 'nama')
                                    ->toArray();
                            })
                            ->label('Kontrak')
                            ->placeholder('Pilih Kontrak')
                            ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                // Disable saat edit, misal jika $record ada berarti edit
                                return $record !== null;
                            })
                            ->live(),
<<<<<<< HEAD
                        TextInput::make('no_po')
                            ->required()
                            ->label('No PO')
                            ->placeholder('Masukkan nomor PO'),
=======

                        TextInput::make('no_po')
                            ->label('No PO')
                            ->placeholder('Masukkan nomor PO')
                            ->numeric()
                            ->required(),
>>>>>>> c9e46f0 (feat: ubah field kontrak, tambah PO & logika status (terima, retur, tolak, setengah) pada penjualan antar pulau)

                        Toggle::make('status')
                            ->label('Status')
                            ->columnSpan(2)
                            ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                            ->default(false) // Default false (buka)
                            ->onColor('danger') // Warna merah saat true (tutup)
                            ->offColor('success'), // Warna hijau saat false (buka)
                    ])->columns(3)
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
<<<<<<< HEAD
                TextColumn::make('no_po')
                    ->label('No PO'),
=======

>>>>>>> c9e46f0 (feat: ubah field kontrak, tambah PO & logika status (terima, retur, tolak, setengah) pada penjualan antar pulau)
                TextColumn::make('harga')->label('Harga')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('no_po')
                    ->label('No PO')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('penjualanLuar.kode')
                    ->alignCenter()
                    ->searchable()
                    ->placeholder('-----')
                    ->label('Kode Penjualan')
                    ->getStateUsing(function ($record) {
                        // Ambil kode dari penjualanLuar
                        $penjualanluarCodes = $record->penjualanLuar->pluck('kode');

                        // Ambil kode dari suratJalan->tronton
                        $suratJalanCodes = $record->suratJalan()
                            ->with('tronton')
                            ->get()
                            ->pluck('tronton.kode')
                            ->filter(); // Filter null values

                        // Gabungkan kedua collection
                        $allCodes = $penjualanluarCodes->merge($suratJalanCodes)->unique();

                        if ($allCodes->count() <= 3) {
                            return $allCodes->implode(', ');
                        }

                        return $allCodes->take(3)->implode(', ') . '...';
                    })
                    ->tooltip(function ($record) {
                        // Ambil kode dari penjualanLuar
                        $penjualanluarCodes = $record->penjualanLuar->pluck('kode');

                        // Ambil kode dari suratJalan->tronton
                        $suratJalanCodes = $record->suratJalan()
                            ->with('tronton')
                            ->get()
                            ->pluck('tronton.kode')
                            ->filter(); // Filter null values

                        // Gabungkan kedua collection
                        $allCodes = $penjualanluarCodes->merge($suratJalanCodes)->unique();

                        return $allCodes->implode(', ');
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-kapasitas-kontrak-jual')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-kapasitas-kontrak-jual", ['record' => $record->id])),
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
            'index' => Pages\ListKapasitasKontrakJuals::route('/'),
            'create' => Pages\CreateKapasitasKontrakJual::route('/create'),
            'edit' => Pages\EditKapasitasKontrakJual::route('/{record}/edit'),
            'view-kapasitas-kontrak-jual' => Pages\ViewKapasitasKontrakJual::route('/{record}/view-kapasitas-kontrak-jual'),
        ];
    }
}
