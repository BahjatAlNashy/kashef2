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