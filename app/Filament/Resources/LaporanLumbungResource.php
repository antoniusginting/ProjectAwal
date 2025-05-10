<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanLumbungResource\Pages;
use App\Filament\Resources\LaporanLumbungResource\RelationManagers;
use App\Models\LaporanLumbung;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class LaporanLumbungResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LaporanLumbung::class;
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
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function canAccess(): bool
    {
        return false; // Menyembunyikan resource dari sidebar
    }
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
            'index' => Pages\ListLaporanLumbungs::route('/'),
            'create' => Pages\CreateLaporanLumbung::route('/create'),
            'edit' => Pages\EditLaporanLumbung::route('/{record}/edit'),
        ];
    }
}
