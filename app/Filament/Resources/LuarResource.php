<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Luar;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Pembelian;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\LuarResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LuarResource\Pages\EditLuar;
use App\Filament\Resources\LuarResource\Pages\ListLuars;
use App\Filament\Resources\LuarResource\Pages\CreateLuar;
use App\Filament\Resources\LuarResource\RelationManagers;
use App\Filament\Resources\PembelianResource\Pages\EditPembelian;

class LuarResource extends Resource
{
    protected static ?string $model = Luar::class;
    protected static ?string $navigationLabel = 'Pembelian Antar Pulau';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 5;
    public static ?string $label = 'Daftar Pembelian Antar Pulau ';
    protected static ?string $navigationIcon = 'heroicon-o-globe-asia-australia';

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
                                            return $record->kode;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (Luar::max('id') ?? 0) + 1;
                                        return 'C' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                // TextInput::make('jam_masuk')
                                //     ->readOnly()
                                //     ->suffixIcon('heroicon-o-clock')
                                //     ->default(now()->setTimezone('Asia/Jakarta')->format('H:i:s')),
                                // TextInput::make('jam_keluar')
                                //     ->label('Jam Keluar')
                                //     ->readOnly()
                                //     ->placeholder('Kosong jika belum keluar')
                                //     ->suffixIcon('heroicon-o-clock')
                                //     ->required(false)
                                //     ->afterStateHydrated(function ($state, callable $set) {
                                //         // Biarkan tetap kosong saat edit
                                //     }),
                                TextInput::make('created_at')
                                    ->label('Tanggal')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                            ])->columns(2)->collapsed(),

                        // TextInput::make('no_po')
                        //     ->label('Nomor PO') // Memberikan label deskriptif
                        //     ->placeholder('Masukkan Nomor PO'), // Placeholder
                        // Menambahkan note
                        // ->helperText('Catatan: Pastikan Nomor PO diisi dengan format yang benar.'),

                        Card::make()
                            ->schema([
                                // Select::make('id_pembelian')
                                //     ->label('Ambil dari Pembelian Sebelumnya')
                                //     ->options(function () {
                                //         return \App\Models\Luar::latest()->take(50)->get()->mapWithKeys(function ($luar) {
                                //             return [
                                //                 $luar->id => "{$luar->plat_polisi} - {$luar->nama_supir} - (Timbangan ke-{$luar->keterangan}) - {$luar->created_at->format('d:m:Y')}"
                                //             ];
                                //         });
                                //     })
                                //     ->searchable()
                                //     ->hidden(fn($livewire) => $livewire->getRecord()?->exists)
                                //     ->reactive()
                                //     ->dehydrated(false) // jangan disimpan ke DB
                                //     ->afterStateUpdated(function (callable $set, $state) {
                                //         if ($state === null) {
                                //             // Kosongkan semua data yang sebelumnya di-set
                                //             $set('plat_polisi', null);
                                //             $set('bruto', null);
                                //             $set('tara', null);
                                //             $set('netto', null);
                                //             $set('nama_supir', null);
                                //             $set('nama_barang', null);
                                //             $set('id_supplier', null);
                                //             $set('keterangan', null);
                                //             $set('brondolan', null);
                                //             return;
                                //         }

                                //         $luar = \App\Models\Luar::find($state);
                                //         if ($luar) {
                                //             $set('plat_polisi', $luar->plat_polisi);
                                //             $set('bruto', $luar->tara);
                                //             // $set('tara', $luar->tara);
                                //             //$set('netto', max(0, intval($luar->bruto) - intval($luar->tara)));
                                //             $set('nama_supir', $luar->nama_supir);
                                //             $set('nama_barang', $luar->nama_barang);
                                //             $set('id_supplier', $luar->id_supplier);
                                //             // Naikkan keterangan jika awalnya 1
                                //             $keteranganBaru = in_array(intval($luar->keterangan), [1, 2, 3, 4])
                                //                 ? intval($luar->keterangan) + 1
                                //                 : $luar->keterangan;
                                //             $set('keterangan', $keteranganBaru);
                                //             $set('brondolan', $luar->brondolan);
                                //         }
                                //     })
                                //     ->columnSpan(2),
                                TextInput::make('kode_segel')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan kode Segel'),
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
                                // TextInput::make('nama_supir')
                                //     ->autocomplete('off')
                                //     ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                //     ->placeholder('Masukkan Nama Supir'),

                                TextInput::make('nama_barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Barang'),
                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->numeric()
                                    ->live(debounce: 600)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                        $bruto = $get('bruto') ?? 0;
                                        $set('netto', max(0, intval($bruto) - intval($state)));

                                        // // Isi jam_keluar kapanpun tara diisi, baik create maupun edit
                                        // if (!empty($state) && empty($get('jam_keluar'))) {
                                        //     $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        // } elseif (empty($state)) {
                                        //     // Jika tara dikosongkan, hapus juga jam_keluar
                                        //     $set('jam_keluar', null);
                                        // }
                                    }),

                                Select::make('id_supplier')
                                    ->label('Supplier')
                                    ->placeholder('Pilih Supplier')
                                    ->options(Supplier::pluck('nama_supplier', 'id')) // Ambil daftar mobil
                                    ->searchable(), // Biar bisa cari
                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric(),

                                // Select::make('keterangan') // Gantilah 'tipe' dengan nama field di database
                                //     ->label('Timbangan ke-')
                                //     ->options([
                                //         '1' => 'Timbangan ke-1',
                                //         '2' => 'Timbangan ke-2',
                                //         '3' => 'Timbangan ke-3',
                                //         '4' => 'Timbangan ke-4',
                                //         '5' => 'Timbangan ke-5',
                                //     ])
                                //     ->default('1')
                                //     ->placeholder('Pilih timbangan ke-')
                                //     // ->inlineLabel() // Membuat label sebelah kiri
                                //     ->native(false) // Mengunakan dropdown modern
                                //     ->required(), // Opsional: Atur default value
                                TextInput::make('no_container')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan No Container'),

                                // Grid::make(2)
                                //     ->schema([
                                //         TextInput::make('jumlah_karung')
                                //             ->numeric()
                                //             ->label('Jumlah Karung')
                                //             ->autocomplete('off')
                                //             ->placeholder('Masukkan Jumlah Karung'),
                                //         Select::make('brondolan') // Gantilah 'tipe' dengan nama field di database
                                //             ->label('Satuan Muatan')
                                //             ->options([
                                //                 'GONI' => 'GONI',
                                //                 'CURAH' => 'CURAH',
                                //             ])
                                //             ->placeholder('Pilih Satuan Timbangan')
                                //             ->native(false) // Mengunakan dropdown modern
                                //             ->required(), // Opsional: Atur default value
                                //     ])->columnSpan(1),
                                // FileUpload::make('foto')
                                //     ->image()
                                //     ->multiple()
                                //     ->openable()
                                //     ->imagePreviewHeight(200)
                                //     ->label('Foto')
                                //     ->columnSpanFull(),
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
            ->recordUrl(function (Luar $record): ?string {
                $user = Auth::user();

                // 1) Super admin bisa edit semua kondisi
                if ($user && $user->hasRole('super_admin')) {
                    return EditLuar::getUrl(['record' => $record]);
                }

                // 2) Admin1 hanya bisa edit jika tara belum ada
                if ($user && $user->hasRole('timbangan')) {
                    if (!$record->tara) {
                        return EditLuar::getUrl(['record' => $record]);
                    }
                    return null;
                }

                // // 3) Admin2 hanya bisa edit jika no_spb belum ada
                // if ($user && $user->hasRole('admin')) {
                //     if (!$record->no_spb) {  // Sesuaikan dengan struktur data BK
                //         return EditPembelian::getUrl(['record' => $record]);
                //     }
                //     return null;
                // }

                // 4) Role lainnya tidak bisa edit
                return null;
            })
            // Query dasar tanpa filter tara
            ->query(Luar::query())
            ->defaultPaginationPageOption(10)
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
                TextColumn::make('kode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),
                TextColumn::make('kode_segel')
                    ->label('kode_segel')
                    ->searchable(),
                // TextColumn::make('nama_supir')
                //     ->searchable(),
                TextColumn::make('supplier.nama_supplier')->label('Supplier')
                    ->searchable(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                // TextColumn::make('keterangan')
                //     ->prefix('Timbangan-')
                //     ->searchable(),
                // TextColumn::make('satuan_muatan')
                //     ->label('Satuan Muatan')
                //     ->alignCenter()
                //     ->getStateUsing(function ($record) {
                //         $karung = $record->jumlah_karung ?? '';
                //         $brondolan = $record->brondolan ?? '-';

                //         if (strtolower($brondolan) === 'curah') {
                //             return $brondolan;
                //         }

                //         return "{$karung} - {$brondolan}";
                //     }),
                TextColumn::make('bruto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tara')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('no_container')
                    ->searchable(),
                // TextColumn::make('jam_masuk'),
                // TextColumn::make('jam_keluar'),
                // ImageColumn::make('foto')
                //     ->label('Foto 1')
                //     ->getStateUsing(fn($record) => $record->foto[0] ?? null)
                //     ->url(fn($record) => asset('storage/' . ($record->foto[0] ?? '')))
                //     ->openUrlInNewTab(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('kode', 'desc')
            // ->actions([
            //     Tables\Actions\Action::make('view-pembelian')
            //         ->label(__("Lihat"))
            //         ->icon('heroicon-o-eye')
            //         ->url(fn($record) => self::getUrl("view-pembelian", ['record' => $record->id])),
            // ], position: ActionsPosition::BeforeColumns)
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
                    ->default(function () {
                        // Filter aktif secara default hanya jika pengguna BUKAN super_admin ,'admin'
                        return !optional(Auth::user())->hasAnyRole(['super_admin']);
                    })
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
            'index' => Pages\ListLuars::route('/'),
            'create' => Pages\CreateLuar::route('/create'),
            'edit' => Pages\EditLuar::route('/{record}/edit'),
        ];
    }
}
