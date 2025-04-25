<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Mobil;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;

use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\PenjualanResource\Pages;
use App\Filament\Resources\PenjualanResource\Pages\EditPenjualan;
use Filament\Forms\Components\Grid;

use function Laravel\Prompts\text;

class PenjualanResource extends Resource
{
    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Timbangan';

    public static ?string $label = 'Daftar Penjualan ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No SPB')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_spb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (Penjualan::max('id') ?? 0) + 1;
                                        return 'J' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
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
                                    ->label('Tanggal Sekarang')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(),
                            ])->columns(4)->collapsed(),
                        Card::make()
                            ->schema([
                                Select::make('id_penjualan')
                                    ->label('Ambil dari Penjualan Sebelumnya')
                                    ->options(function () {
                                        // Ambil id_timbangan_tronton dari SuratJalan
                                        $timbanganTrontonIds = \App\Models\SuratJalan::whereNotNull('id_timbangan_tronton')
                                            ->pluck('id_timbangan_tronton');

                                        // Gunakan subquery untuk mendapatkan semua id penjualan
                                        $existingPenjualans = \App\Models\TimbanganTronton::whereIn('id', $timbanganTrontonIds)
                                            ->select(
                                                'id_timbangan_jual_1',
                                                'id_timbangan_jual_2',
                                                'id_timbangan_jual_3',
                                                'id_timbangan_jual_4',
                                                'id_timbangan_jual_5',
                                                'id_timbangan_jual_6'
                                            )
                                            ->get()
                                            ->flatMap(function ($item) {
                                                $ids = [];
                                                for ($i = 1; $i <= 6; $i++) {
                                                    $field = "id_timbangan_jual_{$i}";
                                                    if (!is_null($item->$field)) {
                                                        $ids[] = $item->$field;
                                                    }
                                                }
                                                return $ids;
                                            })
                                            ->toArray();

                                        // Ambil ID penjualan yang belum ada di tabel timbangan_tronton
                                        return \App\Models\Penjualan::latest()
                                            ->whereNotIn('id', $existingPenjualans)  // Hanya ambil yang belum ada di tabel timbangan_tronton
                                            ->take(50)
                                            ->get()
                                            ->mapWithKeys(function ($penjualan) {
                                                return [
                                                    $penjualan->id => "{$penjualan->plat_polisi} - {$penjualan->nama_supir} - (Timbangan ke-{$penjualan->keterangan}) - {$penjualan->created_at->format('d:m:Y')}"
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->hidden(fn($livewire) => $livewire->getRecord()?->exists)
                                    ->dehydrated(false) // jangan disimpan ke DB
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        $penjualan = \App\Models\Penjualan::find($state);
                                        if ($state === null) {
                                            // Kosongkan semua data yang sebelumnya di-set
                                            $set('plat_polisi', null);
                                            $set('bruto', null);
                                            $set('tara', null);
                                            $set('netto', null);
                                            $set('nama_supir', null);
                                            $set('nama_barang', 'JAGUNG KERING SUPER');
                                            $set('keterangan', null);
                                            $set('brondolan', null);
                                            return;
                                        }
                                        if ($penjualan) {
                                            $set('plat_polisi', $penjualan->plat_polisi);
                                            $set('tara', $penjualan->bruto);
                                            $set('nama_supir', $penjualan->nama_supir);
                                            $set('nama_barang', $penjualan->nama_barang);
                                            // Naikkan keterangan jika awalnya 1, 2, atau 3
                                            $keteranganBaru = in_array(intval($penjualan->keterangan), [1, 2, 3, 4])
                                                ? intval($penjualan->keterangan) + 1
                                                : $penjualan->keterangan;
                                            $set('keterangan', $keteranganBaru);
                                            $set('brondolan', $penjualan->brondolan);
                                        }
                                    })
                                    ->columnSpan(4),

                                TextInput::make('plat_polisi')
                                    ->suffixIcon('heroicon-o-truck')
                                    ->autocomplete('off')
                                    ->columnSpan(2)
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan plat polisi'),
                                TextInput::make('bruto')
                                    ->label('Bruto')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->placeholder('Masukkan Nilai Bruto')
                                    ->live(debounce: 600) // Tunggu 500ms setelah user berhenti mengetik
                                    ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                        $tara = $get('tara') ?? 0;
                                        $set('netto', max(0, intval($state) - intval($tara))); // Hitung netto
                                        $record = $livewire->record ?? null;
                                        // Hanya isi jam_keluar jika sedang edit ($record tidak null) dan jam_keluar masih kosong
                                        if ($record && !empty($state) && empty($get('jam_keluar'))) {
                                            $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                        } elseif (empty($state)) {
                                            // Jika tara dikosongkan, hapus juga jam_keluar
                                            $set('jam_keluar', null);
                                        }
                                    }),
                                TextInput::make('nama_supir')
                                    ->autocomplete('off')
                                    ->columnSpan(2)
                                    ->placeholder('Masukkan Nama Supir')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->required(),
                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->columnSpan(2)
                                    ->placeholder('Masukkan Nilai Tara')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 600) // Tambahkan debounce juga di sini
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $bruto = $get('bruto') ?? 0;
                                        $set('netto', max(0, intval($bruto) - intval($state)));
                                    }),
                                TextInput::make('nama_barang')
                                    ->default('JAGUNG KERING SUPER')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->columnSpan(2)
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric(),
                                TextInput::make('nama_lumbung')
                                    ->placeholder('Masukkan Nama Lumbung')
                                    ->autocomplete('off')
                                    ->columnSpan(2)
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state)),
                                Select::make('keterangan') // Gantilah 'tipe' dengan nama field di database
                                    ->label('Timbangan ke-')
                                    ->columnSpan(2)
                                    ->options([
                                        '1' => 'Timbangan ke-1',
                                        '2' => 'Timbangan ke-2',
                                        '3' => 'Timbangan ke-3',
                                        '4' => 'Timbangan ke-4',
                                        '5' => 'Timbangan ke-5',
                                    ])
                                    ->default(1)
                                    ->placeholder('Pilih timbangan ke-')
                                    // ->inlineLabel() // Membuat label sebelah kiri
                                    ->native(false) // Mengunakan dropdown modern
                                    ->required(), // Opsional: Atur default value
                                TextInput::make('no_lumbung')
                                    ->placeholder('Masukkan No Lumbung')
                                    ->columnSpan(2)
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state)),
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
                                TextInput::make('no_container')
                                    ->label('No Container')
                                    ->placeholder('Masukkan no container')
                                    ->hidden(),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(4),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn(Penjualan $record): ?string =>
                optional(Auth::user())->hasAnyRole(['super_admin'])
                    // super_admin & admin selalu bisa
                    ? EditPenjualan::getUrl(['record' => $record])
                    // selain itu, hanya bisa kalau kernek belum terisi
                    : (! $record->bruto
                        ? EditPenjualan::getUrl(['record' => $record])
                        : null
                    )
            )
            // ->query(
            //     Penjualan::query()->whereNull('bruto') // hanya data yang punya nilai tara
            // )
            ->query(Penjualan::query())
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
                // TextColumn::make('supplier.nama_supplier')->label('Supplier')
                //     ->searchable(),
                // TextColumn::make('supplier.jenis_supplier')->label('Jenis')
                //     ->searchable(),
                TextColumn::make('keterangan')
                    ->prefix('Timbangan ke-')
                    ->searchable(),
                TextColumn::make('satuan_muatan')
                    ->label('Satuan Muatan')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $karung = $record->jumlah_karung ?? '-';
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
                TextColumn::make('no_lumbung')
                    ->alignCenter(),
                TextColumn::make('nama_lumbung')
                    ->alignCenter(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('jam_masuk')
                    ->alignCenter(),
                TextColumn::make('jam_keluar')
                    ->alignCenter(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('no_spb', 'desc') // Megurutkan no_spb terakhir menjadi pertama pada tabel
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-penjualan')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-penjualan", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
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
                    )->toggle(),
                // Filter toggle untuk menampilkan data dimana tara null
                Filter::make('Bruto Kosong')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('bruto')
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
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
            'view-penjualan' => Pages\ViewPenjualan::route('/{record}/view-penjualan'),
        ];
    }
}
