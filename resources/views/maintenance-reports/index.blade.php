@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">الكشوفات الفنية</h2>
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('maintenance-reports.create') }}" class="btn btn-primary">إنشاء كشف جديد</a>
        </div>
        <div class="col-md-6">
            <input type="text" id="search-input" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>الجهة</th>
                <th>الجهاز</th>
                <th>التسلسلي</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr>
                <td>{{ $report->requesting_party }}</td>
                <td>{{ $report->device_name }}</td>
                <td>{{ $report->serial_number }}</td>
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
        </tbody>
    </table>
    {{ $reports->links() }}
</div>

<style>
@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-20px); }
    10% { opacity: 1; transform: translateY(0); }
    90% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-20px); }
}
</style>

<script>
    // متغيرات للتحديث التلقائي
let lastReportsData = null;
let autoRefreshInterval = null;
let isAutoRefreshEnabled = true;

document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value;
    const url = new URL(window.location);
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    window.location.href = url.toString();
});

// ============================================================
// دوال التحديث التلقائي
// ============================================================

function startAutoRefresh() {
    if (!isAutoRefreshEnabled) return;
    autoRefreshInterval = setInterval(checkForUpdates, 5000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

async function checkForUpdates() {
    if (!isAutoRefreshEnabled) return;

    try {
        const response = await fetch('{{ route('api.reports') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await response.json();

        const reportsChanged = hasReportsChanged(data.reports);

        if (reportsChanged) {
            lastReportsData = data.reports;
            updateTable(data.reports);
            showRefreshNotification();
        }
    } catch (error) {
        console.error('Error checking for updates:', error);
    }
}

function hasReportsChanged(newReports) {
    if (!lastReportsData) {
        lastReportsData = newReports;
        return false;
    }

    if (lastReportsData.length !== newReports.length) {
        return true;
    }

    for (let i = 0; i < newReports.length; i++) {
        const oldReport = lastReportsData[i];
        const newReport = newReports[i];

        if (!oldReport || oldReport.id !== newReport.id ||
            oldReport.status !== newReport.status ||
            oldReport.requesting_party !== newReport.requesting_party ||
            oldReport.device_name !== newReport.device_name) {
            return true;
        }
    }

    return false;
}

function updateTable(reports) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;

    let html = '';

    reports.forEach(report => {
        const statusBadge = getStatusBadge(report.status);
        const actions = getReportActions(report);

        html += `
            <tr>
                <td>${report.requesting_party}</td>
                <td>${report.device_name}</td>
                <td>${report.serial_number}</td>
                <td>${new Date(report.created_at).toISOString().split('T')[0]}</td>
                <td>${statusBadge}</td>
                <td>${actions}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function getStatusBadge(status) {
    if (status === 'قيد التنفيذ') {
        return '<span class="badge bg-warning">قيد التنفيذ</span>';
    } else if (status === 'تم الإنجاز') {
        return '<span class="badge bg-success">تم الإنجاز</span>';
    } else {
        return '<span class="badge bg-danger">تم الإلغاء</span>';
    }
}

function getReportActions(report) {
    const isManager = '{{ auth()->user()->role }}' === 'manager';
    const isOwner = report.created_by === '{{ auth()->id() }}';

    let actions = `<a href="/maintenance-reports/${report.id}" class="btn btn-sm btn-info">عرض</a>`;

    if (isManager || isOwner) {
        actions += `<a href="/maintenance-reports/${report.id}/edit" class="btn btn-sm btn-warning">تعديل</a>`;
    }

    if (isManager && report.status === 'قيد التنفيذ') {
        actions += `
            <form action="/maintenance-reports/${report.id}/status" method="POST" style="display:inline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="status" value="تم الإنجاز">
                <button class="btn btn-sm btn-success">إنهاء</button>
            </form>
            <form action="/maintenance-reports/${report.id}/status" method="POST" style="display:inline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="status" value="تم الإلغاء">
                <button class="btn btn-sm btn-danger">إلغاء</button>
            </form>
        `;
    }

    if (isManager || isOwner) {
        actions += `
            <form action="/maintenance-reports/${report.id}" method="POST" style="display:inline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
            </form>
        `;
    }

    return actions;
}

function showRefreshNotification() {
    const notification = document.createElement('div');
    notification.className = 'alert alert-info position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; animation: fadeInOut 3s forwards;';
    notification.innerHTML = '<i class="fas fa-sync"></i> تم تحديث البيانات تلقائياً';
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// بدء التحديث التلقائي عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});
</script>
@endsection