<?php

namespace App\Filament\Resources;
// namespace BezhanSalleh\FilamentShield\Resources;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KapasitasDryer;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KapasitasDryerResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KapasitasDryerResource\RelationManagers;

class KapasitasDryerResource extends Resource implements HasShieldPermissions
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
    // public static function canAccess(): bool
    // {
    //     return false; // Menyembunyikan resource dari sidebar
    // }
    protected static ?string $model = KapasitasDryer::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?string $navigationGroup = 'Kapasitas';
    protected static ?string $navigationLabel = 'Kapasitas Dryer';

    public static ?string $label = 'Daftar Kapasitas Dryer ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('nama_kapasitas_dryer')
                            ->label('Nama Dryer')
                            ->placeholder('Masukkan Nama Kapasitas Dryer'),
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->placeholder('Masukkan Jumlah Kapasitas Total')
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
            ->poll('5s') // polling ulang setiap 5 detik
            //Untuk menempatkan action jadi paling kiri
            //->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->columns([
                TextColumn::make('nama_kapasitas_dryer')
                    ->alignCenter()
                    ->label('Nama Dryer'),
                TextColumn::make('kapasitas_total')
                    ->label('Kapasitas Total')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Tambah pemisah ribuan,
                TextColumn::make('kapasitas_sisa')
                    ->label('Kapasitas Sisa')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Tambah pemisah ribuan,
                TextColumn::make('kapasitas_terpakai')
                    ->label('Kapasitas Terpakai')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $kapasitasTerpakai = $record->kapasitas_total - $record->kapasitas_sisa;
                        return number_format($kapasitasTerpakai, 0, ',', '.');
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ViewAction::make(),
                Action::make('reset_kapasitas')
                    ->label('Reset')
                    ->action(fn($record) => $record->update([
                        'kapasitas_sisa' => $record->kapasitas_total
                    ]))
                    ->requiresConfirmation()
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path'),
            ]);
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ]);
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
            'index' => Pages\ListKapasitasDryers::route('/'),
            'create' => Pages\CreateKapasitasDryer::route('/create'),
            'edit' => Pages\EditKapasitasDryer::route('/{record}/edit'),
        ];
    }
}
