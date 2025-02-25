<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupplierResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SupplierResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class SupplierResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Supplier';

    public static ?string $label = 'Daftar Supplier';

    public static function form(Form $form): Form
    {
        return $form
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
                    ->native(false) // Mengunakan dropdown modern
                    ->required(), // Opsional: Atur default value,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('No'),
                TextColumn::make('nama_supplier'),
                TextColumn::make('jenis_supplier')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }
}
