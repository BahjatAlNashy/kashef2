@extends('layouts.app')

@section('content')
<div class="container" id="printable-area">
    <!-- Action Buttons -->
    <div class="no-print mb-3">
        <a href="{{ route('warehouse-deliveries.index') }}" class="btn btn-secondary">رجوع</a>
        <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
        <button onclick="window.print()" class="btn btn-primary">طباعة</button>
    </div>

    <!-- Report Card -->
    <div class="card" id="report-card">
        <div class="card-header">
            <div class="text-end">
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">الجمهورية العربية السورية</h5>
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">وزارة الإعلام</h5>
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">الهيئة العامة للإذاعة والتلفزيون</h5>
                <h5 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">مديرية المعلوماتية - دائرة الصيانة</h5>
            </div>
            <div class="text-center mt-3">
                <h4 style="font-size: 24px; font-weight: bold;">كشف تسليم مستودع</h4>
            </div>
        </div>
        <div class="card-body">
            <!-- Device Information -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">نوع الجهاز:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->device_type }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">الرقم التسلسلي:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->serial_number ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold">الجهة الطالبة:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->requesting_party }}</div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label class="fw-bold">الوصف:</label>
                <div class="border p-3 bg-light" style="min-height: 100px;">{{ $warehouseDelivery->description }}</div>
            </div>

            <!-- Date and Checker -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">التاريخ:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->date }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">تم الفحص من قبل:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->checked_by }}</div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="row">
                <div class="col-md-6">
                    <label class="fw-bold">مدير الصيانة والدعم الفني:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->maintenance_manager }}</div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">مدير المعلوماتية:</label>
                    <div class="border p-2 bg-light">{{ $warehouseDelivery->it_manager }}</div>
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
.card-body .border { font-size: 17px; padding: 3px 12px; min-height: auto; white-space: pre-wrap; word-wrap: break-word; }
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
}
</style>
@endsection
