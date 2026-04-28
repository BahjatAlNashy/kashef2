@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">سجلات تسليم المستودع</h2>
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

<script>
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
</script>
@endsection
