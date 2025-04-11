<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanRecord extends Model
{
    protected $fillable = [
        "user_id",
        "penjualan_id",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function penjualan_id(){
        return $this->belongsTo(Penjualan::class);
    }

}
