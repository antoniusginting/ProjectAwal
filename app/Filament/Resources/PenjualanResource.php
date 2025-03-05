<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Mobil;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\PenjualanResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class PenjualanResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Penjualan::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Penjualan';


    protected static ?string $navigationGroup = 'Timbangan';

    public static ?string $label = 'Daftar Penjualan ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextInput::make('no_spb')
                //     ->label('No SPB')
                //     ->disabled()
                //     ->extraAttributes(['readonly' => true])
                //     ->dehydrated(false)
                //     ->afterStateUpdated(function (callable $set, $get) {
                //         $nextId = Pembelian::max('id') + 1; // Ambil ID terakhir + 1
                //         $set('no_spb', $get('jenis') . '-' . $nextId);
                //     }),
                Placeholder::make('next_id')
                    ->label('No SPB')
                    ->columnSpan(2)
                    ->content(function ($record) {
                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                        if ($record) {
                            return $record->no_spb;
                        }

                        // Jika sedang membuat data baru, hitung kode berikutnya
                        $nextId = (Penjualan::max('id') ?? 0) + 1;
                        return 'J' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                    }),
                TextInput::make('created_at')
                    ->label('Tanggal Sekarang')
                    ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                    ->disabled(), // Tidak bisa diedit
                TextInput::make('nama_lumbung')
                    ->placeholder('Masukkan Nama Lumbung')
                    ->required(),
                Select::make('id_mobil')
                    ->label('Plat Polisi')
                    ->placeholder('Pilih Plat Polisi')
                    ->options(Mobil::pluck('plat_polisi', 'id')) // Ambil daftar mobil
                    ->searchable() // Biar bisa cari
                    ->required(), // Wajib diisi
                TextInput::make('no_lumbung')
                    ->placeholder('Masukkan No Lumbung')
                    ->required(),
                TextInput::make('nama_supir')
                    ->placeholder('Masukkan Nama Supir'),
                TextInput::make('bruto')
                    ->label('Bruto')
                    ->numeric()
                    ->placeholder('Masukkan Nilai Bruto')
                    ->required()
                    ->live(debounce: 500) // Tunggu 500ms setelah user berhenti mengetik
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $tara = $get('tara') ?? 0;
                        $set('netto', max(0, intval($state) - intval($tara))); // Hitung netto
                    }),

                TextInput::make('nama_barang')
                    ->placeholder('Masukkan Nama Barang')
                    ->required(),
                TextInput::make('tara')
                    ->label('Tara')
                    ->placeholder('Masukkan Nilai Tara')
                    ->numeric()
                    ->live(debounce: 500) // Tambahkan debounce juga di sini
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $bruto = $get('bruto') ?? 0;
                        $set('netto', max(0, intval($bruto) - intval($state)));
                    }),

                TextInput::make('jam_masuk')
                    ->readOnly()
                    ->suffixIcon('heroicon-o-clock')
                    ->default(now()->format('H:i')),
                TextInput::make('netto')
                    ->label('Netto')
                    ->readOnly()
                    ->placeholder('Otomatis Terhitung')
                    ->numeric(),
                TextInput::make('jam_keluar')
                    ->label('Jam Keluar')
                    ->readOnly()
                    ->placeholder('Kosongkan jika belum keluar')
                    ->suffixIcon('heroicon-o-clock')
                    ->required(false) // Bisa kosong saat tambah data
                    ->afterStateHydrated(function ($state, callable $set, $record) {
                        // Jika sedang edit dan jam_keluar kosong, isi waktu sekarang
                        if ($record && empty($state)) {
                            $set('jam_keluar', now()->format('H:i:s'));
                        }
                    }),
                TextInput::make('keterangan')
                    ->placeholder('Masukkan Keterangan'),
                Select::make('id_supplier')
                    ->label('Supplier')
                    ->placeholder('Pilih Supplier')
                    ->options(Supplier::pluck('nama_supplier', 'id')) // Ambil daftar mobil
                    ->searchable() // Biar bisa cari
                    ->required(), // Wajib diisi
                Select::make('kepemilikan')
                    ->label('Kepemilikan')
                    ->options([
                        'Milik Sendiri' => 'Milik Sendiri',
                        'Minjam' => 'Minjam',
                    ])
                    ->placeholder('Pilih Status Kepemilikan')
                    ->native(false) // Mengunakan dropdown modern
                    ->required(), // Opsional: Atur default value,
                TextInput::make('brondolan')
                    ->placeholder('Masukkan Brondolan'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_spb')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),
                TextColumn::make('mobil.plat_polisi')->label('Plat Polisi')
                    ->searchable(),
                TextColumn::make('nama_supir')
                    ->searchable(),
                TextColumn::make('supplier.nama_supplier')->label('Supplier')
                    ->searchable(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('supplier.jenis_supplier')->label('Jenis')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('kepemilikan')
                    ->searchable(),
                TextColumn::make('brondolan'),
                TextColumn::make('bruto'),
                TextColumn::make('tara'),
                TextColumn::make('netto'),
                TextColumn::make('kepemilikan')
                ->searchable(),
                TextColumn::make('jam_masuk'),
                TextColumn::make('jam_keluar'),
                TextColumn::make('no_lumbung'),
                TextColumn::make('nama_lumbung'),
            ])
            ->defaultSort('no_spb', 'desc') // Megurutkan no_spb terakhir menjadi pertama pada tabel
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }
}
