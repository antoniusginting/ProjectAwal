<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SortiranResource\Pages;
use App\Filament\Resources\SortiranResource\RelationManagers;
use App\Models\KapasitasLumbungBasah;
use App\Models\Pembelian;
use App\Models\Sortiran;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                                    ->options(Pembelian::pluck('no_spb', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn($record) => $record !== null)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $pembelian = Pembelian::find($state);
                                            $set('netto_pembelian', $pembelian?->netto ?? 0);
                                            $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');
                                            $set('plat_polisi', $pembelian?->mobil?->plat_polisi ?? 'Plat tidak ditemukan');
                                            $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $pembelian = Pembelian::find($state);
                                        $set('netto_pembelian', $pembelian?->netto ?? 0);
                                        $set('nama_barang', $pembelian?->nama_barang ?? 'Barang tidak ditemukan');
                                        $set('plat_polisi', $pembelian?->mobil?->plat_polisi ?? 'Plat tidak ditemukan');
                                        $set('nama_supplier', $pembelian?->supplier->nama_supplier ?? 'Supplier tidak ditemukan');
                                    }),

                                TextInput::make('plat_polisi')
                                    ->label('Plat Polisi')
                                    ->disabled(),

                                TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->disabled(),

                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->disabled(),

                                TextInput::make('netto_pembelian')
                                    ->label('Netto Pembelian')
                                    ->reactive()
                                    ->afterStateHydrated(fn($state, $set) => $set('netto_pembelian', number_format($state, 0, ',', '.')))
                                    ->disabled(),

                                TextInput::make('total_karung')
                                    ->label('Total Karung')
                                    ->numeric()
                                    ->placeholder('Masukkan Total Karung')
                                    ->required()
                                    ->reactive()
                                    ->debounce(350)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $netto = $get('netto_pembelian') ?? 1; // Pastikan tidak terjadi pembagian dengan nol
                                        $set('tonase_1', ($get('jumlah_karung_1') ?? 0) *  $netto / $state);
                                        $set('tonase_2', ($get('jumlah_karung_2') ?? 0) *  $netto / $state);
                                        $set('tonase_3', ($get('jumlah_karung_3') ?? 0) *  $netto / $state);
                                        $set('tonase_4', ($get('jumlah_karung_4') ?? 0) *  $netto / $state);
                                        $set('tonase_5', ($get('jumlah_karung_5') ?? 0) *  $netto / $state);
                                        $set('tonase_6', ($get('jumlah_karung_6') ?? 0) *  $netto / $state);
                                    }),
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
                                Card::make()
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
                                            ->label('Foto Jagung 1'),
                                        Select::make('x1_x10_1')
                                            ->label('X1-X10 1')
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
                                            ->label('Jumlah Karung 1')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    ($get('jumlah_karung_1') ?? 0) +
                                                        ($get('jumlah_karung_2') ?? 0) +
                                                        ($get('jumlah_karung_3') ?? 0) +
                                                        ($get('jumlah_karung_4') ?? 0) +
                                                        ($get('jumlah_karung_5') ?? 0) +
                                                        ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),

                                        TextInput::make('tonase_1')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase 1')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1), // Satu card per kolom

                                // Kualitas Jagung 2
                                Card::make()
                                    ->schema([
                                        Select::make('kualitas_jagung_2') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 2')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_2')
                                            ->image()
                                            ->label('Foto Jagung 2'),
                                        Select::make('x1_x10_2')
                                            ->label('X1-X10 2')
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
                                            ->label('Jumlah Karung 2')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    ($get('jumlah_karung_1') ?? 0) +
                                                        ($get('jumlah_karung_2') ?? 0) +
                                                        ($get('jumlah_karung_3') ?? 0) +
                                                        ($get('jumlah_karung_4') ?? 0) +
                                                        ($get('jumlah_karung_5') ?? 0) +
                                                        ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),

                                        TextInput::make('tonase_2')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase 2')
                                            ->readOnly(),

                                    ])
                                    ->columnSpan(1),

                                // Kualitas Jagung 3
                                Card::make()
                                    ->schema([
                                        Select::make('kualitas_jagung_3') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 3')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_3')
                                            ->image()
                                            ->label('Foto Jagung 3'),
                                        Select::make('x1_x10_3')
                                            ->label('X1-X10 3')
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
                                            ->label('Jumlah Karung 3')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    ($get('jumlah_karung_1') ?? 0) +
                                                        ($get('jumlah_karung_2') ?? 0) +
                                                        ($get('jumlah_karung_3') ?? 0) +
                                                        ($get('jumlah_karung_4') ?? 0) +
                                                        ($get('jumlah_karung_5') ?? 0) +
                                                        ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),

                                        TextInput::make('tonase_3')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase 3')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1),

                                // Kualitas Jagung 4
                                Card::make()
                                    ->schema([
                                        Select::make('kualitas_jagung_4') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 4')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_4')
                                            ->image()
                                            ->label('Foto Jagung 4'),
                                        Select::make('x1_x10_4')
                                            ->label('X1-X10 4')
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
                                            ->label('Jumlah Karung 4')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    ($get('jumlah_karung_1') ?? 0) +
                                                        ($get('jumlah_karung_2') ?? 0) +
                                                        ($get('jumlah_karung_3') ?? 0) +
                                                        ($get('jumlah_karung_4') ?? 0) +
                                                        ($get('jumlah_karung_5') ?? 0) +
                                                        ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),

                                        TextInput::make('tonase_4')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase 4')
                                            ->readOnly(),
                                    ])
                                    ->columnSpan(1),

                                // Kualitas Jagung 5
                                Card::make()
                                    ->schema([
                                        Select::make('kualitas_jagung_5') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 5')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_5')
                                            ->image()
                                            ->label('Foto Jagung 5'),
                                        Select::make('x1_x10_5')
                                            ->label('X1-X10 5')
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
                                            ->label('Jumlah Karung 5')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    ($get('jumlah_karung_1') ?? 0) +
                                                        ($get('jumlah_karung_2') ?? 0) +
                                                        ($get('jumlah_karung_3') ?? 0) +
                                                        ($get('jumlah_karung_4') ?? 0) +
                                                        ($get('jumlah_karung_5') ?? 0) +
                                                        ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),

                                        TextInput::make('tonase_5')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase 5')
                                            ->readOnly(),

                                    ])
                                    ->columnSpan(1),

                                // Kualitas Jagung 6
                                Card::make()
                                    ->schema([
                                        Select::make('kualitas_jagung_6') // Gantilah 'tipe' dengan nama field di database
                                            ->label('Kualitas Jagung 6')
                                            ->options([
                                                'JG Kering' => 'Jagung Kering',
                                                'JG Basah' => 'Jagung Basah',
                                                'JG Kurang Kering' => 'Jagung Kurang Kering',
                                            ])
                                            ->placeholder('Pilih Kualitas Jagung')
                                            ->native(false), // Mengunakan dropdown modern

                                        FileUpload::make('foto_jagung_6')
                                            ->image()
                                            ->label('Foto Jagung 6'),
                                        Select::make('x1_x10_6')
                                            ->label('X1-X10 6')
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
                                            ->label('Jumlah Karung 6')
                                            ->numeric()
                                            ->placeholder('Masukkan Jumlah Karung')
                                            ->reactive()
                                            ->afterStateUpdated(
                                                fn($state, $set, $get) =>
                                                $set(
                                                    'total_karung',
                                                    ($get('jumlah_karung_1') ?? 0) +
                                                        ($get('jumlah_karung_2') ?? 0) +
                                                        ($get('jumlah_karung_3') ?? 0) +
                                                        ($get('jumlah_karung_4') ?? 0) +
                                                        ($get('jumlah_karung_5') ?? 0) +
                                                        ($get('jumlah_karung_6') ?? 0)
                                                )
                                            ),
                                        TextInput::make('tonase_5')
                                            ->placeholder('Otomatis Terisi Tonase')
                                            ->label('Tonase 5')
                                            ->readOnly(),

                                    ])
                                    ->columnSpan(1),
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
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_sortiran')->label('No Sortiran'),
                TextColumn::make('pembelian.no_spb')->label('No SPB')
                    ->searchable(),
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
                    ->label('Foto Jagung 1'),
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
                    ->label('Foto Jagung 2'),
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
                    ->label('Foto Jagung 3'),
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
                    ->label('Foto Jagung 4'),
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
                    ->label('Foto Jagung 5'),
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
                    ->label('Foto Jagung 6'),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSortirans::route('/'),
            'create' => Pages\CreateSortiran::route('/create'),
            'edit' => Pages\EditSortiran::route('/{record}/edit'),
        ];
    }
}
