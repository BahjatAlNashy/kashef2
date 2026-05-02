# توثيق نظام تتبع التعديلات (updated_by)

## نظرة عامة

تم إضافة نظام لتتبع **من قام بالتعديل** على الكشوفات الفنية وتسليمات المستودع. يُستخدم هذا النظام لمعرفة المستخدم الذي قام بآخر تعديل على السجل.

---

## الجداول المشمولة

| الجدول | الحقل | الغرض |
|--------|-------|-------|
| `maintenance_reports` | `updated_by` | تتبع من قام بتعديل الكشف الفني |
| `warehouse_deliveries` | `updated_by` | تتبع من قام بتعديل تسليم المستودع |

**ملاحظة**: جدول `users` **غير مشمول** في هذا النظام.

---

## Migrations

### إضافة updated_by للكشوفات الفنية وتسليمات المستودع
**الملف**: `2026_05_02_000000_add_updated_by_to_reports_and_deliveries.php`

```php
public function up(): void
{
    // إضافة updated_by لجدول الكشوفات الفنية
    Schema::table('maintenance_reports', function (Blueprint $table) {
        $table->foreignId('updated_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete()
              ->after('updated_at');
    });

    // إضافة updated_by لجدول تسليمات المستودع
    Schema::table('warehouse_deliveries', function (Blueprint $table) {
        $table->foreignId('updated_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete()
              ->after('updated_at');
    });
}
```

---

## Models

### MaintenanceReport Model (`app/Models/MaintenanceReport.php`)

**الحقول الممكن تعبئتها (fillable):**
```php
protected $fillable = [
    'requesting_party',
    'reporter_name',
    'report_date',
    'device_name',
    'brand',
    'serial_number',
    'initial_inspection',
    'failure_cause',
    'device_location',
    'technical_manager',
    'maintenance_head',
    'it_manager',
    'status',
    'created_by',
    'updated_by',        // ← جديد
    'status_changed_by',
    'status_changed_at',
];
```

**العلاقات:**
```php
// المنشئ
public function creator()
{
    return $this->belongsTo(User::class, 'created_by')->withTrashed();
}

// من غيّر الحالة
public function statusChanger()
{
    return $this->belongsTo(User::class, 'status_changed_by')->withTrashed();
}

// من قام بالتعديل ← جديد
public function updater()
{
    return $this->belongsTo(User::class, 'updated_by')->withTrashed();
}
```

### WarehouseDelivery Model (`app/Models/WarehouseDelivery.php`)

**الحقول الممكن تعبئتها (fillable):**
```php
protected $fillable = [
    'requesting_party',
    'device_type',
    'serial_number',
    'description',
    'checked_by',
    'date',
    'maintenance_manager',
    'it_manager',
    'created_by',
    'updated_by',        // ← جديد
];
```

**العلاقات:**
```php
// المنشئ
public function creator()
{
    return $this->belongsTo(User::class, 'created_by')->withTrashed();
}

// من قام بالتعديل ← جديد
public function updater()
{
    return $this->belongsTo(User::class, 'updated_by')->withTrashed();
}
```

---

## Controllers

### MaintenanceReportController (`app/Http/Controllers/MaintenanceReportController.php`)

**عند الإنشاء (store):**
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    $validated['created_by'] = auth()->id();
    // ملاحظة: updated_by يكون null عند الإنشاء
    $validated['status'] = 'قيد التنفيذ';
    $validated['report_date'] = $validated['report_date'] ?? now()->format('Y-m-d');

    MaintenanceReport::create($validated);
    return redirect()->route('home')->with('success', 'تم إنشاء الكشف الفني');
}
```

**عند التعديل (update):**
```php
public function update(Request $request, MaintenanceReport $maintenanceReport)
{
    // ... التحقق من الصلاحيات ...
    
    $validated = $request->validate([...]);

    $validated['updated_by'] = auth()->id();  // ← تسجيل من قام بالتعديل
    $maintenanceReport->update($validated);
    
    return redirect()->route('maintenance-reports.index')->with('success', 'تم التحديث');
}
```

### WarehouseDeliveryController (`app/Http/Controllers/WarehouseDeliveryController.php`)

**عند الإنشاء (store):**
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    $validated['created_by'] = auth()->id();
    // ملاحظة: updated_by يكون null عند الإنشاء
    $validated['date'] = $validated['date'] ?? now()->format('Y-m-d');

    WarehouseDelivery::create($validated);
    return redirect()->route('home')->with('success', 'تم إضافة تسليم المستودع بنجاح');
}
```

**عند التعديل (update):**
```php
public function update(Request $request, WarehouseDelivery $warehouseDelivery)
{
    // ... التحقق من الصلاحيات ...
    
    $validated = $request->validate([...]);

    $validated['updated_by'] = auth()->id();  // ← تسجيل من قام بالتعديل
    $warehouseDelivery->update($validated);
    
    return redirect()->route('warehouse-deliveries.index')->with('success', 'تم التحديث');
}
```

---

## Views

### عرض الكشف الفني (`resources/views/maintenance-reports/show.blade.php`)

**معلومات الإنشاء والتعديل (للمدير أو صاحب الكشف):**
```blade
<!-- معلومات الإنشاء والتعديل (للمدير أو صاحب الكشف) - في الأعلى -->
@if(auth()->user()->role == 'manager' || $maintenanceReport->created_by == auth()->id())
<div class="no-print mb-2" style="text-align: right; font-size: 12px; color: #6c757d;">
    <span>تاريخ الإنشاء: {{ $maintenanceReport->created_at?->format('Y-m-d H:i') ?? '-' }}</span>
    
    @if($maintenanceReport->updated_by && $maintenanceReport->created_at != $maintenanceReport->updated_at)
    <span class="mx-2">|</span>
    <span>تاريخ آخر تعديل: {{ $maintenanceReport->updated_at?->format('Y-m-d H:i') ?? '-' }}</span>
    <span class="mx-2">|</span>
    <span>آخر تعديل بواسطة: <strong>{{ $maintenanceReport->updater?->name ?? 'غير معروف' }}</strong></span>
    @endif
    
    @if(auth()->user()->role == 'manager')
    <br>
    <span>المنشئ: <strong>{{ $maintenanceReport->creator?->name ?? 'غير معروف' }}</strong></span>
    @endif
</div>
@endif
```

**المنطق:**
- يظهر للمدير أو صاحب الكشف فقط
- يعرض تاريخ الإنشاء دائماً
- يعرض تاريخ التعديل ومن قام بالتعديل **فقط إذا**:
  - `updated_by` ليس null
  - تاريخ التعديل مختلف عن تاريخ الإنشاء
- يعرض اسم المنشئ **للمدير فقط**

### عرض تسليم المستودع (`resources/views/warehouse-deliveries/show.blade.php`)

**نفس المنطق تماماً** مع استبدال `$maintenanceReport` بـ `$warehouseDelivery`.

---

## سلوك النظام

### عند الإنشاء
| الحقل | القيمة |
|-------|--------|
| `created_by` | ID المستخدم المنشئ |
| `updated_by` | null |
| `created_at` | الوقت الحالي |
| `updated_at` | الوقت الحالي |

### عند التعديل الأول
| الحقل | القيمة |
|-------|--------|
| `updated_by` | ID المستخدم المُعدّل |
| `updated_at` | الوقت الحالي |

### عند التعديل الثاني (وما بعده)
| الحقل | القيمة |
|-------|--------|
| `updated_by` | ID آخر مستخدم قام بالتعديل |
| `updated_at` | وقت آخر تعديل |

**ملاحظة**: النظام يحتفظ **فقط بآخر تعديل**، ولا يحتفظ بتاريخ التعديلات السابقة.

---

## الأوامر المطلوبة

### تشغيل Migrations
```bash
php artisan migrate
```

### التراجع عن Migrations (إذا لزم الأمر)
```bash
php artisan migrate:rollback --step=2
```

---

## ملخص التغييرات

| الملف | التغيير |
|-------|---------|
| `database/migrations/2026_05_02_000000_add_updated_by_to_reports_and_deliveries.php` | جديد - إضافة حقل updated_by للكشوفات الفنية وتسليمات المستودع |
| `app/Models/MaintenanceReport.php` | إضافة updated_by إلى fillable + علاقة updater() |
| `app/Models/WarehouseDelivery.php` | إضافة updated_by إلى fillable + علاقة updater() |
| `app/Http/Controllers/MaintenanceReportController.php` | تسجيل updated_by في method update() |
| `app/Http/Controllers/WarehouseDeliveryController.php` | تسجيل updated_by في method update() |
| `resources/views/maintenance-reports/show.blade.php` | إضافة عرض معلومات التعديل |
| `resources/views/warehouse-deliveries/show.blade.php` | إضافة عرض معلومات التعديل |

---

## أمان البيانات

- عند حذف المستخدم (Soft Delete)، يبقى `updated_by` محفوظاً ولا يُحذف
- العلاقة `updater()` تستخدم `withTrashed()` لضمان عرض اسم المستخدم حتى بعد تجميده
- المستخدمون غير المخوّلين لا يرون معلومات التعديل

---

**تاريخ التعديل**: 2 مايو 2026
