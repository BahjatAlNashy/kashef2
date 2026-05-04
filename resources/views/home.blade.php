@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
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

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- جدول جميع الكشوف -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5>جميع الكشوف
                                @if(isset($paginatedItems))
                                    <span class="badge bg-secondary">{{ $paginatedItems->total() }}</span>
                                @elseif(isset($reports))
                                    <span class="badge bg-secondary">{{ $reports->total() }}</span>
                                @elseif(isset($deliveries))
                                    <span class="badge bg-secondary">{{ $deliveries->total() }}</span>
                                @endif
                            </h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <select id="filter-type" class="form-control" style="width: 200px;" onchange="window.location.href='{{ route('home') }}?type='+this.value+'&search='+encodeURIComponent('{{ $search ?? '' }}')">
                                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="maintenance" {{ $type == 'maintenance' ? 'selected' : '' }}>الكشوفات الفنية</option>
                                    <option value="warehouse" {{ $type == 'warehouse' ? 'selected' : '' }}>تسليم المستودع</option>
                                </select>
                                <input type="text" id="search-input" class="form-control" style="width: 250px;" placeholder="بحث..." value="{{ $search ?? '' }}">
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
                                            <th>تاريخ الإنشاء</th>
                                            <th>تاريخ التعديل</th>
                                            <th>الحالة</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- عرض العناصر المدمجة (الكل) --}}
                                        @if(isset($paginatedItems))
                                            @foreach($paginatedItems as $item)
                                                @php $data = $item['data']; @endphp
                                                @if($item['type'] === 'maintenance')
                                                    {{-- صف كشف فني --}}
                                                    <tr class="report-row" data-type="maintenance" data-status="{{ $data->status }}" data-search="{{ strtolower($data->requesting_party . ' ' . $data->device_name . ' ' . ($data->serial_number ?? '') . ' ' . $data->brand) }}">
                                                        <td class="row-number">{{ ($paginatedItems->currentPage() - 1) * $paginatedItems->perPage() + $loop->iteration }}</td>
                                                        <td><span class="badge bg-success">كشف فني</span></td>
                                                        <td>{{ $data->requesting_party }}</td>
                                                        <td>{{ $data->device_name }}</td>
                                                        <td>{{ $data->serial_number ?? '-' }}</td>
                                                        <td>{{ optional($data->created_at)->format('Y-m-d H:i') }}</td>
                                                        <td>{{ optional($data->updated_at)->format('Y-m-d H:i') }}</td>
                                                        <td>
                                                            @if($data->status == 'قيد التنفيذ')
                                                                <span class="badge bg-warning">قيد التنفيذ</span>
                                                            @elseif($data->status == 'تم الإنجاز')
                                                                <span class="badge bg-success">تم الإنجاز</span>
                                                            @else
                                                                <span class="badge bg-danger">تم الإلغاء</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('maintenance-reports.show', $data) }}" class="btn btn-sm btn-info">عرض</a>
                                                            @if(auth()->user()->role == 'manager' || $data->created_by == auth()->id())
                                                                <a href="{{ route('maintenance-reports.edit', $data) }}" class="btn btn-sm btn-warning">تعديل</a>
                                                            @endif
                                                            @if(auth()->user()->role == 'manager')
                                                                @if($data->status == 'قيد التنفيذ')
                                                                    <form action="{{ route('maintenance.status.update', $data) }}" method="POST" style="display:inline">
                                                                        @csrf @method('PATCH')
                                                                        <input type="hidden" name="status" value="تم الإنجاز">
                                                                        <button class="btn btn-sm btn-success">إنهاء</button>
                                                                    </form>
                                                                    <form action="{{ route('maintenance.status.update', $data) }}" method="POST" style="display:inline">
                                                                        @csrf @method('PATCH')
                                                                        <input type="hidden" name="status" value="تم الإلغاء">
                                                                        <button class="btn btn-sm btn-danger">إلغاء</button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                            @if(auth()->user()->role == 'manager' || $data->created_by == auth()->id())
                                                                <form action="{{ route('maintenance-reports.destroy', $data) }}" method="POST" style="display:inline">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @else
                                                    {{-- صف تسليم مستودع --}}
                                                    <tr class="report-row" data-type="warehouse" data-status="none" data-search="{{ strtolower($data->requesting_party . ' ' . $data->device_type . ' ' . ($data->serial_number ?? '')) }}">
                                                        <td class="row-number">{{ ($paginatedItems->currentPage() - 1) * $paginatedItems->perPage() + $loop->iteration }}</td>
                                                        <td><span class="badge bg-primary">تسليم مستودع</span></td>
                                                        <td>{{ $data->requesting_party }}</td>
                                                        <td>{{ $data->device_type }}</td>
                                                        <td>{{ $data->serial_number ?? '-' }}</td>
                                                        <td>{{ optional($data->created_at)->format('Y-m-d H:i') }}</td>
                                                        <td>{{ optional($data->updated_at)->format('Y-m-d H:i') }}</td>
                                                        <td>-</td>
                                                        <td>
                                                            <a href="{{ route('warehouse-deliveries.show', $data) }}" class="btn btn-sm btn-info">عرض</a>
                                                            @if(auth()->user()->role == 'manager' || $data->created_by == auth()->id())
                                                                <a href="{{ route('warehouse-deliveries.edit', $data) }}" class="btn btn-sm btn-warning">تعديل</a>
                                                            @endif
                                                            @if(auth()->user()->role == 'manager' || $data->created_by == auth()->id())
                                                                <form action="{{ route('warehouse-deliveries.destroy', $data) }}" method="POST" style="display:inline">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif

                                        {{-- عرض الكشوفات الفنية فقط --}}
                                        @if(isset($reports))
                                            @foreach($reports as $report)
                                                <tr class="report-row" data-type="maintenance" data-status="{{ $report->status }}" data-search="{{ strtolower($report->requesting_party . ' ' . $report->device_name . ' ' . ($report->serial_number ?? '') . ' ' . $report->brand) }}">
                                                    <td class="row-number">{{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}</td>
                                                    <td><span class="badge bg-success">كشف فني</span></td>
                                                    <td>{{ $report->requesting_party }}</td>
                                                    <td>{{ $report->device_name }}</td>
                                                    <td>{{ $report->serial_number ?? '-' }}</td>
                                                    <td>{{ optional($report->created_at)->format('Y-m-d H:i') }}</td>
                                                    <td>{{ optional($report->updated_at)->format('Y-m-d H:i') }}</td>
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
                                        @endif

                                        {{-- عرض تسليمات المستودع فقط --}}
                                        @if(isset($deliveries))
                                            @foreach($deliveries as $delivery)
                                                <tr class="report-row" data-type="warehouse" data-status="none" data-search="{{ strtolower($delivery->requesting_party . ' ' . $delivery->device_type . ' ' . ($delivery->serial_number ?? '')) }}">
                                                    <td class="row-number">{{ ($deliveries->currentPage() - 1) * $deliveries->perPage() + $loop->iteration }}</td>
                                                    <td><span class="badge bg-primary">تسليم مستودع</span></td>
                                                    <td>{{ $delivery->requesting_party }}</td>
                                                    <td>{{ $delivery->device_type }}</td>
                                                    <td>{{ $delivery->serial_number ?? '-' }}</td>
                                                    <td>{{ optional($delivery->created_at)->format('Y-m-d H:i') }}</td>
                                                    <td>{{ optional($delivery->updated_at)->format('Y-m-d H:i') }}</td>
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
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- روابط Pagination --}}
                            <div class="d-flex justify-content-center mt-4">
                                @if(isset($paginatedItems))
                                    {{ $paginatedItems->links('pagination::bootstrap-5') }}
                                @elseif(isset($reports))
                                    {{ $reports->links('pagination::bootstrap-5') }}
                                @elseif(isset($deliveries))
                                    {{ $deliveries->links('pagination::bootstrap-5') }}
                                @endif
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
    // الحصول على رقم الصفحة من URL
    const urlParams = new URLSearchParams(window.location.search);
    const page = parseInt(urlParams.get('page')) || 1;
    const perPage = 10; // نفس القيمة في Controller

    const visibleRows = document.querySelectorAll('.report-row:not([style*="display: none"])');
    visibleRows.forEach((row, index) => {
        const numberCell = row.querySelector('.row-number');
        if (numberCell) {
            numberCell.textContent = (page - 1) * perPage + index + 1;
        }
    });
}

document.getElementById('search-input').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        const searchTerm = e.target.value;
        const type = document.getElementById('filter-type').value;
        window.location.href = '{{ route('home') }}?type=' + type + '&search=' + encodeURIComponent(searchTerm);
    }, 500);
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
