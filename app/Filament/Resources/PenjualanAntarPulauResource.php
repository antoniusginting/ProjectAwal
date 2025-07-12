<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PenjualanAntarPulau;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PenjualanAntarPulauResource\Pages;
use App\Filament\Resources\PenjualanAntarPulauResource\RelationManagers;
use Filament\Forms\Components\Grid;

class PenjualanAntarPulauResource extends Resource
{
    protected static ?string $model = PenjualanAntarPulau::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 2;
    public static ?string $label = 'Daftar Penjualan Antar Pulau ';
    protected static ?string $navigationLabel = 'Penjualan Antar Pulau';

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
                                            return $record->kode;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (PenjualanAntarPulau::max('id') ?? 0) + 1;
                                        return 'CJ' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('created_at')
                                    ->label('Tanggal')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                            ])->columns(2)->collapsed(),

                        Card::make()
                            ->schema([
                                TextInput::make('kode_segel')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan kode Segel'),
                                TextInput::make('nama_barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Barang'),
                                // Select::make('id_supplier')
                                //     ->label('Supplier')
                                //     ->placeholder('Pilih Supplier')
                                //     ->options(Supplier::pluck('nama_supplier', 'id')) // Ambil daftar mobil
                                //     ->searchable(), // Biar bisa cari
                                // Select::make('silo_id')
                                //     ->label('Supplier')
                                //     ->options(function () {
                                //         return Silo::whereNotIn('nama', [
                                //             'SILO STAFFEL A',
                                //             'SILO STAFFEL B',
                                //             'SILO 2500',
                                //             'SILO 1800'
                                //         ])
                                //             ->get()
                                //             ->mapWithKeys(function ($item) {
                                //                 return [
                                //                     $item->id =>  $item->nama
                                //                 ];
                                //             });
                                //     })
                                //     ->searchable()
                                //     ->preload()
                                //     ->nullable()
                                //     ->placeholder('Pilih')
                                //     ->reactive()
                                //     ->afterStateUpdated(function ($state, callable $set) {
                                //         if ($state) {
                                //             // Ambil data silo berdasarkan ID yang dipilih
                                //             $silo = Silo::find($state);
                                //             if ($silo) {
                                //                 $set('status_silo', $silo->nama); // Set status sesuai nama silo
                                //             }
                                //         } else {
                                //             $set('status_silo', null);
                                //         }
                                //     }),
                                Select::make('luar_pulau_id')
                                    ->label('Supplier')
                                    ->native(false)
                                    ->required()
                                    ->options(function () {
                                        return \App\Models\LuarPulau::query()
                                            ->where('status', false)
                                            ->pluck('nama', 'id')
                                            ->toArray();
                                    })
                                    ->placeholder('Pilih Supplier')
                                    ->searchable()
                                    ->live(),
                                Grid::make()
                                    ->schema([
                                        TextInput::make('netto')
                                            ->label('Netto')
                                            ->placeholder('Masukkan Netto')
                                            ->numeric(),
                                        TextInput::make('no_container')
                                            ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                            ->placeholder('Masukkan No Container'),
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
                    ->label('No SPB')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),
                TextColumn::make('kode_segel')
                    ->label('Kode Segel')
                    ->searchable(),
                TextColumn::make('luarPulau.nama')->label('Supplier')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('no_container')
                    ->searchable(),
                TextColumn::make('nama_ekspedisi')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
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
            'index' => Pages\ListPenjualanAntarPulaus::route('/'),
            'create' => Pages\CreatePenjualanAntarPulau::route('/create'),
            'edit' => Pages\EditPenjualanAntarPulau::route('/{record}/edit'),
        ];
    }
}
