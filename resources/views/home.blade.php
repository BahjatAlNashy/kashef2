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
                            @if(auth()->user()->role == 'manager')
                                <div class="border-end pe-2 me-2">
                                    <a href="{{ route('users.index') }}" class="btn btn-info">
                                        <i class="fas fa-users"></i> إدارة المستخدمين
                                        <span class="badge bg-light text-dark ms-1">{{ \App\Models\User::count() }}</span>
                                    </a>
                                </div>
                            @endif
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
                                <select id="filter-type" class="form-control" style="width: 200px;" onchange="window.location.href='{{ route('home') }}?type='+this.value+'&search='+encodeURIComponent('{{ $search ?? '' }}')+'&status={{ $status ?? 'all' }}'">
                                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>الكل</option>
                                    <option value="maintenance" {{ $type == 'maintenance' ? 'selected' : '' }}>الكشوفات الفنية</option>
                                    <option value="warehouse" {{ $type == 'warehouse' ? 'selected' : '' }}>تسليم المستودع</option>
                                </select>
                                <input type="text" id="search-input" class="form-control" style="width: 250px;" placeholder="بحث..." value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        <div class="px-3 pt-2 pb-0">
                            <div class="btn-group mb-2" role="group">
                                <a href="{{ route('home', ['type' => $type, 'search' => $search, 'status' => 'all']) }}"
                                   class="btn btn-sm btn-info {{ $status == 'all' ? 'active-filter' : '' }}">الكل</a>
                                <a href="{{ route('home', ['type' => $type, 'search' => $search, 'status' => 'قيد التنفيذ']) }}"
                                   class="btn btn-sm btn-warning {{ $status == 'قيد التنفيذ' ? 'active-filter' : '' }}">
                                    قيد التنفيذ <span class="badge bg-light text-dark ms-1">{{ $pendingCount }}</span>
                                </a>
                                <a href="{{ route('home', ['type' => $type, 'search' => $search, 'status' => 'completed']) }}"
                                   class="btn btn-sm btn-success {{ $status == 'completed' ? 'active-filter' : '' }}">تم الإنجاز</a>
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
                                                    <tr class="report-row" data-type="maintenance" data-status="{{ $data->status }}" data-search="{{ strtolower($data->requesting_party . ' ' . ($data->serial_number ?? '')) }}">
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
                                                            @else
                                                                <span class="badge bg-success">تم الإنجاز</span>
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
                                                    <tr class="report-row" data-type="warehouse" data-status="none" data-search="{{ strtolower($data->requesting_party . ' ' . ($data->serial_number ?? '')) }}">
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
                                                <tr class="report-row" data-type="maintenance" data-status="{{ $report->status }}" data-search="{{ strtolower($report->requesting_party . ' ' . ($report->serial_number ?? '')) }}">
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
                                                        @else
                                                            <span class="badge bg-success">تم الإنجاز</span>
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
                                                <tr class="report-row" data-type="warehouse" data-status="none" data-search="{{ strtolower($delivery->requesting_party . ' ' . ($delivery->serial_number ?? '')) }}">
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

                            <div class="d-flex justify-content-center mt-4">
                                @if(isset($paginatedItems))
                                    {{ $paginatedItems->appends(['type' => $type, 'search' => $search, 'status' => $status])->links('pagination::bootstrap-5') }}
                                @elseif(isset($reports))
                                    {{ $reports->appends(['type' => $type, 'search' => $search, 'status' => $status])->links('pagination::bootstrap-5') }}
                                @elseif(isset($deliveries))
                                    {{ $deliveries->appends(['type' => $type, 'search' => $search, 'status' => $status])->links('pagination::bootstrap-5') }}
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
let searchTimeout = null;
document.getElementById('search-input').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        const searchTerm = e.target.value;
        const type = document.getElementById('filter-type').value;
        const status = '{{ $status ?? 'all' }}';
        window.location.href = '{{ route('home') }}?type=' + type + '&search=' + encodeURIComponent(searchTerm) + '&status=' + status;
    }, 500);
});
</script>

<style>
.active-filter {
    box-shadow: inset 0 3px 5px rgba(0,0,0,0.3) !important;
    transform: translateY(1px);
    border: 2px solid #000 !important;
}
#btn-all.active-filter { background-color: #0dcaf0 !important; border-color: #000 !important; }
#btn-pending.active-filter { background-color: #d39e00 !important; border-color: #000 !important; }
#btn-completed.active-filter { background-color: #1e7e34 !important; border-color: #000 !important; }
</style>
@endsection