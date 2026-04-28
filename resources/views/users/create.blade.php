@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">إضافة مستخدم جديد</h2>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="fw-bold">الاسم:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">كلمة المرور:</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required minlength="8">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">👁️</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">تأكيد كلمة المرور:</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">👁️</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">الدور:</label>
                    <select name="role" class="form-control" required>
                        <option value="employee">موظف</option>
                        <option value="manager">مدير</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
            <button type="submit" class="btn btn-success">حفظ</button>
        </div>
    </form>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
}
</script>
@endsection
