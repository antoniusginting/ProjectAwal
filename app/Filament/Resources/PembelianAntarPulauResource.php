<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PembelianAntarPulau;
use Filament\Forms\Components\Card;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\PembelianAntarPulauExporter;
use App\Filament\Resources\PembelianAntarPulauResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\PembelianAntarPulauResource\RelationManagers;

class PembelianAntarPulauResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PembelianAntarPulau::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationGroup = 'Antar Pulau';
    protected static ?int $navigationSort = 1;
    public static ?string $label = 'Daftar Pembelian Antar Pulau ';
    protected static ?string $navigationLabel = 'Pembelian Antar Pulau';
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
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([
                                Placeholder::make('next_id')
                                    ->label('No SPB')
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->kode;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (PembelianAntarPulau::max('id') ?? 0) + 1;
                                        return 'CJ' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                TextInput::make('created_at')
                                    ->label('Tanggal')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                            ])->columns(2)->collapsed(),

                        Card::make()
                            ->schema([
                                TextInput::make('kode_segel')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->autocomplete('off')
                                    ->placeholder('Masukkan kode Segel'),
                                TextInput::make('nama_barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Barang'),

                                Select::make('kapasitas_kontrak_beli_id')
                                    ->label('Supplier')
                                    ->native(false)
                                    ->required()
                                    ->options(function () {
                                        return \App\Models\KapasitasKontrakBeli::query()
                                            ->where('status', false)
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [$item->id => $item->nama . ' - ' . $item->supplier];
                                            })
                                            ->toArray();
                                    })

                                    ->placeholder('Pilih Supplier')
                                    ->searchable()
                                    ->live(),
                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->placeholder('Masukkan Netto')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('no_container')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan No Container'),
                                TextInput::make('nama_ekspedisi')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Nama Ekspedisi'),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(2)
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 15, 50])
            ->columns([
                BadgeColumn::make('created_at')
                    ->label('Tanggal')
                    ->alignCenter()
                    ->colors([
                        'success' => fn($state) => Carbon::parse($state)->isToday(),
                        'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                        'gray' => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                    ])
                    ->formatStateUsing(function ($state) {
                        // Mengatur lokalitas ke Bahasa Indonesia
                        Carbon::setLocale('id');

                        return Carbon::parse($state)
                            ->locale('id') // Memastikan locale di-set ke bahasa Indonesia
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('kode')
                    ->label('No SPB')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('berhasil menyalin'),
                TextColumn::make('kode_segel')
                    ->label('Kode Segel')
                    ->searchable(),
                TextColumn::make('kapasitasKontrakBeli.nama')->label('Supplier')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('nama_barang')
                    ->searchable(),
                TextColumn::make('netto')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('no_container')
                    ->searchable(),
                TextColumn::make('nama_ekspedisi')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('kode', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['dari_tanggal']) && !empty($data['sampai_tanggal'])) {
                            return $query->whereBetween('created_at', [
                                Carbon::parse($data['dari_tanggal'])->startOfDay(),
                                Carbon::parse($data['sampai_tanggal'])->endOfDay(),
                            ]);
                        }

                        if (!empty($data['dari_tanggal'])) {
                            return $query->where('created_at', '>=', Carbon::parse($data['dari_tanggal'])->startOfDay());
                        }

                        if (!empty($data['sampai_tanggal'])) {
                            return $query->where('created_at', '<=', Carbon::parse($data['sampai_tanggal'])->endOfDay());
                        }

                        return $query;
                    }),
            ])
            ->headerActions([
                ExportAction::make()->exporter(PembelianAntarPulauExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(PembelianAntarPulauExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPembelianAntarPulaus::route('/'),
            'create' => Pages\CreatePembelianAntarPulau::route('/create'),
            'edit' => Pages\EditPembelianAntarPulau::route('/{record}/edit'),
        ];
    }
}
