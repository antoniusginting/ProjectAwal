<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KontrakResource\Pages;
use App\Filament\Resources\KontrakResource\RelationManagers;
use App\Models\Kontrak;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KontrakResource extends Resource
{
    protected static ?string $model = Kontrak::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Kontrak';
    protected static ?string $navigationGroup = 'Kontrak';
    public static ?string $label = 'Daftar Kontrak ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama')
                            ->placeholder('Masukkan nama kontrak'),
                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->placeholder('Masukkan NPWP')
                            ->rule('digits:16') // Pastikan harus tepat 16 digit
                            ->mask('9999999999999999') // Tambahkan format input
                            ->numeric(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->state(
                        fn($record, $livewire) =>
                        $livewire->getTableRecords()->search($record) + 1
                    ),
                TextColumn::make('nama')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable(),

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
            'index' => Pages\ListKontraks::route('/'),
            'create' => Pages\CreateKontrak::route('/create'),
            'edit' => Pages\EditKontrak::route('/{record}/edit'),
        ];
    }
}
