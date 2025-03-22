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
                                    ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
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
                                    ->placeholder('Masukkan no PO'),
                                TextInput::make('kota')
                                    ->label('Kota')
                                    ->placeholder('Masukkan Kota')
                                    ->required(),
                            ])->columns(2),
                        Card::make('Informasi Timbangan')
                            ->schema([
                                Select::make('id_timbangan_tronton')
                                    ->label('ID Timbangan Tronton')
                                    ->options(TimbanganTronton::latest()->with('penjualan1')->get()->mapWithKeys(function ($item) {
                                        return [
                                            $item->id => $item->id . ' - ' . $item->penjualan1->nama_supir . ' - ' .
                                                ($item->penjualan1->plat_polisi ?? $item->penjualan1->no_container)
                                        ];
                                    }))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        // Ambil data timbangan berdasarkan id yang dipilih
                                        $timbangan = TimbanganTronton::where('id', $get('id_timbangan_tronton'))->first();

                                        // Set field-field lain berdasarkan data yang didapat
                                        $set('brondolan', $timbangan?->penjualan1?->brondolan ?? '');
                                        $set('nama_supir', $timbangan?->penjualan1?->nama_supir ?? '');
                                        $set('nama_barang', $timbangan?->penjualan1?->nama_barang ?? '');
                                        // Pastikan nilai taranya default ke 0 bila tidak ada (untuk perhitungan)
                                        $taranya = $timbangan?->penjualan1?->tara ?? 0;
                                        $set('tara', $taranya);
                                        // Pastikan bruto_final juga default ke 0 bila null
                                        $brutoFinal = $timbangan?->bruto_final ?? 0;
                                        $set('bruto_final', $brutoFinal);

                                        // Hitung netto secara langsung: netto = bruto_final - tara
                                        $set('netto', max(0, (float) $brutoFinal - (float) $taranya));
                                    })->columnSpan(2),
                                TextInput::make('brondolan')
                                    ->label('Brondolan')
                                    ->disabled() // Hanya untuk menampilkan, tidak bisa diubah
                                    ->dehydrated(false), // Tidak tersimpan ke database
                                TextInput::make('bruto_final')
                                    ->label('Bruto Final')
                                    ->disabled() // Field ini tidak bisa diubah langsung oleh user
                                    ->default(0)
                                    ->reactive()
                                    ->live() // Agar update secara langsung jika diubah (jika ada mekanisme update nilai)
                                    ->afterStateUpdated(
                                        fn(callable $set, callable $get) =>
                                        $set('netto', max(0, (float) $get('bruto_final') - (float) $get('tara')))
                                    )
                                    ->dehydrated(false),
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('tara')
                                    ->label('Tara')
                                    ->disabled() // Field ini hanya ditampilkan sebagai hasil dari database
                                    ->default(0)
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(
                                        fn(callable $set, callable $get) =>
                                        $set('netto', max(0, (float) $get('bruto_final') - (float) $get('tara')))
                                    )
                                    ->dehydrated(false),
                                TextInput::make('nama_supir')
                                    ->label('Nama Supir')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->default(0)
                                    ->readOnly(),
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
                    ->searchable(),
                TextColumn::make('kontrak.nama')
                    ->label('Kepada Yth.')
                    ->searchable(),
                TextColumn::make('alamat.alamat')
                    ->label('Alamat')
                    ->wrap()
                    ->searchable()
                    ->extraAttributes(['style' => 'max-width: 250px;']),
                TextColumn::make('netto')
                    ->label('Netto')
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tronton.penjualan1.brondolan')
                    ->label('Brondolan')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('tronton.penjualan1.nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
