<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseDelivery extends Model
{
    //
    use HasFactory;

     protected $fillable = [
        'requesting_party',
        'device_type',
        'serial_number',
        'description',
        'checked_by',
        'date',
        'maintenance_manager',
        'it_manager',
        'created_by',
    ];
}
