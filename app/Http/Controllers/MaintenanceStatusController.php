<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use Illuminate\Http\Request;

class MaintenanceStatusController extends Controller
{
    // ============================================================
    // تحديث حالة الكشف الفني (إنهاء أو إلغاء)
    // ============================================================
    public function update(Request $request, MaintenanceReport $report)
    {
        $request->validate([
            'status' => 'required|in:تم الإنجاز,تم الإلغاء',
        ]);

        $report->status = $request->status;
        $report->status_changed_by = auth()->id();
        $report->status_changed_at = now();
        $report->save();

        return back()->with('success', 'تم تغيير حالة الكشف إلى ' . $request->status);
    }
}