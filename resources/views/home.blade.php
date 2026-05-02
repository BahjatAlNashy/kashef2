@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3>لوحة التحكم - نظام إدارة الصيانة والمستودع</h3>
                        <div class="d-flex gap-2 flex-wrap">
                            <!-- إدارة المستخدمين (للمدير فقط) -->
                            @if(auth()->user()->role == 'manager')
                                <div class="border-end pe-2 me-2">
                                    <a href="{{ route('users.index') }}" class="btn btn-info">
                                        <i class="fas fa-users"></i> إدارة المستخدمين
                                        <span class="badge bg-light text-dark ms-1">{{ \App\Models\User::count() }}</span>
                                    </a>
                                </div>

                                <!-- فلاتر الحالة -->
                                <button id="btn-all" onclick="filterByStatus('all')" class="btn btn-info active-filter">الكل</button>
                                <button id="btn-pending" onclick="filterByStatus('قيد التنفيذ')" class="btn btn-warning text-dark">قيد التنفيذ ({{ $reports->where('status', 'قيد التنفيذ')->count() }})</button>
                                <button id="btn-completed" onclick="filterByStatus('completed')" class="btn btn-success">تم الإنجاز والإلغاء ({{ $reports->whereIn('status', ['تم الإنجاز', 'تم الإلغاء'])->count() }})</button>
                            @endif

                            <!-- أزرار إنشاء جديد -->
                            <a href="{{ route('maintenance-reports.create') }}" class="btn btn-success">+ كشف فني</a>
                            <a href="{{ route('warehouse-deliveries.create') }}" class="btn btn-primary">+ تسليم مستودع</a>
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
                            <h5>جميع الكشوف <span class="badge bg-secondary">{{ $reports->count() + $deliveries->count() }}</span></h5>
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
                                <table class="table table-bordered table-striped" id="reports-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">الرقم</th>
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
                                            <td class="row-number">{{ $loop->iteration }}</td>
                                            <td><span class="badge bg-success">كشف فني</span></td>
                                            <td>{{ $report->requesting_party }}</td>
                                            <td>{{ $report->device_name }}</td>
                                            <td>{{ $report->serial_number ?? '-' }}</td>
                                            <td>{{ optional($report->created_at)->format('Y-m-d') }}</td>
                                            <!-- حالة الكشف -->
                                            <td>
                                                @if($report->status == 'قيد التنفيذ')
                                                    <span class="badge bg-warning">قيد التنفيذ</span>
                                                @elseif($report->status == 'تم الإنجاز')
                                                    <span class="badge bg-success">تم الإنجاز</span>
                                                @else
                                                    <span class="badge bg-danger">تم الإلغاء</span>
                                                @endif
                                            </td>

                                            <!-- أزرار إجراءات الكشف الفني -->
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
                                        
                                        @php $deliveryStartNumber = $reports->count(); @endphp
                                        @foreach($deliveries as $delivery)
                                        <!-- صف تسليم المستودع -->
                                        <tr class="report-row" data-type="warehouse" data-status="none" data-search="{{ strtolower($delivery->requesting_party . ' ' . $delivery->device_type . ' ' . ($delivery->serial_number ?? '')) }}">
                                            <td class="row-number">{{ $deliveryStartNumber + $loop->iteration }}</td>
                                            <td><span class="badge bg-primary">تسليم مستودع</span></td>
                                            <td>{{ $delivery->requesting_party }}</td>
                                            <td>{{ $delivery->device_type }}</td>
                                            <td>{{ $delivery->serial_number ?? '-' }}</td>
                                            <td>{{ optional($delivery->created_at)->format('Y-m-d') }}</td>
                                            <td>-</td>

                                            <!-- أزرار إجراءات تسليم المستودع -->
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
// متغيرات الفلترة
let currentStatusFilter = 'all';
let searchTimeout = null;

function filterByStatus(status) {
    currentStatusFilter = status;

    // تحديث شكل الأزرار
    document.getElementById('btn-all').classList.remove('active-filter');
    document.getElementById('btn-pending').classList.remove('active-filter');
    document.getElementById('btn-completed').classList.remove('active-filter');

    // إضافة الشكل النشط للزر المحدد
    if (status === 'all') {
        document.getElementById('btn-all').classList.add('active-filter');
    } else if (status === 'قيد التنفيذ') {
        document.getElementById('btn-pending').classList.add('active-filter');
    } else if (status === 'completed') {
        document.getElementById('btn-completed').classList.add('active-filter');
    }

    filterReports();
}

function filterReports() {
    applyFilters();
}

function applyFilters() {
    const filterType = document.getElementById('filter-type').value;
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const rows = document.querySelectorAll('.report-row');
    
    rows.forEach(row => {
        const rowType = row.getAttribute('data-type');
        const rowStatus = row.getAttribute('data-status') || 'none';
        const searchData = row.getAttribute('data-search') || '';
        
        // فلترة حسب النوع (الكل / كشوفات فنية / تسليم مستودع)
        const matchesType = filterType === 'all' || rowType === filterType;
        
        // فلترة حسب الحالة (للكشوفات الفنية فقط)
        const matchesStatus = currentStatusFilter === 'all' || 
                              (currentStatusFilter === 'completed' && (rowStatus === 'تم الإنجاز' || rowStatus === 'تم الإلغاء')) ||
                              (currentStatusFilter === rowStatus);
        
        // فلترة حسب البحث
        const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
        
        if (matchesType && matchesStatus && matchesSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // إعادة ترقيم الصفوف المرئية
    renumberRows();
}

function renumberRows() {
    const visibleRows = document.querySelectorAll('.report-row:not([style*="display: none"])');
    visibleRows.forEach((row, index) => {
        const numberCell = row.querySelector('.row-number');
        if (numberCell) {
            numberCell.textContent = index + 1;
        }
    });
}

document.getElementById('search-input').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

// تطبيق الفلاتر عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    applyFilters();
});
</script>

<style>
/* تأثير الزر النشط - يظهر كأنه مضغوط */
.active-filter {
    box-shadow: inset 0 3px 5px rgba(0,0,0,0.3) !important;
    transform: translateY(1px);
    border: 2px solid #000 !important;
}

/* تأثيرات إضافية للزر النشط حسب نوعه */
#btn-all.active-filter {
    background-color: #0dcaf0 !important;
    border-color: #000 !important;
}

#btn-pending.active-filter {
    background-color: #d39e00 !important;
    border-color: #000 !important;
}

#btn-completed.active-filter {
    background-color: #1e7e34 !important;
    border-color: #000 !important;
}
</style>
@endsection
