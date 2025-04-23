<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Kendaraan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KendaraanResource\Pages;
use App\Filament\Resources\KendaraanResource\Pages\EditKendaraan;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanResource\RelationManagers;

class KendaraanResource extends Resource
{
    protected static ?string $model = Kendaraan::class;
    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Kendaraan';

    public static ?string $label = 'Daftar Kendaraan ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('plat_polisi_terbaru')
                            ->label('Plat polisi Terbaru')
                            ->autocomplete('off')
                            ->placeholder('Masukkan plat polisi Terbaru')
                            ->required(),
                        TextInput::make('nama_kernek')
                            ->label('Nama Kernek')
                            ->autocomplete('off')
                            ->placeholder('Masukkan nama kernek'),
                        TextInput::make('plat_polisi_sebelumnya')
                            ->label('Plat polisi Sebelumnya')
                            ->autocomplete('off')
                            ->placeholder('Masukkan plat polisi Sebelumnya'),
                        Select::make('jenis_mobil')
                            ->label('Jenis Mobil')
                            ->options([
                                'DUMP TRUCK (DT)' => 'DUMP TRUCK (DT)',
                                'COLD DIESEL (CD)' => 'COLD DIESEL (CD)',
                            ])
                            ->placeholder('Pilih Jenis Mobil')
                            ->native(false)
                            ->required(),
                        TextInput::make('pemilik')
                            ->label('Pemilik')
                            ->autocomplete('off')
                            ->placeholder('Masukkan nama pemilik')
                            ->required(),
                        Select::make('status_sp')
                            ->label('Status SP')
                            ->options([
                                'SP 1' => 'SP 1',
                                'SP 2' => 'SP 2',
                                'SP 3' => 'SP 3',
                            ])
                            ->placeholder('Pilih Status SP')
                            ->native(false),
                        TextInput::make('nama_supir')
                            ->label('Nama Supir')
                            ->autocomplete('off')
                            ->placeholder('Masukkan nama nama supir'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->recordUrl(
            //     fn(Kendaraan $record): ?string =>
            //     $record->nama_kernek
            //         ? null
            //         : EditKendaraan::getUrl(['record' => $record])
            // )
            ->columns([
                TextColumn::make('No')
                    ->label('No')
                    ->rowIndex(), // auto generate number sesuai urutan tampilan
                TextColumn::make('plat_polisi_terbaru')
                    ->label('Plat Baru')
                    ->searchable(),
                TextColumn::make('plat_polisi_sebelumnya')
                    ->label('Plat Sebelumnya')
                    ->searchable(),
                TextColumn::make('pemilik')
                    ->label('Pemilik')
                    ->searchable(),
                TextColumn::make('nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
                TextColumn::make('nama_kernek')
                    ->label('Nama Kernek')
                    ->searchable(),
                TextColumn::make('jenis_mobil')
                    ->label('Jenis Mobil')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('status_sp')
                    ->label('Status SP')
                    ->alignCenter()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListKendaraans::route('/'),
            'create' => Pages\CreateKendaraan::route('/create'),
            'edit' => Pages\EditKendaraan::route('/{record}/edit'),
        ];
    }
}
