@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center mb-4">إنشاء كشف فني جديد</h2>

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

    <form method="POST" action="{{ route('maintenance-reports.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <!-- الصف الأول: 3 حقول -->
                 <div class="row mb-3">
                     <div class="col-md-4">
                        <label class="fw-bold">الجهة طالبة الصيانة:</label>
                        <input type="text" name="requesting_party" class="form-control" required>
                    </div>    
                    </div>
                <div class="row mb-3">
                  
                    <div class="col-md-4">
                        <label class="fw-bold">الاسم والكنية:</label>
                        <input type="text" name="reporter_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">اسم الجهاز:</label>
                        <input type="text" name="device_name" class="form-control">
                    </div>
                </div>

                <!-- الصف الثاني: 3 حقول -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">الماركة:</label>
                        <input type="text" name="brand" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">الرقم التسلسلي:</label>
                        <input type="text" name="serial_number" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">تاريخ الإبلاغ:</label>
                        <input type="date" name="report_date" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">سبب العطل:</label>
                        <select name="failure_cause" class="form-control">
                            <option value="">-- اختر --</option>
                            <option value="طبيعي">طبيعي</option>
                            <option value="سوء استخدام">سوء استخدام</option>
                            <option value="غير ذلك">غير ذلك</option>
                        </select>
                    </div>
                </div>

                <!-- وصف (يشبه حقل الوصف في تسليم المستودع) -->
                <div class="mb-3">
                    <label class="fw-bold">الكشف الفني الأولي:</label>
                    <textarea name="initial_inspection" class="form-control" rows="3"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">مكان تواجد الجهاز:</label>
                        <select name="device_location" class="form-control">
                            <option value="">-- اختر --</option>
                            <option value="لدى صاحب العلاقة">لدى صاحب العلاقة</option>
                            <option value="في دائرة الصيانة">في دائرة الصيانة</option>
                            <option value="في الصيانة الخارجية (لجنة الشراء)">في الصيانة الخارجية (لجنة الشراء)</option>
                        </select>
                    </div>
                </div>

                <!-- الاعتمادات (صف المديرين) -->
                <hr class="my-4">
                <h5 class="fw-bold">الاعتمادات</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">المسؤول الفني:</label>
                        <input type="text" name="technical_manager" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">ر.د الصيانة والدعم الفني:</label>
                        <input type="text" name="maintenance_head" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">مدير المعلوماتية:</label>
                        <input type="text" name="it_manager" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('maintenance-reports.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
            <button type="submit" class="btn btn-success">حفظ الكشف</button>
        </div>
    </form>
</div>
@endsection