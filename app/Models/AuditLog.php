<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Allow mass assignment for these fields
    protected $fillable = [
        'inventory_id',
        'action',
        'user',
        'location',
    ];

    /**
     * Get the inventory item that this audit log belongs to.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
