@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">سجلات تسليم المستودع</h2>

    <!-- شريط الإجراءات: زر إنشاء + حقل البحث -->
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('warehouse-deliveries.create') }}" class="btn btn-primary">إنشاء تسليم جديد</a>
        </div>
        <div class="col-md-6">
            <input type="text" id="search-input" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- جدول تسليمات المستودع -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>الجهة الطالبة</th>
                <th>نوع الجهاز</th>
                <th>الرقم التسلسلي</th>
                <th>التاريخ</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $delivery)
            <tr>
                <td>{{ $delivery->requesting_party }}</td>
                <td>{{ $delivery->device_type }}</td>
                <td>{{ $delivery->serial_number }}</td>
                <td>{{ optional($delivery->created_at)->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('warehouse-deliveries.show', $delivery) }}" class="btn btn-sm btn-info">عرض</a>
                    @if(auth()->user()->role == 'manager' || $delivery->created_by == auth()->id())
                        <a href="{{ route('warehouse-deliveries.edit', $delivery) }}" class="btn btn-sm btn-warning">تعديل</a>
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
    {{ $deliveries->links() }}
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
let lastDeliveriesData = null;
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

        const deliveriesChanged = hasDeliveriesChanged(data.deliveries);

        if (deliveriesChanged) {
            lastDeliveriesData = data.deliveries;
            updateTable(data.deliveries);
            showRefreshNotification();
        }
    } catch (error) {
        console.error('Error checking for updates:', error);
    }
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

function updateTable(deliveries) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;

    let html = '';

    deliveries.forEach(delivery => {
        const actions = getDeliveryActions(delivery);

        html += `
            <tr>
                <td>${delivery.requesting_party}</td>
                <td>${delivery.device_type}</td>
                <td>${delivery.serial_number}</td>
                <td>${new Date(delivery.created_at).toISOString().split('T')[0]}</td>
                <td>${actions}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
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
