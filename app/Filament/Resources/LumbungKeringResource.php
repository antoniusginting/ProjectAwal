<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungKering;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LumbungKeringResource\Pages;
use App\Filament\Resources\LumbungKeringResource\RelationManagers;
use Filament\Forms\Components\Grid;

class LumbungKeringResource extends Resource
{
    protected static ?string $model = LumbungKering::class;
    public static function getNavigationSort(): int
    {
        return 4; // Ini akan muncul di atas
    }

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Lumbung')
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No LB')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_lb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (LumbungKering::max('id') ?? 0) + 1;
                                        return 'LK' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('jenis_jagung')
                                    ->label('Jenis Jagung')
                                    ->placeholder('Masukkan Jenis Jagung'),
                            ])->columns(2)
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Card::make('Laporan penjualan ke-1')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_1')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1),
                                Card::make('Laporan penjualan ke-2')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_2')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1),
                                Card::make('Laporan penjualan ke-3')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_3')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1),
                                Card::make('Laporan penjualan ke-4')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_4')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Laporan penjualan ke-5')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_5')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Laporan penjualan ke-6')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_6')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Laporan penjualan ke-7')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_7')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Laporan penjualan ke-8')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_8')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Laporan penjualan ke-9')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_9')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                                Card::make('Laporan penjualan ke-10')
                                    ->schema([
                                        TextInput::make('id_laporan_penjualan_10')
                                            ->label('No laporan penjualan'),
                                    ])->columnSpan(1)->collapsed(),
                            ])
                    ])
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
            'index' => Pages\ListLumbungKerings::route('/'),
            'create' => Pages\CreateLumbungKering::route('/create'),
            'edit' => Pages\EditLumbungKering::route('/{record}/edit'),
        ];
    }
}
