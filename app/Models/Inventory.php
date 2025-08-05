<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'price',
        'location',
        'image', // Add other columns you're mass assigning
    ];
}
