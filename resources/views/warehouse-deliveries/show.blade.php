@extends('layouts.app')

@section('content')

<style>
@import url('https://googleapis.com');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.page-wrap {
    font-family: 'Tajawal', sans-serif;
    direction: rtl;
    background: #e8eaed;
    padding: 20px 16px 48px;
    min-height: 100vh;
}

/* ── Action bar ── */
.action-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    max-width: 794px;
    margin: 0 auto 14px;
}
.btn {
    font-family: 'Tajawal', sans-serif;
    font-size: 13px;
    font-weight: 500;
    padding: 7px 18px;
    border-radius: 7px;
    border: 1px solid #d1d5db;
    cursor: pointer;
    background: #fff;
    color: #374151;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1;
    transition: background .15s;
}
.btn:hover      { background: #f3f4f6; }
.btn-dark       { background: #111827; color: #fff; border-color: #111827; }
.btn-info       { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }

/* ── A4 sheet ── */
.a4 {
    width: 794px;
    min-height: 1123px;
    margin: 0 auto;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,.18);
    padding: 40px 44px 50px;
    display: flex;
    flex-direction: column;
}

/* ── Document header ── */
.doc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding-bottom: 16px;
    border-bottom: 2.5px solid #0f172a;
    margin-bottom: 24px;
}
.org-block p {
    font-size: 14px;
    font-weight: 500;
    color: #0f172a;
    line-height: 1.8;
    margin: 0;
}
.org-block p:first-child { font-size: 15px; font-weight: 700; }

.title-block { text-align: center; flex: 1; padding: 2px 12px 0; }
.title-block h1 {
    font-size: 26px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -.5px;
    margin-bottom: 10px;
    margin-top: 20px;
}

/* ── Field rows ── */
.f-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}
.field {
    flex: 1;
    min-width: 0;
    border: 1.5px solid #94a3b8;
    border-radius: 7px;
    padding: 11px 16px 10px;
    background: #fff;
}
.f-lbl {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #000;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 6px;
    white-space: nowrap;
}
.f-val {
    font-size: 18px;
    font-weight: 500;
    color: #0f172a;
    line-height: 1.45;
    min-height: 24px;
}

/* ── Textarea field ── */
.ta-field {
    border: 1.5px solid #94a3b8;
    border-radius: 7px;
    padding: 12px 16px;
    background: #f8fafc;
    margin-bottom: 10px;
}
.ta-lbl {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #000;
    margin-bottom: 8px;
}
.ta-val {
    font-size: 18px;
    font-weight: 400;
    color: #000;
    line-height: 1.8;
    min-height: 80px;
    white-space: pre-wrap;
}

.rule { height: 1.5px; background: #e2e8f0; margin: 20px 0; }

.sig-row { display: flex; gap: 10px; margin-top: 10px; }
.sig-cell {
    flex: 1;
    /* border: 1.5px solid #94a3b8; */
    border-radius: 7px;
    padding: 14px 16px;
    text-align: center;
}
.sig-lbl {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #000;
    margin-bottom: 15px;
}
.sig-line { border-bottom: 1px solid #94a3b8; margin: 0 10px; }
.sig-name { font-size: 18px; font-weight: 500; color: #1e293b; margin-top: 8px; }

/* ── Print Settings ── */
@media print {
    @page { size: A4 portrait; margin: 1.2cm; }
    .action-bar, nav, footer, .no-print { display: none !important; }
    .page-wrap { background: #fff !important; padding: 0 !important; }
    .a4 { width: 100% !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; }
    .doc-header { border-bottom: 2px solid #000 !important; }
    .field, .ta-field, .sig-cell { border-color: #000 !important; }
}
</style>

<div class="page-wrap">

    <div class="action-bar no-print">
        <a href="{{ route('warehouse-deliveries.index') }}" class="btn">← رجوع</a>
        <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
        <button onclick="window.print()" class="btn btn-dark">◫ طباعة الكشف</button>
    </div>

    <div class="a4">

        {{-- الهيدر الرسمي --}}
        <div class="doc-header">
            <div class="org-block">
                <p>الجمهورية العربية السورية</p>
                <p>وزارة الإعلام</p>
                <p>الهيئة العامة للإذاعة والتلفزيون</p>
                <p>مديرية المعلوماتية - دائرة الصيانة</p>
            </div>

            <div class="title-block">
                <h1>كشف تسليم مستودع</h1>
            </div>
            
            <div style="width: 180px;"></div> {{-- موازن للهيدر --}}
        </div>
{{-- الجهة طالبة الصيانة --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">الجهة طالبة الصيانة</span>
                <div class="f-val">{{ $warehouseDelivery->requesting_party }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">التاريخ</span>
                <div class="f-val">{{ $warehouseDelivery->date }}</div>
            </div>
        </div>  
       
        {{-- بيانات الجهاز --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">نوع الجهاز</span>
                <div class="f-val">{{ $warehouseDelivery->device_type ?? '-' }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">الماركة</span>
                <div class="f-val">{{ $warehouseDelivery->brand ?? '-' }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">S/N الرقم التسلسلي</span>
                <div class="f-val">{{ $warehouseDelivery->serial_number ?? '-' }}</div>
            </div>
        </div>

        {{-- الوصف --}}
        <div class="ta-field">
            <span class="ta-lbl">الوصف التفصيلي:</span>
            <div class="ta-val">{{ $warehouseDelivery->description }}</div>
        </div>

        {{-- حالة الجهاز --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">حالة الجهاز</span>
                <div class="f-val">{{ $warehouseDelivery->device_status ?? '-' }}</div>
            </div>
        </div>

        <div class="rule"></div>

        {{-- التوقيعات --}}
        <div class="sig-row">
            <div class="sig-cell">
                <span class="sig-lbl">تم الفحص من قبل</span>
                <!-- <div class="sig-line"></div> -->
                <div class="sig-name">{{ $warehouseDelivery->checked_by }}</div>
            </div>
            <div class="sig-cell">
                <span class="sig-lbl">مدير الصيانة والدعم الفني</span>
                <!-- <div class="sig-line"></div> -->
                <div class="sig-name">{{ $warehouseDelivery->maintenance_manager }}</div>
            </div>
            <div class="sig-cell">
                <span class="sig-lbl">مدير المعلوماتية</span>
                <!-- <div class="sig-line"></div> -->
                <div class="sig-name">{{ $warehouseDelivery->it_manager }}</div>
            </div>
        </div>

    </div> {{-- نهاية A4 --}}

</div>

@endsection
