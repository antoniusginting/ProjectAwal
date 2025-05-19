<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanLumbung extends Model
{
    protected $fillable = [
        "user_id",
    ];
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
