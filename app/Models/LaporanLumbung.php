<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanLumbung extends Model
{
    protected $fillable = [
        "user_id",
        "lumbung",
        "status_silo",
        "berat_langsir",
        "status",
        "silo_id",
        "keterangan",
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Relasi ke Silo
     */
    public function silos(): BelongsTo
    {
        return $this->belongsTo(Silo::class, 'silo_id');
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Dryers (Data Masuk)
     */
    public function dryers(): HasMany
    {
        return $this->hasMany(Dryer::class);
    }

    /**
     * Relasi ke Penjualans (Data Keluar)
     */
    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class);
    }

    /**
     * Transfer yang keluar dari lumbung ini (lumbung sebagai asal)
     */
    public function transferKeluar(): HasMany
    {
        return $this->hasMany(Transfer::class, 'laporan_lumbung_keluar_id');
    }

    /**
     * Transfer yang masuk ke lumbung ini (lumbung sebagai tujuan) - LANGSIR GONIAN
     */
    public function transferMasuk(): HasMany
    {
        return $this->hasMany(Transfer::class, 'laporan_lumbung_masuk_id');
    }


    /**
     * Accessor untuk mendapatkan kode laporan dengan format yang konsisten
     */
    public function getKodeAttribute()
    {
        return $this->attributes['kode'] ?? 'IO-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Hitung total berat masuk (Dryers + Transfer Masuk/Langsir Gonian)
     */
    public function getTotalMasukAttribute(): int
    {
        $totalDryers = $this->dryers->sum('total_netto') ?? 0;
        $totalTransferMasuk = $this->transferMasuk->sum('netto') ?? 0;

        return $totalDryers + $totalTransferMasuk;
    }

    /**
     * Hitung total berat keluar (Penjualans + Transfer Keluar)
     */
    public function getTotalKeluarAttribute(): int
    {
        $totalPenjualan = $this->penjualans
            ->filter(fn($p) => !empty($p->no_spb))
            ->sum('netto') ?? 0;

        $totalTransferKeluar = $this->transferKeluar->sum('netto') ?? 0;

        return $totalPenjualan + $totalTransferKeluar;
    }

    /**
     * Hitung persentase keluar - HANYA ketika status = true (ditutup)
     * Logika diperbaiki: persentase HANYA muncul ketika lumbung ditutup
     */
    public function getPersentaseKeluarAttribute(): float
    {
        // Persentase HANYA dihitung jika status = true (lumbung ditutup)
        if ($this->status !== true) {
            return 0;
        }

        $totalMasuk = $this->total_masuk;
        $totalKeluar = $this->total_keluar;

        // Jika tidak ada data masuk, return 0
        if ($totalMasuk <= 0) {
            return 0;
        }

        return ($totalKeluar / $totalMasuk) * 100;
    }

    /**
     * Check apakah persentase harus ditampilkan
     * HANYA tampilkan jika status = true (lumbung ditutup)
     */
    public function getShouldShowPercentageAttribute(): bool
    {
        return $this->status === true;
    }

    /**
     * Check apakah ada langsir gonian aktif
     */
    public function getHasLangsirGonianAttribute(): bool
    {
        return $this->transferMasuk->count() > 0;
    }

    /**
     * Get status text untuk display
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->status === true) {
            return 'Ditutup';
        }

        if ($this->has_langsir_gonian) {
            return 'Aktif (Langsir Gonian)';
        }

        return 'Aktif';
    }

    /**
     * Get status color untuk badge
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->status === true) {
            return 'success'; // hijau
        }

        if ($this->has_langsir_gonian) {
            return 'warning'; // kuning/orange
        }

        return 'primary'; // biru
    }


    /**
     * Scope untuk laporan yang sudah ditutup
     */
    public function scopeClosed($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope untuk laporan yang masih aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', true);
    }

    /**
     * Scope untuk laporan yang memiliki langsir gonian
     */
    public function scopeWithLangsirGonian($query)
    {
        return $query->whereHas('transferMasuk');
    }

    /**
     * Scope untuk filter berdasarkan lumbung
     */
    public function scopeByLumbung($query, $lumbung)
    {
        return $query->where('lumbung', $lumbung);
    }

    // ============= HELPER METHODS =============

    /**
     * Method untuk menutup laporan lumbung
     */
    public function closeLaporan(): bool
    {
        return $this->update(['status' => true]);
    }

    /**
     * Method untuk membuka kembali laporan lumbung
     */
    public function reopenLaporan(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Method untuk mendapatkan ringkasan laporan
     */
    public function getSummary(): array
    {
        return [
            'kode' => $this->kode,
            'lumbung' => $this->lumbung,
            'total_masuk' => $this->total_masuk,
            'total_keluar' => $this->total_keluar,
            'persentase_keluar' => $this->persentase_keluar,
            'status' => $this->status_text,
            'has_langsir_gonian' => $this->has_langsir_gonian,
            'should_show_percentage' => $this->should_show_percentage,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Method untuk validasi apakah laporan bisa ditutup
     */
    public function canBeClosed(): bool
    {
        // Bisa ditutup jika ada data masuk dan belum ditutup
        return $this->total_masuk > 0 && $this->status !== true;
    }

    /**
     * Method untuk validasi apakah laporan bisa dibuka kembali
     */
    public function canBeReopened(): bool
    {
        // Bisa dibuka kembali jika sudah ditutup dan tidak ada kendala bisnis lainnya
        return $this->status === true;
    }
}
