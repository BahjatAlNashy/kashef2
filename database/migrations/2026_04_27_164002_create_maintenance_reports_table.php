<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('requesting_party');        // 1-الجهة طالبة الصيانة
            $table->string('reporter_name');           // 2-الاسم والكنية
            $table->date('report_date');               // 3-تاريخ الإبلاغ
            $table->string('device_name');             // 4-اسم الجهاز
            $table->string('brand');                   // 5-الشركة (الماركة)
            $table->string('serial_number');           // 6-الرقم التسلسلي
            $table->text('initial_inspection')->nullable(); // 7-الكشف الفني الأولي
            $table->enum('failure_cause', ['طبيعي', 'سوء استخدام', 'غير ذلك'])->nullable()->default(NULL); // 8
            $table->string('request_party_sign_before'); // 9-اسم وتوقيع الجهة الطالبة (قبل)
            $table->string('technician_sign_before');    // 10-اسم وتوقيع المسؤول الفني (قبل)
            $table->enum('device_location', ['لدى صاحب العلاقة', 'في دائرة الصيانة', 'في الصيانة الخارجية (لجنة الشراء)'])->nullable(); // 11
            $table->enum('maintenance_procedure', ['الاستلام من المستودع', 'في الصيانة الخارجية'])->nullable(); // 12
            $table->text('post_maintenance_notes')->nullable(); // 13-الحالة الفنية بعد الصيانة والملاحظات
            $table->string('request_party_sign_after')->nullable();  // 14
            $table->string('technician_sign_after')->nullable();     // 15
            $table->string('maintenance_head');                      // 16-ر.د الصيانة والدعم الفني
            $table->string('it_manager');                            // 17-مدير المعلوماتية
            $table->enum('status', ['قيد التنفيذ', 'تم الإنجاز', 'تم الإلغاء'])->default('قيد التنفيذ');
            $table->foreignId('created_by')->constrained('users');   // من أنشأ الكشف
            $table->foreignId('status_changed_by')->nullable()->constrained('users'); // من غيّر الحالة
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
