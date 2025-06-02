<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanLumbung extends Model
{
    protected $fillable = [
        "user_id",
        "lumbung",
    ];
    public function timbanganTrontons(): BelongsToMany
    {
        return $this->belongsToMany(TimbanganTronton::class, 'laporan_lb_has_timbangant')
            ->withTimestamps();
    }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function dryers(): BelongsToMany
    {
        return $this->belongsToMany(Dryer::class, 'laporan_lumbung_has_dryers')
            ->withTimestamps();
    }
}
