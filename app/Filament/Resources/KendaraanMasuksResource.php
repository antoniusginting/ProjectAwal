<?php

namespace App\Filament\Resources;

use DateTime;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KendaraanMasuks;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\KendaraanMasuksExporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanMasuksResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\KendaraanMasuksResource\RelationManagers;
use App\Filament\Resources\KendaraanMasuksResource\Pages\EditKendaraanMasuks;

class KendaraanMasuksResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = KendaraanMasuks::class;

    protected static ?string $navigationGroup = 'Satpam';
    protected static ?string $navigationIcon = 'heroicon-c-truck';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Registrasi Kendaraan';
    public static ?string $label = 'Daftar Registrasi Kendaraan ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->native(false)
                                    ->options([
                                        'TAMU' => 'TAMU',
                                        'SUPPLIER' => 'SUPPLIER',
                                        'BONAR JAYA' => 'BONAR JAYA',
                                        'EKSPEDISI' => 'EKSPEDISI',
                                    ])
                                    ->live() // Penting! Untuk memicu pembaruan otomatis
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        // Jika status adalah TAMU
                                        if ($state === 'TAMU') {
                                            $set('nomor_antrian', null);
                                            return;
                                        }

                                        // Jika status bukan TAMU, dapatkan nomor antrian terakhir
                                        $lastQueueNumber = KendaraanMasuks::where('status', $state)
                                            ->max('nomor_antrian') ?? 0;

                                        // // Set nomor antrian berikutnya
                                        // $set('nomor_antrian', $lastQueueNumber + 1);
                                    })
                                    ->helperText(function (Get $get) {
                                        $status = $get('status');

                                        // Jika status belum dipilih, tidak tampilkan helper text
                                        if (empty($status)) {
                                            return null; // Return null atau string kosong agar tidak menampilkan helper text
                                        }

                                        // Jika status adalah TAMU
                                        if ($status === 'TAMU') {
                                            return 'Status TAMU tidak memerlukan nomor antrian';
                                        }

                                        // Untuk status selain TAMU, tampilkan nomor antrian terakhir
                                        // Tetapi hanya ambil nomor antrian dari hari ini
                                        $lastNumber = KendaraanMasuks::where('status', $status)
                                            ->whereDate('created_at', Carbon::today())
                                            ->max('nomor_antrian') ?? 0;

                                        return "Nomor antrian terakhir untuk {$status}: {$lastNumber}";
                                    }),

                                TextInput::make('nomor_antrian')
                                    ->numeric()
                                    ->label('Nomor Antrian')
                                    ->disabled(fn(Get $get) => $get('status') === 'TAMU')
                                    ->dehydrated(fn(Get $get) => $get('status') !== 'TAMU'),

                                TextInput::make('nama_sup_per')
                                    ->label('Nama Supplier/Perusahaan')
                                    ->placeholder('Masukkan nama supplier atau perusahaan')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(['default' => 1, 'md' => 1]),

                                TextInput::make('plat_polisi')
                                    ->label('Plat Polisi')
                                    ->placeholder('Masukkan plat polisi')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(['default' => 1, 'md' => 1]),

                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->placeholder('Masukkan nama barang')
                                    ->autocomplete('off')
                                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state))
                                    ->columnSpan(['default' => 1, 'md' => 1]),

                                FileUpload::make('foto')
                                    ->label('Foto')
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->imagePreviewHeight(200),

                                TextInput::make('jam_masuk')
                                    ->label('Jam Masuk')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->columnSpan(['default' => 1, 'md' => 1]),

                                TextInput::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->readOnly()
                                    ->placeholder('Akan terisi saat toggle diaktifkan')
                                    ->suffixIcon('heroicon-o-clock')
                                    ->columnSpan(['default' => 1, 'md' => 1]),

                                // Second group - toggles
                                Grid::make()
                                    ->schema([
                                        Toggle::make('status_awal')
                                            ->label('Tombol Masuk')
                                            ->helperText('Klik jika sudah Masuk')
                                            ->onIcon('heroicon-m-bolt')
                                            ->offIcon('heroicon-m-user')
                                            ->dehydrated(true)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Toggle aktif, isi jam_masuk
                                                    $set('jam_masuk', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                                } else {
                                                    // Toggle nonaktif, kosongkan jam_masuk
                                                    $set('jam_masuk', null);
                                                }
                                            })
                                            ->columnSpan(1),

                                        Toggle::make('status_selesai')
                                            ->label('Tombol Keluar')
                                            ->helperText('Klik jika sudah Keluar')
                                            ->onIcon('heroicon-m-bolt')
                                            ->offIcon('heroicon-m-user')
                                            ->dehydrated(true)
                                            ->reactive()
                                            ->disabled(fn($get) => !$get('status_awal'))
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    // Toggle aktif, isi jam_keluar
                                                    $set('jam_keluar', now()->setTimezone('Asia/Jakarta')->format('H:i:s'));
                                                } else {
                                                    // Toggle nonaktif, kosongkan jam_keluar
                                                    $set('jam_keluar', null);
                                                }
                                            })
                                            ->columnSpan(1),
                                    ])
                                    ->columns([
                                        'default' => 1, // On mobile: 2 columns for toggles side by side
                                        'md' => 2,      // On larger screens: still 2 columns
                                    ])
                                    ->columnSpan(['default' => 'full', 'md' => 2]), // Changed to 1 column on desktop

                                // Last item - keterangan
                                Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('Masukkan Keterangan'),
                                Select::make('jenis')
                                    ->label('Jenis')
                                    ->native(false)
                                    ->options([
                                        'REGULER' => 'REGULER',
                                        'PRIORITAS' => 'PRIORITAS',
                                    ])
                                    ->default('REGULER'),

                                // Hidden field
                                Hidden::make('user_id')
                                    ->default(Auth::id()),
                            ])
                            ->columns([
                                'default' => 1, // Mobile: 1 column (stacked)
                                'md' => 2,      // Tablet & Desktop: 2 columns
                            ]),
                    ]),
            ]);
    }

    // // Tambahkan validasi di model atau di form
    // public static function rules($record = null)
    // {
    //     return [
    //         'status' => ['required', 'string'],
    //         'nomor_antrian' => [
    //             'nullable',
    //             'numeric',
    //             function ($attribute, $value, $fail) use ($record) {
    //                 // Skip validasi jika status adalah TAMU
    //                 if (request('status') === 'TAMU') {
    //                     return;
    //                 }

    //                 // Pastikan nomor antrian unik untuk status tersebut
    //                 $query = KendaraanMasuks::where('status', request('status'))
    //                     ->where('nomor_antrian', $value);

    //                 // Jika ini adalah update, exclude record saat ini
    //                 if ($record) {
    //                     $query->where('id', '!=', $record->id);
    //                 }

    //                 if ($query->exists()) {
    //                     $fail("Nomor antrian {$value} sudah digunakan untuk status " . request('status'));
    //                 }
    //             },
    //         ],
    //     ];
    // }
    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s') // polling ulang setiap 5 detik
            ->recordUrl(function (KendaraanMasuks $record): ?string {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                // 1) Super admin bisa edit semua kondisi
                if ($user && $user->hasRole('super_admin')) {
                    return EditKendaraanMasuks::getUrl(['record' => $record]);
                }

                if ($user && $user->hasRole('satpam')) {
                    if (!$record->jam_keluar) {
                        return EditKendaraanMasuks::getUrl(['record' => $record]);
                    }
                    return null;
                }
                return null;
            })
            ->defaultPaginationPageOption(10)
            ->columns([
                // IconColumn::make('status_selesai')
                //     ->label('')
                //     ->boolean()
                //     ->alignCenter()
                //     ->getStateUsing(function ($record) {
                //         // Konversi null menjadi false atau nilai lain yang sesuai
                //         return $record->status_selesai ?? false;
                //     }),
                IconColumn::make('status_selesai')
                    ->label('')
                    ->boolean()  // Menandakan kolom adalah boolean (0 atau 1)
                    ->icon(fn($record) => $record->status ? 'heroicon-o-check-circle' : 'heroicon-o-check-circle')  // Tentukan ikon berdasarkan nilai status
                    ->alignCenter(),  // Rata tengah untuk ikon
                TextColumn::make('nama_sup_per')
                    ->label('Nama Sup/Per')
                    ->searchable(),

                TextColumn::make('plat_polisi')
                    ->label('Plat Mobil')
                    ->searchable(),
                TextColumn::make('nomor_antrian')
                    ->label('Nomor')
                    ->alignCenter()
                    ->searchable()
                    ->badge() // Menggunakan badge untuk tampilan yang lebih menarik
                    ->color(function ($record) {
                        return match ($record->status) {
                            'TAMU' => 'tamu',
                            'SUPPLIER' => 'supplier',    // Hijau
                            'BONAR JAYA' => 'primary',  // Biru
                            'EKSPEDISI' => 'ekspedisi',   // Oranye
                            default => 'secondary',
                        };
                    })
                    // Tampilkan strip jika nomor antrian kosong
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->status === 'TAMU' || empty($state)) {
                            return '-';
                        }

                        return $state;
                    }),
                BadgeColumn::make('jenis')
                    ->label('Jenis')
                    ->alignCenter()
                    ->colors([
                        'success' => 'PRIORITAS',
                        'gray' => 'REGULER',
                    ]),
                TextColumn::make('status')
                    ->label('Status')
                    ->color(function ($record) {
                        return match ($record->status) {
                            'TAMU' => 'tamu',
                            'SUPPLIER' => 'supplier',    // Hijau
                            'BONAR JAYA' => 'primary',  // Biru
                            'EKSPEDISI' => 'ekspedisi',   // Oranye
                            default => 'secondary',
                        };
                    })
                    ->searchable(),

                ImageColumn::make('foto')
                    ->alignCenter()
                    ->label('Foto')
                    ->getStateUsing(fn($record) => $record->foto[0] ?? null)
                    ->url(fn($record) => asset('storage/' . ($record->foto[0] ?? '')))
                    ->openUrlInNewTab(),
                // TextColumn::make('created_at_time')
                //     ->label('Jam Dibuat')
                //     ->state(fn($record) => \Carbon\Carbon::parse($record->created_at)->format('H:i:s'))
                //     ->alignCenter(),
                TextColumn::make('jam_masuk')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('jam_keluar')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Tanggal | Jam')
                    ->alignCenter()
                    // ->colors([
                    //     'success' => fn($state) => Carbon::parse($state)->isToday(),
                    //     'warning' => fn($state) => Carbon::parse($state)->isYesterday(),
                    //     'gray' => fn($state) => Carbon::parse($state)->isBefore(Carbon::yesterday()),
                    // ])
                    ->formatStateUsing(function ($state) {
                        // Mengatur lokalitas ke Bahasa Indonesia
                        Carbon::setLocale('id');

                        return Carbon::parse($state)
                            ->locale('id') // Memastikan locale di-set ke bahasa Indonesia
                            ->isoFormat('D MMMM YYYY | HH:mm:ss');
                    }),
                TextColumn::make('user.name')
                    ->label('User')
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                ExportAction::make()->exporter(KendaraanMasuksExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to Excel')
                    ->size('xs')
                    ->outlined()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(KendaraanMasuksExporter::class)->label('Export to Excel'),
                ]),
            ])
            ->actions([
                // Tables\Actions\DeleteAction::make(),
            ])
            // ->bulkActions([
            //     // Tables\Actions\BulkActionGroup::make([
            //     Tables\Actions\DeleteBulkAction::make(),
            //     // ]),
            // ])
            ->filters([
                // Filter untuk Hari Ini
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
                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->native(false)
                    ->options([
                        'REGULER' => 'REGULER',
                        'PRIORITAS' => 'PRIORITAS',
                    ])
                    ->placeholder('Semua Status'),
                // Filter berdasarkan Status
                SelectFilter::make('status')
                    ->label('Status')
                    ->native(false)
                    ->options([
                        'TAMU' => 'TAMU',
                        'SUPPLIER' => 'SUPPLIER',
                        'BONAR JAYA' => 'BONAR JAYA',
                        'EKSPEDISI' => 'EKSPEDISI',
                    ])
                    ->placeholder('Semua Status'),
                Filter::make('Jam Keluar Kosong')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereNull('jam_keluar')
                    )
                    ->toggle() // Filter ini dapat diaktifkan/nonaktifkan oleh pengguna
                    ->default(function () {
                        // Filter aktif secara default hanya jika pengguna BUKAN super_admin ,'admin'
                        return !optional(Auth::user())->hasAnyRole(['super_admin']);
                    })
            ]);
        // ->filtersFormColumns(2); // Optional: Menampilkan filter dalam 2 kolom
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
            'index' => Pages\ListKendaraanMasuks::route('/'),
            'create' => Pages\CreateKendaraanMasuks::route('/create'),
            'edit' => Pages\EditKendaraanMasuks::route('/{record}/edit'),
        ];
    }
}
