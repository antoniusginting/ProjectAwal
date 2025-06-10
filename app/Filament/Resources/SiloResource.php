<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiloResource\Pages;
use App\Filament\Resources\SiloResource\RelationManagers;
use App\Models\Silo;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\text;

class SiloResource extends Resource
{
    protected static ?string $model = Silo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Card::make()
                    ->schema([
                        TextInput::make('stok')
                            ->numeric()
                            ->label('Stok Awal')
                            ->placeholder('Masukkan stok awal'),
                        Select::make('silo')
                            ->native(false)
                            ->options([
                                'SILO BESAR' => 'SILO BESAR',
                                'SILO STAFFEL A' => 'SILO STAFFEL A',
                                'SILO STAFFEL B' => 'SILO STAFFEL B',
                            ])
                            ->label('SILO')
                            ->placeholder('Pilih silo'),
                        Card::make('STOK BESAR')
                            ->schema([
                                TextInput::make('laporan_lumbung_sebelumnya')
                                    ->label('laporan lumbung sebelumnya'),
                                TextInput::make('laporan_penjualan')
                                    ->label('laporan penjualan'),
                            ])->columnSpan(1),
                        Card::make('PENJUALAN')
                            ->schema([
                                TextInput::make('laporan_penjualan_sebelumnya')
                                    ->label('laporan penjualan sebelumnya'),
                                TextInput::make('laporan_penjualan')
                                    ->label('laporan penjualan'),
                            ])->columnSpan(1)
                    ])->columns(2)
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
            'index' => Pages\ListSilos::route('/'),
            'create' => Pages\CreateSilo::route('/create'),
            'edit' => Pages\EditSilo::route('/{record}/edit'),
        ];
    }
}
