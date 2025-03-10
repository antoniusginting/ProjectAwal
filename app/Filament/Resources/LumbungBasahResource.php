<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Sortiran;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungBasah;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use App\Models\KapasitasLumbungBasah;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LumbungBasahResource\Pages;
use App\Filament\Resources\LumbungBasahResource\RelationManagers;

class LumbungBasahResource extends Resource
{
    protected static ?string $model = LumbungBasah::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    public static ?string $label = 'Daftar Lumbung Basah ';

    protected static ?string $navigationLabel = 'Lumbung Basah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Lumbung')
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No LB')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_spb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (LumbungBasah::max('id') ?? 0) + 1;
                                        return 'LB' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('total_netto')
                                    ->label('Total netto')
                                    ->readOnly()
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric()
                                    ->suffix(' kg'),
                                Select::make('no_lumbung_basah')
                                    ->label('No Lumbung Basah')
                                    ->placeholder('Pilih No Lumbung')
                                    ->options(KapasitasLumbungBasah::pluck('no_kapasitas_lumbung', 'id'))
                                    ->searchable() // Biar bisa cari
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn($record) => $record !== null)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                            $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                        $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                    }),
                                TextInput::make('jenis_jagung')
                                    ->label('Jenis Jagung')
                                    ->placeholder('Masukkan Jenis Jagung'),

                                TextInput::make('kapasitas_sisa')
                                    ->label('Kapasitas Sisa')
                                    ->placeholder('Pilih terlebih dahulu no lumbung basah')
                                    ->disabled(),

                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->placeholder(now()->format('d-m-Y')) // Tampilkan di input
                                    ->disabled(), // Tidak bisa diedit
                            ])->columns(2)
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                //Card No sortiran
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_1')
                                            ->label('No Sortiran 1')
                                            ->placeholder('Pilih No Sortiran 1')
                                            ->options(Sortiran::pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Ambil data sortiran berdasarkan ID yang dipilih
                                                $sortiran1 = Sortiran::with('pembelian')->find($state);
                                                // Set netto hanya untuk sortiran pertama
                                                $set('netto_1', $sortiran1?->pembelian?->netto ?? 'Tidak ada');

                                                $sortiran1 = Sortiran::find($state);
                                                $set('no_lumbung_1', $sortiran1?->no_lumbung ?? 'Tidak ada');

                                                // Langsung hitung total netto di sini
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),

                                        TextInput::make('netto_1')
                                            ->label('Netto Pembelian 1')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 1')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_1')
                                            ->label('No lumbung 1')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 1')
                                            ->disabled(),
                                    ])->columnSpan(1),
                                //Card No sortiran 2
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_2')
                                            ->label('No Sortiran 2')
                                            ->placeholder('Pilih No Sortiran 2')
                                            ->options(Sortiran::pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Ambil data sortiran berdasarkan ID yang dipilih
                                                $sortiran2 = Sortiran::with('pembelian')->find($state);
                                                // Set netto hanya untuk sortiran kedua
                                                $set('netto_2', $sortiran2?->pembelian?->netto ?? 'Tidak ada');

                                                $sortiran2 = Sortiran::find($state);
                                                $set('no_lumbung_2', $sortiran2?->no_lumbung ?? 'Tidak ada');

                                                // Langsung hitung total netto di sini
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),
                                        TextInput::make('netto_2')
                                            ->label('Netto Pembelian 2')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 2')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_2')
                                            ->label('No lumbung 2')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 2')
                                            ->disabled(),

                                    ])->columnSpan(1),
                                //No sortiran 3
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_3')
                                            ->label('No Sortiran 3')
                                            ->placeholder('Pilih No Sortiran 3')
                                            ->options(Sortiran::pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Ambil data sortiran berdasarkan ID yang dipilih
                                                $sortiran3 = Sortiran::with('pembelian')->find($state);
                                                // Set netto hanya untuk sortiran kedua
                                                $set('netto_3', $sortiran3?->pembelian?->netto ?? 'Tidak ada');

                                                $sortiran3 = Sortiran::find($state);
                                                $set('no_lumbung_3', $sortiran3?->no_lumbung ?? 'Tidak ada');

                                                // Langsung hitung total netto di sini
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),
                                        TextInput::make('netto_3')
                                            ->label('Netto Pembelian 3')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 3')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_3')
                                            ->label('No lumbung 3')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 3')
                                            ->disabled(),
                                    ])->columnSpan(1),
                                //No Sortiran 4
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_4')
                                            ->label('No Sortiran 4')
                                            ->placeholder('Pilih No Sortiran 4')
                                            ->options(Sortiran::pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Ambil data sortiran berdasarkan ID yang dipilih
                                                $sortiran4 = Sortiran::with('pembelian')->find($state);
                                                // Set netto hanya untuk sortiran kedua
                                                $set('netto_4', $sortiran4?->pembelian?->netto ?? 'Tidak ada');

                                                $sortiran4 = Sortiran::find($state);
                                                $set('no_lumbung_4', $sortiran4?->no_lumbung ?? 'Tidak ada');

                                                // Langsung hitung total netto di sini
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),
                                        TextInput::make('netto_4')
                                            ->label('Netto Pembelian 4')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 4')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_4')
                                            ->label('No lumbung 4')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 4')
                                            ->disabled(),

                                    ])->columnSpan(1),
                                //No sortiran 5
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_5')
                                            ->label('No Sortiran 5')
                                            ->placeholder('Pilih No Sortiran 5')
                                            ->options(Sortiran::pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Ambil data sortiran berdasarkan ID yang dipilih
                                                $sortiran5 = Sortiran::with('pembelian')->find($state);
                                                // Set netto hanya untuk sortiran kedua
                                                $set('netto_5', $sortiran5?->pembelian?->netto ?? 'Tidak ada');

                                                $sortiran5 = Sortiran::find($state);
                                                $set('no_lumbung_5', $sortiran5?->no_lumbung ?? 'Tidak ada');

                                                // Langsung hitung total netto di sini
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),
                                        TextInput::make('netto_5')
                                            ->label('Netto Pembelian 5')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 5')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_5')
                                            ->label('No lumbung 5')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 5')
                                            ->disabled(),
                                    ])->columnSpan(1),
                                //No Sortiran 6
                                Card::make()
                                    ->schema([
                                        Select::make('id_sortiran_6')
                                            ->label('No Sortiran 6')
                                            ->placeholder('Pilih No Sortiran 6')
                                            ->options(Sortiran::pluck('no_sortiran', 'id')->toArray())
                                            ->searchable()
                                            ->reactive()
                                            ->disabled(fn($record) => $record !== null)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Ambil data sortiran berdasarkan ID yang dipilih
                                                $sortiran6 = Sortiran::with('pembelian')->find($state);
                                                // Set netto hanya untuk sortiran kedua
                                                $set('netto_6', $sortiran6?->pembelian?->netto ?? 'Tidak ada');

                                                $sortiran6 = Sortiran::find($state);
                                                $set('no_lumbung_6', $sortiran6?->no_lumbung ?? 'Tidak ada');

                                                // Langsung hitung total netto di sini
                                                $totalNetto =
                                                    ($get('netto_1') ?? 0) +
                                                    ($get('netto_2') ?? 0) +
                                                    ($get('netto_3') ?? 0) +
                                                    ($get('netto_4') ?? 0) +
                                                    ($get('netto_5') ?? 0) +
                                                    ($get('netto_6') ?? 0);

                                                $set('total_netto', $totalNetto);
                                            }),
                                        TextInput::make('netto_6')
                                            ->label('Netto Pembelian 6')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 6')
                                            ->disabled(),
                                        TextInput::make('no_lumbung_6')
                                            ->label('No lumbung 6')
                                            ->placeholder('Pilih terlebih dahulu no sortiran 6')
                                            ->disabled(),
                                    ])->columnSpan(1),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_lb')->label('No LB'),
                TextColumn::make('no_lumbung_basah')->label('No Lumbung Basah')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('jenis_jagung')->label('Jenis Jagung')
                    ->searchable()
                    ->alignCenter(),

                //Jagung 1
                TextColumn::make('sortiran_1')
                    ->label('Sortiran 1'),

                //Jagung 2
                TextColumn::make('sortiran_2')
                    ->label('Sortiran 2'),

                //Jagung 3
                TextColumn::make('sortiran_3')
                    ->label('Sortiran 3'),

                //Jagung 4
                TextColumn::make('sortiran_4')
                    ->label('Sortiran 4'),

                //Jagung 5
                TextColumn::make('sortiran_5')
                    ->label('Sortiran 5'),

                //Jagung 6
                TextColumn::make('sortiran_6')
                    ->label('Sortiran 6'),

                TextColumn::make('total_netto')
                    ->label('Total Netto'),
                TextColumn::make('status')
                    ->label('Status'),
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
            'index' => Pages\ListLumbungBasahs::route('/'),
            'create' => Pages\CreateLumbungBasah::route('/create'),
            'edit' => Pages\EditLumbungBasah::route('/{record}/edit'),
        ];
    }
}
