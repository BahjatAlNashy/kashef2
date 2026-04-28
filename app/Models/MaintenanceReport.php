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
        'request_party_sign_before',
        'technician_sign_before',
        'device_location',
        'maintenance_procedure',
        'post_maintenance_notes',
        'request_party_sign_after',
        'technician_sign_after',
        'maintenance_head',
        'it_manager',
        'status',
        'created_by',
        'status_changed_by',
        'status_changed_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'status_changed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusChanger()
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }
}

