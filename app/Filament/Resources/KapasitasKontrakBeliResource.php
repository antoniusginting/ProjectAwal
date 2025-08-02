<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use App\Models\KapasitasKontrakBeli;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\KapasitasKontrakBeliResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KapasitasKontrakBeliResource\RelationManagers;

class KapasitasKontrakBeliResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = KapasitasKontrakBeli::class;
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Kapasitas Kontrak Beli';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('stok')
                            ->label('Nilai Kontrak')
                            ->placeholder('Masukkan Nilai Kontrak')
                            ->live(debounce: 200)
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)),
                        Select::make('nama')
                            ->native(false)
                            ->searchable()
                            ->required()
                            ->options([
                                'MAKASSAR' => 'MAKASSAR',
                                'GORONTALO' => 'GORONTALO',
                            ])
                            ->label('Kontrak')
                            ->placeholder('Pilih Kontrak')
                            ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                return $record !== null;
                            })
                            ->live(),
                        TextInput::make('harga')
                            ->label('Harga')
                            ->placeholder('Masukkan harga')
                            ->live(debounce: 200)
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)),
                        Toggle::make('status')
                            ->label('Status')
                            ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                            ->default(false) // Default false (buka)
                            ->onColor('danger') // Warna merah saat true (tutup)
                            ->offColor('success'), // Warna hijau saat false (buka)
                        Select::make('supplier')
                            ->label('Supplier')
                            ->options(Supplier::pluck('nama_supplier', 'nama_supplier')) // ganti field sesuai dengan tabelmu
                            ->searchable()
                            ->required(),
                    ])->columns(4)
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
                        Carbon::setLocale('id');
                        return Carbon::parse($state)
                            ->locale('id')
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Tutup' : 'Buka';
                    })
                    ->color(function ($state) {
                        return $state ? 'danger' : 'success';
                    }),
                TextColumn::make('stok')->label('Nilai Kontrak')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('harga')->label('Harga')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama')
                    ->label('Jagung')
                    ->searchable(),
                TextColumn::make('supplier')
                    ->label('Supplier')
                    ->searchable(),
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-kapasitas-kontrak-beli')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-kapasitas-kontrak-beli", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListKapasitasKontrakBelis::route('/'),
            'create' => Pages\CreateKapasitasKontrakBeli::route('/create'),
            'edit' => Pages\EditKapasitasKontrakBeli::route('/{record}/edit'),
            'view-kapasitas-kontrak-beli' => Pages\ViewKapasitasKontrakBeli::route('/{record}/view-kapasitas-kontrak-beli'),

            // Tambah 2 route baru
            'gorontalo' => Pages\GorontaloGabungan::route('/gorontalo'),
            'makassar' => Pages\MakassarGabungan::route('/makassar'),
        ];
    }
}
