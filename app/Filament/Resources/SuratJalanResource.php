<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kontrak;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\SuratJalan;
use Filament\Tables\Table;
use App\Models\AlamatKontrak;
use App\Models\TimbanganTronton;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Exports\SuratJalanExporter;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratJalanResource\Pages;
use App\Filament\Resources\SuratJalanResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Grid;

class SuratJalanResource extends Resource implements HasShieldPermissions
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
    protected static ?string $model = SuratJalan::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'Surat Jalan';
    protected static ?string $navigationGroup = 'Timbangan';
    protected static ?int $navigationSort = 4;
    public static ?string $label = 'Daftar Surat Jalan ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Card::make('Informasi Kontrak')
                            ->schema([
                                Select::make('id_kontrak')
                                    ->columnSpan(2)
                                    ->label('Nama Kontrak')
                                    ->required()
                                    ->options(Kontrak::all()->pluck('nama', 'id'))
                                    // ->options(
                                    //     Kontrak::where('nama', 'like', '%bonar%')
                                    //         ->orWhere('nama', 'like', '%dharma%')
                                    //         ->pluck('nama', 'id')
                                    // )
                                    ->searchable()
                                    ->reactive(), // Agar saat memilih kontrak, alamat terfilter
                                TextInput::make('created_at')
                                    ->label('Tanggal Sekarang')
                                    ->columnSpan(2)
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y'))
                                    ->disabled(), // Tidak bisa diedit
                                Select::make('kapasitas_kontrak_jual_id')
                                    ->label('Kepada Yth.')
                                    ->columnSpan(2)
                                    ->native(false)
                                    ->required()
                                    ->options(function () {
                                        return \App\Models\KapasitasKontrakJual::query()
                                            ->where('status', false)
                                            ->where('nama', 'not like', '%kontainer%')
                                            ->pluck('nama', 'id')
                                            ->toArray();
                                    })
                                    ->placeholder('Pilih Supplier')
                                    ->searchable()
                                    ->live(),

                                Select::make('id_alamat')
                                    ->label('Pilih Alamat')
                                    ->columnSpan(2)
                                    ->options(
                                        fn(callable $get) =>
                                        $get('kapasitas_kontrak_jual_id')
                                            ? AlamatKontrak::whereHas('kontrak', function ($query) use ($get) {
                                                $namaKontrak = \App\Models\KapasitasKontrakJual::find($get('kapasitas_kontrak_jual_id'))?->nama;
                                                if ($namaKontrak) {
                                                    $query->where('nama', $namaKontrak);
                                                }
                                            })->pluck('alamat', 'id')
                                            : []
                                    )
                                    ->searchable(),
                                TextInput::make('kota')
                                    ->label('Kota')
                                    ->columnSpan(2)
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan Kota')
                                    ->required(),
                                TextInput::make('po')
                                    ->label('PO')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->placeholder('Masukkan no PO'),
                                Select::make('status')
                                    ->native(false)
                                    ->options([
                                        'TERIMA' => 'TERIMA',
                                        'RETUR' => 'RETUR',
                                    ])
                                    ->label('Status')
                                    ->placeholder('Belum ada Status')
                                    ->live(), // Penting untuk reaktivitas
                            ])->columns(4),
                        Card::make('Informasi Timbangan')
                            ->schema([
                                Select::make('id_timbangan_tronton')
                                    ->label('ID Laporan Penjualan')

                                    ->options(function ($get) {
                                        $selectedId = $get('id_timbangan_tronton');

                                        // Ambil semua id yang sudah dipakai
                                        $usedIds = SuratJalan::pluck('id_timbangan_tronton')->toArray();

                                        // Hapus dulu selectedId dari daftar id yang digunakan (agar tidak terfilter saat edit)
                                        if ($selectedId) {
                                            $usedIds = array_diff($usedIds, [$selectedId]);
                                        }
                                        // Query untuk mengambil data TimbanganTronton yang id_luar_1 nya NULL dan belum digunakan
                                        $query = TimbanganTronton::whereNull('id_penjualan_antar_pulau_1')
                                            ->whereNotIn('id', $usedIds)
                                            ->latest()
                                            ->with('penjualan1');

                                        // Jika sedang edit dan ID tidak masuk list, tambahkan secara manual
                                        if ($selectedId && !in_array($selectedId, $query->pluck('id')->toArray())) {
                                            $query->orWhere('id', $selectedId);
                                        }

                                        return $query->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->kode . ' - ' . ($item->penjualan1->nama_supir ?? '-') . ' - ' .
                                                        ($item->penjualan1->plat_polisi ?? $item->penjualan1->no_container ?? '-')
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        // Ambil data timbangan berdasarkan id yang dipilih
                                        $timbangan = TimbanganTronton::where('id', $get('id_timbangan_tronton'))->first();

                                        // Set field-field lain berdasarkan data yang didapat
                                        $set('nama_supir', $timbangan?->penjualan1?->nama_supir ?? '');
                                        $set('nama_barang', $timbangan?->penjualan1?->nama_barang ?? '');
                                        $set('plat_polisi', $timbangan?->penjualan1?->plat_polisi ?? '');
                                        $set('tara_awal', $timbangan?->tara_awal ?? '');
                                        $set('bruto_akhir', $timbangan?->bruto_akhir ?? '');
                                        $set('total_netto', $timbangan?->total_netto ?? '');
                                    })
                                    ->afterStateHydrated(function (callable $set, callable $get, $state) {
                                        // Pastikan hanya berjalan saat edit data
                                        if ($state) {
                                            $timbangan = TimbanganTronton::where('id', $state)->first();

                                            $set('plat_polisi', $timbangan?->penjualan1?->plat_polisi ?? '');
                                            $set('nama_supir', $timbangan?->penjualan1?->nama_supir ?? '');
                                            $set('nama_barang', $timbangan?->penjualan1?->nama_barang ?? '');
                                            $set('tara_awal', $timbangan?->tara_awal ?? 0);
                                            $set('bruto_akhir', $timbangan?->bruto_akhir ?? 0);
                                            $set('total_netto', $timbangan?->total_netto ?? 0);
                                        }
                                    }),
                                TextInput::make('bruto_akhir')
                                    ->label('Bruto Awal')
                                    ->readOnly()
                                    ->reactive()
                                    ->hidden(),
                                TextInput::make('total_netto')
                                    ->label('Netto Awal')
                                    ->readOnly()
                                    ->reactive()
                                    ->hidden(),
                                TextInput::make('tambah_berat')
                                    ->label('Tambah Berat')
                                    ->numeric()
                                    ->placeholder('Masukkan berat yang ingin ditambah')
                                    ->suffixAction(
                                        Action::make('hitungBerat')
                                            ->icon('heroicon-o-calculator')
                                            ->tooltip('Klik untuk menghitung')
                                            ->color('primary')
                                            ->action(function ($state, callable $set, callable $get) {
                                                $set('bruto_final', ($get('bruto_akhir') ?? 0) + ($state ?? 0));
                                                $set('netto_final', ($get('total_netto') ?? 0) + ($state ?? 0));

                                                // Optional: Notifikasi berhasil
                                                Notification::make()
                                                    ->title('Berhasil dihitung!')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                TextInput::make('plat_polisi')
                                    ->label('Plat Polisi')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('bruto_final')
                                    ->label('Bruto')
                                    ->readOnly(), // Field ini tidak bisa diubah langsung oleh user
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('tara_awal')
                                    ->label('Tara')
                                    ->disabled() // Field ini hanya ditampilkan sebagai hasil dari database
                                    ->dehydrated(false),
                                Select::make('jenis_mobil') // Gantilah 'tipe' dengan nama field di database
                                    ->label('Jenis Mobil')
                                    ->options([
                                        'TRONTON' => 'TRONTON',
                                        'COLT DIESEL' => 'COLD DIESEL (CD)',
                                        'CONTAINER' => 'CONTAINER',
                                    ])
                                    ->placeholder('Pilih Jenis Mobil')
                                    // ->inlineLabel() // Membuat label sebelah kiri
                                    ->native(false) // Mengunakan dropdown modern
                                    ->required(), // Opsional: Atur default value
                                Grid::make()
                                    ->schema([
                                        TextInput::make('netto_final')
                                            ->label('Netto')
                                            ->required()
                                            ->readOnly(),
                                        TextInput::make('netto_diterima')
                                            ->label('Netto Diterima')
                                            ->placeholder('Masukkan netto diterima')
                                            ->numeric()
                                            ->disabled(fn(Get $get) => $get('status') !== 'TERIMA') // Hanya aktif jika status TERIMA
                                            ->dehydrated(fn(Get $get) => $get('status') === 'TERIMA') // Hanya simpan jika status TERIMA
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Reset nilai ketika status bukan TERIMA
                                                if ($get('status') !== 'TERIMA') {
                                                    $set('netto_diterima', null);
                                                }
                                            }),
                                    ])->columnSpan(1),
                                Hidden::make('user_id')
                                    ->label('User ID')
                                    ->default(Auth::id()) // Set nilai default user yang sedang login,
                            ])->columns(2),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                TextColumn::make('status')
                    ->alignCenter()
                    ->label('Status'),
                TextColumn::make('tronton.kode')
                    ->alignCenter()
                    ->label('No Penjualan'),
                TextColumn::make('tronton.penjualan1.plat_polisi')
                    ->label('Plat Polisi')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('po')
                    ->alignCenter()
                    ->label('No PO')
                    ->searchable(),
                TextColumn::make('kontrak.nama')
                    ->label('Nama Kontrak')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('kapasitasKontrakJual.nama')
                    ->label('Kepada Yth.')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('alamat.alamat')
                    ->label('Alamat')
                    ->wrap()
                    ->searchable()
                    ->extraAttributes(['style' => 'width: 250px;']),
                TextColumn::make('netto_final')
                    ->label('Netto')
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                TextColumn::make('tronton.penjualan1.nama_supir')
                    ->label('Nama Supir')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
            ])->defaultSort('id', 'desc')
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
                ExportAction::make()->exporter(SuratJalanExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(SuratJalanExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('View')
                    ->label(__("Lihat"))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => self::getUrl("view-surat-jalan", ['record' => $record->id])),
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
            'index' => Pages\ListSuratJalans::route('/'),
            'create' => Pages\CreateSuratJalan::route('/create'),
            'edit' => Pages\EditSuratJalan::route('/{record}/edit'),
            'view-surat-jalan' => Pages\ViewSuratJalan::route('/{record}/view-surat-jalan'),
        ];
    }
}
