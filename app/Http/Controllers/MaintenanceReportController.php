<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use Illuminate\Http\Request;

class MaintenanceReportController extends Controller
{
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

    public function create()
    {
        return view('maintenance-reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requesting_party' => 'required|string|max:255',
            'reporter_name' => 'required|string|max:255',
            'report_date' => 'nullable|date',
            'device_name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:maintenance_reports',
            'initial_inspection' => 'nullable|string',
            'failure_cause' => 'nullable|in:طبيعي,سوء استخدام,غير ذلك',
            'request_party_sign_before' => 'required|string|max:255',
            'technician_sign_before' => 'required|string|max:255',
            'device_location' => 'nullable|in:لدى صاحب العلاقة,في دائرة الصيانة,في الصيانة الخارجية (لجنة الشراء)',
            'maintenance_procedure' => 'nullable|in:الاستلام من المستودع,في الصيانة الخارجية',
            'post_maintenance_notes' => 'nullable|string',
            'request_party_sign_after' => 'nullable|string|max:255',
            'technician_sign_after' => 'nullable|string|max:255',
            'maintenance_head' => 'required|string|max:255',
            'it_manager' => 'required|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'قيد التنفيذ';
        $validated['report_date'] = $validated['report_date'] ?? now()->format('Y-m-d');

        MaintenanceReport::create($validated);
        return redirect()->route('home')->with('success', 'تم إنشاء الكشف الفني');
    }

    public function show(MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك الوصول لهذا الكشف');
        }
        return view('maintenance-reports.show', compact('maintenanceReport'));
    }

    public function edit(MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك تعديل هذا الكشف');
        }
        return view('maintenance-reports.edit', compact('maintenanceReport'));
    }

    public function update(Request $request, MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك تحديث هذا الكشف');
        }
        $validated = $request->validate([
            'requesting_party' => 'required|string|max:255',
            'reporter_name' => 'required|string|max:255',
            'report_date' => 'required|date',
            'device_name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:maintenance_reports,serial_number,'.$maintenanceReport->id,
            'initial_inspection' => 'nullable|string',
            'failure_cause' => 'nullable|in:طبيعي,سوء استخدام,غير ذلك',
            'request_party_sign_before' => 'required|string|max:255',
            'technician_sign_before' => 'required|string|max:255',
            'device_location' => 'nullable|in:لدى صاحب العلاقة,في دائرة الصيانة,في الصيانة الخارجية (لجنة الشراء)',
            'maintenance_procedure' => 'nullable|in:الاستلام من المستودع,في الصيانة الخارجية',
            'post_maintenance_notes' => 'nullable|string',
            'request_party_sign_after' => 'nullable|string|max:255',
            'technician_sign_after' => 'nullable|string|max:255',
            'maintenance_head' => 'required|string|max:255',
            'it_manager' => 'required|string|max:255',
        ]);

        $maintenanceReport->update($validated);
        return redirect()->route('maintenance-reports.index')->with('success', 'تم التحديث');
    }

    public function destroy(MaintenanceReport $maintenanceReport)
    {
        if (auth()->user()->role === 'employee' && $maintenanceReport->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك حذف هذا الكشف');
        }
        $maintenanceReport->delete();
        return redirect()->route('maintenance-reports.index')->with('success', 'تم الحذف');
    }
}