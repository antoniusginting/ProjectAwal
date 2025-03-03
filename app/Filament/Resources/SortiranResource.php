<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SortiranResource\Pages;
use App\Filament\Resources\SortiranResource\RelationManagers;
use App\Models\Sortiran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SortiranResource extends Resource
{
    protected static ?string $model = Sortiran::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')
                    ->dateTime('d-m-Y'),
                TextColumn::make('pembelian.no_spb')->label('No SPB')
                    ->searchable(),
                TextColumn::make('lumbung'),
                TextColumn::make('kualitas_jagung_1')
                    ->label('Kualitas Jagung 1'),
                TextColumn::make('foto_jagung_1')
                    ->label('Foto Jagung 1'),
                TextColumn::make('x1_x10_1')
                    ->label('X1 - X10'),
                TextColumn::make('jumlah_karung_2')
                    ->label('Jumlah Karung 2'),
                TextColumn::make('kadar_air_2')
                    ->label('Kadar Air 2'),
                TextColumn::make('kualitas_jagung_2')
                    ->label('Kualitas Jagung 2'),
                TextColumn::make('foto_jagung_2')
                    ->label('Foto Jagung 2'),
                TextColumn::make('x1_x10_2')
                    ->label('X1 - X10'),
                TextColumn::make('jumlah_karung_2')
                    ->label('Jumlah Karung 2'),
                TextColumn::make('kadar_air_2')
                    ->label('Kadar Air 2'),
                TextColumn::make('kualitas_jagung_3')
                    ->label('Kualitas Jagung 3'),
                TextColumn::make('foto_jagung_3')
                    ->label('Foto Jagung 3'),
                TextColumn::make('x1_x10_3')
                    ->label('X1 - X10'),
                TextColumn::make('jumlah_karung_3')
                    ->label('Jumlah Karung 3'),
                TextColumn::make('kadar_air_3')
                    ->label('Kadar Air 3'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
