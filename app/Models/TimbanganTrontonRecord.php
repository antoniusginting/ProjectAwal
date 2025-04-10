<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbanganTrontonRecord extends Model
{
    protected $fillable = [
        "user_id",
        "timbangan_id",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function timbangan_id(){
        return $this->belongsTo(TimbanganTronton::class);
    }
}
