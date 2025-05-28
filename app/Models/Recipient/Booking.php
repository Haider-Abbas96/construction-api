<?php

namespace App\Models\Recipient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'user_id',
        "material_name",
        'unit',
        'booking_type',
        'quantity',
        'address',
        'time',
        'date',
        'total_price',
        'status',
    ];

    // Relationships
    public function material()
    {
        return $this->belongsTo(\App\Models\Contractor\Material::class);
    }
    
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
