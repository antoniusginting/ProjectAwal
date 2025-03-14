<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Dryer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungBasah;
use App\Models\KapasitasDryer;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\DryerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DryerResource\RelationManagers;

class DryerResource extends Resource
{
    public static function getNavigationSort(): int
    {
        return 3; // Ini akan muncul di atas
    }
    protected static ?string $model = Dryer::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Dryer';

    public static ?string $label = 'Daftar Dryer ';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dryer')
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No Dryer')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_lb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (Dryer::max('id') ?? 0) + 1;
                                        return 'D' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                Select::make('id_kapasitas_dryer')
                                    ->label('Nama Dryer')
                                    ->placeholder('Pilih nama Dryer')
                                    ->options(KapasitasDryer::pluck('nama_kapasitas_dryer', 'id'))
                                    ->searchable() // Biar bisa cari
                                    ->required()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $kapasitasdryer = KapasitasDryer::find($state);
                                            $set('kapasitas_total', $kapasitasdryer?->kapasitas_total ?? 'Tidak ada');
                                            $formattedSisa = number_format($kapasitasdryer?->kapasitas_total ?? 0, 0, ',', '.');
                                            $set('kapasitas_total', $formattedSisa);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $kapasitasdryer = KapasitasDryer::find($state);
                                        $set('kapasitas_total', $kapasitasdryer?->kapasitas_total ?? 'Tidak ada');
                                        $formattedSisa = number_format($kapasitasdryer?->kapasitas_total ?? 0, 0, ',', '.');
                                        $set('kapasitas_total', $formattedSisa);
                                    }),
                                Select::make('lumbung_tujuan')
                                    ->label('Nama lumbung kering')
                                    ->options([
                                        'A' => 'A',
                                        'B' => 'B',
                                        'C' => 'C',
                                        'D' => 'D',
                                        'E' => 'E',
                                        'F' => 'F',
                                        'G' => 'G',
                                        'H' => 'H',
                                        'I' => 'I',
                                    ])
                                    ->placeholder('Pilih nama lumbung kering')
                                    ->native(false),
                                TextInput::make('kapasitas_total')
                                    ->label('Kapasitas Total')
                                    ->placeholder('Pilih terlebih dahulu nama Dryer')
                                    ->disabled(),
                                TextInput::make('created_at')
                                    ->label('Tanggal/Jam')
                                    ->placeholder(now()->format('d-m-Y H:i:s')) // Tampilkan di input
                                    ->disabled(), // Tidak bisa diedit
                                TextInput::make('operator')
                                    ->label('Operator Dryer')
                                    ->required()
                                    ->placeholder('Masukkan Operator Dryer'),
                                TextInput::make('rencana_kadar')
                                    ->label('Rencana Kadar')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('Masukkan rencana kadar'),
                                TextInput::make('hasil_kadar')
                                    ->label('Hasil Kadar')
                                    ->numeric()
                                    ->placeholder('Masukkan hasil kadar'),
                                TextInput::make('jenis_jagung')
                                    ->label('Jenis Jagung')
                                    ->required()
                                    ->placeholder('Masukkan jenis jagung'),
                                TextInput::make('total_netto')
                                    ->label('Total Netto LB')
                                    ->placeholder('Otomatis terhitung')
                                    ->readOnly(),
                            ])->columns(2)
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                //Card Lumbung 1
                                Card::make('Lumbung ke-1')
                                    ->schema([
                                        Select::make('id_lumbung_1')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih No Lumbung 1')
                                            ->options(LumbungBasah::latest()->pluck('no_lb', 'id')->toArray())
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            //->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_1', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_1', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_1', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    $set('total_netto_1', null);
                                                    $set('no_lumbung_1', null);
                                                    $set('jenis_jagung_1', null);
                                                } else {
                                                    $selectedLumbungs = [
                                                        $get('id_lumbung_1'),
                                                        $get('id_lumbung_2'),
                                                        $get('id_lumbung_3'),
                                                        $get('id_lumbung_4'),
                                                    ];
                                                    $occurrences = array_count_values(array_filter($selectedLumbungs));
                                                    if ($occurrences[$state] > 1) {
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No lumbung tidak boleh sama.')
                                                            ->danger()
                                                            ->send();

                                                        $set('id_lumbung_1', null);
                                                        return;
                                                    }

                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_1', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_1', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_1', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }

                                                $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                                    + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                                $set('total_netto', $totalNetto); // Simpan sebagai angka
                                            }),
                                        TextInput::make('total_netto_1')
                                            ->label('Total Netto')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 1')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_1')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 1')
                                            ->disabled(),

                                        TextInput::make('jenis_jagung_1')
                                            ->label('Jenis Jagung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 1')
                                            ->disabled(),
                                    ])->columnSpan(1)
                                    ->collapsible(),
                                //Card Lumbung 2
                                Card::make('Lumbung ke-2')
                                    ->schema([
                                        Select::make('id_lumbung_2')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih No Lumbung 2')
                                            ->options(LumbungBasah::latest()->pluck('no_lb', 'id')->toArray()) // Urutan data dari terbaru ke lama
                                            ->searchable()
                                            ->reactive()
                                            //->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_2', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_2', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_2', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    $set('total_netto_2', null);
                                                    $set('no_lumbung_2', null);
                                                    $set('jenis_jagung_2', null);
                                                } else {
                                                    $selectedLumbungs = [
                                                        $get('id_lumbung_1'),
                                                        $get('id_lumbung_2'),
                                                        $get('id_lumbung_3'),
                                                        $get('id_lumbung_4'),
                                                    ];
                                                    $occurrences = array_count_values(array_filter($selectedLumbungs));
                                                    if ($occurrences[$state] > 1) {
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No lumbung tidak boleh sama.')
                                                            ->danger()
                                                            ->send();

                                                        $set('id_lumbung_2', null);
                                                        return;
                                                    }

                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_2', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_2', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_2', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }

                                                $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                                    + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                                $set('total_netto', $totalNetto); // Simpan sebagai angka
                                            }),
                                        TextInput::make('total_netto_2')
                                            ->label('Total Netto')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 2')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_2')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 2')
                                            ->disabled(),

                                        TextInput::make('jenis_jagung_2')
                                            ->label('Jenis Jagung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 2')
                                            ->disabled(),


                                    ])->columnSpan(1)->collapsible(),
                                //Card Lumbung 3
                                Card::make('Lumbung ke-3')
                                    ->schema([
                                        Select::make('id_lumbung_3')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih No Lumbung 3')
                                            ->options(LumbungBasah::latest()->pluck('no_lb', 'id')->toArray()) // Urutan data dari terbaru ke lama
                                            ->searchable()
                                            ->reactive()
                                            //->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_3', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_3', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_3', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    $set('total_netto_3', null);
                                                    $set('no_lumbung_3', null);
                                                    $set('jenis_jagung_3', null);
                                                } else {
                                                    $selectedLumbungs = [
                                                        $get('id_lumbung_1'),
                                                        $get('id_lumbung_2'),
                                                        $get('id_lumbung_3'),
                                                        $get('id_lumbung_4'),
                                                    ];
                                                    $occurrences = array_count_values(array_filter($selectedLumbungs));
                                                    if ($occurrences[$state] > 1) {
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No lumbung tidak boleh sama.')
                                                            ->danger()
                                                            ->send();

                                                        $set('id_lumbung_3', null);
                                                        return;
                                                    }

                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_3', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_3', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_3', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }

                                                $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                                    + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                                $set('total_netto', $totalNetto); // Simpan sebagai angka
                                            }),
                                        TextInput::make('total_netto_3')
                                            ->label('Total Netto')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 3')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_3')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 3')
                                            ->disabled(),

                                        TextInput::make('jenis_jagung_3')
                                            ->label('Jenis Jagung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 3')
                                            ->disabled(),


                                    ])->columnSpan(1)->collapsed(),
                                //Card Lumbung 4
                                Card::make('Lumbung ke-4')
                                    ->schema([
                                        Select::make('id_lumbung_4')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih No Lumbung 4')
                                            ->options(LumbungBasah::latest()->pluck('no_lb', 'id')->toArray()) // Urutan data dari terbaru ke lama
                                            ->searchable()
                                            ->reactive()
                                            //->disabled(fn($record) => $record !== null)
                                            ->afterStateHydrated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_4', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_4', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_4', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($state)) {
                                                    $set('total_netto_4', null);
                                                    $set('no_lumbung_4', null);
                                                    $set('jenis_jagung_4', null);
                                                } else {
                                                    $selectedLumbungs = [
                                                        $get('id_lumbung_1'),
                                                        $get('id_lumbung_2'),
                                                        $get('id_lumbung_3'),
                                                        $get('id_lumbung_4'),
                                                    ];
                                                    $occurrences = array_count_values(array_filter($selectedLumbungs));
                                                    if ($occurrences[$state] > 1) {
                                                        Notification::make()
                                                            ->title('Peringatan!')
                                                            ->body('No lumbung tidak boleh sama.')
                                                            ->danger()
                                                            ->send();

                                                        $set('id_lumbung_4', null);
                                                        return;
                                                    }

                                                    $lumbung = LumbungBasah::find($state);
                                                    $set('total_netto_4', $lumbung?->total_netto ?? 0); // Simpan sebagai angka
                                                    $set('no_lumbung_4', $lumbung?->no_lumbung_basah ?? 'Tidak ada');
                                                    $set('jenis_jagung_4', $lumbung?->jenis_jagung ?? 'Tidak ada');
                                                }

                                                $totalNetto = (float) ($get('total_netto_1') ?? 0) + (float) ($get('total_netto_2') ?? 0)
                                                    + (float) ($get('total_netto_3') ?? 0) + (float) ($get('total_netto_4') ?? 0);
                                                $set('total_netto', $totalNetto); // Simpan sebagai angka
                                            }),
                                        TextInput::make('total_netto_4')
                                            ->label('Total Netto')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 4')
                                            ->disabled(),

                                        TextInput::make('no_lumbung_4')
                                            ->label('No Lumbung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 4')
                                            ->disabled(),

                                        TextInput::make('jenis_jagung_4')
                                            ->label('Jenis Jagung')
                                            ->placeholder('Pilih terlebih dahulu No Lumbung 4')
                                            ->disabled(),


                                    ])->columnSpan(1)->collapsed(),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('no_dryer')
                    ->label('No Dryer')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('kapasitasdryer.nama_kapasitas_dryer')
                    ->label('Nama Dryer')
                    ->alignCenter(),
                TextColumn::make('lumbung_tujuan')
                    ->label('Lumbung Tujuan')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('jenis_jagung')
                    ->label('Jenis Jagung')
                    ->searchable()
                    ->alignCenter(),

                //Jagung 1
                TextColumn::make('lumbung1.no_lb')
                    ->label('No Lumbung 1'),
                //Jagung 2
                TextColumn::make('lumbung2.no_lb')
                    ->label('No Lumbung 2'),
                //Jagung 2
                TextColumn::make('lumbung3.no_lb')
                    ->label('No Lumbung 3'),
                //Jagung 2
                TextColumn::make('lumbung4.no_lb')
                    ->label('No Lumbung 4'),
                TextColumn::make('total_netto')
                    ->label('Total Netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListDryers::route('/'),
            'create' => Pages\CreateDryer::route('/create'),
            'edit' => Pages\EditDryer::route('/{record}/edit'),
        ];
    }
}
