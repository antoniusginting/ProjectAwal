<?php

namespace App\Filament\Resources;
// namespace BezhanSalleh\FilamentShield\Resources;
use Dom\Text;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Sortiran;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LumbungBasah;
use Filament\Resources\Resource;
use function Laravel\Prompts\text;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use App\Models\KapasitasLumbungBasah;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Filament\Resources\LumbungBasahResource\Pages;
use Filament\Forms\Components\Actions\Action as FormAction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\LumbungBasahResource\RelationManagers;

class LumbungBasahResource extends Resource implements HasShieldPermissions
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
    public static function getNavigationSort(): int
    {
        return 2; // Ini akan muncul di atas
    }
    protected static ?string $model = LumbungBasah::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static ?string $label = 'Daftar Lumbung Basah ';

    protected static ?string $navigationLabel = 'Lumbung Basah';

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
                                    ->columnSpan(2)
                                    ->content(function ($record) {
                                        // Jika sedang dalam mode edit, tampilkan kode yang sudah ada
                                        if ($record) {
                                            return $record->no_lb;
                                        }

                                        // Jika sedang membuat data baru, hitung kode berikutnya
                                        $nextId = (LumbungBasah::max('id') ?? 0) + 1;
                                        return 'LB' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    }),
                                Select::make('no_lumbung_basah')
                                    ->label('No Lumbung Basah')
                                    ->placeholder('Pilih No Lumbung')
                                    ->options(KapasitasLumbungBasah::pluck('no_kapasitas_lumbung', 'id'))
                                    ->searchable() // Biar bisa cari
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn($record) => $record !== null)
                                    ->afterStateHydrated(function ($state, callable $set) {
                                        if ($state) {
                                            $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                            $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                            $formattedSisa = number_format($kapasitaslumbungbasah?->kapasitas_sisa ?? 0, 0, ',', '.');
                                            $set('kapasitas_sisa', $formattedSisa);
                                            $set('kapasitas_total', $kapasitaslumbungbasah?->kapasitas_total ?? 'Tidak ada');
                                            $formattedtotal = number_format($kapasitaslumbungbasah?->kapasitas_total ?? 0, 0, ',', '.');
                                            $set('kapasitas_total', $formattedtotal);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $kapasitaslumbungbasah = KapasitasLumbungBasah::find($state);
                                        $set('kapasitas_sisa', $kapasitaslumbungbasah?->kapasitas_sisa ?? 'Tidak ada');
                                        $formattedSisa = number_format($kapasitaslumbungbasah?->kapasitas_sisa ?? 0, 0, ',', '.');
                                        $set('kapasitas_sisa', $formattedSisa);
                                        $set('kapasitas_total', $kapasitaslumbungbasah?->kapasitas_total ?? 'Tidak ada');
                                        $formattedtotal = number_format($kapasitaslumbungbasah?->kapasitas_total ?? 0, 0, ',', '.');
                                        $set('kapasitas_total', $formattedtotal);
                                        // Reset nilai sortirans ketika no_lumbung_basah berubah
                                        $set('sortirans', null);
                                        $set('total_netto', null);
                                    }),
                                TextInput::make('kapasitas_total')
                                    ->label('Kapasitas Total')
                                    ->placeholder('Pilih terlebih dahulu no lumbung basah')
                                    ->disabled(),
                                TextInput::make('tujuan')
                                    ->label('Tujuan')
                                    ->placeholder('Masukkan Tujuan'),
                                TextInput::make('total_netto')
                                    ->label('Total Netto')
                                    ->readOnly()
                                    ->reactive()
                                    ->formatStateUsing(function ($state) {
                                        // Format untuk tampilan (dari integer ke string terformat)
                                        if (is_numeric($state)) {
                                            return number_format((int)$state, 0, ',', '.');
                                        }
                                        return $state;
                                    })
                                    ->dehydrateStateUsing(function ($state) {
                                        // Konversi ke integer saat menyimpan ke database
                                        return (int)str_replace(['.', ','], ['', '.'], $state);
                                    }),

                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                                TextInput::make('kapasitas_sisa')
                                    ->label('Kapasitas Sisa')
                                    ->placeholder('Pilih terlebih dahulu no lumbung basah')
                                    ->disabled(),
                            ])->columns(2)
                    ])->collapsible(),
                Card::make()
                    ->schema([
                        Select::make('sortirans')
                            ->label('Sortiran')
                            ->multiple()
                            ->relationship('sortirans', 'no_sortiran', function ($query, $livewire) {
                                // Dapatkan no_lumbung_basah yang dipilih
                                $selectedLumbungId = $livewire->data['no_lumbung_basah'] ?? null;

                                // Jika no_lumbung_basah belum dipilih, kembalikan query yang tidak akan mengembalikan hasil apapun
                                if (!$selectedLumbungId) {
                                    // Gunakan nama tabel yang eksplisit untuk menghindari ambiguitas
                                    return $query->where('sortirans.id', -1);
                                }

                                // Dapatkan record saat ini (untuk mode edit)
                                $record = $livewire->getRecord();

                                // Dapatkan semua sortiran yang sudah ada di semua lumbung basah
                                $usedSortiranIds = DB::table('lumbung_basah_has_sortiran')
                                    ->pluck('sortiran_id')
                                    ->toArray();

                                // Base query - filter berdasarkan no_lumbung jika ada
                                $query = $query->when($selectedLumbungId, function ($q) use ($selectedLumbungId) {
                                    // Dapatkan no_lumbung dari KapasitasLumbungBasah
                                    $kapasitasLumbung = KapasitasLumbungBasah::find($selectedLumbungId);
                                    if ($kapasitasLumbung) {
                                        // Filter sortiran berdasarkan no_lumbung yang sama
                                        return $q->where('sortirans.no_lumbung', $kapasitasLumbung->no_kapasitas_lumbung);
                                    }
                                    return $q;
                                });

                                // Jika dalam mode edit, kita perlu menyertakan sortiran yang sudah terkait dengan record ini
                                if ($record) {
                                    $currentSortiranIds = $record->sortirans()
                                        ->select('sortirans.id')
                                        ->pluck('sortirans.id')
                                        ->toArray();

                                    // Filter lengkap:
                                    return $query->where(function ($q) use ($usedSortiranIds, $currentSortiranIds) {
                                        $q->whereNotIn('sortirans.id', $usedSortiranIds)
                                            ->orWhereIn('sortirans.id', $currentSortiranIds);
                                    })->latest('sortirans.created_at');
                                } else {
                                    // Dalam mode create, hanya tampilkan sortiran yang belum digunakan
                                    return $query->whereNotIn('sortirans.id', $usedSortiranIds)
                                        ->latest('sortirans.created_at');
                                }
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $noBk = $record->pembelian ? $record->pembelian->plat_polisi : 'N/A';
                                return $record->no_sortiran . ' - ' . $noBk;
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                // Mendapatkan nilai kapasitas sisa awal
                                $noLumbungBasah = $get('no_lumbung_basah');
                                $kapasitasAwal = 0;

                                // Dapatkan record saat ini (untuk mode edit)
                                $record = $livewire->getRecord();
                                $isEditMode = $record !== null;

                                // Dapatkan kapasitas awal dari database
                                if ($noLumbungBasah) {
                                    $kapasitasLumbung = KapasitasLumbungBasah::find($noLumbungBasah);
                                    if ($kapasitasLumbung) {
                                        $kapasitasAwal = $kapasitasLumbung->kapasitas_sisa;
                                    }
                                }

                                // Jika dalam mode edit, tambahkan kembali kapasitas yang sudah terpakai sebelumnya
                                if ($isEditMode) {
                                    // Mendapatkan sortiran yang sudah ada sebelumnya
                                    // Perbaiki query untuk menghindari ambiguitas
                                    $oldSortiranIds = $record->sortirans()
                                        ->select('sortirans.id') // Spesifikkan tabel untuk kolom id
                                        ->pluck('sortirans.id')
                                        ->toArray();

                                    $oldSortirans = \App\Models\Sortiran::whereIn('id', $oldSortiranIds)->get();

                                    // Tambahkan kembali kapasitas dari sortiran yang sebelumnya terpakai
                                    $totalOldNetto = 0;
                                    foreach ($oldSortirans as $oldSortiran) {
                                        $oldNettoValue = (int) preg_replace('/[^0-9]/', '', $oldSortiran->netto_bersih);
                                        $totalOldNetto += $oldNettoValue;
                                    }

                                    // Tambahkan kapasitas yang sebelumnya terpakai
                                    $kapasitasAwal += $totalOldNetto;
                                }

                                // Jika tidak ada sortiran dipilih, reset total netto dan gunakan kapasitas awal
                                if (empty($state)) {
                                    $set('total_netto', 0);
                                    $set('kapasitas_sisa', number_format($kapasitasAwal, 0, ',', '.'));
                                    return;
                                }

                                // Ambil semua sortiran yang dipilih saat ini
                                $selectedSortirans = \App\Models\Sortiran::whereIn('id', $state)->get();

                                // Hitung total netto dari semua sortiran yang dipilih saat ini
                                $totalNetto = 0;
                                foreach ($selectedSortirans as $sortiran) {
                                    $nettoValue = (int) preg_replace('/[^0-9]/', '', $sortiran->netto_bersih);
                                    $totalNetto += $nettoValue;
                                }

                                // Set nilai total_netto dengan format
                                $set('total_netto', number_format($totalNetto, 0, ',', '.'));

                                // Hitung kapasitas sisa baru dengan mengurangi kapasitas awal dengan total netto baru
                                $kapasitasSisaBaru = $kapasitasAwal - $totalNetto;

                                // Format kapasitas sisa untuk tampilan
                                $formattedKapasitasSisaBaru = number_format($kapasitasSisaBaru, 0, ',', '.');

                                // Set nilai kapasitas_sisa baru
                                $set('kapasitas_sisa', $formattedKapasitasSisaBaru);

                                // Tampilkan notifikasi
                                $notificationMessage = $isEditMode ?
                                    "Kapasitas diperbarui (mode edit)" :
                                    "Kapasitas diperbarui";
                            })
                            ->preload()
                            ->searchable(),
                        Hidden::make('user_id')
                            ->label('User ID')
                            ->default(Auth::id()) // Set nilai default user yang sedang login,
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->columns([
                BadgeColumn::make('created_at')
                    ->label('Tanggal')
                    ->alignCenter()
                    ->colors([
                        'success' => fn($state) => Carbon::parse($state)->isToday(),
                        'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                        'gray' => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                    ])
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d M Y')),
                TextColumn::make('no_lb')->label('No LB'),
                TextColumn::make('no_lumbung_basah')->label('No Lumbung Basah')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('sortirans')
                    ->alignCenter()
                    // ->extraAttributes(['style' => 'width: 250px;'])
                    ->label('No Sortiran')
                    ->formatStateUsing(function ($record) {
                        return $record->sortirans->map(function ($sortiran) {
                            return $sortiran->no_sortiran . ''; //. $sortiran->netto_bersih . ' - ' . $sortiran->pembelian->nama_supir . '';
                        })->implode(', ');
                    })
                    ->wrap()
                    ->limit(50),
                TextColumn::make('tujuan')
                    ->alignCenter()
                    ->label('Tujuan'),
                TextColumn::make('total_netto')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->label('Total Netto'),
                TextColumn::make('status')
                    ->label('Status'),
                TextColumn::make('user.name')
                    ->label('User'),
            ])
            ->defaultSort('no_lb', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view-lumbungbasah')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-lumbung-basah", ['record' => $record->id])),
            ], position: ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListLumbungBasahs::route('/'),
            'create' => Pages\CreateLumbungBasah::route('/create'),
            'edit' => Pages\EditLumbungBasah::route('/{record}/edit'),
            'view-lumbung-basah' => Pages\ViewLumbungBasah::route('/{record}/view-lumbung-basah'),
        ];
    }
}
