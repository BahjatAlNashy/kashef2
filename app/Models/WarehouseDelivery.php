<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WarehouseDelivery extends Model
{
    //
    use HasFactory;

     protected $fillable = [
        'requesting_party',
        'device_type',
        'brand',
        'serial_number',
        'device_status',
        'description',
        'checked_by',
        'date',
        'maintenance_manager',
        'it_manager',
        'created_by',
        'updated_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
