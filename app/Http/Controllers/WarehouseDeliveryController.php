<?php

namespace App\Http\Controllers;

use App\Models\WarehouseDelivery;
use Illuminate\Http\Request;

class WarehouseDeliveryController extends Controller
{
    // ============================================================
    // عرض قائمة تسليمات المستودع
    // ============================================================
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = WarehouseDelivery::latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('requesting_party', 'like', "%{$search}%")
                  ->orWhere('device_type', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $deliveries = $query->paginate(15);
        return view('warehouse-deliveries.index', compact('deliveries'));
    }

    // ============================================================
    // عرض نموذج إنشاء تسليم مستودع جديد
    // ============================================================
    public function create()
    {
        return view('warehouse-deliveries.create');
    }

    // ============================================================
    // حفظ تسليم مستودع جديد
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requesting_party' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'checked_by' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'maintenance_manager' => 'nullable|string|max:255',
            'it_manager' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['date'] = $validated['date'] ?? now()->format('Y-m-d');

        WarehouseDelivery::create($validated);
        return redirect()->route('home')->with('success', 'تم إضافة تسليم المستودع بنجاح');
    }

    // ============================================================
    // عرض تفاصيل تسليم المستودع
    // ============================================================
    public function show(WarehouseDelivery $warehouseDelivery)
    {
        return view('warehouse-deliveries.show', compact('warehouseDelivery'));
    }

    // ============================================================
    // عرض نموذج تعديل تسليم المستودع
    // ============================================================
    public function edit(WarehouseDelivery $warehouseDelivery)
    {
        if (auth()->user()->role === 'employee' && $warehouseDelivery->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك تعديل هذا السجل');
        }
        return view('warehouse-deliveries.edit', compact('warehouseDelivery'));
    }

    // ============================================================
    // تحديث تسليم المستودع
    // ============================================================
    public function update(Request $request, WarehouseDelivery $warehouseDelivery)
    {
        if (auth()->user()->role === 'employee' && $warehouseDelivery->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك تحديث هذا السجل');
        }
        $validated = $request->validate([
            'requesting_party' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'checked_by' => 'required|string|max:255',
            'date' => 'nullable|date',
            'maintenance_manager' => 'nullable|string|max:255',
            'it_manager' => 'nullable|string|max:255',
        ]);

        $validated['updated_by'] = auth()->id();
        $warehouseDelivery->update($validated);
        return redirect()->route('warehouse-deliveries.index')->with('success', 'تم التحديث');
    }

    // ============================================================
    // حذف تسليم المستودع
    // ============================================================
    public function destroy(WarehouseDelivery $warehouseDelivery)
    {
        if (auth()->user()->role === 'employee' && $warehouseDelivery->created_by !== auth()->id()) {
            abort(403, 'لا يمكنك حذف هذا السجل');
        }
        $warehouseDelivery->delete();
        return redirect()->route('warehouse-deliveries.index')->with('success', 'تم الحذف');
    }
}