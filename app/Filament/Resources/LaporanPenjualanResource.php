<?php

namespace App\Filament\Resources;
namespace BezhanSalleh\FilamentShield\Resources;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LaporanPenjualan;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LaporanPenjualanResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\LaporanPenjualanResource\RelationManagers;

class LaporanPenjualanResource extends Resource implements HasShieldPermissions
{
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
    public static function canAccess(): bool
    {
        return false; // Menyembunyikan resource dari sidebar
    }
    protected static ?string $model = LaporanPenjualan::class;
    public static function getNavigationSort(): int
    {
        return 5; // Ini akan muncul di atas
    }
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Laporan Penjualan')
                    ->schema([
                        Card::make()
                            ->schema([])
                    ]),
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Card::make('Lumbung kering ke-1')
                                    ->schema([
                                        TextInput::make('id_lumbung_kering_1')
                                            ->label('No Lumbung Kering'),
                                        TextInput::make('berat_1')
                                            ->label('Berat')
                                            ->numeric(),
                                    ])->columnSpan(1)->collapsible(),
                                Card::make('Lumbung kering ke-2')
                                    ->schema([
                                        TextInput::make('id_lumbung_kering_2')
                                            ->label('No Lumbung Kering'),
                                        TextInput::make('berat_2')
                                            ->label('Berat')
                                            ->numeric(),
                                    ])->columnSpan(1)->collapsible(),
                                Card::make('Lumbung kering ke-3')
                                    ->schema([
                                        TextInput::make('id_lumbung_kering_3')
                                            ->label('No Lumbung Kering'),
                                        TextInput::make('berat_3')
                                            ->label('Berat')
                                            ->numeric(),
                                    ])->columnSpan(1)->collapsible(),
                                Card::make('Lumbung kering ke-4')
                                    ->schema([
                                        TextInput::make('id_lumbung_kering_4')
                                            ->label('No Lumbung Kering'),
                                        TextInput::make('berat_4')
                                            ->label('Berat')
                                            ->numeric(),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Lumbung kering ke-5')
                                    ->schema([
                                        TextInput::make('id_lumbung_kering_5')
                                            ->label('No Lumbung Kering'),
                                        TextInput::make('berat_5')
                                            ->label('Berat')
                                            ->numeric(),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Lumbung kering ke-6')
                                    ->schema([
                                        TextInput::make('id_lumbung_kering_6')
                                            ->label('No Lumbung Kering'),
                                        TextInput::make('berat_6')
                                            ->label('Berat')
                                            ->numeric(),
                                    ])->columnSpan(1)->collapsed(),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
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
            'index' => Pages\ListLaporanPenjualans::route('/'),
            'create' => Pages\CreateLaporanPenjualan::route('/create'),
            'edit' => Pages\EditLaporanPenjualan::route('/{record}/edit'),
        ];
    }
}
