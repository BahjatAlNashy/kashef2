@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">إضافة مستخدم جديد</h2>

    <!-- عرض أخطاء التحقق -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <!-- الاسم -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">الاسم:</label>
                    <div class="col-md-9">
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>

                <!-- البريد الإلكتروني -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">البريد الإلكتروني:</label>
                    <div class="col-md-9">
                        <input type="email" name="email" class="form-control" required>
                        <small class="text-muted">يجب أن يكون فريداً (unique)</small>
                    </div>
                </div>

                <!-- كلمة المرور -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">كلمة المرور:</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">👁️</button>
                        </div>
                        <small class="text-muted">الحد الأدنى 8 أحرف</small>
                    </div>
                </div>

                <!-- تأكيد كلمة المرور -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">تأكيد كلمة المرور:</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">👁️</button>
                        </div>
                    </div>
                </div>

                <!-- الدور -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">الدور:</label>
                    <div class="col-md-9">
                        <select name="role" class="form-control" required>
                            <option value="employee">موظف</option>
                            <option value="manager">مدير</option>
                        </select>
                    </div>
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
