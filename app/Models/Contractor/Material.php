<?php

namespace App\Models\Contractor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'material_type_id',
        'name',
        'description',
        'unit',
        'price_per_unit',
        'total_price',
        'quantity',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
