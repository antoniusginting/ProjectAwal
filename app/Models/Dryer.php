<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dryer extends Model
{
    protected $fillable = [
        'id_kapasitas_dryer',
        'id_lumbung_1',
        'id_lumbung_2',
        'id_lumbung_3',
        'id_lumbung_4',
        'operator',
        'nama_barang',
        'lumbung_tujuan',
        'rencana_kadar',
        'hasil_kadar',
        'total_netto',
        'pj',
        'status',
    ];


    public function sortirans(): BelongsToMany
    {
        return $this->belongsToMany(Sortiran::class, 'dryer_has_sortiran')
            ->withTimestamps();
    }
    public function timbanganTrontons(): BelongsToMany
    {
        return $this->belongsToMany(TimbanganTronton::class, 'dryers_has_timbangan_trontons')
            ->withTimestamps();
    }
    public function laporanLumbungs(): BelongsToMany
    {
        return $this->belongsToMany(LaporanLumbung::class, 'laporan_lumbung_has_dryers')
            ->withTimestamps();
    }
    // Relasi ke Kapasitas
    public function kapasitasdryer()
    {
        return $this->belongsTo(KapasitasDryer::class, 'id_kapasitas_dryer', 'id');
    }

    public function lumbung1()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_1');
    }

    public function lumbung2()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_2');
    }

    public function lumbung3()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_3');
    }

    public function lumbung4()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_4');
    }

   public function getTotalNettoIntegerAttribute(): int
    {
        return (int) str_replace('.', '', $this->total_netto);
    }
}
