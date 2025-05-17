<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanLumbung extends Model
{
    protected $guarded = [];

    public function dryers(): BelongsToMany
    {
        return $this->belongsToMany(Dryer::class, 'laporan_lumbung_has_dryers')
            ->withTimestamps();
    }
}
