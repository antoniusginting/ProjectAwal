<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Sortiran;
use Filament\Forms\Form;
use App\Models\Pembelian;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\KapasitasLumbungBasah;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\SortiranResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SortiranResource\RelationManagers;
use App\Filament\Resources\SortiranResource\Pages\ViewSortiran;

class SortiranResource extends Resource
{
    public static function getNavigationSort(): int
    {
        return 1; // Ini akan muncul di atas
    }
    protected static ?string $model = Sortiran::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Sortiran';
    public static ?string $label = 'Daftar Sortiran ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pembelian') //Menambahkan Header
                    ->schema([
                        Card::make()
                            ->schema([
                                // TextInput::make('created_at')
                                //     ->label('Tanggal Sekarang')
                                //     ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                                //     ->disabled(), // Tidak bisa diedit
                                Select::make('id_pembelian')
                                    ->label('No SPB')
                                    ->placeholder('Pilih No SPB Pembelian')
                                    ->options(
                                        Pembelian::whereNotIn('id', Sortiran::pluck('id_pembelian')) // Exclude yang sudah ada
                                            ->latest()
                                            ->with(['mobil', 'supplier'])
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->no_spb . ' - Timbangan ' . $item->keterangan . ' - ' .
                                                        $item->supplier->nama_supplier . ' - ' . $item->plat_polisi
                                                ];
                                            })
                                    )
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn($record) => $record !== null)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $pembelian = Pembelian::find($state);
                                            $set('netto_pembelian', $pembelian?->netto ?? 0);
                                            $set('netto_bersih', $pembelian?->netto ?? 0);
                                            $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');
                                            $set('plat_polisi', $pembelian?->plat_polisi ?? 'Plat tidak ditemukan');
                                            $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $pembelian = Pembelian::find($state);
                                        $set('netto_pembelian', $pembelian?->netto ?? 0);
                                        $set('netto_bersih', $pembelian?->netto ?? 0);
                                        $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');
                                        $set('plat_polisi', $pembelian?->plat_polisi ?? 'Plat tidak ditemukan');
                                        $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                    }),
                                TextInput::make('netto_pembelian')
                                    ->label('Netto Pembelian')
                                    ->reactive()
                                    ->afterStateHydrated(fn($state, $set) => $set('netto_pembelian', number_format($state, 0, ',', '.')))
                                    ->disabled(),
                                TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->placeholder('Otomatis terisi saat memilih no SPB')
                                    ->disabled(),
                                TextInput::make('netto_bersih')
                                    ->label('Netto Bersih')
                                    ->placeholder('Otomatis terisi')
                                    ->afterStateHydrated(function ($state, callable $set, callable $get, $record) {
                                        // Jika data sudah ada di database, gunakan nilai dari record
                                        if ($record && $record->netto_bersih !== null) {
                                            // Parsing nilai dari record dan pastikan format angka
                                            $value = (float) str_replace(['.', ','], ['', '.'], $record->netto_bersih);
                                        } else {
                                            // Jika tidak ada di database, ambil dari netto_pembelian
                                            $value = $get('netto_pembelian');
                                        }

                                        // Format angka dengan ribuan menggunakan titik dan desimal menggunakan koma
                                        $set('netto_bersih', number_format($value, 0, ',', '.'));
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Hilangkan format sebelum melakukan operasi matematis
                                        $cleanState = (float) str_replace(['.', ','], ['', '.'], $state);
                                        $cleanTungkul = (float) str_replace(['.', ','], ['', '.'], $get('berat_tungkul') ?? 0);

                                        // Lakukan pengurangan
                                        $nettoBersih = max(0, ($cleanState - $cleanTungkul));

                                        // Format kembali hasilnya
                                        $set('netto_bersih', number_format($nettoBersih, 0, ',', '.'));
                                    })
                                    ->reactive()
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('plat_polisi')
                                    ->label('Plat Polisi')
                                    ->placeholder('Otomatis terisi saat memilih no SPB')
                                    ->disabled(),
                                TextInput::make('berat_tungkul')
                                    ->label('Berat Tungkul')
                                    ->placeholder('Masukkan berat tungkul jika ada')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Ambil nilai dasar dari netto_pembelian jika ada, agar tidak terjadi double subtraction
                                        $nettoPembelian = (float) str_replace(['.', ','], ['', '.'], $get('netto_pembelian') ?? 1);
                                        $beratTungkul = (float) str_replace(['.', ','], ['', '.'], $state);

                                        // Hitung updated netto bersih
                                        $updatedNettoBersih = max(0, $nettoPembelian - $beratTungkul);

                                        // Perbaharui tampilan atau nilai netto_bersih dengan format ribuan
                                        $set('netto_bersih', number_format($updatedNettoBersih, 0, ',', '.'));
                                    }),
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->placeholder('Otomatis terisi saat memilih no SPB')
                                    ->disabled(),
                                TextInput::make('total_karung')
                                    ->label('Total Karung')
                                    ->numeric()
                                    ->helperText('Keterangan: Ketik ulang total karung hanya pada menu ubah Sortiran')
                                    ->placeholder('Masukkan Total Karung')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Ambil nilai dasar netto: gunakan 'netto_pembelian' sebagai basis
                                        $nettoPembelian = floatval(str_replace(['.', ','], ['', '.'], $get('netto_pembelian') ?? 1));
                                        // Ambil berat tungkul, default 0 jika tidak ada
                                        $beratTungkul = floatval(str_replace(['.', ','], ['', '.'], $get('berat_tungkul') ?? 0));

                                        // Hitung netto bersih yang up to date
                                        $updatedNettoBersih = max(0, $nettoPembelian - $beratTungkul);

                                        $totalKarung = floatval($state ?: 1); // Menghindari pembagian dengan nol

                                        // Jika field total_karung kosong, reset tonase
                                        if (empty($state)) {
                                            foreach (range(1, 6) as $i) {
                                                $set("tonase_$i", null);
                                            }
                                            return;
                                        }
                                        // Hitung nilai tonase untuk tiap unit karung
                                        foreach (range(1, 6) as $i) {
                                            $jumlahKarung = floatval($get("jumlah_karung_$i") ?? 0);
                                            $tonase = (($jumlahKarung * $updatedNettoBersih) / $totalKarung);

                                            // Tentukan presisi desimal, misalnya 0 (bulat)
                                            $precision = 0;
                                            $roundedTonase = round($tonase, $precision);
                                            $formattedTonase = number_format($roundedTonase, $precision, ',', '.');
                                            $set("tonase_$i", $formattedTonase);
                                        }

                                        // Opsi: Jika Anda ingin secara visual juga mengupdate field netto_bersih
                                        $set('netto_bersih', number_format($updatedNettoBersih, 0, ',', '.'));
                                    }),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(2),
                    ])
                    ->collapsible(),
                Card::make()
                    ->schema([

                        Placeholder::make('next_idi')
                            ->label('No Sortiran')
                            ->columnSpan(2)
                            ->content(function ($record) {
                                // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                if ($record) {
                                    return $record->no_sortiran;
                                }

                                // Jika sedang membuat data baru, hitung kode berikutnya
                                $nextId = (Sortiran::max('id') ?? 0) + 1;
                                return 'S' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                            }),

                        TextInput::make('no_lumbung')
                            ->label('No Lumbung')
                            ->placeholder('Masukkan No Lumbung')
                            ->required()
                            ->numeric()
                            ->columnSpan(2),
                        // Grid untuk menyusun field ke kanan
                        Grid::make(3) // 3 Kolom
                            ->schema([
                                // Kualitas Jagung 1
                                Card::make('Jagung ke-1')
                                    ->schema([
                                        Select::make('kualitas_jagung_1') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 1')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false) // Mengunakan dropdown modern
                                            ->required(), // Opsional: Atur default value
                                        FileUpload::make('foto_jagung_1')
                                            ->image()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_1')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false) // Mengunakan dropdown modern
                                            ->required(), // Opsional: Atur default value
                                        TextInput::make('jumlah_karung_1')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),
                                        TextInput::make('tonase_1')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(), // Satu card per kolom

                                // Kualitas Jagung 2
                                Card::make('Jagung ke-2')
                                    ->schema([
                                        Select::make('kualitas_jagung_2') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_2')
                                            ->image()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_2')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_2')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),

                                        TextInput::make('tonase_2')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase')
                                            ->readOnly(),

                                    ])
                                    ->columnSpan(1)->collapsible(),

                                // Kualitas Jagung 3
                                Card::make('Jagung ke-3')
                                    ->schema([
                                        Select::make('kualitas_jagung_3') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_3')
                                            ->image()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_3')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_3')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),


                                        TextInput::make('tonase_3')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(),

                                // Kualitas Jagung 4
                                Card::make('Jagung ke-4')
                                    ->schema([
                                        Select::make('kualitas_jagung_4') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_4')
                                            ->image()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_4')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_4')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),


                                        TextInput::make('tonase_4')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(),

                                // Kualitas Jagung 5
                                Card::make('Jagung ke-5')
                                    ->schema([
                                        Select::make('kualitas_jagung_5') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_5')
                                            ->image()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_5')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_5')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),


                                        TextInput::make('tonase_5')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(),

                                // Kualitas Jagung 6
                                Card::make('Jagung ke-6')
                                    ->schema([
                                        Select::make('kualitas_jagung_6') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_6')
                                            ->image()
                                            ->openable()
                                            ->imagePreviewHeight(200)
                                            ->label('Foto Jagung'),
                                        Select::make('x1_x10_6')
                                            ->label('X1-X10')
                                            ->options([
                                                'X0' => 'X0',
                                                'X1' => 'X1',
                                                'X2' => 'X2',
                                                'X3' => 'X3',
                                                'X4' => 'X4',
                                                'X5' => 'X5',
                                                'X6' => 'X6',
                                                'X7' => 'X7',
                                                'X8' => 'X8',
                                                'X9' => 'X9',
                                                'X10' => 'X10',
                                            ])
                                            ->placeholder('Pilih Silang Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        TextInput::make('jumlah_karung_6')
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->label('Jumlah Karung')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    (int) ($get('jumlah_karung_1') ?? 0) +
                                                        (int) ($get('jumlah_karung_2') ?? 0) +
                                                        (int) ($get('jumlah_karung_3') ?? 0) +
                                                        (int) ($get('jumlah_karung_4') ?? 0) +
                                                        (int) ($get('jumlah_karung_5') ?? 0) +
                                                        (int) ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),
                                        TextInput::make('tonase_6')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1)->collapsible(),
                            ]),
                        Card::make()
                            ->schema([
                                TextInput::make('kadar_air')
                                    ->label('Kadar Air')
                                    ->numeric()
                                    ->placeholder('Masukkan kadar air')
                                    ->required(),


                            ])
                            ->columnSpan(3), // Menggunakan lebar penuh agar sejajar

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_sortiran')
                    ->label('No Sortiran')
                    ->alignCenter(),
                TextColumn::make('pembelian.no_spb')->label('No SPB')
                    ->searchable(),
                TextColumn::make('pembelian.netto')->label('Netto')
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('pembelian.tara')->label('Tara')
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('no_lumbung')->label('No Lumbung')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('total_karung')->label('Total Karung')
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                //Jagung 1
                TextColumn::make('kualitas_jagung_1')
                    ->label('Kualitas Jagung 1'),
                ImageColumn::make('foto_jagung_1')
                    ->label('Foto Jagung 1')
                    ->url(fn($record) => asset('storage/' . $record->foto_jagung_1))
                    ->openUrlInNewTab(),
                TextColumn::make('x1_x10_1')
                    ->label('X1 - X10 1')
                    ->alignCenter(),
                TextColumn::make('jumlah_karung_1')
                    ->label('Jumlah Karung 1')
                    ->alignCenter(),
                TextColumn::make('tonase_1')
                    ->label('Tonase 1'),

                //Jagung 2
                TextColumn::make('kualitas_jagung_2')
                    ->label('Kualitas Jagung 2'),
                ImageColumn::make('foto_jagung_2')
                    ->url(fn($record) => asset('storage/' . $record->foto_jagung_2))
                    ->openUrlInNewTab(),
                TextColumn::make('x1_x10_2')
                    ->label('X1 - X10 2')
                    ->alignCenter(),
                TextColumn::make('jumlah_karung_2')
                    ->label('Jumlah Karung 2')
                    ->alignCenter(),
                TextColumn::make('tonase_2')
                    ->label('Tonase 2'),

                //Jagung 3
                TextColumn::make('kualitas_jagung_3')
                    ->label('Kualitas Jagung 3'),
                ImageColumn::make('foto_jagung_3')
                    ->url(fn($record) => asset('storage/' . $record->foto_jagung_3))
                    ->openUrlInNewTab(),
                TextColumn::make('x1_x10_3')
                    ->label('X1 - X10 3')
                    ->alignCenter(),
                TextColumn::make('jumlah_karung_3')
                    ->label('Jumlah Karung 3')
                    ->alignCenter(),
                TextColumn::make('tonase_3')
                    ->label('Tonase 3'),
                //Jagung 4
                TextColumn::make('kualitas_jagung_4')
                    ->label('Kualitas Jagung 4'),
                ImageColumn::make('foto_jagung_4')
                    ->url(fn($record) => asset('storage/' . $record->foto_jagung_4))
                    ->openUrlInNewTab(),
                TextColumn::make('x1_x10_4')
                    ->label('X1 - X10 4')
                    ->alignCenter(),
                TextColumn::make('jumlah_karung_4')
                    ->label('Jumlah Karung 4')
                    ->alignCenter(),
                TextColumn::make('tonase_4')
                    ->label('Tonase 4'),
                //Jagung 5
                TextColumn::make('kualitas_jagung_5')
                    ->label('Kualitas Jagung 5'),
                ImageColumn::make('foto_jagung_5')
                    ->url(fn($record) => asset('storage/' . $record->foto_jagung_5))
                    ->openUrlInNewTab(),
                TextColumn::make('x1_x10_5')
                    ->label('X1 - X10 5')
                    ->alignCenter(),
                TextColumn::make('jumlah_karung_5')
                    ->label('Jumlah Karung 5')
                    ->alignCenter(),
                TextColumn::make('tonase_5')
                    ->label('Tonase 6'),
                //Jagung 6
                TextColumn::make('kualitas_jagung_6')
                    ->label('Kualitas Jagung 6'),
                ImageColumn::make('foto_jagung_6')
                    ->url(fn($record) => asset('storage/' . $record->foto_jagung_6))
                    ->openUrlInNewTab(),
                TextColumn::make('x1_x10_6')
                    ->label('X1 - X10 6')
                    ->alignCenter(),
                TextColumn::make('jumlah_karung_6')
                    ->label('Jumlah Karung 6')
                    ->alignCenter(),
                TextColumn::make('tonase_6')
                    ->label('Tonase 6'),
                TextColumn::make('kadar_air')
                    ->suffix('%'),
                TextColumn::make('user.name')
                    ->label('User')
            ])->defaultSort('no_sortiran', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('View')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-sortiran", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
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
            'index' => Pages\ListSortirans::route('/'),
            'create' => Pages\CreateSortiran::route('/create'),
            'edit' => Pages\EditSortiran::route('/{record}/edit'),
            'view-sortiran' => Pages\ViewSortiran::route('/{record}/view-sortiran'),
        ];
    }
}
