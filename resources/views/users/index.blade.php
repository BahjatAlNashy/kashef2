@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
    </div>

    <h2 class="mb-4">إدارة المستخدمين</h2>

    <!-- رسائل النجاح والخطأ -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <small><i class="fas fa-check-circle"></i> {{ session('success') }}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.5rem;"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <small><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.5rem;"></button>
        </div>
    @endif

    <!-- زر إضافة مستخدم -->
    <a href="{{ route('users.create') }}" class="btn btn-success mb-3"><i class="fas fa-plus"></i> إضافة مستخدم جديد</a>

    <!-- جدول المستخدمين -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الدور</th>
                <th>الحالة</th>
                <th>تاريخ الإنشاء</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr @if($user->trashed()) style="background-color: #f8d7da; opacity: 0.8;" @endif>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->role == 'manager')
                        <span class="badge bg-danger">مدير</span>
                    @else
                        <span class="badge bg-primary">موظف</span>
                    @endif
                </td>
                <td>
                    @if($user->trashed())
                        <span class="badge bg-secondary">مجمد</span>
                    @else
                        <span class="badge bg-success">مفعل</span>
                    @endif
                </td>
                <td><small>{{ $user->created_at?->format('Y-m-d') }}</small></td>
                <td><small>{{ $user->updated_at?->format('Y-m-d H:i') ?? '-' }}</small></td>
                <td><small>{{ $user->updater?->name ?? '-' }}</small></td>
                <td>
                    @if($user->trashed())
                        <!-- مستخدم مجمد - زر تفعيل -->
                        <form action="{{ route('users.restore', $user->id) }}" method="POST" style="display:inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-success" onclick="return confirm('متأكد من تفعيل المستخدم؟')">تفعيل</button>
                        </form>
                    @else
                        <!-- مستخدم مفعل - أزرار تعديل وتجميد -->
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-info">تعديل</a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-warning" onclick="return confirm('متأكد من تجميد المستخدم؟')">تجميد</button>
                            </form>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
