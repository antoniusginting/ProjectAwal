<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DryerRecord extends Model
{
    protected $fillable = [
        "user_id",
        "dryer_id",
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function dryer_id(){
        return $this->belongsTo(Dryer::class);
    }

}
