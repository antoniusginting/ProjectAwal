<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\Action;
use App\Models\KapasitasLumbungBasah;
use Filament\Notifications\Collection;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KapasitasLumbungBasahResource\Pages;
use App\Filament\Resources\KapasitasLumbungBasahResource\RelationManagers;

class KapasitasLumbungBasahResource extends Resource
{

    protected static ?string $model = KapasitasLumbungBasah::class;
    protected static ?string $navigationGroup = 'Kapasitas';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Kapasitas Lumbung Basah';

    public static ?string $label = 'Daftar Kapasitas Lumbung Basah ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('no_kapasitas_lumbung')
                            ->label('No Lumbung')
                            ->columnSpan(2)
                            ->placeholder('Masukkan No Kapasitas Lumbung'),
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->placeholder('Masukkan Jumlah Kapasitas Total')
                            ->live() // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                        TextInput::make('kapasitas_sisa')
                            ->label('Kapasitas Sisa')
                            ->placeholder('Masukkan Jumlah Kapasitas Sisa')
                            ->live() // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')->label('No'),
                TextColumn::make('no_kapasitas_lumbung')
                    ->label('No Lumbung'),
                TextColumn::make('kapasitas_total')
                    ->label('Kapasitas Total')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Tambah pemisah ribuan,
                TextColumn::make('kapasitas_sisa')
                    ->label('Kapasitas Sisa')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
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
                    ->action(fn($record) => $record->update([
                        'kapasitas_sisa' => $record->kapasitas_total
                    ]))
                    ->requiresConfirmation()
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('reset_kapasitas')
                    ->label('Reset Kapasitas')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'kapasitas_sisa' => $record->kapasitas_total,
                            ]);
                        }
                    })
                    ->requiresConfirmation()
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path'),
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
