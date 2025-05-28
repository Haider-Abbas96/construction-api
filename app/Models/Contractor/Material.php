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
        "material_type_name",
        'name',
        'description',
        'unit',
        'price_per_unit',
        'price',
        'quantity',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
