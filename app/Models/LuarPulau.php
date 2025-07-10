<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuarPulau extends Model
{
     protected $fillable = [
        'stok',
        'nama',
        'status',
    ];

    public function luars()
    {
        return $this->hasMany(Luar::class);
    }
}
