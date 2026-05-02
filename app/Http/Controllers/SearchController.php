<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use App\Models\WarehouseDelivery;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // ============================================================
    // البحث في الكشوفات وتسليمات المستودع
    // ============================================================
    public function index(Request $request)
    {
        $serial = $request->input('serial_number');
        $party = $request->input('requesting_party');

        $warehouseResults = collect();
        $maintenanceResults = collect();

        if ($serial) {
            $warehouseResults = WarehouseDelivery::where('serial_number', 'LIKE', "%{$serial}%")->get();
            $maintenanceResults = MaintenanceReport::where('serial_number', 'LIKE', "%{$serial}%")->get();
        } elseif ($party) {
            $warehouseResults = WarehouseDelivery::where('requesting_party', 'LIKE', "%{$party}%")->get();
            $maintenanceResults = MaintenanceReport::where('requesting_party', 'LIKE', "%{$party}%")->get();
        }

        return view('search.index', compact('warehouseResults', 'maintenanceResults', 'serial', 'party'));
    }
}