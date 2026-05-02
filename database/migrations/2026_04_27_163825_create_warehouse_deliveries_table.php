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
        Schema::create('warehouse_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('requesting_party');      // الجهة الطالبة (إجباري)
            $table->string('device_type')->nullable();      // نوع الجهاز
            $table->string('serial_number')->nullable();    // الرقم التسلسلي
            $table->text('description')->nullable();          // الوصف
            $table->string('checked_by')->nullable();        // تم الفحص من قبل
            $table->date('date')->nullable();                 // التاريخ
            $table->string('maintenance_manager')->nullable(); // مدير الصيانة
            $table->string('it_manager')->nullable();          // مدير المعلوماتية
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_deliveries');
    }
};
