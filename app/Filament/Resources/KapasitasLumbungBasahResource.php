<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KapasitasLumbungBasahResource\Pages;
use App\Filament\Resources\KapasitasLumbungBasahResource\RelationManagers;
use App\Models\KapasitasLumbungBasah;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KapasitasLumbungBasahResource extends Resource
{
    protected static ?string $model = KapasitasLumbungBasah::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Kapasitas Lumbung Basah';

    public static ?string $label = 'Daftar Kapasitas Lumbung Basah ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('no_kapasitas_lumbung')
                ->label('No Lumbung')
                ->columnSpan(2)
                ->placeholder('Masukkan No Kapasitas Lumbung'),
                TextInput::make('kapasitas_total')
                ->label('Kapasitas Total')
                ->placeholder('Masukkan Jumlah Kapasitas Total'),
                TextInput::make('kapasitas_sisa')
                ->label('Kapasitas Sisa')
                ->placeholder('Masukkan Jumlah Kapasitas Sisa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')->label('No'),
                TextColumn::make('no_kapasitas_lumbung')->label('No Lumbung'),
                TextColumn::make('kapasitas_total')->label('Kapasitas Total'),
                TextColumn::make('kapasitas_sisa')->label('Kapasitas Sisa'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
                Action::make('reset_kapasitas')
                ->label('Reset')
                ->action(fn ($record) => $record->update([
                    'kapasitas_sisa' => $record->kapasitas_total
                ]))
                ->requiresConfirmation()
                ->color('warning')
                ->icon('heroicon-o-arrow-path'),
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
            'index' => Pages\ListKapasitasLumbungBasahs::route('/'),
            'create' => Pages\CreateKapasitasLumbungBasah::route('/create'),
            'edit' => Pages\EditKapasitasLumbungBasah::route('/{record}/edit'),
        ];
    }
}
