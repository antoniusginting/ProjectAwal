<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Mobil;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Pembelian;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\PembelianResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class PembelianResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Pembelian';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Timbangan';

    public static ?string $label = 'Daftar Pembelian';

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
                        $nextId = (Pembelian::max('id') ?? 0) + 1;
                        return 'B' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                    }),
                TextInput::make('created_at')
                    ->label('Tanggal')
                    ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                    ->disabled(), // Tidak bisa diedit

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

                Select::make('id_mobil')
                    ->label('Plat Polisi')
                    ->options(Mobil::pluck('plat_polisi', 'id')) // Ambil daftar mobil
                    ->searchable() // Biar bisa cari
                    ->required(), // Wajib diisi

                TextInput::make('tara')
                    ->label('Tara')
                    ->placeholder('Masukkan Nilai Tara')
                    ->numeric()
                    ->live(debounce: 500) // Tambahkan debounce juga di sini
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $bruto = $get('bruto') ?? 0;
                        $set('netto', max(0, intval($bruto) - intval($state)));
                    }),

                TextInput::make('nama_supir')
                    ->placeholder('Masukkan Nama Supir'),

                TextInput::make('netto')
                    ->label('Netto')
                    ->readOnly()
                    ->placeholder('Otomatis Terhitung')
                    ->numeric(),

                TextInput::make('nama_barang')
                    ->placeholder('Masukkan Nama Barang'),
                TextInput::make('keterangan')
                    ->placeholder('Masukkan Keterangan'),
                Select::make('kepemilikan')
                    ->label('Kepemilikan')
                    ->options([
                        'Milik Sendiri' => 'Milik Sendiri',
                        'Minjam' => 'Minjam',
                    ])
                    ->placeholder('Pilih Status Kepemilikan')
                    // ->inlineLabel() // Membuat label sebelah kiri
                    ->native(false) // Mengunakan dropdown modern
                    ->required(), // Opsional: Atur default value,

                Select::make('id_supplier')
                    ->label('Supplier')
                    ->placeholder('Pilih Supplier')
                    ->options(Supplier::pluck('nama_supplier', 'id')) // Ambil daftar mobil
                    ->searchable() // Biar bisa cari
                    ->required(), // Wajib diisi

                //     Select::make('id_supplier')
                //     ->label('Pilih Supplier')
                //     ->searchable()
                //     ->options(Supplier::pluck('nama_supplier', 'id'))
                //     ->reactive() // Reaktif agar memantau perubahan
                //     ->afterStateUpdated(function ($state, callable $set) {
                //         $supplier = Supplier::find($state);
                //         $set('nama_supplier', $supplier?->nama_supplier);
                //         $set('jenis_supplier', $supplier?->jenis_supplier);
                //     }),

                // Placeholder::make('jenis_supplier')
                //     ->label('Jenis Supplier')
                //     ->content(fn($get) => $get('jenis_supplier') ?? 'Pilih Nama Supplier terlebih dahulu'),
                TextInput::make('jam_masuk')
                    ->readOnly()
                    ->suffixIcon('heroicon-o-clock')
                    ->default(now()->format('H:i')),
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
                TextInput::make('no_container')
                    ->placeholder('Masukkan No Container'),
                TextInput::make('brondolan')
                    ->placeholder('Masukkan Brondolan')
                    ->extraAttributes(['style' => 'margin-bottom: 20px;']),
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
                TextColumn::make('no_container')
                    ->searchable(),
                TextColumn::make('brondolan'),
                TextColumn::make('bruto'),
                TextColumn::make('tara'),
                TextColumn::make('netto'),
                TextColumn::make('kepemilikan'),
                TextColumn::make('jam_masuk'),
                TextColumn::make('jam_keluar'),

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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
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
