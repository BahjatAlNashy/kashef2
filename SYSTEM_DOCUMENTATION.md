# نظام إدارة الصيانة والمستودع - التوثيق الفني

## نظرة عامة
نظام Laravel لإدارة الكشوفات الفنية وتسليمات المستودع مع نظام مستخدمين متكامل.

---

## المميزات الرئيسية

### 1. إدارة المستخدمين
- **صلاحيات المدير**: إدارة جميع الكشوفات والمستخدمين
- **صلاحيات الموظف**: إدارة الكشوفات التي أنشأها فقط
- **نظام التجميد (Soft Delete)**: تجميد المستخدمين بدلاً من حذفهم نهائياً

### 2. الكشوفات الفنية
- إنشاء وعرض وتعديل كشوفات صيانة الأجهزة
- حقول: الجهة طالبة، الجهاز، الماركة، الرقم التسلسلي، الفحص الأولي، سبب العطل، الموقع
- **حالات الكشف**: قيد التنفيذ، تم الإنجاز، تم الإلغاء
- **الاعتمادات**: المسؤول الفني، رئيس الصيانة، مدير المعلوماتية

### 3. تسليمات المستودع
- إنشاء سجلات تسليم أجهزة من المستودع
- حقول: الجهة طالبة، نوع الجهاز، الرقم التسلسلي، الوصف، الفحص، التاريخ، الاعتمادات

### 4. البحث والفلترة
- البحث في جميع الكشوفات
- فلترة حسب النوع والحالة
- البحث حسب الجهة أو الرقم التسلسلي

---

## البنية التقنية

### قاعدة البيانات

#### جدول `users`
| الحقل | النوع | الوصف |
|-------|-------|-------|
| id | bigint | معرف فريد |
| name | string | اسم المستخدم |
| email | string | البريد الإلكتروني (فريد) |
| password | string | كلمة المرور (مشفرة) |
| role | enum | الدور: manager / employee |
| deleted_at | timestamp | للتجميد (Soft Delete) |
| timestamps | - | created_at, updated_at |

#### جدول `maintenance_reports`
| الحقل | النوع | الوصف |
|-------|-------|-------|
| id | bigint | معرف فريد |
| requesting_party | string | **إجباري** - الجهة طالبة الصيانة |
| reporter_name | string | اسم المبلغ (اختياري) |
| report_date | date | تاريخ الكشف (اختياري) |
| device_name | string | اسم الجهاز (اختياري) |
| brand | string | الماركة (اختياري) |
| serial_number | string | الرقم التسلسلي (اختياري) |
| initial_inspection | text | الفحص الأولي (اختياري) |
| failure_cause | text | سبب العطل (اختياري) |
| device_location | string | موقع الجهاز (اختياري) |
| technical_manager | string | المسؤول الفني (اختياري) |
| maintenance_head | string | رئيس قسم الصيانة (اختياري) |
| it_manager | string | مدير المعلوماتية (اختياري) |
| status | string | الحالة: قيد التنفيذ / تم الإنجاز / تم الإلغاء |
| created_by | bigint | معرف المنشئ ( foreign key ) |
| status_changed_by | bigint | معرف من غيّر الحالة |
| status_changed_at | datetime | وقت تغيير الحالة |
| timestamps | - | created_at, updated_at |

#### جدول `warehouse_deliveries`
| الحقل | النوع | الوصف |
|-------|-------|-------|
| id | bigint | معرف فريد |
| requesting_party | string | **إجباري** - الجهة طالبة |
| device_type | string | نوع الجهاز (اختياري) |
| serial_number | string | الرقم التسلسلي (اختياري) |
| description | text | الوصف (اختياري) |
| checked_by | string | تم الفحص من قبل (اختياري) |
| date | date | التاريخ (اختياري) |
| maintenance_manager | string | مدير الصيانة (اختياري) |
| it_manager | string | مدير المعلوماتية (اختياري) |
| created_by | bigint | معرف المنشئ ( foreign key ) |
| timestamps | - | created_at, updated_at |

---

## النماذج (Models)

### User
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    
    // العلاقات
    public function maintenanceReports()
    {
        return $this->hasMany(MaintenanceReport::class, 'created_by');
    }
}
```

### MaintenanceReport
```php
class MaintenanceReport extends Model
{
    protected $fillable = [
        'requesting_party', 'reporter_name', 'report_date',
        'device_name', 'brand', 'serial_number', 'initial_inspection',
        'failure_cause', 'device_location', 'technical_manager',
        'maintenance_head', 'it_manager', 'status', 'created_by',
        'status_changed_by', 'status_changed_at'
    ];
    
    // العلاقات
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
    
    public function statusChanger()
    {
        return $this->belongsTo(User::class, 'status_changed_by')->withTrashed();
    }
}
```

### WarehouseDelivery
```php
class WarehouseDelivery extends Model
{
    protected $fillable = [
        'requesting_party', 'device_type', 'serial_number',
        'description', 'checked_by', 'date',
        'maintenance_manager', 'it_manager', 'created_by'
    ];
    
    // العلاقات
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
```

---

## المسارات (Routes)

```php
// صفحة تسجيل الدخول
Route::get('/', fn() => redirect()->route('login'));

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    // لوحة التحكم
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // الكشوفات الفنية
    Route::resource('maintenance-reports', MaintenanceReportController::class)
        ->except(['index']);
    
    // تسليمات المستودع
    Route::resource('warehouse-deliveries', WarehouseDeliveryController::class)
        ->except(['index']);
    
    // تغيير حالة الكشف
    Route::patch('/maintenance-reports/{report}/status', 
        [MaintenanceStatusController::class, 'update'])
        ->name('maintenance.status.update')
        ->middleware('role:manager');
    
    // البحث
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    
    // إدارة المستخدمين (مدير فقط)
    Route::middleware('role:manager')->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::patch('/users/{user}/restore', [UserController::class, 'restore'])
            ->name('users.restore');
    });
});
```

---

## صلاحيات المستخدمين

### المدير (manager)
- إنشاء/عرض/تعديل/حذف جميع الكشوفات
- تغيير حالة الكشوفات (إنهاء/إلغاء)
- إدارة المستخدمين (إضافة/تعديل/تجميد/تفعيل)
- البحث في جميع الكشوفات
- رؤية معلومات المنشئ في صفحة العرض

### الموظف (employee)
- إنشاء كشوفات جديدة
- عرض/تعديل/حذف الكشوفات التي أنشأها فقط
- البحث في الكشوفات

---

## التحقق من البيانات (Validation)

### الكشف الفني
```php
$validated = $request->validate([
    'requesting_party' => 'required|string|max:255',  // إجباري فقط
    'reporter_name' => 'nullable|string|max:255',
    'report_date' => 'nullable|date',
    'device_name' => 'nullable|string|max:255',
    'brand' => 'nullable|string|max:255',
    'serial_number' => 'nullable|string|max:255',
    'initial_inspection' => 'nullable|string',
    'failure_cause' => 'nullable|string',
    'device_location' => 'nullable|string|max:255',
    'technical_manager' => 'nullable|string|max:255',
    'maintenance_head' => 'nullable|string|max:255',
    'it_manager' => 'nullable|string|max:255',
]);
```

### تسليم المستودع
```php
$validated = $request->validate([
    'requesting_party' => 'required|string|max:255',  // إجباري فقط
    'device_type' => 'nullable|string|max:255',
    'serial_number' => 'nullable|string|max:255',
    'description' => 'nullable|string',
    'checked_by' => 'nullable|string|max:255',
    'date' => 'nullable|date',
    'maintenance_manager' => 'nullable|string|max:255',
    'it_manager' => 'nullable|string|max:255',
]);
```

---

## نظام التجميد (Soft Delete)

### فكرة العمل
- عند "حذف" مستخدم، يتم تجميده فقط (Soft Delete)
- البيانات تبقى محفوظة مع اسم المستخدم في الكشوفات
- يمكن إعادة تفعيل المستخدم لاحقاً

### في نموذج User
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    // ...
}
```

### في العلاقات
```php
// لضمان ظهور اسم المستخدم حتى بعد تجميده
public function creator()
{
    return $this->belongsTo(User::class, 'created_by')->withTrashed();
}
```

### في Controller
```php
// تجميد مستخدم
public function destroy(User $user)
{
    $user->delete(); // Soft delete
    return redirect()->route('users.index')
        ->with('success', 'تم تجميد المستخدم بنجاح');
}

// تفعيل مستخدم مجمد
public function restore($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->restore();
    return redirect()->route('users.index')
        ->with('success', 'تم تفعيل المستخدم بنجاح');
}
```

---

## تنسيقات الطباعة

### الكشف الفني (maintenance-reports/show.blade.php)
- العنوان في الزاوية اليمنى العليا (الجمهورية العربية السورية...)
- معلومات المنشئ تظهر في الأسفل (للمدير فقط)
- تنسيقات CSS مخصصة للطباعة
- أعمدة ثابتة عبر المتصفحات

### تسليم المستودع (warehouse-deliveries/show.blade.php)
- نفس تنسيق الكشف الفني
- أزرار التحكم تُخفى عند الطباعة

---

## الأوامر المهمة

```bash
# تشغيل السيرفر المحلي
php artisan serve

# تنفيذ migrations
php artisan migrate

# إنشاء migration جديد
php artisan make:migration add_soft_deletes_to_users_table --table=users

# تحديث البيانات (إذا لزم الأمر)
php artisan migrate:fresh --seed
```

---

## الأمان

1. **Middleware `auth`**: جميع المسارات محمية بتسجيل الدخول
2. **Middleware `role:manager`**: بعض المسارات محصورة على المدير
3. **التحقق من الملكية**: في `edit`, `update`, `destroy` - التحقق أن المستخدم هو منشئ الكشف أو مدير
4. **Soft Deletes**: حماية البيانات من الحذف العرضي

---

## ملاحظات هامة

1. **الحقل الإجباري الوحيد**: `requesting_party` (الجهة طالبة) في كلا النوعين
2. **الاعتمادات**: جميعها اختيارية (technical_manager, maintenance_head, it_manager...)
3. **اسم المنشئ**: يظل محفوظاً حتى بعد تجميد المستخدم بفضل `withTrashed()`
4. **الطباعة**: استخدم `window.print()` مع CSS مخصص لضبط التنسيق

---

## الملفات الرئيسية

### Controllers
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/MaintenanceReportController.php`
- `app/Http/Controllers/WarehouseDeliveryController.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/SearchController.php`
- `app/Http/Controllers/MaintenanceStatusController.php`

### Models
- `app/Models/User.php`
- `app/Models/MaintenanceReport.php`
- `app/Models/WarehouseDelivery.php`

### Views
- `resources/views/home.blade.php`
- `resources/views/maintenance-reports/*.blade.php`
- `resources/views/warehouse-deliveries/*.blade.php`
- `resources/views/users/*.blade.php`
- `resources/views/search/index.blade.php`

### Routes
- `routes/web.php`

### Migrations
- `database/migrations/2026_04_27_164002_create_maintenance_reports_table.php`
- `database/migrations/2026_04_27_163825_create_warehouse_deliveries_table.php`
- `database/migrations/2026_05_01_000000_add_soft_deletes_to_users_table.php`
