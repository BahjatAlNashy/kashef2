@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center mb-4">كشف فني (خاص لتسليم المستودع)</h2>
    <form method="POST" action="{{ route('warehouse-deliveries.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">نوع الجهاز:</label>
                        <input type="text" name="device_type" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">الرقم التسلسلي:</label>
                        <input type="text" name="serial_number" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">الجهة الطالبة:</label>
                        <input type="text" name="requesting_party" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">الوصف:</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">التاريخ:</label>
                        <input type="date" name="date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">تم الفحص من قبل:</label>
                        <input type="text" name="checked_by" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">مدير الصيانة والدعم الفني:</label>
                        <input type="text" name="maintenance_manager" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">مدير المعلوماتية:</label>
                        <input type="text" name="it_manager" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('warehouse-deliveries.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
            <button type="submit" class="btn btn-success">حفظ</button>
        </div>
    </form>
</div>
@endsection
