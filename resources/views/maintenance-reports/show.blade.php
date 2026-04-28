@extends('layouts.app')

@section('content')
<div class="container" id="printable-area">
    <div class="no-print mb-3">
        <a href="{{ route('maintenance-reports.index') }}" class="btn btn-secondary">رجوع</a>
        <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
        <button onclick="window.print()" class="btn btn-primary">طباعة</button>
        @if(auth()->user()->role == 'manager' && $maintenanceReport->status == 'قيد التنفيذ')
            <form action="{{ route('maintenance.status.update', $maintenanceReport) }}" method="POST" style="display:inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="تم الإنجاز">
                <button class="btn btn-success">إنهاء الكشف</button>
            </form>
            <form action="{{ route('maintenance.status.update', $maintenanceReport) }}" method="POST" style="display:inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="تم الإلغاء">
                <button class="btn btn-danger">إلغاء الكشف</button>
            </form>
        @endif
    </div>

    <div class="card" id="report-card">
        <div class="card-header">
            <div class="text-end">
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">الجمهورية العربية السورية</h5>
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">وزارة الإعلام</h5>
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">الهيئة العامة للإذاعة والتلفزيون</h5>
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">مديرية المعلوماتية - دائرة الصيانة</h5>
            </div>
            <div class="text-center mt-3">
                <h4 style="font-size: 24px; font-weight: bold;">كشف فني 
                    <span class="badge 
                        @if($maintenanceReport->status == 'قيد التنفيذ') bg-warning
                        @elseif($maintenanceReport->status == 'تم الإنجاز') bg-success
                        @else bg-danger @endif">
                        {{ $maintenanceReport->status }}
                    </span>
                </h4>
            </div>
        </div>
        <div class="card-body">
            <!-- بيانات أساسية -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">الجهة طالبة الصيانة:</label>
                    <div class="border p-2">{{ $maintenanceReport->requesting_party }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">اسم الجهاز:</label>
                    <div class="border p-2">{{ $maintenanceReport->device_name }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">الماركة:</label>
                    <div class="border p-2">{{ $maintenanceReport->brand }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">الاسم والكنية:</label>
                    <div class="border p-2">{{ $maintenanceReport->reporter_name }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">التاريخ:</label>
                    <div class="border p-2">{{ optional($maintenanceReport->report_date)->format('Y-m-d') }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">الرقم التسلسلي:</label>
                    <div class="border p-2">{{ $maintenanceReport->serial_number ?? '-' }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">سبب العطل:</label>
                    <div class="border p-2">{{ $maintenanceReport->failure_cause ?: '-' }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label class="fw-bold">الكشف الفني الأولي:</label>
                    <div class="border p-2">{{ $maintenanceReport->initial_inspection ?: '-' }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">اسم وتوقيع الجهة الطالبة (قبل الصيانة):</label>
                    <div class="border p-2">{{ $maintenanceReport->request_party_sign_before }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">اسم وتوقيع المسؤول الفني (قبل الصيانة):</label>
                    <div class="border p-2">{{ $maintenanceReport->technician_sign_before }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">مكان تواجد الجهاز:</label>
                    <div class="border p-2">{{ $maintenanceReport->device_location ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">الإجراءات المتبعة:</label>
                    <div class="border p-2">{{ $maintenanceReport->maintenance_procedure ?: '-' }}</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">الحالة الفنية بعد الصيانة والملاحظات:</label>
                <div class="border p-3" style="min-height: 100px;">{{ $maintenanceReport->post_maintenance_notes ?: '-' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">اسم وتوقيع الجهة بعد الصيانة:</label>
                    <div class="border p-2">{{ $maintenanceReport->request_party_sign_after ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">اسم وتوقيع المسؤول الفني بعد الصيانة:</label>
                    <div class="border p-2">{{ $maintenanceReport->technician_sign_after ?: '-' }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">ر.د الصيانة والدعم الفني:</label>
                    <div class="border p-2">{{ $maintenanceReport->maintenance_head }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">مدير المعلوماتية:</label>
                    <div class="border p-2">{{ $maintenanceReport->it_manager }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.no-print { display: block; }
.card-header h5 { font-size: 18px; font-weight: bold; margin-bottom: 8px; color: #000; }
.card-header h4 { font-size: 24px; font-weight: bold; margin-bottom: 0; color: #000; }
.card-body label { font-size: 17px; font-weight: bold; margin-bottom: 0px; color: #000; }
.card-body .border { font-size: 17px; padding: 3px 12px; min-height: auto; }
.card-body .row { margin-bottom: 10px; }
.card-body h5 { font-size: 18px; font-weight: bold; margin: 10px 0; color: #000; }
@media print {
    .no-print { display: none !important; }
    .navbar { display: none !important; }
    .footer { display: none !important; }
    .container { max-width: 100% !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; margin: 0 !important; }
    .card-header { border-bottom: none !important; padding: 10px 15px !important; }
    .card-body { padding: 18px !important; }
    .border { border: none !important; padding: 3px 12px !important; min-height: auto !important; white-space: pre-wrap !important; word-wrap: break-word !important; }
    .row { margin-bottom: 10px !important; }
    .mb-3 { margin-bottom: 10px !important; }
    .p-2 { padding: 3px 12px !important; }
    .p-3 { padding: 5px 14px !important; }
    h5 { font-size: 16px !important; margin: 10px 0 !important; color: #000 !important; }
    h4 { font-size: 18px !important; margin: 8px 0 !important; color: #000 !important; }
    label { font-size: 16px !important; margin-bottom: 0px !important; color: #000 !important; }
    body { font-size: 16px !important; }
    .badge { display: none !important; }
}
.border { white-space: pre-wrap; word-wrap: break-word; }
</style>
@endsection