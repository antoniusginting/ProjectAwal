<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Infolists\Infolist;
use App\Models\Mobil;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MobilResource\Pages;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MobilResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Card;

class MobilResource extends Resource implements HasShieldPermissions
{
    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    public static function getNavigationSort(): int
    {
        return 6; // Ini akan muncul di atas
    }
    protected static ?string $model = Mobil::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';


    protected static ?string $navigationLabel = 'Mobil';

    public static ?string $label = 'Daftar Mobil ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Textinput::make('plat_polisi')
                            ->prefixIcon('heroicon-o-truck')
                            ->placeholder('Masukkan Plat Polisi'),
                        Select::make('jenis_mobil') // Gantilah 'tipe' dengan nama field di database
                            ->label('Jenis Mobil')
                            ->options([
                                'CD' => 'Colt Diesel (CD)',
                                'DT' => 'Dump Truck (DT)',
                                'Eltor' => 'Eltor',
                                'L300' => 'L300',
                            ])
                            ->placeholder('Pilih Jenis Mobil')
                            // ->inlineLabel() // Membuat label sebelah kiri
                            ->native(false) // Mengunakan dropdown modern
                            ->required(), // Opsional: Atur default value

                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('No')
                    ->copyable()
                    ->sortable(),
                TextColumn::make('plat_polisi')
                    ->copyable()
                    ->label('Plat Polisi')
                    ->searchable(),
                TextColumn::make('jenis_mobil')->label('Jenis Mobil')
                    ->searchable()
                    ->copyable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMobils::route('/'),
            'create' => Pages\CreateMobil::route('/create'),
            'edit' => Pages\EditMobil::route('/{record}/edit'),
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
