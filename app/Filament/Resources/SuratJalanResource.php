<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kontrak;
use Filament\Forms\Form;
use App\Models\SuratJalan;
use Filament\Tables\Table;
use App\Models\AlamatKontrak;
use App\Models\TimbanganTronton;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratJalanResource\Pages;
use App\Filament\Resources\SuratJalanResource\RelationManagers;

class SuratJalanResource extends Resource
{
    protected static ?string $model = SuratJalan::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'Surat Jalan';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 4;
    public static ?string $label = 'Daftar Surat Jalan ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Card::make('Informasi Kontrak')
                            ->schema([
                                Select::make('id_kontrak2')
                                    ->label('Nama Kontrak')
                                    ->required()
                                    ->options(Kontrak::all()->pluck('nama', 'id'))
                                    ->searchable()
                                    ->reactive(), // Agar saat memilih kontrak, alamat terfilter
                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                                Select::make('id_kontrak')
                                    ->label('Kepada Yth.')
                                    ->required()
                                    ->options(Kontrak::all()->pluck('nama', 'id'))
                                    ->searchable()
                                    ->reactive(), // Agar saat memilih kontrak, alamat terfilter
                                Select::make('id_alamat')
                                    ->label('Pilih Alamat')
                                    ->options(
                                        fn(callable $get) =>
                                        $get('id_kontrak')
                                            ? AlamatKontrak::where('id_kontrak', $get('id_kontrak'))
                                            ->pluck('alamat', 'id')
                                            : []
                                    )
                                    ->searchable()
                                    ->required(),
                                TextInput::make('po')
                                    ->label('PO')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan no PO'),
                                TextInput::make('kota')
                                    ->label('Kota')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Kota')
                                    ->required(),
                            ])->columns(2),
                        Card::make('Informasi Timbangan')
                            ->schema([
                                Select::make('id_timbangan_tronton')
                                    ->label('ID Laporan Penjualan')

                                    ->options(
                                        TimbanganTronton::whereNotIn('id', SuratJalan::pluck('id_timbangan_tronton')) // Exclude yang sudah ada
                                            ->latest()
                                            ->with(['penjualan1'])
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->kode . ' - ' . $item->penjualan1->nama_supir . ' - ' .
                                                        ($item->penjualan1->plat_polisi ?? $item->penjualan1->no_container)
                                                ];
                                            })
                                    )
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        // Ambil data timbangan berdasarkan id yang dipilih
                                        $timbangan = TimbanganTronton::where('id', $get('id_timbangan_tronton'))->first();

                                        // Set field-field lain berdasarkan data yang didapat
                                        $set('nama_supir', $timbangan?->penjualan1?->nama_supir ?? '');
                                        $set('nama_barang', $timbangan?->penjualan1?->nama_barang ?? '');
                                        $set('tara_awal', $timbangan?->tara_awal ?? '');
                                        $set('bruto_akhir', $timbangan?->bruto_akhir ?? '');
                                        $set('total_netto', $timbangan?->total_netto ?? '');
                                    })
                                    ->afterStateHydrated(function (callable $set, callable $get, $state) {
                                        // Pastikan hanya berjalan saat edit data
                                        if ($state) {
                                            $timbangan = TimbanganTronton::where('id', $state)->first();

                                            $set('nama_supir', $timbangan?->penjualan1?->nama_supir ?? '');
                                            $set('nama_barang', $timbangan?->penjualan1?->nama_barang ?? '');
                                            $set('tara_awal', $timbangan?->tara_awal ?? 0);
                                            $set('bruto_akhir', $timbangan?->bruto_akhir ?? 0);
                                            $set('total_netto', $timbangan?->total_netto ?? 0);
                                        }
                                    }),
                                TextInput::make('bruto_akhir')
                                    ->label('Bruto Awal')
                                    ->readOnly()
                                    ->reactive()
                                    ->hidden(),
                                TextInput::make('total_netto')
                                    ->label('Netto Awal')
                                    ->readOnly()
                                    ->reactive()
                                    ->hidden(),
                                TextInput::make('tambah_berat')
                                    ->label('Tambah Berat')
                                    ->numeric()
                                    ->placeholder('Masukkan berat yang ingin ditambah')
                                    ->live(debounce: 600)
                                    ->reactive() // Menjadikan field ini responsif
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('bruto_final', ($get('bruto_akhir') ?? 0) + ($state ?? 0));
                                        $set('netto_final', ($get('total_netto') ?? 0) + ($state ?? 0));
                                    }),
                                TextInput::make('satuan_muatan')
                                    ->label('Satuan Muatan')
                                    ->mutateDehydratedStateUsing(fn ($state) => strtoupper($state))
                                    ->placeholder('Masukkan satuan muatan'),
                                TextInput::make('bruto_final')
                                    ->label('Bruto')
                                    ->readOnly(), // Field ini tidak bisa diubah langsung oleh user
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('tara_awal')
                                    ->label('Tara')
                                    ->disabled() // Field ini hanya ditampilkan sebagai hasil dari database
                                    ->dehydrated(false),
                                TextInput::make('nama_supir')
                                    ->label('Nama Supir')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('netto_final')
                                    ->label('Netto')
                                    ->readOnly(),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(2),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->alignCenter(),
                TextColumn::make('po')
                    ->label('No PO')
                    ->searchable(),
                TextColumn::make('kontrak2.nama')
                    ->label('Nama Kontrak')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('kontrak.nama')
                    ->label('Kepada Yth.')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('alamat.alamat')
                    ->label('Alamat')
                    ->wrap()
                    ->searchable()
                    ->extraAttributes(['style' => 'width: 250px;']),
                TextColumn::make('netto_final')
                    ->label('Netto')
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('satuan_muatan')
                    ->label('Satuan Muatan')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('tronton.penjualan1.nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('View')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-surat-jalan", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSuratJalans::route('/'),
            'create' => Pages\CreateSuratJalan::route('/create'),
            'edit' => Pages\EditSuratJalan::route('/{record}/edit'),
            'view-surat-jalan' => Pages\ViewSuratJalan::route('/{record}/view-surat-jalan'),
        ];
    }
}
