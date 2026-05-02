<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use App\Models\WarehouseDelivery;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ============================================================
    // عرض لوحة التحكم الرئيسية
    // ============================================================
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $queryReports = MaintenanceReport::with('creator');
        $queryDeliveries = WarehouseDelivery::query();
        
        // فلترة حسب الدور
        if (auth()->user()->role == 'employee') {
            $queryReports->where('created_by', auth()->id());
            $queryDeliveries->where('created_by', auth()->id());
        }
        
        // إذا كان المستخدم مديراً، فرز الكشوف الفنية بحيث تظهر قيد التنفيذ أولاً
        if (auth()->user()->role == 'manager') {
            $queryReports->orderByRaw("CASE WHEN status = 'قيد التنفيذ' THEN 1 WHEN status = 'تم الإنجاز' THEN 2 ELSE 3 END");
        } else {
            $queryReports->latest();
        }
        
        $reports = $queryReports->get();
        $deliveries = $queryDeliveries->latest()->get();
        
        return view('home', compact('reports', 'deliveries', 'type'));
    }

    // ============================================================
    // جلب الكشوفات والتسليمات بصيغة JSON (للتحديث الفوري)
    // ============================================================
    public function getReportsJson(Request $request)
    {
        $queryReports = MaintenanceReport::with('creator');
        $queryDeliveries = WarehouseDelivery::query();

        // فلترة حسب الدور
        if (auth()->user()->role == 'employee') {
            $queryReports->where('created_by', auth()->id());
            $queryDeliveries->where('created_by', auth()->id());
        }

        // إذا كان المستخدم مديراً، فرز الكشوف الفنية بحيث تظهر قيد التنفيذ أولاً
        if (auth()->user()->role == 'manager') {
            $queryReports->orderByRaw("CASE WHEN status = 'قيد التنفيذ' THEN 1 WHEN status = 'تم الإنجاز' THEN 2 ELSE 3 END");
        } else {
            $queryReports->latest();
        }

        $reports = $queryReports->get();
        $deliveries = $queryDeliveries->latest()->get();

        return response()->json([
            'reports' => $reports,
            'deliveries' => $deliveries,
            'timestamp' => now()->timestamp
        ]);
    }
}
