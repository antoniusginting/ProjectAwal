<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Luar;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use App\Models\Pembelian;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\LuarResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LuarResource\Pages\EditLuar;
use App\Filament\Resources\LuarResource\Pages\ListLuars;
use App\Filament\Resources\LuarResource\Pages\CreateLuar;
use App\Filament\Resources\LuarResource\RelationManagers;
use App\Filament\Resources\PembelianResource\Pages\EditPembelian;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class LuarResource extends Resource implements HasShieldPermissions
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
    protected static ?string $model = Luar::class;
    protected static ?string $navigationLabel = 'Pembelian Antar Pulau';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 5;
    public static ?string $label = 'Daftar Pembelian Antar Pulau ';
    protected static ?string $navigationIcon = 'heroicon-o-globe-asia-australia';

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
                                        $nextId = (Luar::max('id') ?? 0) + 1;
                                        return 'C' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
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
                                Select::make('id_supplier')
                                    ->label('Supplier')
                                    ->placeholder('Pilih Supplier')
                                    ->options(Supplier::pluck('nama_supplier', 'id')) // Ambil daftar mobil
                                    ->searchable(), // Biar bisa cari
                                TextInput::make('netto')
                                    ->label('Netto')
                                    ->placeholder('Masukkan Netto')
                                    ->numeric(),
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
            ->recordUrl(function (Luar $record): ?string {
                $user = Auth::user();

                // 1) Super admin bisa edit semua kondisi
                if ($user && $user->hasRole('super_admin')) {
                    return EditLuar::getUrl(['record' => $record]);
                }

                // 2) Admin1 hanya bisa edit jika tara belum ada
                if ($user && $user->hasRole('timbangan')) {
                    if (!$record->tara) {
                        return EditLuar::getUrl(['record' => $record]);
                    }
                    return null;
                }

                // // 3) Admin2 hanya bisa edit jika no_spb belum ada
                // if ($user && $user->hasRole('admin')) {
                //     if (!$record->no_spb) {  // Sesuaikan dengan struktur data BK
                //         return EditPembelian::getUrl(['record' => $record]);
                //     }
                //     return null;
                // }

                // 4) Role lainnya tidak bisa edit
                return null;
            })
            // Query dasar tanpa filter tara
            ->query(Luar::query())
            ->defaultPaginationPageOption(10)
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
                TextColumn::make('supplier.nama_supplier')->label('Supplier')
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
            // ->actions([
            //     Tables\Actions\Action::make('view-pembelian')
            //         ->label(__("Lihat"))
            //         ->icon('heroicon-o-eye')
            //         ->url(fn($record) => self::getUrl("view-pembelian", ['record' => $record->id])),
            // ], position: ActionsPosition::BeforeColumns)
            ->filters([
                // Filter untuk menampilkan data pada hari ini
                Filter::make('Hari Ini')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDate('created_at', Carbon::today())
                    )->toggle(),
                // Filter toggle untuk menampilkan data dimana tara null
                Filter::make('Tara Kosong')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('tara')
                    )
                    ->toggle() // Filter ini dapat diaktifkan/nonaktifkan oleh pengguna
                    // ->default(function () {
                    //     // Filter aktif secara default hanya jika pengguna BUKAN super_admin ,'admin'
                    //     return !optional(Auth::user())->hasAnyRole(['super_admin']);
                    // })
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
            'index' => Pages\ListLuars::route('/'),
            'create' => Pages\CreateLuar::route('/create'),
            'edit' => Pages\EditLuar::route('/{record}/edit'),
        ];
    }
}
