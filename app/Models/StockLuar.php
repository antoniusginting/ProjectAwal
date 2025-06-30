<?php

namespace App\Models;

// use App\Models\Silo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLuar extends Model
{
    use HasFactory;

    protected $fillable = [
        'silo_id',
        'quantity',
        'notes',
        'date_added',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date_added' => 'date',
    ];

    public function silo()
    {
        return $this->belongsTo(Silo::class);
    }
}
