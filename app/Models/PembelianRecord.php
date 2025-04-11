<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianRecord extends Model
{
    protected $fillable = [
        "user_id",
        "pembelian_id",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function pembelian_id(){
        return $this->belongsTo(Pembelian::class);
    }
}
