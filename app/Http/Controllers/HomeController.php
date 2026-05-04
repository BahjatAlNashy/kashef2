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
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        $queryReports = MaintenanceReport::with('creator');
        $queryDeliveries = WarehouseDelivery::query();

        // فلترة حسب الدور
        if (auth()->user()->role == 'employee') {
            $queryReports->where('created_by', auth()->id());
            $queryDeliveries->where('created_by', auth()->id());
        }

        // البحث في الجهة الطالبة والرقم التسلسلي فقط
        if ($search) {
            $queryReports->where(function($q) use ($search) {
                $q->where('requesting_party', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
            $queryDeliveries->where(function($q) use ($search) {
                $q->where('requesting_party', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // فلترة حسب الحالة (للكشوفات الفنية فقط)
        if ($status !== 'all' && $status !== 'completed') {
            $queryReports->where('status', $status);
        } elseif ($status === 'completed') {
            $queryReports->whereIn('status', ['تم الإنجاز', 'تم الإلغاء']);
        }

        // حساب عدد الكشوفات قيد التنفيذ
        $pendingQuery = MaintenanceReport::where('status', 'قيد التنفيذ');
        if (auth()->user()->role == 'employee') {
            $pendingQuery->where('created_by', auth()->id());
        }
        $pendingCount = $pendingQuery->count();

        // فلترة حسب النوع إذا تم تحديده
        if ($type === 'maintenance') {
            $reports = $queryReports->orderByDesc('updated_at')->paginate(10);
            return view('home', compact('reports', 'type', 'search', 'status', 'pendingCount'));
        } elseif ($type === 'warehouse') {
            $deliveries = $queryDeliveries->orderByDesc('updated_at')->paginate(10);
            return view('home', compact('deliveries', 'type', 'search', 'status', 'pendingCount'));
        }

        // جلب جميع البيانات ودمجها
        $reports = $queryReports->get();

        // عند فلترة حسب الحالة، لا نعرض تسليمات المستودع لأنها لا تحتوي على حالة
        if ($status !== 'all') {
            $allItems = $reports->map(function ($report) {
                return [
                    'type' => 'maintenance',
                    'data' => $report,
                    'sort_date' => $report->updated_at ?? $report->created_at,
                ];
            })->sortByDesc('sort_date')->values();
        } else {
            $deliveries = $queryDeliveries->get();
            $reportItems = $reports->map(function ($report) {
                return [
                    'type' => 'maintenance',
                    'data' => $report,
                    'sort_date' => $report->updated_at ?? $report->created_at,
                ];
            });
            $deliveryItems = $deliveries->map(function ($delivery) {
                return [
                    'type' => 'warehouse',
                    'data' => $delivery,
                    'sort_date' => $delivery->updated_at ?? $delivery->created_at,
                ];
            });
            $allItems = $reportItems->concat($deliveryItems)->sortByDesc('sort_date')->values();
        }

        // تقسيم إلى صفحات (10 عناصر لكل صفحة)
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $total = $allItems->count();

        $slicedItems = $allItems->slice(($currentPage - 1) * $perPage, $perPage);

        // إنشاء pagination يدوي
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $slicedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('home', compact('paginatedItems', 'type', 'search', 'status', 'pendingCount'));
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
        ])->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
