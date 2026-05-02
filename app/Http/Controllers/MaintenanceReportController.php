<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use Illuminate\Http\Request;

class MaintenanceReportController extends Controller
{
    // ============================================================
    // عرض قائمة الكشوفات الفنية
    // ============================================================
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = MaintenanceReport::with('creator')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('requesting_party', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate(15);
        return view('maintenance-reports.index', compact('reports'));
    }

    // ============================================================
    // عرض نموذج إنشاء كشف جديد
    // ============================================================
    public function create()
    {
        return view('maintenance-reports.create');
    }

    // ============================================================
    // حفظ كشف فني جديد
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requesting_party' => 'required|string|max:255',
            'reporter_name' => 'nullable|string|max:255',
            'report_date' => 'nullable|date',
            'device_name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'initial_inspection' => 'nullable|string',
            'failure_cause' => 'nullable|in:طبيعي,سوء استخدام,غير ذلك',
            'device_location' => 'nullable|in:لدى صاحب العلاقة,في دائرة الصيانة,في الصيانة الخارجية (لجنة الشراء)',
            'technical_manager' => 'nullable|string|max:255',
            'maintenance_head' => 'nullable|string|max:255',
            'it_manager' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'قيد التنفيذ';
        $validated['report_date'] = $validated['report_date'] ?? now()->format('Y-m-d');

        MaintenanceReport::create($validated);
        return redirect()->route('home')->with('success', 'تم إنشاء الكشف الفني');
    }

    // ============================================================
    // عرض تفاصيل كشف فني
    // ============================================================
    public function show(MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك الوصول لهذا الكشف');
        }
        return view('maintenance-reports.show', compact('maintenanceReport'));
    }

    // ============================================================
    // عرض نموذج تعديل كشف فني
    // ============================================================
    public function edit(MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك تعديل هذا الكشف');
        }
        return view('maintenance-reports.edit', compact('maintenanceReport'));
    }

    // ============================================================
    // تحديث كشف فني
    // ============================================================
    public function update(Request $request, MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك تحديث هذا الكشف');
        }
        $validated = $request->validate([
            'requesting_party' => 'required|string|max:255',
            'reporter_name' => 'nullable|string|max:255',
            'report_date' => 'nullable|date',
            'device_name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'initial_inspection' => 'nullable|string',
            'failure_cause' => 'nullable|in:طبيعي,سوء استخدام,غير ذلك',
            'device_location' => 'nullable|in:لدى صاحب العلاقة,في دائرة الصيانة,في الصيانة الخارجية (لجنة الشراء)',
            'technical_manager' => 'nullable|string|max:255',
            'maintenance_head' => 'nullable|string|max:255',
            'it_manager' => 'nullable|string|max:255',
        ]);

        $validated['updated_by'] = auth()->id();
        $maintenanceReport->update($validated);
        return redirect()->route('maintenance-reports.index')->with('success', 'تم التحديث');
    }

    // ============================================================
    // حذف كشف فني
    // ============================================================
    public function destroy(MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك حذف هذا الكشف');
        }
        $maintenanceReport->delete();
        return redirect()->route('maintenance-reports.index')->with('success', 'تم الحذف');
    }
}