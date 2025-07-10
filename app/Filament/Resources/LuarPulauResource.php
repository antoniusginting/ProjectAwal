<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kontrak;
use Filament\Forms\Form;
use App\Models\LuarPulau;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\LuarPulauResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LuarPulauResource\RelationManagers;

class LuarPulauResource extends Resource
{
    protected static ?string $model = LuarPulau::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Kontrak';
    protected static ?string $navigationGroup = 'Kapasitas';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('stok')
                            ->label('Stok Awal')
                            ->placeholder('Masukkan stok awal')
                            ->live() // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                        Select::make('nama')
                            ->native(false)
                            ->required()
                            ->options(function () {
                                return \App\Models\Kontrak::query()
                                    ->pluck('nama', 'nama')
                                    ->toArray();
                            })
                            ->label('STOK')
                            ->placeholder('Pilih Stok')
                            ->disabled(function (callable $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                // Disable saat edit, misal jika $record ada berarti edit
                                return $record !== null;
                            })
                            ->live(),
                        Toggle::make('status')
                            ->label('Status')
                            ->helperText('Aktifkan untuk menutup, nonaktifkan untuk membuka')
                            ->default(false) // Default false (buka)
                            ->onColor('danger') // Warna merah saat true (tutup)
                            ->offColor('success'), // Warna hijau saat false (buka)
                    ])->columns(3)
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
                TextColumn::make('stok')->label('Stok Awal')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-luar-pulau')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-luar-pulau", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
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
            'index' => Pages\ListLuarPulaus::route('/'),
            'create' => Pages\CreateLuarPulau::route('/create'),
            'edit' => Pages\EditLuarPulau::route('/{record}/edit'),
            'view-luar-pulau' => Pages\ViewLuarPulau::route('/{record}/view-luar-pulau'),
        ];
    }
}
