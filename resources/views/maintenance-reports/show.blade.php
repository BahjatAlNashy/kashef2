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

    <!-- معلومات الإنشاء والتعديل (للمدير أو صاحب الكشف) - في الأعلى -->
    @if(auth()->user()->role == 'manager' || $maintenanceReport->created_by == auth()->id())
    <div class="no-print mb-2" style="text-align: right; font-size: 12px; color: #6c757d;">
        <span>تاريخ الإنشاء: {{ $maintenanceReport->created_at?->format('Y-m-d H:i') ?? '-' }}</span>
        @if($maintenanceReport->updated_by && $maintenanceReport->created_at != $maintenanceReport->updated_at)
        <span class="mx-2">|</span>
        <span>تاريخ آخر تعديل: {{ $maintenanceReport->updated_at?->format('Y-m-d H:i') ?? '-' }}</span>
        <span class="mx-2">|</span>
        <span>آخر تعديل بواسطة: <strong>{{ $maintenanceReport->updater?->name ?? 'غير معروف' }}</strong></span>
        @endif
        @if(auth()->user()->role == 'manager')
        <br>
        <span>المنشئ: <strong>{{ $maintenanceReport->creator?->name ?? 'غير معروف' }}</strong></span>
        @endif
    </div>
    @endif

    <div class="card" id="report-card">
        <div class="card-header" style="position: relative;">
            <!-- العنوان في الزاوية اليمنى العليا -->
            <div class="header-title" style="position: absolute; top: 10px; right: 15px; text-align: right;">
                <h5 style="font-size: 14px; font-weight: bold; margin-bottom: 3px;">الجمهورية العربية السورية</h5>
                <h5 style="font-size: 14px; font-weight: bold; margin-bottom: 3px;">وزارة الإعلام</h5>
                <h5 style="font-size: 14px; font-weight: bold; margin-bottom: 3px;">الهيئة العامة للإذاعة والتلفزيون</h5>
                <h5 style="font-size: 14px; font-weight: bold; margin-bottom: 3px;">مديرية المعلوماتية - دائرة الصيانة</h5>
            </div>
            <div class="text-center" style="padding-top: 80px;">
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
                <div class="col-md-6">
                    <label class="fw-bold">اسم الجهاز:</label>
                    <div class="border p-2">{{ $maintenanceReport->device_name }}</div>
                </div>
                <div class="col-md-6">
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

            <!-- الكشف الفني الأولي (مطابق لـ textarea في الإنشاء) -->
            <div class="mb-3">
                <label class="fw-bold">الكشف الفني الأولي:</label>
                <div class="form-control-static" style="min-height: calc(1.5em * 3 + 0.75rem * 2); padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem;">{{ $maintenanceReport->initial_inspection ?: '-' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">مكان تواجد الجهاز:</label>
                    <div class="border p-2">{{ $maintenanceReport->device_location ?: '-' }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">المسؤول الفني:</label>
                    <div class="border p-2">{{ $maintenanceReport->technical_manager }}</div>
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
.card-body label { font-size: 17px; font-weight: bold; margin-bottom: 0px; color: #000; display: block; }
.card-body .border { font-size: 17px; padding: 3px 12px; min-height: auto; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; }
.card-body .row { margin-bottom: 10px; display: flex; flex-wrap: wrap; }
.card-body h5 { font-size: 18px; font-weight: bold; margin: 10px 0; color: #000; }

@media print {
    * { box-sizing: border-box; }
    .no-print { display: none !important; }
    .navbar { display: none !important; }
    .footer { display: none !important; }
    .container { max-width: 100% !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .card { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; }
    .card-header { border-bottom: none !important; padding: 10px 15px !important; text-align: center !important; position: relative !important; }
    .card-header .text-end { text-align: center !important; }
    .card-header .header-title {
        position: absolute !important;
        top: 5px !important;
        right: 10px !important;
        text-align: right !important;
    }
    .card-header .header-title h5 {
        font-size: 11px !important;
        margin-bottom: 2px !important;
    }
    .card-body { padding: 10px 15px !important; width: 100% !important; padding-top: 25px !important; }
    .row { display: flex !important; flex-wrap: wrap !important; margin: 0 -5px !important; margin-bottom: 8px !important; width: 100% !important; }
    .col-md-4, .col-md-6, .col-12 {
        display: inline-block !important;
        vertical-align: top !important;
        padding: 0 5px !important;
        margin-bottom: 5px !important;
    }
    .col-md-4 { width: 33.333% !important; }
    .col-md-6 { width: 50% !important; }
    .col-12 { width: 100% !important; }
    .border {
        border: 1px solid #ddd !important;
        padding: 4px 8px !important;
        min-height: auto !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        font-size: 14px !important;
    }
    .mb-3 { margin-bottom: 8px !important; }
    .p-2 { padding: 4px 8px !important; }
    h5 { font-size: 14px !important; margin: 5px 0 !important; color: #000 !important; }
    h4 { font-size: 16px !important; margin: 5px 0 !important; color: #000 !important; }
    label {
        font-size: 13px !important;
        margin-bottom: 2px !important;
        color: #000 !important;
        font-weight: bold !important;
        display: block !important;
    }
    body { font-size: 14px !important; direction: rtl !important; }
    .badge { display: none !important; }
    #printable-area { width: 100% !important; max-width: 100% !important; }
}
.border { white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; }
</style>
@endsection