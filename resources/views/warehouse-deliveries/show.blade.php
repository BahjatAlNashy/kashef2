@extends('layouts.app')

@section('content')
<div class="container" id="printable-area">

    <!-- أزرار الإجراءات (تُخفى عند الطباعة) -->
    <div class="no-print mb-3">
        <a href="{{ route('warehouse-deliveries.index') }}" class="btn btn-secondary">رجوع</a>
        <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
        <button onclick="window.print()" class="btn btn-primary">طباعة</button>
    </div>

    <!-- معلومات الإنشاء والتعديل (للمدير أو صاحب الكشف) - في الأعلى -->
    @if(auth()->user()->role == 'manager' || $warehouseDelivery->created_by == auth()->id())
    <div class="no-print mb-2" style="text-align: right; font-size: 12px; color: #6c757d;">
        <span>تاريخ الإنشاء: {{ $warehouseDelivery->created_at?->format('Y-m-d H:i') ?? '-' }}</span>
        @if($warehouseDelivery->updated_by && $warehouseDelivery->created_at != $warehouseDelivery->updated_at)
        <span class="mx-2">|</span>
        <span>تاريخ آخر تعديل: {{ $warehouseDelivery->updated_at?->format('Y-m-d H:i') ?? '-' }}</span>
        <span class="mx-2">|</span>
        <span>آخر تعديل بواسطة: <strong>{{ $warehouseDelivery->updater?->name ?? 'غير معروف' }}</strong></span>
        @endif
        @if(auth()->user()->role == 'manager')
        <br>
        <span>المنشئ: <strong>{{ $warehouseDelivery->creator?->name ?? 'غير معروف' }}</strong></span>
        @endif
    </div>
    @endif

    <!-- بطاقة الكشف -->
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
                <h4 style="font-size: 24px; font-weight: bold;">كشف تسليم مستودع</h4>
            </div>
        </div>

        <div class="card-body">
            <!-- بيانات الجهاز -->
             <div class="row mb-3">
    <div class="col-md-4">
        <label class="fw-bold">الجهة طالبة الصيانة:</label>
        <div class="p-2">{{ $warehouseDelivery->requesting_party }}</div>
    </div>
</div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">نوع الجهاز:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->device_type ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">الماركة:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->brand ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">الرقم التسلسلي:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->serial_number ?? '-' }}</div>
                </div>
            </div>

            <!-- الوصف (مطابق لـ textarea في الإنشاء) -->
            <div class="mb-3">
                <label class="fw-bold">الوصف:</label>
                <div class="form-control-static" style="min-height: calc(1.5em * 3 + 0.75rem * 2); padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem;">{{ $warehouseDelivery->description }}</div>
            </div>

            <!-- حالة الجهاز -->
            <div class="mb-3">
                <label class="fw-bold">حالة الجهاز:</label>
                <div class="p-2 bg-light">{{ $warehouseDelivery->device_status ?? '-' }}</div>
            </div>

            <!-- التاريخ والفحص -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">التاريخ:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->date }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">تم الفحص من قبل:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->checked_by }}</div>
                </div>
            </div>

            <!-- الاعتمادات -->
            <div class="row">
                <div class="col-md-6">
                    <label class="fw-bold">مدير الصيانة والدعم الفني:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->maintenance_manager }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">مدير المعلوماتية:</label>
                    <div class="p-2 bg-light">{{ $warehouseDelivery->it_manager }}</div>
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
        background-color: transparent !important;
    }
    .bg-light { background-color: transparent !important; }
    .mb-3 { margin-bottom: 8px !important; }
    .p-2 { padding: 4px 8px !important; }
    .p-3 { padding: 4px 8px !important; }
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
    #printable-area { width: 100% !important; max-width: 100% !important; }
}
</style>
@endsection
