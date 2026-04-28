@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>لوحة التحكم - نظام إدارة الصيانة والمستودع</h3>
                        <div class="d-flex gap-2">
                            @if(auth()->user()->role == 'manager')
                                <button onclick="filterByStatus('all')" class="btn btn-secondary">الكل</button>
                                <button onclick="filterByStatus('قيد التنفيذ')" class="btn btn-warning">قيد التنفيذ ({{ $reports->where('status', 'قيد التنفيذ')->count() }})</button>
                                <button onclick="filterByStatus('completed')" class="btn btn-success">تم الإنجاز والإلغاء ({{ $reports->whereIn('status', ['تم الإنجاز', 'تم الإلغاء'])->count() }})</button>
                            @endif
                            <a href="{{ route('maintenance-reports.create') }}" class="btn btn-success">إنشاء كشف فني</a>
                            <a href="{{ route('warehouse-deliveries.create') }}" class="btn btn-primary">إنشاء تسليم مستودع</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- جدول جميع الكشوف -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>جميع الكشوف</h5>
                            <div class="d-flex gap-2">
                                <select id="filter-type" class="form-control" style="width: 200px;" onchange="filterReports()">
                                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="maintenance" {{ $type == 'maintenance' ? 'selected' : '' }}>الكشوفات الفنية</option>
                                    <option value="warehouse" {{ $type == 'warehouse' ? 'selected' : '' }}>تسليم المستودع</option>
                                </select>
                                <input type="text" id="search-input" class="form-control" style="width: 250px;" placeholder="بحث...">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>النوع</th>
                                            <th>الجهة</th>
                                            <th>الجهاز/النوع</th>
                                            <th>الرقم التسلسلي</th>
                                            <th>التاريخ</th>
                                            <th>الحالة</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reports as $report)
                                        <tr class="report-row" data-type="maintenance" data-status="{{ $report->status }}" data-search="{{ strtolower($report->requesting_party . ' ' . $report->device_name . ' ' . ($report->serial_number ?? '') . ' ' . $report->brand) }}">
                                            <td><span class="badge bg-success">كشف فني</span></td>
                                            <td>{{ $report->requesting_party }}</td>
                                            <td>{{ $report->device_name }}</td>
                                            <td>{{ $report->serial_number ?? '-' }}</td>
                                            <td>{{ optional($report->created_at)->format('Y-m-d') }}</td>
                                            <td>
                                                @if($report->status == 'قيد التنفيذ')
                                                    <span class="badge bg-warning">قيد التنفيذ</span>
                                                @elseif($report->status == 'تم الإنجاز')
                                                    <span class="badge bg-success">تم الإنجاز</span>
                                                @else
                                                    <span class="badge bg-danger">تم الإلغاء</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('maintenance-reports.show', $report) }}" class="btn btn-sm btn-info">عرض</a>
                                                @if(auth()->user()->role == 'manager' || $report->created_by == auth()->id())
                                                    <a href="{{ route('maintenance-reports.edit', $report) }}" class="btn btn-sm btn-warning">تعديل</a>
                                                @endif
                                                @if(auth()->user()->role == 'manager')
                                                    @if($report->status == 'قيد التنفيذ')
                                                        <form action="{{ route('maintenance.status.update', $report) }}" method="POST" style="display:inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="تم الإنجاز">
                                                            <button class="btn btn-sm btn-success">إنهاء</button>
                                                        </form>
                                                        <form action="{{ route('maintenance.status.update', $report) }}" method="POST" style="display:inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="تم الإلغاء">
                                                            <button class="btn btn-sm btn-danger">إلغاء</button>
                                                        </form>
                                                    @endif
                                                @endif
                                                @if(auth()->user()->role == 'manager' || $report->created_by == auth()->id())
                                                    <form action="{{ route('maintenance-reports.destroy', $report) }}" method="POST" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        @foreach($deliveries as $delivery)
                                        <tr class="report-row" data-type="warehouse" data-status="none" data-search="{{ strtolower($delivery->requesting_party . ' ' . $delivery->device_type . ' ' . ($delivery->serial_number ?? '')) }}">
                                            <td><span class="badge bg-primary">تسليم مستودع</span></td>
                                            <td>{{ $delivery->requesting_party }}</td>
                                            <td>{{ $delivery->device_type }}</td>
                                            <td>{{ $delivery->serial_number ?? '-' }}</td>
                                            <td>{{ optional($delivery->created_at)->format('Y-m-d') }}</td>
                                            <td>-</td>
                                            <td>
                                                <a href="{{ route('warehouse-deliveries.show', $delivery) }}" class="btn btn-sm btn-info">عرض</a>
                                                @if(auth()->user()->role == 'manager' || $delivery->created_by == auth()->id())
                                                    <a href="{{ route('warehouse-deliveries.edit', $delivery) }}" class="btn btn-sm btn-warning">تعديل</a>
                                                @endif
                                                @if(auth()->user()->role == 'manager' || $delivery->created_by == auth()->id())
                                                    <form action="{{ route('warehouse-deliveries.destroy', $delivery) }}" method="POST" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;
let currentStatusFilter = 'all';

function filterByStatus(status) {
    currentStatusFilter = status;
    filterReports();
}

function filterReports() {
    const filterType = document.getElementById('filter-type').value;
    const rows = document.querySelectorAll('.report-row');
    
    rows.forEach(row => {
        const rowType = row.getAttribute('data-type');
        const rowStatus = row.getAttribute('data-status') || 'none';
        
        const matchesType = filterType === 'all' || rowType === filterType;
        const matchesStatus = currentStatusFilter === 'all' || 
                              (currentStatusFilter === 'completed' && (rowStatus === 'تم الإنجاز' || rowStatus === 'تم الإلغاء')) ||
                              (currentStatusFilter === rowStatus);
        
        if (matchesType && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // تطبيق البحث أيضاً
    performSearch();
}

document.getElementById('search-input').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(performSearch, 300);
});

function performSearch() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const filterType = document.getElementById('filter-type').value;
    const rows = document.querySelectorAll('.report-row');
    
    rows.forEach(row => {
        const rowType = row.getAttribute('data-type');
        const rowStatus = row.getAttribute('data-status') || 'none';
        const searchData = row.getAttribute('data-search') || '';
        
        const matchesType = filterType === 'all' || rowType === filterType;
        const matchesStatus = currentStatusFilter === 'all' || 
                              (currentStatusFilter === 'completed' && (rowStatus === 'تم الإنجاز' || rowStatus === 'تم الإلغاء')) ||
                              (currentStatusFilter === rowStatus);
        const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
        
        if (matchesType && matchesStatus && matchesSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// تطبيق الفلتر عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    filterReports();
});
</script>
@endsection
