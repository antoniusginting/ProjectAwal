<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SortiranRecord extends Model
{
    protected $fillable = [
        "user_id",
        "sortiran_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sortiran_id()
    {
        return $this->belongsTo(Sortiran::class);
    }
}
