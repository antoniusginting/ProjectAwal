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
use Illuminate\Support\Facades\Auth;
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
    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
    public static function getNavigationSort(): int
    {
        return 7; // Ini akan muncul di atas
    }
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Kontrak';
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
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Supplier'),

                                Select::make('jenis_supplier')
                                    ->label('Jenis Supplier')
                                    ->options([
                                        'BONAR JAYA' => 'BONAR JAYA',
                                        'SIMPANG 2' => 'SIMPANG 2',
                                        'AGEN PURCHASING' => 'AGEN PURCHASING',
                                    ])
                                    ->placeholder('Pilih Jenis Supplier')
                                    ->native(false),

                                TextInput::make('no_ktp')
                                    ->label('Nomor KTP')
                                    ->numeric()
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan nomor KTP'),

                                TextInput::make('npwp')
                                    ->label('NPWP')
                                    ->numeric()
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan NPWP'),

                                TextInput::make('no_rek')
                                    ->label('Nomor rekening')
                                    ->numeric()
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan nomor rekening'),

                                Select::make('nama_bank')
                                    ->label('Nama Bank')
                                    ->options([
                                        'BRI' => 'BRI',
                                        'BCA' => 'BCA',
                                        'MANDIRI' => 'MANDIRI',
                                    ])
                                    ->placeholder('Pilih nama bank')
                                    ->native(false),

                                TextInput::make('atas_nama_bank')
                                    ->label('Atas nama bank')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
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
                TextColumn::make('No')
                    ->label('No')
                    ->alignCenter()
                    ->rowIndex(), // auto generate number sesuai urutan tampilan
                TextColumn::make('nama_supplier')
                    ->label('Nama supplier')
                    ->searchable(),
                TextColumn::make('no_ktp')
                    ->label('No KTP')
                    ->visible(fn () => optional(Auth::user())->hasAnyRole(['Admin','super_admin','Purchasing']))
                    ->searchable(),
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->visible(fn () => optional(Auth::user())->hasAnyRole(['Admin','super_admin','Purchasing']))
                    ->searchable(),
                TextColumn::make('no_rek')
                    ->label('No Rekening')
                    ->visible(fn () => optional(Auth::user())->hasAnyRole(['Admin','super_admin','Purchasing']))
                    ->searchable(),
                TextColumn::make('nama_bank')
                    ->label('Nama Bank')
                    ->visible(fn () => optional(Auth::user())->hasAnyRole(['Admin','super_admin','Purchasing']))
                    ->searchable(),
                TextColumn::make('atas_nama_bank')
                    ->label('Atas nama')
                    ->visible(fn () => optional(Auth::user())->hasAnyRole(['Admin','super_admin','Purchasing']))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
