@extends('layouts.app')

@section('content')
<div class="container">
    <h2>بحث</h2>
    <form method="GET" action="{{ route('search') }}" class="row g-3 mb-4">
        <div class="col-md-5">
            <input type="text" name="serial_number" class="form-control" placeholder="الرقم التسلسلي" value="{{ $serial ?? '' }}">
        </div>
        <div class="col-md-5">
            <input type="text" name="requesting_party" class="form-control" placeholder="الجهة الطالبة" value="{{ $party ?? '' }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">بحث</button>
        </div>
    </form>

    @if(isset($warehouseResults) && $warehouseResults->count() > 0)
        <h4>نتائج تسليم المستودع</h4>
        <table class="table table-bordered">
            <thead><tr><th>الجهة</th><th>الرقم التسلسلي</th><th>النوع</th><th>التاريخ</th><th>عرض</th></tr></thead>
            <tbody>
            @foreach($warehouseResults as $item)
                <tr>
                    <td>{{ $item->requesting_party }}</td>
                    <td>{{ $item->serial_number }}</td>
                    <td>{{ $item->device_type }}</td>
                    <td>{{ $item->date }}</td>
                    <td><a href="{{ route('warehouse-deliveries.show', $item) }}" class="btn btn-sm btn-info">عرض</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($maintenanceResults) && $maintenanceResults->count() > 0)
        <h4>نتائج الكشوفات الفنية</h4>
        <table class="table table-bordered">
            <thead><tr><th>الجهة</th><th>التسلسلي</th><th>الجهاز</th><th>الحالة</th><th>عرض</th></tr></thead>
            <tbody>
            @foreach($maintenanceResults as $item)
                <tr>
                    <td>{{ $item->requesting_party }}</td>
                    <td>{{ $item->serial_number }}</td>
                    <td>{{ $item->device_name }}</td>
                    <td>{{ $item->status }}</td>
                    <td><a href="{{ route('maintenance-reports.show', $item) }}" class="btn btn-sm btn-info">عرض</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if( (isset($warehouseResults) && $warehouseResults->count()==0) && (isset($maintenanceResults) && $maintenanceResults->count()==0) && ($serial || $party) )
        <div class="alert alert-warning">لا توجد نتائج</div>
    @endif
</div>
@endsection