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
use Filament\Forms\Components\Card;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\Pages\EditPembelian;
use Filament\Forms\Components\Grid;

class PembelianResource extends Resource
{
    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';

    protected static ?string $navigationLabel = 'Pembelian';


    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 1;
    public static ?string $label = 'Daftar Pembelian ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
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
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_spb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (Pembelian::max('id') ?? 0) + 1;
                                        return 'B' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('jam_masuk')
                                    ->readOnly()
                                    ->suffixIcon('heroicon-o-clock')
                                    ->default(now()->setTimezone('Asia/Jakarta')->format('H:i:s')),
                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Kosong jika belum keluar')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->required(false)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        // Biarkan tetap kosong saat edit
                                    }),
                                TextInput::make('created_at')
                                    ->label('Tanggal')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                            ])->columns(4)->collapsed(),

                        // TextInput::make('no_po')
                        //     ->label('Nomor PO') // Memberikan label deskriptif
                        //     ->placeholder('Masukkan Nomor PO'), // Placeholder
                        // Menambahkan note
                        // ->helperText('Catatan: Pastikan Nomor PO diisi dengan format yang benar.'),



                        Card::make()
                            ->schema([
                                Select::make('id_pembelian')
                                    ->label('Ambil dari Pembelian Sebelumnya')
                                    ->options(function () {
                                        return \App\Models\Pembelian::latest()->take(50)->get()->mapWithKeys(function ($pembelian) {
                                            return [
                                                $pembelian->id => "{$pembelian->plat_polisi} - {$pembelian->nama_supir} - (Timbangan ke-{$pembelian->keterangan}) - {$pembelian->created_at->format('d:m:Y')}"
                                            ];
                                        });
                                    })
                                    ->searchable()
                                    ->hidden(fn($livewire) => $livewire->getRecord()?->exists)
                                    ->reactive()
                                    ->dehydrated(false) // jangan disimpan ke DB
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state === null) {
                                            // Kosongkan semua data yang sebelumnya di-set
                                            $set('plat_polisi', null);
                                            $set('bruto', null);
                                            $set('tara', null);
                                            $set('netto', null);
                                            $set('nama_supir', null);
                                            $set('nama_barang', null);
                                            $set('id_supplier', null);
                                            $set('keterangan', null);
                                            $set('brondolan', null);
                                            return;
                                        }

                                        $pembelian = \App\Models\Pembelian::find($state);
                                        if ($pembelian) {
                                            $set('plat_polisi', $pembelian->plat_polisi);
                                            $set('bruto', $pembelian->tara);
                                            // $set('tara', $pembelian->tara);
                                            //$set('netto', max(0, intval($pembelian->bruto) - intval($pembelian->tara)));
                                            $set('nama_supir', $pembelian->nama_supir);
                                            $set('nama_barang', $pembelian->nama_barang);
                                            $set('id_supplier', $pembelian->id_supplier);
                                            // Naikkan keterangan jika awalnya 1
                                            $keteranganBaru = in_array(intval($pembelian->keterangan), [1, 2, 3, 4])
                                                ? intval($pembelian->keterangan) + 1
                                                : $pembelian->keterangan;
                                            $set('keterangan', $keteranganBaru);
                                            $set('brondolan', $pembelian->brondolan);
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('plat_polisi')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan plat polisi'),
                                TextInput::make('bruto')
                                    ->placeholder('Masukkan nilai bruto')
                                    ->label('Bruto')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 600) // Tunggu 500ms setelah user berhenti mengetik
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $tara = $get('tara') ?? 0;
                                        $set('netto', max(0, intval($state) - intval($tara))); // Hitung netto
                                    }),
                                TextInput::make('nama_supir')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Supir'),
                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->numeric()
                                    ->live(debounce: 600) // Tambahkan debounce juga di sini
                                    ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                        $bruto = $get('bruto') ?? 0;
                                        $set('netto', max(0, intval($bruto) - intval($state)));

                                        $record = $livewire->record ?? null;
                                        // Hanya isi jam_keluar jika sedang edit ($record tidak null) dan jam_keluar masih kosong
                                        if ($record && !empty($state) && empty($get('jam_keluar'))) {
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } elseif (empty($state)) {
                                            // Jika tara dikosongkan, hapus juga jam_keluar
                                            $set('jam_keluar', null);
                                        }
                                    }),

                                TextInput::make('nama_barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Barang'),

                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric(),
                                Select::make('id_supplier')
                                    ->label('Supplier')
                                    ->placeholder('Pilih Supplier')
                                    ->options(Supplier::pluck('nama_supplier', 'id')) // Ambil daftar mobil
                                    ->searchable(), // Biar bisa cari

                                Select::make('keterangan') // Gantilah 'tipe' dengan nama field di database
                                    ->label('Timbangan ke-')
                                    ->options([
                                        '1' => 'Timbangan ke-1',
                                        '2' => 'Timbangan ke-2',
                                        '3' => 'Timbangan ke-3',
                                        '4' => 'Timbangan ke-4',
                                        '5' => 'Timbangan ke-5',
                                    ])
                                    ->default('1')
                                    ->placeholder('Pilih timbangan ke-')
                                    // ->inlineLabel() // Membuat label sebelah kiri
                                    ->native(false) // Mengunakan dropdown modern
                                    ->required(), // Opsional: Atur default value
                                TextInput::make('no_container')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan No Container'),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('jumlah_karung')
                                            ->numeric()
                                            ->label('Jumlah Karung')
                                            ->autocomplete('off')
                                            ->placeholder('Masukkan Jumlah Karung'),
                                        Select::make('brondolan') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Satuan Muatan')
                                            ->options([
                                                'GONI' => 'GONI',
                                                'CURAH' => 'CURAH',
                                            ])
                                            ->placeholder('Pilih Satuan Timbangan')
                                            ->native(false) // Mengunakan dropdown modern
                                            ->required(), // Opsional: Atur default value
                                    ])->columnSpan(1),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(2)
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn(Pembelian $record): ?string =>
                optional(Auth::user())->hasAnyRole(['super_admin'])
                    // super_admin & admin selalu bisa
                    ? EditPembelian::getUrl(['record' => $record])
                    // selain itu, hanya bisa kalau kernek belum terisi
                    : (! $record->tara
                        ? EditPembelian::getUrl(['record' => $record])
                        : null
                    )
            )
            // Query dasar tanpa filter tara
            ->query(Pembelian::query())
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_spb')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),
                TextColumn::make('plat_polisi')->label('Plat Polisi')
                    ->searchable(),
                TextColumn::make('nama_supir')
                    ->searchable(),
                TextColumn::make('supplier.nama_supplier')->label('Supplier')
                    ->searchable(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->prefix('Timbangan-')
                    ->searchable(),
                TextColumn::make('satuan_muatan')
                    ->label('Satuan Muatan')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $karung = $record->jumlah_karung ?? '';
                        $brondolan = $record->brondolan ?? '-';

                        if (strtolower($brondolan) === 'curah') {
                            return $brondolan;
                        }

                        return "{$karung} - {$brondolan}";
                    }),
                TextColumn::make('bruto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tara')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('no_container')
                    ->searchable(),
                TextColumn::make('jam_masuk'),
                TextColumn::make('jam_keluar'),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('no_spb', 'desc')
            ->actions([
                Tables\Actions\Action::make('view-pembelian')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-pembelian", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->filters([
                // Filter untuk menampilkan data pada hari ini
                Filter::make('Hari Ini')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDate('created_at', Carbon::today())
                    )->toggle(),
                // Filter toggle untuk menampilkan data dimana tara null
                Filter::make('Tara Kosong')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('tara')
                    )
                    ->toggle() // Filter ini dapat diaktifkan/nonaktifkan oleh pengguna
                    ->default(),
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
            'view-pembelian' => Pages\ViewPembelian::route('/{record}/view-pembelian'),
        ];
    }
}
