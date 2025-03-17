<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupplierResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SupplierResource\RelationManagers;

class SupplierResource extends Resource
{
    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
    public static function getNavigationSort(): int
    {
        return 7; // Ini akan muncul di atas
    }
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Supplier';


    public static ?string $label = 'Daftar Supplier ';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Card::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('nama_supplier')
                                ->required()
                                ->placeholder('Masukkan Nama Supplier'),
        
                            Select::make('jenis_supplier')
                                ->label('Jenis Supplier')
                                ->options([
                                    'Bonar Jaya' => 'Bonar Jaya',
                                    'Simpang 2' => 'Simpang 2',
                                    'Agen Purchasing' => 'Agen Purchasing',
                                ])
                                ->placeholder('Pilih Jenis Supplier')
                                ->native(false)
                                ->required(),
        
                            TextInput::make('no_ktp')
                                ->label('Nomor KTP')
                                ->required()
                                ->numeric()
                                ->placeholder('Masukkan nomor KTP'),
        
                            TextInput::make('npwp')
                                ->label('NPWP')
                                ->numeric()
                                ->placeholder('Masukkan NPWP'),
        
                            TextInput::make('no_rek')
                                ->label('Nomor rekening')
                                ->required()
                                ->numeric()
                                ->placeholder('Masukkan nomor rekening'),
        
                            Select::make('nama_bank')
                                ->label('Nama Bank')
                                ->options([
                                    'BRI' => 'BRI',
                                    'BCA' => 'BCA',
                                    'Mandiri' => 'Mandiri',
                                ])
                                ->placeholder('Pilih nama bank')
                                ->native(false)
                                ->required(),
        
                            TextInput::make('atas_nama_bank')
                                ->label('Atas nama bank')
                                ->required()
                                ->placeholder('Masukkan atas nama bank'),
                        ])->columns(2) // 2 kolom di layar besar
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->columns([
                // Kalau mau buat border di tabel ->extraAttributes(['style' => 'border-right: 1px solid #ddd;'])
                TextColumn::make('id')
                    ->label('No'),
                TextColumn::make('nama_supplier')
                    ->label('Nama supplier')
                    ->searchable(),
                TextColumn::make('jenis_supplier')
                    ->label('Jenis supplier')
                    ->searchable(),
                TextColumn::make('no_ktp')
                    ->label('No KTP')
                    ->searchable(),
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable(),
                TextColumn::make('no_rek')
                    ->label('No Rekening')
                    ->searchable(),
                TextColumn::make('nama_bank')
                    ->label('Nama Bank')
                    ->searchable(),
                TextColumn::make('atas_nama_bank')
                    ->label('Atas nama')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

}
