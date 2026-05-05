@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center mb-4">تعديل الكشف الفني</h2>

    <!-- عرض أخطاء التحقق -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('maintenance-reports.update', $maintenanceReport) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <!-- الصف الأول: الجهة طالبة الصيانة والاسم -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">الجهة طالبة الصيانة:</label>
                        <input type="text" name="requesting_party" value="{{ $maintenanceReport->requesting_party }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">الاسم والكنية:</label>
                        <input type="text" name="reporter_name" value="{{ $maintenanceReport->reporter_name }}" class="form-control">
                    </div>
                </div>

                <!-- الصف الثاني: بيانات الجهاز -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">اسم الجهاز:</label>
                        <input type="text" name="device_name" value="{{ $maintenanceReport->device_name }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">الماركة:</label>
                        <input type="text" name="brand" value="{{ $maintenanceReport->brand }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">الرقم التسلسلي:</label>
                        <input type="text" name="serial_number" value="{{ $maintenanceReport->serial_number }}" class="form-control">
                    </div>
                </div>

                <!-- الصف الثالث: التاريخ وسبب العطل -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">تاريخ الإبلاغ:</label>
                        <input type="date" name="report_date" value="{{ optional($maintenanceReport->report_date)->format('Y-m-d') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">سبب العطل:</label>
                        <select name="failure_cause" class="form-control">
                            <option value="">-- اختر --</option>
                            <option value="طبيعي" {{ $maintenanceReport->failure_cause == 'طبيعي' ? 'selected' : '' }}>طبيعي</option>
                            <option value="سوء استخدام" {{ $maintenanceReport->failure_cause == 'سوء استخدام' ? 'selected' : '' }}>سوء استخدام</option>
                            <option value="غير ذلك" {{ $maintenanceReport->failure_cause == 'غير ذلك' ? 'selected' : '' }}>غير ذلك</option>
                        </select>
                    </div>
                </div>

                <!-- الكشف الفني الأولي -->
                <div class="mb-3">
                    <label class="fw-bold">الكشف الفني الأولي:</label>
                    <textarea name="initial_inspection" class="form-control" rows="3">{{ $maintenanceReport->initial_inspection }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">مكان تواجد الجهاز:</label>
                        <select name="device_location" class="form-control">
                            <option value="">-- اختر --</option>
                            <option value="لدى صاحب العلاقة" {{ $maintenanceReport->device_location == 'لدى صاحب العلاقة' ? 'selected' : '' }}>لدى صاحب العلاقة</option>
                            <option value="في دائرة الصيانة" {{ $maintenanceReport->device_location == 'في دائرة الصيانة' ? 'selected' : '' }}>في دائرة الصيانة</option>
                            <option value="في الصيانة الخارجية (لجنة الشراء)" {{ $maintenanceReport->device_location == 'في الصيانة الخارجية (لجنة الشراء)' ? 'selected' : '' }}>في الصيانة الخارجية (لجنة الشراء)</option>
                        </select>
                    </div>
                </div>

                <!-- الاعتمادات -->
                <hr class="my-4">
                <h5 class="fw-bold">الاعتمادات</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">المسؤول الفني:</label>
                        <input type="text" name="technical_manager" value="{{ $maintenanceReport->technical_manager }}" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">ر.د الصيانة والدعم الفني:</label>
                        <input type="text" name="maintenance_head" value="{{ $maintenanceReport->maintenance_head }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">مدير المعلوماتية:</label>
                        <input type="text" name="it_manager" value="{{ $maintenanceReport->it_manager }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('maintenance-reports.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
            <button type="submit" class="btn btn-success">تحديث الكشف</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            if (event.target.tagName !== 'TEXTAREA') {
                event.preventDefault(); // منع الحفظ

                // البحث عن جميع العناصر التي يمكن التركيز عليها (Inputs, Selects)
                const form = event.target.form;
                const index = Array.prototype.indexOf.call(form, event.target);
                
                // الانتقال إلى العنصر التالي إذا وجد
                if (form.elements[index + 1]) {
                    form.elements[index + 1].focus();
                }
            }
        }
    });
</script>
@endsection