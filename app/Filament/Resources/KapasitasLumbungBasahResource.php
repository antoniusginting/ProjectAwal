<?php

namespace App\Filament\Resources;
// namespace BezhanSalleh\FilamentShield\Resources;
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
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KapasitasLumbungBasahResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KapasitasLumbungBasahResource\RelationManagers;

class KapasitasLumbungBasahResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = KapasitasLumbungBasah::class;
    protected static ?string $navigationGroup = 'Kapasitas Lumbung';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Lumbung Basah';
    protected static ?int $navigationSort = 1;
    public static ?string $label = 'Daftar Kapasitas Lumbung Basah ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('no_kapasitas_lumbung')
                            ->label('No Lumbung')
                            ->placeholder('Masukkan No Kapasitas Lumbung')
                            ->required(),
                        TextInput::make('kapasitas_total')
                            ->label('Kapasitas Total')
                            ->required()
                            ->placeholder('Masukkan Jumlah Kapasitas Total')
                            ->live(debounce: 200) // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                        TextInput::make('kapasitas_sisa')
                            ->label('Kapasitas Sisa')
                            ->required()
                            ->placeholder('Masukkan Jumlah Kapasitas Sisa')
                            ->live(debounce: 200) // Memastikan perubahan langsung terjadi di Livewire
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "event.target.value = event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                            ])
                            ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)), // Hapus titik sebelum dikirim ke database
                    ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s') // polling ulang setiap 5 detik
            ->modifyQueryUsing(function (Builder $query) {
                // Filter query untuk mengecualikan baris dengan no_lumbung = 'Muat'
                return $query->whereNotIn('no_kapasitas_lumbung', ['Muat', 'TANPA LUMBUNG']);
            })
            ->columns([
                // TextColumn::make('id')->label('No'),
                TextColumn::make('no_kapasitas_lumbung')
                    ->alignCenter()
                    ->label('No Lumbung'),
                TextInputColumn::make('jenis')
                    ->label('Jenis')
                    ->alignCenter()
                    ->placeholder('Masukkan Jenis Jagung'),
                TextColumn::make('kapasitas_total')
                    ->alignCenter()
                    ->label('Kapasitas Total')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')), // Tambah pemisah ribuan,
                TextColumn::make('kapasitas_sisa')
                    ->label('Kapasitas Sisa')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
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
                // Action::make('reset_kapasitas')
                //     ->label('Reset')
                //     ->action(fn($record) => $record->update([
                //         'kapasitas_sisa' => $record->kapasitas_total
                //     ]))
                //     ->requiresConfirmation()
                //     ->color('warning')
                //     ->icon('heroicon-o-arrow-path'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
                ])
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
