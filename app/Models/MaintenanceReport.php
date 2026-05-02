<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MaintenanceReport extends Model
{
    //
     use HasFactory;

    protected $fillable = [
        'requesting_party',
        'reporter_name',
        'report_date',
        'device_name',
        'brand',
        'serial_number',
        'initial_inspection',
        'failure_cause',
        'device_location',
        'technical_manager',
        'maintenance_head',
        'it_manager',
        'status',
        'created_by',
        'updated_by',
        'status_changed_by',
        'status_changed_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'status_changed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function statusChanger()
    {
        return $this->belongsTo(User::class, 'status_changed_by')->withTrashed();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}

