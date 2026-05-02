# نظام إدارة الصيانة والمستودع - التوثيق الكامل لآلية العمل

## نظرة عامة على النظام

نظام Laravel متكامل لإدارة الكشوفات الفنية وتسليمات المستودع في مديرية المعلوماتية - دائرة الصيانة (الهيئة العامة للإذاعة والتلفزيون - سوريا).

### الهدف الرئيسي
تسجيل وإدارة:
1. **الكشوفات الفنية**: كشوفات صيانة الأجهزة التقنية
2. **تسليمات المستودع**: سجلات تسليم الأجهزة من المستودع

### المستخدمون
- **المدير (manager)**: صلاحيات كاملة
- **الموظف (employee)**: صلاحيات محدودة على كشوفاته فقط

---

## هيكل المشروع

```
empty laravel project/‏‏rebuild2/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controllers الرئيسية
│   │   │   ├── HomeController.php
│   │   │   ├── MaintenanceReportController.php
│   │   │   ├── WarehouseDeliveryController.php
│   │   │   ├── MaintenanceStatusController.php
│   │   │   ├── UserController.php
│   │   │   └── SearchController.php
│   │   └── Middleware/
│   │       └── CheckRole.php     # التحقق من الصلاحيات
│   └── Models/
│       ├── User.php
│       ├── MaintenanceReport.php
│       └── WarehouseDelivery.php
├── database/
│   └── migrations/               # ملفات الهجرة
├── resources/
│   └── views/                    # قوالب Blade
│       ├── home.blade.php
│       ├── maintenance-reports/
│       ├── warehouse-deliveries/
│       └── users/
├── routes/
│   └── web.php                   # تعريف المسارات
└── config/
    └── app.php                   # الإعدادات العامة
```

---

## قاعدة البيانات

### 1. جدول users (المستخدمون)

| الحقل | النوع | الوصف |
|-------|-------|-------|
| id | bigint | معرف فريد |
| name | string | اسم المستخدم |
| email | string | البريد الإلكتروني (فريد) |
| password | string | كلمة المرور (مشفرة) |
| role | enum | الدور: manager / employee |
| deleted_at | timestamp | للتجميد (Soft Delete) |
| timestamps | - | created_at, updated_at |

**Soft Deletes**: عند "حذف" مستخدم، يتم تجميده فقط وتحتفظ الكشوفات باسم المنشئ.

### 2. جدول maintenance_reports (الكشوفات الفنية)

| الحقل | النوع | الوصف | إجباري |
|-------|-------|-------|--------|
| id | bigint | معرف فريد | ✅ |
| requesting_party | string | الجهة طالبة الصيانة | ✅ |
| reporter_name | string | الاسم والكنية | ❌ |
| report_date | date | تاريخ الإبلاغ | ❌ |
| device_name | string | اسم الجهاز | ❌ |
| brand | string | الماركة | ❌ |
| serial_number | string | الرقم التسلسلي | ❌ |
| initial_inspection | text | الكشف الفني الأولي | ❌ |
| failure_cause | enum | سبب العطل: طبيعي/سوء استخدام/غير ذلك | ❌ |
| device_location | enum | مكان الجهاز | ❌ |
| technical_manager | string | المسؤول الفني | ❌ |
| maintenance_head | string | ر.د الصيانة والدعم الفني | ❌ |
| it_manager | string | مدير المعلوماتية | ❌ |
| status | enum | الحالة: قيد التنفيذ/تم الإنجاز/تم الإلغاء | ✅ (default) |
| created_by | foreignId | معرف المنشئ | ❌ |
| status_changed_by | foreignId | من غيّر الحالة | ❌ |
| status_changed_at | timestamp | وقت تغيير الحالة | ❌ |
| timestamps | - | created_at, updated_at | ✅ |

### 3. جدول warehouse_deliveries (تسليمات المستودع)

| الحقل | النوع | الوصف | إجباري |
|-------|-------|-------|--------|
| id | bigint | معرف فريد | ✅ |
| requesting_party | string | الجهة الطالبة | ✅ |
| device_type | string | نوع الجهاز | ❌ |
| serial_number | string | الرقم التسلسلي | ❌ |
| description | text | الوصف | ❌ |
| checked_by | string | تم الفحص من قبل | ❌ |
| date | date | التاريخ | ❌ |
| maintenance_manager | string | مدير الصيانة | ❌ |
| it_manager | string | مدير المعلوماتية | ❌ |
| created_by | foreignId | معرف المنشئ | ❌ |
| timestamps | - | created_at, updated_at | ✅ |

---

## الـ Models والعلاقات

### User Model (`app/Models/User.php`)

```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    
    protected $fillable = ['name', 'email', 'password', 'role'];
    
    // علاقة: المستخدم لديه كشوفات فنية
    public function maintenanceReports()
    {
        return $this->hasMany(MaintenanceReport::class, 'created_by');
    }
}
```

**الميزات:**
- **SoftDeletes**: يسمح بتجميد المستخدم بدلاً من الحذف النهائي
- **role**: تحديد نوع المستخدم (manager/employee)

### MaintenanceReport Model (`app/Models/MaintenanceReport.php`)

```php
class MaintenanceReport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'requesting_party', 'reporter_name', 'report_date',
        'device_name', 'brand', 'serial_number', 'initial_inspection',
        'failure_cause', 'device_location', 'technical_manager',
        'maintenance_head', 'it_manager', 'status', 'created_by',
        'status_changed_by', 'status_changed_at'
    ];
    
    protected $casts = [
        'report_date' => 'date',
        'status_changed_at' => 'datetime',
    ];
    
    // علاقة: الكشف ينتمي لمستخدم (المنشئ)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
    
    // علاقة: من غيّر الحالة
    public function statusChanger()
    {
        return $this->belongsTo(User::class, 'status_changed_by')->withTrashed();
    }
}
```

**الميزات:**
- **withTrashed()**: يضمن ظهور اسم المنشئ حتى بعد تجميده
- **status**: تتبع حالة الكشف (قيد التنفيذ/تم الإنجاز/تم الإلغاء)
- **status_changed_by/at**: تتبع من غيّر الحالة ومتى

### WarehouseDelivery Model (`app/Models/WarehouseDelivery.php`)

```php
class WarehouseDelivery extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'requesting_party', 'device_type', 'serial_number',
        'description', 'checked_by', 'date',
        'maintenance_manager', 'it_manager', 'created_by'
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
```

---

## الـ Controllers - آلية العمل

### 1. HomeController (`app/Http/Controllers/HomeController.php`)

**الغرض**: عرض لوحة التحكم الرئيسية مع جميع الكشوفات.

**آلية العمل:**

```php
public function index(Request $request)
{
    $type = $request->get('type', 'all');
    
    $queryReports = MaintenanceReport::with('creator');
    $queryDeliveries = WarehouseDelivery::query();
    
    // فلترة حسب الدور
    if (auth()->user()->role == 'employee') {
        $queryReports->where('created_by', auth()->id());
        $queryDeliveries->where('created_by', auth()->id());
    }
    
    // للمدير: فرز الكشوفات بحيث تظهر قيد التنفيذ أولاً
    if (auth()->user()->role == 'manager') {
        $queryReports->orderByRaw("CASE WHEN status = 'قيد التنفيذ' THEN 1 WHEN status = 'تم الإنجاز' THEN 2 ELSE 3 END");
    }
    
    $reports = $queryReports->get();
    $deliveries = $queryDeliveries->latest()->get();
    
    return view('home', compact('reports', 'deliveries', 'type'));
}
```

**المنطق:**
1. **الموظف**: يرى فقط كشوفاته (فلترة بـ `created_by`)
2. **المدير**: يرى جميع الكشوفات
3. **الفرز**: الكشوفات قيد التنفيذ تظهر أولاً للمدير

### 2. MaintenanceReportController

**آلية إنشاء كشف جديد:**

```php
public function store(Request $request)
{
    // التحقق من البيانات - فقط requesting_party إجباري
    $validated = $request->validate([
        'requesting_party' => 'required|string|max:255',
        'reporter_name' => 'nullable|string|max:255',
        // ... باقي الحقول nullable
    ]);
    
    // تعيين القيم التلقائية
    $validated['created_by'] = auth()->id();
    $validated['status'] = 'قيد التنفيذ';
    $validated['report_date'] = $validated['report_date'] ?? now()->format('Y-m-d');
    
    MaintenanceReport::create($validated);
    return redirect()->route('home')->with('success', 'تم إنشاء الكشف الفني');
}
```

**آلية التحقق من الصلاحيات:**

```php
public function show(MaintenanceReport $maintenanceReport)
{
    // الموظف لا يمكنه رؤية كشوفات غيره
    if (auth()->user()->role === 'employee' && 
        $maintenanceReport->created_by !== auth()->id()) {
        abort(403, 'لا يمكنك الوصول لهذا الكشف');
    }
    return view('maintenance-reports.show', compact('maintenanceReport'));
}
```

**المنطق:**
- إنشاء الكشف: أي مستخدم مسجل
- عرض/تعديل/حذف: الموظف فقط لكشوفاته، المدير للجميع

### 3. WarehouseDeliveryController

**نفس آلية MaintenanceReportController** مع اختلاف الحقول.

### 4. MaintenanceStatusController

**آلية تغيير الحالة (مدير فقط):**

```php
public function update(Request $request, MaintenanceReport $report)
{
    $request->validate([
        'status' => 'required|in:تم الإنجاز,تم الإلغاء',
    ]);
    
    $report->status = $request->status;
    $report->status_changed_by = auth()->id();
    $report->status_changed_at = now();
    $report->save();
    
    return back()->with('success', 'تم تغيير حالة الكشف');
}
```

**المنطق:**
- يمكن فقط للمدير تغيير الحالة
- يتم تسجيل من غيّر الحالة ومتى

### 5. UserController

**آلية تجميد المستخدم (Soft Delete):**

```php
public function destroy(User $user)
{
    if ($user->id === auth()->id()) {
        return back()->with('error', 'لا يمكن تجميد حسابك الخاص');
    }
    
    // الاحتفاظ بعدد الكشوفات المرتبطة
    $reportsCount = $user->maintenanceReports()->count();
    
    $user->delete(); // Soft delete فقط
    
    return redirect()->route('users.index')
        ->with('success', "تم تجميد المستخدم مع الاحتفاظ بـ {$reportsCount} كشف");
}
```

**آلية إعادة التفعيل:**

```php
public function restore($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->restore();
    return redirect()->route('users.index')
        ->with('success', 'تم تفعيل المستخدم بنجاح');
}
```

---

## الـ Routes (`routes/web.php`)

```php
// الصفحة الرئيسية - تسجيل الدخول
Route::get('/', fn() => redirect()->route('login'));

// مسارات المصادقة (بدون تسجيل جديد)
Auth::routes(['register' => false]);

// المسارات المحمية (تتطلب تسجيل دخول)
Route::middleware('auth')->group(function () {
    
    // لوحة التحكم
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // توجيه index إلى الصفحة الرئيسية
    Route::get('/warehouse-deliveries', fn() => redirect()->route('home', ['type' => 'warehouse']));
    Route::get('/maintenance-reports', fn() => redirect()->route('home', ['type' => 'maintenance']));
    
    // CRUD للكشوفات (بدون index)
    Route::resource('warehouse-deliveries', WarehouseDeliveryController::class)->except(['index']);
    Route::resource('maintenance-reports', MaintenanceReportController::class)->except(['index']);
    
    // تغيير الحالة (مدير فقط)
    Route::patch('/maintenance-reports/{report}/status', [MaintenanceStatusController::class, 'update'])
        ->middleware('role:manager');
    
    // البحث
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    
    // إدارة المستخدمين (مدير فقط)
    Route::middleware('role:manager')->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::patch('/users/{user}/restore', [UserController::class, 'restore']);
    });
});
```

---

## نظام الصلاحيات

### CheckRole Middleware (`app/Http/Middleware/CheckRole.php`)

```php
public function handle(Request $request, Closure $next, string $role): Response
{
    if (auth()->check() && auth()->user()->role === $role) {
        return $next($request);
    }
    abort(403, 'غير مصرح');
}
```

### جدول الصلاحيات

| العملية | المدير | الموظف |
|---------|--------|--------|
| إنشاء كشف فني | ✅ | ✅ |
| إنشاء تسليم مستودع | ✅ | ✅ |
| عرض جميع الكشوفات | ✅ | ❌ (كشوفاته فقط) |
| تعديل أي كشف | ✅ | ❌ (كشوفاته فقط) |
| حذف أي كشف | ✅ | ❌ (كشوفاته فقط) |
| تغيير حالة الكشف | ✅ | ❌ |
| إدارة المستخدمين | ✅ | ❌ |
| البحث في جميع الكشوفات | ✅ | ❌ (كشوفاته فقط) |

---

## الـ Views - آلية العرض

### الصفحة الرئيسية (`resources/views/home.blade.php`)

**الوظائف الرئيسية:**

1. **فلترة حسب النوع**: الكل / كشوفات فنية / تسليم مستودع
2. **فلترة حسب الحالة** (للمدير فقط): الكل / قيد التنفيذ / تم الإنجاز والإلغاء
3. **البحث الفوري**: بحث في الجهة، الجهاز، الرقم التسلسلي

**JavaScript للبحث والفلترة:**

```javascript
let currentStatusFilter = 'all';
let searchTimeout = null;

function filterByStatus(status) {
    currentStatusFilter = status;
    applyFilters();
}

function applyFilters() {
    const filterType = document.getElementById('filter-type').value;
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const rows = document.querySelectorAll('.report-row');
    
    rows.forEach(row => {
        const rowType = row.getAttribute('data-type');
        const rowStatus = row.getAttribute('data-status');
        const searchData = row.getAttribute('data-search');
        
        // فلترة حسب النوع
        const matchesType = filterType === 'all' || rowType === filterType;
        
        // فلترة حسب الحالة
        const matchesStatus = currentStatusFilter === 'all' || 
            (currentStatusFilter === 'completed' && 
             (rowStatus === 'تم الإنجاز' || rowStatus === 'تم الإلغاء')) ||
            (currentStatusFilter === rowStatus);
        
        // فلترة حسب البحث
        const matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
        
        row.style.display = (matchesType && matchesStatus && matchesSearch) ? '' : 'none';
    });
    
    renumberRows();
}

// البحث الفوري مع تأخير 300ms
document.getElementById('search-input').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});
```

### صفحة عرض الكشف الفني (`resources/views/maintenance-reports/show.blade.php`)

**الميزات:**
1. **التنسيق الرسمي**: عنوان الجمهورية العربية السورية في الأعلى
2. **دعم الطباعة**: CSS مخصص لإخفاء العناصر غير الضرورية
3. **معلومات المنشئ**: تظهر للمدير فقط
4. **تغيير الحالة**: أزرار إنهاء/إلغاء للمدير فقط

**CSS للطباعة:**

```css
@media print {
    .no-print { display: none !important; }
    .navbar { display: none !important; }
    .container { max-width: 100% !important; }
    .card { border: none !important; box-shadow: none !important; }
    .badge { display: none !important; } /* إخفاء حالة الكشف */
    /* ... تنسيقات إضافية */
}
```

---

## آلية العمل التفصيلية

### 1. تسجيل الدخول

```
المستخدم يدخل email + password
    ↓
التحقق من البيانات (Laravel Auth)
    ↓
التوجيه إلى /home
```

### 2. إنشاء كشف فني

```
المستخدم يضغط "+ كشف فني"
    ↓
عرض النموذج (create.blade.php)
    ↓
ملء الحقول (فقط requesting_party إجباري)
    ↓
إرسال POST إلى /maintenance-reports
    ↓
Validation (تحقق من البيانات)
    ↓
حفظ في قاعدة البيانات مع:
    - created_by = auth()->id()
    - status = 'قيد التنفيذ'
    - report_date = now() (إذا فارغ)
    ↓
التوجيه إلى home مع رسالة نجاح
```

### 3. عرض الكشوفات

```
المدير:
    - يرى جميع الكشوفات
    - الكشوفات قيد التنفيذ أولاً
    - يمكنه فلترة حسب الحالة
    - يرى اسم المنشئ

الموظف:
    - يرى كشوفاته فقط (created_by = his_id)
    - لا يوجد فلترة حسب الحالة
    - لا يرى معلومات المنشئ
```

### 4. تغيير حالة الكشف

```
المدير يضغط "إنهاء" أو "إلغاء"
    ↓
إرسال PATCH إلى /maintenance-reports/{id}/status
    ↓
Middleware يتحقق أن المستخدم مدير
    ↓
تحديث:
    - status = 'تم الإنجاز' أو 'تم الإلغاء'
    - status_changed_by = auth()->id()
    - status_changed_at = now()
    ↓
العودة إلى الصفحة السابقة
```

### 5. تجميد مستخدم

```
المدير يضغط "تجميد" على مستخدم
    ↓
التحقق أنه لا يجمد نفسه
    ↓
$user->delete() // Soft Delete
    ↓
المستخدم يصبح "مجمد" (deleted_at = now())
    ↓
الكشوفات تبقى محفوظة مع اسم المنشئ
    ↓
يمكن إعادة التفعيل لاحقاً (restore)
```

---

## الميزات المتقدمة

### 1. البحث الفوري

- **التأخير**: 300ms لمنع الطلبات الزائدة
- **البحث في**: الجهة، الجهاز، الرقم التسلسلي، الماركة
- **التنفيذ**: JavaScript على جانب العميل (لا يحتاج server)

### 2. الترقيم الديناميكي

```javascript
function renumberRows() {
    const visibleRows = document.querySelectorAll('.report-row:not([style*="display: none"])');
    visibleRows.forEach((row, index) => {
        row.querySelector('.row-number').textContent = index + 1;
    });
}
```

### 3. Soft Deletes للمستخدمين

**فائدة**: الاحتفاظ بسجل المستخدم في الكشوفات حتى بعد "حذفه"

**التنفيذ:**
- `withTrashed()` في العلاقات
- `deleted_at` timestamp
- `restore()` لإعادة التفعيل

### 4. تنسيق الطباعة

**العنوان الرسمي:**
```
الجمهورية العربية السورية
وزارة الإعلام
الهيئة العامة للإذاعة والتلفزيون
مديرية المعلوماتية - دائرة الصيانة
```

**العناصر المخفية عند الطباعة:**
- شريط التنقل (navbar)
- الأزرار (إلا زر الطباعة)
- حالة الكشف (badge)

---

## تدفق البيانات

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   المستخدم     │────▶│   Controller    │────▶│    Database     │
│  (Browser)      │     │  (Laravel)      │     │   (MySQL)       │
└─────────────────┘     └─────────────────┘     └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     Views       │◀────│     Models      │◀────│   Migrations    │
│   (Blade)       │     │  (Eloquent)     │     │   (Schema)      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

---

## الملفات الأساسية

### للتطوير

| الملف | الغرض |
|-------|-------|
| `app/Http/Controllers/HomeController.php` | لوحة التحكم الرئيسية |
| `app/Http/Controllers/MaintenanceReportController.php` | إدارة الكشوفات الفنية |
| `app/Http/Controllers/WarehouseDeliveryController.php` | إدارة تسليمات المستودع |
| `app/Http/Controllers/UserController.php` | إدارة المستخدمين |
| `app/Http/Middleware/CheckRole.php` | التحقق من الصلاحيات |

### للعرض

| الملف | الغرض |
|-------|-------|
| `resources/views/home.blade.php` | الصفحة الرئيسية |
| `resources/views/maintenance-reports/show.blade.php` | عرض كشف فني + طباعة |
| `resources/views/maintenance-reports/create.blade.php` | إنشاء كشف فني |
| `resources/views/warehouse-deliveries/show.blade.php` | عرض تسليم مستودع |

### قاعدة البيانات

| الملف | الغرض |
|-------|-------|
| `database/migrations/2026_04_27_164002_create_maintenance_reports_table.php` | جدول الكشوفات |
| `database/migrations/2026_04_27_163825_create_warehouse_deliveries_table.php` | جدول التسليمات |
| `database/migrations/0001_01_01_000000_create_users_table.php` | جدول المستخدمين |
| `database/migrations/2026_05_01_000000_add_soft_deletes_to_users_table.php` | Soft Deletes |

---

## ملاحظات هامة

1. **الحقول الإجبارية**: فقط `requesting_party` إجباري في كلا النوعين
2. **التوقيت**: مضبوط على `Asia/Damascus`
3. **الترتيب**: للمدير، الكشوفات قيد التنفيذ تظهر أولاً
4. **الأمان**: جميع المسارات محمية بـ `auth` middleware
5. **الصلاحيات**: التحقق على مستوى Controller و Middleware
6. **الحفاظ على البيانات**: Soft Deletes تحافظ على سجل المستخدم في الكشوفات

---

## الأوامر المفيدة

```bash
# تشغيل السيرفر
php artisan serve

# تشغيل migrations
php artisan migrate

# إنشاء مستخدم مدير (عبر Tinker)
php artisan tinker
>>> $user = new App\Models\User();
>>> $user->name = 'مدير النظام';
>>> $user->email = 'admin@example.com';
>>> $user->password = bcrypt('password');
>>> $user->role = 'manager';
>>> $user->save();

# مسح الكاش
php artisan cache:clear
php artisan config:clear
```

---

**تاريخ الإنشاء**: 2 مايو 2026
**الإصدار**: 1.0
**المطور**: BahjatAlNashy/kashef
