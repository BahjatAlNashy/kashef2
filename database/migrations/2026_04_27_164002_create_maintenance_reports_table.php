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
            $table->string('requesting_party');                    // 1-الجهة طالبة الصيانة (إجباري)
            $table->string('reporter_name')->nullable();             // 2-الاسم والكنية
            $table->date('report_date')->nullable();                 // 3-تاريخ الإبلاغ
            $table->string('device_name')->nullable();               // 4-اسم الجهاز
            $table->string('brand')->nullable();                     // 5-الشركة (الماركة)
            $table->string('serial_number')->nullable();             // 6-الرقم التسلسلي
            $table->text('initial_inspection')->nullable();          // 7-الكشف الفني الأولي
            $table->enum('failure_cause', ['طبيعي', 'سوء استخدام', 'غير ذلك'])->nullable(); // 8-سبب العطل
            $table->enum('device_location', ['لدى صاحب العلاقة', 'في دائرة الصيانة', 'في الصيانة الخارجية (لجنة الشراء)'])->nullable(); // 9-مكان الجهاز
            $table->string('technical_manager')->nullable();         // 10-المسؤول الفني (جديد)
            $table->string('maintenance_head')->nullable();          // 11-ر.د الصيانة والدعم الفني
            $table->string('it_manager')->nullable();                // 12-مدير المعلوماتية
            $table->enum('status', ['قيد التنفيذ', 'تم الإنجاز', 'تم الإلغاء'])->default('قيد التنفيذ');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();   // من أنشأ الكشف
            $table->foreignId('status_changed_by')->nullable()->constrained('users')->nullOnDelete(); // من غيّر الحالة
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
