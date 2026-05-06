@extends('layouts.app')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');

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
.btn-dark:hover { background: #1f2937; }
.btn-success    { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.btn-success:hover { background: #dcfce7; }
.btn-info       { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.btn-info:hover    { background: #dbeafe; }

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
    margin-bottom: 24px;
}
.org-block p {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    line-height: 2.1;
    margin: 0;
}

.title-block { text-align: center; flex: 1; padding: 2px 12px 0; }
.title-block h1 {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -.5px;
    margin-bottom: 10px;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 5px 16px;
    border-radius: 20px;
}
.status-pill .dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: currentColor;
    opacity: .8;
}
.pill-pending  { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
.pill-done     { background: #f0fdf4; color: #166534; border: 1px solid #86efac; }

.spacer { width: 160px; flex-shrink: 0; }

/* ── Field rows ── */
.f-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

/* ── Individual field box ── */
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
    font-size: 16px;
    font-weight: 700;
    color: #000;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: 6px;
    white-space: nowrap;
}
.f-val {
    font-size: 17px;
    font-weight: 500;
    color: #0f172a;
    line-height: 1.45;
    min-height: 26px;
    word-break: break-word;
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
    font-size: 16px;
    font-weight: 700;
    color: #000;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: 8px;
}
.ta-val {
    font-size: 17px;
    font-weight: 400;
    color: #000;
    line-height: 1.85;
    min-height: 80px;
    white-space: pre-wrap;
    word-break: break-word;
}

/* ── Divider ── */
.rule { height: 1.5px; background: #e2e8f0; margin: 20px 0; }

/* ── Signature row ── */
.sig-row { display: flex; gap: 10px; margin-top: 4px; }
.sig-cell {
    flex: 1;
    border-radius: 7px;
    padding: 14px 16px;
    text-align: center;
}
.sig-lbl {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #000;
    letter-spacing: .04em;
    margin-bottom: 10px;
    line-height: 1.5;
}
.sig-name { font-size: 17px; font-weight: 500; color: #1e293b; margin-top: 8px; }

/* ── Meta row below sheet ── */
.meta-row {
    font-size: 12px;
    color: #94a3b8;
    max-width: 794px;
    margin: 12px auto 0;
    display: flex;
    gap: 6px 18px;
    flex-wrap: wrap;
}
.meta-row strong { color: #64748b; font-weight: 500; }

/* ══════════════════════════════
    PRINT
══════════════════════════════ */
@media print {
    @page { size: A4 portrait; margin: 1.2cm 1.5cm; }
    .no-print { 
        display: none !important; 
    }
    .action-bar,
    .meta-row,
    nav, .navbar,
    footer { display: none !important; }

    .page-wrap {
        background: #fff !important;
        padding: 0 !important;
        min-height: unset;
    }
    .a4 {
        width: 100% !important;
        min-height: unset !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .doc-header {
        margin-bottom: 16px !important;
        padding-bottom: 12px !important;
    }
    .org-block p           { font-size: 15px !important; line-height: 1.9 !important; }
    .title-block h1        { font-size: 24px !important; }

    .status-pill {
        background: #fff !important;
        color: #333 !important;
        border: 1px solid #999 !important;
        font-size: 12px !important;
    }
    .status-pill .dot { display: none !important; }

    .f-row       { gap: 8px !important; margin-bottom: 8px !important; }
    .field       { border: 1.5px solid #7a8a9a !important; border-radius: 5px !important; padding: 9px 13px !important; }
    .f-lbl       { font-size: 18px !important; margin-bottom: 5px !important; }
    .f-val       { font-size: 17px !important; }

    .ta-field    { border: 1.5px solid #7a8a9a !important; border-radius: 5px !important; padding: 10px 13px !important; }
    .ta-lbl      { font-size: 18px !important; }
    .ta-val      { font-size: 17px !important; min-height: 60px !important; }

    .rule        { background: #c8cdd3 !important; margin: 16px 0 !important; }

    .sig-row     { gap: 8px !important; }
    .sig-cell    { padding: 12px 14px !important; }
    .sig-lbl     { font-size: 18px !important; margin-bottom: 10px !important; }
    .sig-name    { font-size: 18px !important; }
}
</style>

<div class="page-wrap">

    {{-- ── Action bar (hidden on print) ── --}}
    <div class="action-bar">
        <a href="{{ route('maintenance-reports.index') }}" class="btn">← رجوع</a>
        <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
        <button onclick="window.print()" class="btn btn-dark">◫ طباعة</button>

        @if(auth()->user()->role == 'manager' && $maintenanceReport->status == 'قيد التنفيذ')
            <form action="{{ route('maintenance.status.update', $maintenanceReport) }}" method="POST" style="display:inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="تم الإنجاز">
                <button class="btn btn-success">✓ إنهاء الكشف</button>
            </form>
        @endif
    </div>

    {{-- ── A4 document ── --}}
    <div class="a4">

        {{-- Header --}}
        <div class="doc-header">
            <div class="org-block">
                <p>الجمهورية العربية السورية</p>
                <p>وزارة الإعلام</p>
                <p>الهيئة العامة للإذاعة والتلفزيون</p>
                <p>مديرية المعلوماتية — دائرة الصيانة</p>
            </div>
            <div class="title-block">
                <h1>كشف فني</h1>
                <span class="status-pill no-print
                    @if($maintenanceReport->status == 'قيد التنفيذ')  pill-pending
                    @else pill-done @endif">
                    <span class="dot"></span>{{ $maintenanceReport->status }}
                </span>
            </div>
            <div class="spacer"></div>
        </div>

        {{-- Requesting party --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">الجهة طالبة الصيانة</span>
                <div class="f-val">{{ $maintenanceReport->requesting_party ?? '—' }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">الاسم والكنية</span>
                <div class="f-val">{{ $maintenanceReport->reporter_name ?? '—' }}</div>
            </div>
        </div>

        {{-- Device --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">اسم الجهاز</span>
                <div class="f-val">{{ $maintenanceReport->device_name ?? '—' }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">الماركة</span>
                <div class="f-val">{{ $maintenanceReport->brand ?? '—' }}</div>
            </div>
        </div>

        {{-- Reporter / date / serial --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">الرقم التسلسلي</span>
                <div class="f-val">{{ $maintenanceReport->serial_number ?? '—' }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">التاريخ</span>
                <div class="f-val">{{ optional($maintenanceReport->report_date)->format('Y-m-d') ?? '—' }}</div>
            </div>
        </div>

        {{-- Initial inspection --}}
        <div class="ta-field">
            <span class="ta-lbl">الكشف الفني الأولي</span>
            <div class="ta-val">{{ $maintenanceReport->initial_inspection ?: '—' }}</div>
        </div>

        {{-- Failure + location --}}
        <div class="f-row">
            <div class="field">
                <span class="f-lbl">سبب العطل</span>
                <div class="f-val">{{ $maintenanceReport->failure_cause ?: '—' }}</div>
            </div>
            <div class="field">
                <span class="f-lbl">مكان تواجد الجهاز</span>
                <div class="f-val">{{ $maintenanceReport->device_location ?: '—' }}</div>
            </div>
        </div>

        <div class="rule"></div>

        {{-- Signatures --}}
        <div class="sig-row">
            <div class="sig-cell">
                <span class="sig-lbl">المسؤول الفني</span>
                <div class="sig-name">{{ $maintenanceReport->technical_manager ?? '—' }}</div>
            </div>
            <div class="sig-cell">
                <span class="sig-lbl">ر.د الصيانة والدعم الفني</span>
                <div class="sig-name">{{ $maintenanceReport->maintenance_head ?? '—' }}</div>
            </div>
            <div class="sig-cell">
                <span class="sig-lbl">مدير المعلوماتية</span>
                <div class="sig-name">{{ $maintenanceReport->it_manager ?? '—' }}</div>
            </div>
        </div>

    </div>{{-- /a4 --}}

    {{-- ── Meta info below sheet (hidden on print) ── --}}
    @if(auth()->user()->role == 'manager' || $maintenanceReport->created_by == auth()->id())
    <div class="meta-row">
        <span>تاريخ الإنشاء: <strong>{{ $maintenanceReport->created_at?->format('Y-m-d H:i') ?? '—' }}</strong></span>
        @if($maintenanceReport->updated_by && $maintenanceReport->created_at != $maintenanceReport->updated_at)
            <span>آخر تعديل: <strong>{{ $maintenanceReport->updated_at?->format('Y-m-d H:i') ?? '—' }}</strong></span>
            <span>تعديل بواسطة: <strong>{{ $maintenanceReport->updater?->name ?? 'غير معروف' }}</strong></span>
        @endif
        @if(auth()->user()->role == 'manager')
            <span>المنشئ: <strong>{{ $maintenanceReport->creator?->name ?? 'غير معروف' }}</strong></span>
        @endif
    </div>
    @endif

</div>{{-- /page-wrap --}}

@endsection