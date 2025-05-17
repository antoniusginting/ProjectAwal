<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanLumbungRecord extends Model
{
    protected $fillable = [
        "user_id",
        "laporan_lumbung_id",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function laporan_lumbung_id(){
        return $this->belongsTo(LaporanLumbung::class);
    }
}
