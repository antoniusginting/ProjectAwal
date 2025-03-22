<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanRecord extends Model
{
    protected $fillable = [
        "user_id",
        "suratjalan_id",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function suratjalan_id(){
        return $this->belongsTo(SuratJalan::class);
    }
}
