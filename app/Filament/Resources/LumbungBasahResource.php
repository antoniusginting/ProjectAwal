<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungBasah;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LumbungBasahResource\Pages;
use App\Filament\Resources\LumbungBasahResource\RelationManagers;
use App\Models\KapasitasLumbungBasah;
use App\Models\Sortiran;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;

class LumbungBasahResource extends Resource
{
    protected static ?string $model = LumbungBasah::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->readOnly()
                                    ->placeholder('Otomatis Terhitung')
                                    ->numeric(),
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
                                Select::make('jenis_jagung')
                                    ->label('Jenis Jagung')
                                    ->options([
                                        'JGK' => 'JGK',
                                    ])
                                    ->placeholder('Pilih Jenis Jagung')
                                    ->native(false) // Mengunakan dropdown modern
                                    ->required(), // Opsional: Atur default value,

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
                        Select::make('id_sortiran')
                            ->label('No Sortiran 1')
                            ->placeholder('Pilih No Sortiran 1')
                            ->options(Sortiran::pluck('no_sortiran', 'id'))
                            ->searchable() // Biar bisa cari
                            ->required()
                            ->reactive()
                            ->disabled(fn($record) => $record !== null)
                            ->afterStateHydrated(function ($state, callable $set) {
                                if ($state) {
                                    $sortiran = Sortiran::find($state);
                                    $set('netto', $sortiran?->netto ?? 'Tidak ada');
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                $sortiran = Sortiran::find($state);
                                $set('netto', $sortiran?->netto ?? ' Tidak ada');
                            }),
                            TextInput::make('netto')
                                    ->label('Netto')
                                    ->placeholder('Pilih terlebih dahulu no sortiran')
                                    ->disabled(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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
            'index' => Pages\ListLumbungBasahs::route('/'),
            'create' => Pages\CreateLumbungBasah::route('/create'),
            'edit' => Pages\EditLumbungBasah::route('/{record}/edit'),
        ];
    }
}
