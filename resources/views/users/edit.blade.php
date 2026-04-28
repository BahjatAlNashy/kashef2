@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">تعديل مستخدم: {{ $user->username }}</h2>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                {{-- اسم المستخدم --}}
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">اسم المستخدم:</label>
                    <div class="col-md-9">
                        <input type="text" name="username" value="{{ $user->username }}" class="form-control" required>
                        <small class="text-muted">يجب أن يكون فريداً (unique)</small>
                    </div>
                </div>

                {{-- كلمة المرور الجديدة --}}
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">
                        كلمة المرور الجديدة:
                        <small class="text-muted d-block">(اتركه فارغاً للإبقاء على الحالية)</small>
                    </label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">👁️</button>
                        </div>
                        <small class="text-muted">الحد الأدنى 8 أحرف</small>
                    </div>
                </div>

                {{-- تأكيد كلمة المرور --}}
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">تأكيد كلمة المرور:</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">👁️</button>
                        </div>
                    </div>
                </div>

                {{-- الدور --}}
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">الدور:</label>
                    <div class="col-md-9">
                        <select name="role" class="form-control" required>
                            <option value="employee" {{ $user->role == 'employee' ? 'selected' : '' }}>موظف</option>
                            <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>مدير</option>
                        </select>
                    </div>
                </div>

                {{-- معلومات إضافية --}}
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label fw-bold text-md-end">تاريخ الإنشاء:</label>
                    <div class="col-md-9">
                        <p class="form-control-plaintext">{{ $user->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('home') }}" class="btn btn-info">الصفحة الرئيسية</a>
            <button type="submit" class="btn btn-success">حفظ التغييرات</button>
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
