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
                            {{-- إدارة المستخدمين - منفصل ومميز --}}
                            @if(auth()->user()->role == 'manager')
                                <div class="border-end pe-2 me-2">
                                    <a href="{{ route('users.index') }}" class="btn btn-info">
                                        <i class="fas fa-users"></i> إدارة المستخدمين
                                        <span class="badge bg-light text-dark ms-1">{{ \App\Models\User::count() }}</span>
                                    </a>
                                </div>

                                {{-- فلاتر الحالة --}}
                                <button id="btn-all" onclick="filterByStatus('all')" class="btn btn-info active-filter">الكل</button>
                                <button id="btn-pending" onclick="filterByStatus('قيد التنفيذ')" class="btn btn-warning text-dark">قيد التنفيذ ({{ $reports->where('status', 'قيد التنفيذ')->count() }})</button>
                                <button id="btn-completed" onclick="filterByStatus('completed')" class="btn btn-success">تم الإنجاز والإلغاء ({{ $reports->whereIn('status', ['تم الإنجاز', 'تم الإلغاء'])->count() }})</button>
                            @endif

                            {{-- أزرار الإنشاء --}}
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
                                <table class="table table-bordered table-striped" id="reports-table">
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
    // بدء التحديث الفوري
    startPolling();
});

// متغيرات للتحديث الفوري
let lastTimestamp = 0;
let pollingInterval = null;

function startPolling() {
    // جلب البيانات كل 5 ثواني
    pollingInterval = setInterval(fetchReports, 5000);
}

function fetchReports() {
    fetch('{{ route('api.reports') }}')
        .then(response => response.json())
        .then(data => {
            if (data.timestamp > lastTimestamp) {
                lastTimestamp = data.timestamp;
                updateTable(data.reports, data.deliveries);
            }
        })
        .catch(error => console.error('Error fetching reports:', error));
}

function updateTable(newReports, newDeliveries) {
    const tbody = document.querySelector('#reports-table tbody');
    if (!tbody) return;

    // حفظ الفلتر الحالي
    const currentFilter = document.getElementById('filter-type').value;
    const currentStatusFilter = currentStatusFilter;

    // مسح الجدول الحالي
    tbody.innerHTML = '';

    // إضافة الكشوفات الفنية
    newReports.forEach(report => {
        const row = createReportRow(report);
        tbody.appendChild(row);
    });

    // إضافة تسليمات المستودع
    newDeliveries.forEach(delivery => {
        const row = createDeliveryRow(delivery);
        tbody.appendChild(row);
    });

    // إعادة تطبيق الفلتر
    filterReports();
}

function createReportRow(report) {
    const tr = document.createElement('tr');
    tr.className = 'report-row';
    tr.setAttribute('data-type', 'maintenance');
    tr.setAttribute('data-status', report.status);
    tr.setAttribute('data-search', `${report.serial_number} ${report.requesting_party} ${report.device_name}`.toLowerCase());

    const statusBadge = getStatusBadge(report.status);

    tr.innerHTML = `
        <td>
            <a href="{{ route('maintenance-reports.show', ':id') }}".replace(':id', report.id)" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i> عرض
            </a>
        </td>
        <td>${report.serial_number || '-'}</td>
        <td>${report.requesting_party}</td>
        <td>${report.device_name || '-'}</td>
        <td>${report.brand || '-'}</td>
        <td>${statusBadge}</td>
        <td>${report.report_date}</td>
        <td>${report.creator ? report.creator.name : '-'}</td>
        <td>
            <a href="{{ route('maintenance-reports.edit', ':id') }}".replace(':id', report.id)" class="btn btn-sm btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
        </td>
    `;

    return tr;
}

function getStatusBadge(status) {
    const badges = {
        'قيد التنفيذ': '<span class="badge bg-warning text-dark">قيد التنفيذ</span>',
        'تم الإنجاز': '<span class="badge bg-success">تم الإنجاز</span>',
        'تم الإلغاء': '<span class="badge bg-danger">تم الإلغاء</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
}

function createDeliveryRow(delivery) {
    const tr = document.createElement('tr');
    tr.className = 'report-row';
    tr.setAttribute('data-type', 'warehouse');
    tr.setAttribute('data-status', 'none');
    tr.setAttribute('data-search', `${delivery.serial_number} ${delivery.requesting_party} ${delivery.device_type}`.toLowerCase());

    tr.innerHTML = `
        <td><span class="badge bg-primary">تسليم مستودع</span></td>
        <td>${delivery.requesting_party}</td>
        <td>${delivery.device_type}</td>
        <td>${delivery.serial_number || '-'}</td>
        <td>${delivery.created_at ? new Date(delivery.created_at).toISOString().split('T')[0] : '-'}</td>
        <td>-</td>
        <td>
            <a href="{{ route('warehouse-deliveries.show', ':id') }}".replace(':id', delivery.id)" class="btn btn-sm btn-info">عرض</a>
            <a href="{{ route('warehouse-deliveries.edit', ':id') }}".replace(':id', delivery.id)" class="btn btn-sm btn-warning">تعديل</a>
        </td>
    `;

    return tr;
}
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
