# نظام إدارة الصيانة والمستودع - التوثيق الشامل

## نظرة عامة
نظام لإدارة كشوفات الصيانة الفنية وتسليمات المستودع، مع دعم الصلاحيات (مدير/موظف) والبحث الفوري والطباعة.

---

## هيكل قاعدة البيانات

### جدول maintenance_reports
- **id**: المفتاح الأساسي
- **requesting_party**: الجهة طالبة الصيانة ✅ **إجباري**
- **reporter_name**: الاسم والكنية (nullable)
- **report_date**: تاريخ الإبلاغ (nullable)
- **device_name**: اسم الجهاز (nullable)
- **brand**: الماركة (nullable)
- **serial_number**: الرقم التسلسلي (nullable, unique)
- **initial_inspection**: الكشف الفني الأولي (nullable)
- **failure_cause**: سبب العطل (nullable, in: طبيعي,سوء استخدام,غير ذلك)
- **request_party_sign_before**: اسم وتوقيع الجهة الطالبة (قبل الصيانة) ✅ **إجباري**
- **technician_sign_before**: اسم وتوقيع المسؤول الفني (قبل الصيانة) ✅ **إجباري**
- **device_location**: مكان تواجد الجهاز (nullable, in: لدى صاحب العلاقة,في دائرة الصيانة,في الصيانة الخارجية)
- **maintenance_procedure**: الإجراءات المتبعة (nullable, in: الاستلام من المستودع,في الصيانة الخارجية)
- **post_maintenance_notes**: الحالة الفنية بعد الصيانة والملاحظات (nullable)
- **request_party_sign_after**: اسم وتوقيع الجهة بعد الصيانة (nullable)
- **technician_sign_after**: اسم وتوقيع المسؤول الفني (بعد الصيانة) ✅ **إجباري**
- **maintenance_head**: ر.د الصيانة والدعم الفني (nullable)
- **it_manager**: مدير المعلوماتية (nullable)
- **status**: حالة الكشف (default: قيد التنفيذ, in: قيد التنفيذ,تم الإنجاز,تم الإلغاء)
- **created_by**: معرف المستخدم الذي أنشأ الكشف (foreign key on users)
- **created_at**: تاريخ الإنشاء
- **updated_at**: تاريخ التعديل

### جدول warehouse_deliveries
- **id**: المفتاح الأساسي
- **device_type**: نوع الجهاز (nullable)
- **serial_number**: الرقم التسلسلي (nullable)
- **requesting_party**: الجهة الطالبة ✅ **إجباري**
- **description**: الوصف (nullable)
- **date**: التاريخ (nullable)
- **checked_by**: تم الفحص من قبل ✅ **إجباري**
- **maintenance_manager**: مدير الصيانة والدعم الفني (nullable)
- **it_manager**: مدير المعلوماتية (nullable)
- **created_by**: معرف المستخدم الذي أنشأ التسليم (foreign key on users)
- **created_at**: تاريخ الإنشاء
- **updated_at**: تاريخ التعديل

### جدول users
- **id**: المفتاح الأساسي
- **name**: الاسم ✅ **إجباري**
- **email**: البريد الإلكتروني (unique) ✅ **إجباري**
- **password**: كلمة المرور (hashed) ✅ **إجباري**
- **role**: الدور (in: manager, employee) ✅ **إجباري**
- **created_at**: تاريخ الإنشاء
- **updated_at**: تاريخ التعديل

---

## ملخص الحقول الإجبارية

### الكشف الفني (Maintenance Reports)
الحقول الإجبارية عند الإنشاء والتعديل:

| الحقل | الوصف |
|-------|-------|
| `requesting_party` | الجهة طالبة الصيانة |
| `request_party_sign_before` | اسم وتوقيع الجهة الطالبة (قبل الصيانة) |
| `technician_sign_before` | اسم وتوقيع المسؤول الفني (قبل الصيانة) |
| `technician_sign_after` | اسم وتوقيع المسؤول الفني (بعد الصيانة) |

**ملاحظة**: جميع الحقول الأخرى اختيارية (nullable).

### تسليم المستودع (Warehouse Deliveries)
الحقول الإجبارية عند الإنشاء والتعديل:

| الحقل | الوصف |
|-------|-------|
| `requesting_party` | الجهة الطالبة |
| `checked_by` | تم الفحص من قبل |

**ملاحظة**: جميع الحقول الأخرى اختيارية (nullable).

---

## Controllers

### HomeController
**المسار**: `app/Http/Controllers/HomeController.php`

**الدوال**:
- `index(Request $request)`: عرض الصفحة الرئيسية
  - للموظف: يعرض كشوفه فقط
  - للمدير: يعرض جميع الكشوف مع فرز الكشوفات الفنية (قيد التنفيذ أولاً)
  - يدعم الفلترة حسب النوع (maintenance, warehouse, all)

### MaintenanceReportController
**المسار**: `app/Http/Controllers/MaintenanceReportController.php`

**الدوال**:
- `index(Request $request)`: (مُحال إلى الصفحة الرئيسية)
- `create()`: عرض نموذج إنشاء كشف فني
- `store(Request $request)`: حفظ كشف فني جديد
  - التحقق من جميع الحقول المطلوبة
  - التحقق من تفرد الرقم التسلسلي (إذا كان موجوداً)
  - تعيين created_by و status تلقائياً
  - التوجيه إلى الصفحة الرئيسية بعد الحفظ
- `show(MaintenanceReport $maintenanceReport)`: عرض تفاصيل كشف فني
  - التحقق من الصلاحيات (الموظف يرى كشوفه فقط)
- `edit(MaintenanceReport $maintenanceReport)`: عرض نموذج تعديل كشف فني
  - التحقق من الصلاحيات
- `update(Request $request, MaintenanceReport $maintenanceReport)`: تحديث كشف فني
  - التحقق من الصلاحيات
- `destroy(MaintenanceReport $maintenanceReport)`: حذف كشف فني
  - التحقق من الصلاحيات

### WarehouseDeliveryController
**المسار**: `app/Http/Controllers/WarehouseDeliveryController.php`

**الدوال**:
- `index(Request $request)`: (مُحال إلى الصفحة الرئيسية)
- `create()`: عرض نموذج إنشاء تسليم مستودع
- `store(Request $request)`: حفظ تسليم مستودع جديد
  - التحقق من جميع الحقول المطلوبة
  - تعيين created_by تلقائياً
  - التوجيه إلى الصفحة الرئيسية بعد الحفظ
- `show(WarehouseDelivery $warehouseDelivery)`: عرض تفاصيل تسليم مستودع
- `edit(WarehouseDelivery $warehouseDelivery)`: عرض نموذج تعديل تسليم مستودع
  - التحقق من الصلاحيات
- `update(Request $request, WarehouseDelivery $warehouseDelivery)`: تحديث تسليم مستودع
  - التحقق من الصلاحيات
- `destroy(WarehouseDelivery $warehouseDelivery)`: حذف تسليم مستودع
  - التحقق من الصلاحيات

### MaintenanceStatusController
**المسار**: `app/Http/Controllers/MaintenanceStatusController.php`

**الدوال**:
- `update(Request $request, MaintenanceReport $report)`: تحديث حالة كشف فني
  - مخصص للمدير فقط
  - يمكن تغيير الحالة إلى "تم الإنجاز" أو "تم الإلغاء"

### UserController
**المسار**: `app/Http/Controllers/UserController.php`

**الدوال**:
- `index()`: عرض جميع المستخدمين (مدير فقط)
- `create()`: عرض نموذج إنشاء مستخدم (مدير فقط)
- `store(Request $request)`: حفظ مستخدم جديد (مدير فقط)
  - الحقول: name, email (unique), password, role
- `edit(User $user)`: عرض نموذج تعديل مستخدم (مدير فقط)
- `update(Request $request, User $user)`: تحديث مستخدم (مدير فقط)
  - يمكن تغيير name, email, role
  - يمكن تغيير password (اختياري)
- `destroy(User $user)`: حذف مستخدم (مدير فقط)
  - يحذف المستخدم مع الاحتفاظ بكشوفاته (created_by = null)

---

## Routes

**المسار**: `routes/web.php`

### المسارات العامة
- `/`: توجيه إلى صفحة تسجيل الدخول
- `/home`: الصفحة الرئيسية (محمي بالمصادقة)

### مسارات الكشوفات الفنية
- `GET /maintenance-reports`: (مُحال إلى الصفحة الرئيسية مع فلتر maintenance)
- `GET /maintenance-reports/create`: نموذج إنشاء كشف فني
- `POST /maintenance-reports`: حفظ كشف فني
- `GET /maintenance-reports/{id}`: عرض كشف فني
- `GET /maintenance-reports/{id}/edit`: تعديل كشف فني
- `PUT/PATCH /maintenance-reports/{id}`: تحديث كشف فني
- `DELETE /maintenance-reports/{id}`: حذف كشف فني
- `PATCH /maintenance-reports/{report}/status`: تحديث حالة كشف فني (مدير فقط)

### مسارات تسليم المستودع
- `GET /warehouse-deliveries`: (مُحال إلى الصفحة الرئيسية مع فلتر warehouse)
- `GET /warehouse-deliveries/create`: نموذج إنشاء تسليم مستودع
- `POST /warehouse-deliveries`: حفظ تسليم مستودع
- `GET /warehouse-deliveries/{id}`: عرض تسليم مستودع
- `GET /warehouse-deliveries/{id}/edit`: تعديل تسليم مستودع
- `PUT/PATCH /warehouse-deliveries/{id}`: تحديث تسليم مستودع
- `DELETE /warehouse-deliveries/{id}`: حذف تسليم مستودع

### مسارات المستخدمين (مدير فقط)
- `GET /users`: عرض جميع المستخدمين
- `GET /users/create`: نموذج إنشاء مستخدم
- `POST /users`: حفظ مستخدم
- `GET /users/{id}/edit`: نموذج تعديل مستخدم
- `PUT/PATCH /users/{id}`: تحديث مستخدم
- `DELETE /users/{id}`: حذف مستخدم

---

## Views

### الصفحة الرئيسية
**المسار**: `resources/views/home.blade.php`

**الميزات**:
- جدول موحد يعرض جميع الكشوفات (الكشوفات الفنية + تسليم المستودع)
- للمدير: أزرار فلترة حسب الحالة (الكل، قيد التنفيذ، تم الإنجاز والإلغاء) مع عدادات
- للموظف: يرى كشوفه فقط بدون أزرار الفلترة
- فلترة حسب النوع (الكل، الكشوفات الفنية، تسليم المستودع)
- بحث فوري مع تأخير 300ms
- أزرار إنشاء كشف فني وتسليم مستودع في العنوان

### صفحات الكشوفات الفنية
- `create.blade.php`: نموذج إنشاء كشف فني
- `show.blade.php`: عرض تفاصيل كشف فني مع دعم الطباعة
- `edit.blade.php`: نموذج تعديل كشف فني

### صفحات تسليم المستودع
- `create.blade.php`: نموذج إنشاء تسليم مستودع
- `show.blade.php`: عرض تفاصيل تسليم مستودع مع دعم الطباعة
- `edit.blade.php`: نموذج تعديل تسليم مستودع

### صفحات المصادقة
- `login.blade.php`: صفحة تسجيل الدخول
- `register.blade.php`: (معطل)
- `passwords/reset.blade.php`: إعادة تعيين كلمة المرور (معطل رابط النسيان)
- `passwords/confirm.blade.php`: تأكيد كلمة المرور

---

## الصلاحيات

### المدير (manager)
- يرى جميع الكشوفات الفنية وتسليمات المستودع
- يمكنه إنشاء وتعديل وحذف جميع الكشوفات
- يمكنه تغيير حالة الكشوفات الفنية (إنهاء/إلغاء)
- يمكنه إدارة المستخدمين (إنشاء وحذف)
- يرى أزرار الفلترة حسب الحالة مع عدادات
- الكشوفات الفنية تُفرز بحيث تظهر قيد التنفيذ أولاً

### الموظف (employee)
- يرى فقط الكشوفات التي أنشأها
- يمكنه إنشاء وتعديل وحذف كشوفه فقط
- لا يمكنه تغيير حالة الكشوفات
- لا يمكنه إدارة المستخدمين
- لا تظهر أزرار الفلترة حسب الحالة

---

## الميزات الرئيسية

### 1. البحث الفوري
- بحث فوري في الصفحة الرئيسية مع تأخير 300ms
- البحث في: الجهة، اسم الجهاز/النوع، الرقم التسلسلي، الماركة
- يعمل مع الفلترة حسب النوع والحالة

### 2. الفلترة
- فلترة حسب النوع (الكل، الكشوفات الفنية، تسليم المستودع)
- للمدير: فلترة حسب الحالة (الكل، قيد التنفيذ، تم الإنجاز والإلغاء)
- يمكن الجمع بين الفلاتر

### 3. الطباعة
- دعم الطباعة لصفحات العرض
- تنسيق خاص للطباعة:
  - إخفاء العناصر غير الضرورية (navbar, footer)
  - إخفاء حالة الكشف عند الطباعة
  - إزالة الحدود والظلال
  - دعم التفاف النص للنصوص الطويلة

### 4. تنسيق العنوان
- العنوان منسق على عدة أسطر:
  - الجمهورية العربية السورية
  - وزارة الإعلام
  - الهيئة العامة للإذاعة والتلفزيون
  - مديرية المعلوماتية - دائرة الصيانة
- جميع الأسطر يمين المحاذاة
- اسم الكشف في المنتصف

### 5. التوقيت
- التوقيت مضبوط على `Asia/Damascus`
- التواريخ تُعرض بالتوقيت الصحيح

### 6. إظهار/إخفاء كلمة المرور
- أزرار إظهار/إخفاء كلمة المرور في جميع صفحات المصادقة
- استخدام الرمز 👁️

### 7. إدارة المستخدمين (مدير فقط)
- عرض جميع المستخدمين مع عداد في الصفحة الرئيسية
- إنشاء مستخدم جديد (name, email, password, role)
- تعديل المستخدم (name, email, role, password اختياري)
- حذف المستخدم مع الاحتفاظ بكشوفاته
- نموذج أفقي لإنشاء وتعديل المستخدمين
- إشعارات صغيرة قابلة للإغلاق

---

## الملفات المعدلة

### Controllers
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/MaintenanceReportController.php`
- `app/Http/Controllers/WarehouseDeliveryController.php`

### Views
- `resources/views/home.blade.php`
- `resources/views/maintenance-reports/create.blade.php`
- `resources/views/maintenance-reports/show.blade.php`
- `resources/views/warehouse-deliveries/create.blade.php`
- `resources/views/warehouse-deliveries/show.blade.php`
- `resources/views/auth/passwords/reset.blade.php`
- `resources/views/auth/passwords/confirm.blade.php`

### Config
- `config/app.php` (التوقيت)

### Routes
- `routes/web.php` (توجيه صفحات index إلى الصفحة الرئيسية)

### Migrations
- `database/migrations/2026_04_27_221318_add_created_by_to_warehouse_deliveries_table.php`

---

## التثبيت والتشغيل

### المتطلبات
- PHP 8.x
- Laravel 10.x
- MySQL/MariaDB
- Composer

### خطوات التثبيت
1. استنساخ المشروع
2. تثبيت الاعتماديات: `composer install`
3. نسخ ملف البيئة: `cp .env.example .env`
4. إنشاء مفتاح التشفير: `php artisan key:generate`
5. إعداد قاعدة البيانات في `.env`
6. تشغيل المigrations: `php artisan migrate`
7. تشغيل الخادم: `php artisan serve`

### إنشاء مستخدم مدير
```bash
php artisan tinker
>>> $user = new App\Models\User();
>>> $user->name = 'مدير النظام';
>>> $user->email = 'admin@example.com';
>>> $user->password = bcrypt('admin123');
>>> $user->role = 'manager';
>>> $user->save();
```

---

## ملاحظات مهمة

1. **التاريخ**: تم تعديل التوقيت إلى `Asia/Damascus` لحل مشكلة التاريخ
2. **الرقم التسلسلي**: التحقق من التفرد فقط إذا كان الرقم التسلسلي موجوداً
3. **الصفحات المنفصلة**: تم دمج صفحات index في الصفحة الرئيسية
4. **النصوص الطويلة**: تم إضافة دعم التفاف النص في صفحات العرض والطباعة
5. **البحث**: البحث فوري مع تأخير لمنع الطلبات الزائدة
6. **الصلاحيات**: الفلترة على مستوى قاعدة البيانات في HomeController

---

## تاريخ التعديلات

### 28 أبريل 2026 (مساءً)
- تعديل الحقول الإجبارية في الكشوفات:
  - الكشف الفني: 4 حقول إجبارية فقط (الجهة الطالبة + توقيع الجهة الطالبة + توقيع المسؤول الفني قبل وبعد الصيانة)
  - تسليم المستودع: حقلين إجباريين فقط (الجهة الطالبة + تم الفحص من قبل)
- إضافة توثيق شاملة للحقول الإجبارية
- تحديث ملف DOCUMENTATION.md

### 28 أبريل 2026 (صباحاً)
- دمج صفحات index في الصفحة الرئيسية
- إضافة البحث الفوري في الصفحة الرئيسية
- إضافة الفلترة حسب الحالة للمدير
- إضافة عدادات للكشوفات قيد التنفيذ
- إصلاح حفظ الكشوفات الفنية وتسليم المستودع
- إضافة دعم التفاف النص للطباعة
- تعديل التوقيت إلى Asia/Damascus
- إضافة فلترة الكشوفات للموظف (يرى كشوفه فقط)
