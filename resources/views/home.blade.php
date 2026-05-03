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
                                 <button id="btn-pending" onclick="filterByStatus('قيد التنفيذ')" class="btn btn-warning text-dark">قيد التنفيذ (<span id="pending-count">{{ $reports->where('status', 'قيد التنفيذ')->count() }}</span>)</button>
                                <button id="btn-completed" onclick="filterByStatus('completed')" class="btn btn-success">تم الإنجاز والإلغاء (<span id="completed-count">{{ $reports->whereIn('status', ['تم الإنجاز', 'تم الإلغاء'])->count() }}</span>)</button>
                            @endif

                            <!-- أزرار إنشاء جديد -->
                            <a href="{{ route('maintenance-reports.create') }}" class="btn btn-success">+ كشف فني</a>
                            <a href="{{ route('warehouse-deliveries.create') }}" class="btn btn-primary">+ تسليم مستودع</a>
                        </div>
                    </div>
                </div>

                <!-- مؤشر حالة التحديث التلقائي -->
                <div class="d-flex align-items-center gap-2" style="font-size: 12px; color: #666;">
                    <span id="auto-refresh-status">
                        <i class="fas fa-sync fa-spin text-success"></i> تحديث تلقائي: نشط
                    </span>
                    <span id="last-refresh-time">-</span>
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

// متغيرات للتحديث التلقائي
let lastReportsData = null;
let lastDeliveriesData = null;
let autoRefreshInterval = null;
let isAutoRefreshEnabled = true;

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
    startAutoRefresh();
});

// ============================================================
// دوال التحديث التلقائي
// ============================================================

function startAutoRefresh() {
    if (!isAutoRefreshEnabled) return;

    // التحقق من وجود تحديثات كل 5 دقائق
    autoRefreshInterval = setInterval(checkForUpdates, 300000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        isAutoRefreshEnabled = false;
        updateRefreshStatus('stopped', 'تم الإيقاف');
        console.log('[AutoRefresh] Auto refresh stopped');
    }
}

async function checkForUpdates() {
    if (!isAutoRefreshEnabled) return;

    // تحديث حالة التحميل
    updateRefreshStatus('loading');

    try {
        console.log('[AutoRefresh] Fetching data from server...');

        const response = await fetch('{{ route('api.reports') }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            },
            cache: 'no-store'
        });

        // التحقق من حالة الـ response
        if (!response.ok) {
            console.error('[AutoRefresh] HTTP Error:', response.status, response.statusText);
            updateRefreshStatus('error', 'خطأ: ' + response.status);
            if (response.status === 401) {
                console.warn('[AutoRefresh] Session expired, stopping auto-refresh');
                stopAutoRefresh();
                updateRefreshStatus('stopped', 'الجلسة منتهية');
            }
            return;
        }

        const data = await response.json();
        console.log('[AutoRefresh] Data received, reports:', data.reports?.length, 'deliveries:', data.deliveries?.length);

        // التحقق من وجود البيانات
        if (!data.reports || !data.deliveries) {
            console.error('[AutoRefresh] Invalid data structure');
            updateRefreshStatus('error', 'بيانات غير صالحة');
            return;
        }

        // تحديث وقت آخر تحديث
        updateLastRefreshTime();
        updateRefreshStatus('active');

        // مقارنة الكشوفات الفنية
        const reportsChanged = hasReportsChanged(data.reports);
        const deliveriesChanged = hasDeliveriesChanged(data.deliveries);

        if (reportsChanged || deliveriesChanged) {
            console.log('[AutoRefresh] Changes detected, updating table...');

            // تحديث البيانات المحفوظة
            lastReportsData = data.reports;
            lastDeliveriesData = data.deliveries;

            // تحديث الجدول
            updateTable(data.reports, data.deliveries);

            // إظهار إشعار بسيط
            showRefreshNotification();
        } else {
            console.log('[AutoRefresh] No changes detected');
        }
    } catch (error) {
        console.error('[AutoRefresh] Error:', error.message);
        updateRefreshStatus('error', 'خطأ في الاتصال');

        // إذا كانت المشكلة متعلقة بالشبكة، نوقف التحديث مؤقتاً
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            console.warn('[AutoRefresh] Network error, will retry next cycle');
        }
    }
}

// تحديث حالة المؤشر
function updateRefreshStatus(status, message = '') {
    const statusEl = document.getElementById('auto-refresh-status');
    if (!statusEl) return;

    switch(status) {
        case 'loading':
            statusEl.innerHTML = '<i class="fas fa-sync fa-spin text-info"></i> جاري التحديث...';
            break;
        case 'active':
            statusEl.innerHTML = '<i class="fas fa-sync fa-spin text-success"></i> تحديث تلقائي: نشط';
            break;
        case 'error':
            statusEl.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> ' + (message || 'خطأ في التحديث');
            break;
        case 'stopped':
            statusEl.innerHTML = '<i class="fas fa-pause-circle text-warning"></i> ' + (message || 'متوقف');
            break;
    }
}

// تحديث وقت آخر تحديث
function updateLastRefreshTime() {
    const timeEl = document.getElementById('last-refresh-time');
    if (!timeEl) return;

    const now = new Date();
    const timeStr = now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    timeEl.textContent = '(آخر تحديث: ' + timeStr + ')';
}

function hasReportsChanged(newReports) {
    if (!lastReportsData) {
        lastReportsData = newReports;
        return false;
    }

    if (lastReportsData.length !== newReports.length) {
        return true;
    }

    // التحقق من وجود تغييرات في الحالة أو البيانات
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

function hasDeliveriesChanged(newDeliveries) {
    if (!lastDeliveriesData) {
        lastDeliveriesData = newDeliveries;
        return false;
    }

    if (lastDeliveriesData.length !== newDeliveries.length) {
        return true;
    }

    for (let i = 0; i < newDeliveries.length; i++) {
        const oldDelivery = lastDeliveriesData[i];
        const newDelivery = newDeliveries[i];

        if (!oldDelivery || oldDelivery.id !== newDelivery.id ||
            oldDelivery.requesting_party !== newDelivery.requesting_party ||
            oldDelivery.device_type !== newDelivery.device_type) {
            return true;
        }
    }

    return false;
}

function updateTable(reports, deliveries) {
    const tbody = document.querySelector('#reports-table tbody');
    if (!tbody) return;

    let html = '';

    // إضافة الكشوفات الفنية
    reports.forEach((report, index) => {
        const statusBadge = getStatusBadge(report.status);
        const actions = getReportActions(report);

        html += `
            <tr class="report-row" data-type="maintenance" data-status="${report.status}" data-search="${(report.requesting_party + ' ' + report.device_name + ' ' + (report.serial_number || '') + ' ' + report.brand).toLowerCase()}">
                <td class="row-number">${index + 1}</td>
                <td><span class="badge bg-success">كشف فني</span></td>
                <td>${report.requesting_party}</td>
                <td>${report.device_name}</td>
                <td>${report.serial_number || '-'}</td>
                <td>${new Date(report.created_at).toISOString().split('T')[0]}</td>
                <td>${statusBadge}</td>
                <td>${actions}</td>
            </tr>
        `;
    });

    // إضافة تسليمات المستودع
    const deliveryStartNumber = reports.length;
    deliveries.forEach((delivery, index) => {
        const actions = getDeliveryActions(delivery);

        html += `
            <tr class="report-row" data-type="warehouse" data-status="none" data-search="${(delivery.requesting_party + ' ' + delivery.device_type + ' ' + (delivery.serial_number || '')).toLowerCase()}">
                <td class="row-number">${deliveryStartNumber + index + 1}</td>
                <td><span class="badge bg-primary">تسليم مستودع</span></td>
                <td>${delivery.requesting_party}</td>
                <td>${delivery.device_type}</td>
                <td>${delivery.serial_number || '-'}</td>
                <td>${new Date(delivery.created_at).toISOString().split('T')[0]}</td>
                <td>-</td>
                <td>${actions}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    // تحديث العدادات
    updateCounters(reports);

    // إعادة تطبيق الفلاتر
    applyFilters();
}

function updateCounters(reports) {
    const pendingCount = reports.filter(r => r.status === 'قيد التنفيذ').length;
    const completedCount = reports.filter(r => r.status === 'تم الإنجاز' || r.status === 'تم الإلغاء').length;

    const pendingCountEl = document.getElementById('pending-count');
    const completedCountEl = document.getElementById('completed-count');

    if (pendingCountEl) {
        pendingCountEl.textContent = pendingCount;
    }
    if (completedCountEl) {
        completedCountEl.textContent = completedCount;
    }
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

function getDeliveryActions(delivery) {
    const isManager = '{{ auth()->user()->role }}' === 'manager';
    const isOwner = delivery.created_by === '{{ auth()->id() }}';

    let actions = `<a href="/warehouse-deliveries/${delivery.id}" class="btn btn-sm btn-info">عرض</a>`;

    if (isManager || isOwner) {
        actions += `<a href="/warehouse-deliveries/${delivery.id}/edit" class="btn btn-sm btn-warning">تعديل</a>`;
    }

    if (isManager || isOwner) {
        actions += `
            <form action="/warehouse-deliveries/${delivery.id}" method="POST" style="display:inline">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
            </form>
        `;
    }

    return actions;
}

function showRefreshNotification() {
    // إظهار إشعار بسيط
    const notification = document.createElement('div');
    notification.className = 'alert alert-info position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; animation: fadeInOut 3s forwards;';
    notification.innerHTML = '<i class="fas fa-sync"></i> تم تحديث البيانات تلقائياً';
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
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

/* animation للإشعار */
@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-20px); }
    10% { opacity: 1; transform: translateY(0); }
    90% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-20px); }
}
</style>
@endsection
