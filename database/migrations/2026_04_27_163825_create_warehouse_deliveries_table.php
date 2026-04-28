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
            $table->string('requesting_party'); // الجهة الطالبة
            $table->string('device_type');      // نوع الجهاز
            $table->string('serial_number')->unique();
            $table->text('description')->nullable(); // الوصف
            $table->string('checked_by');       // تم الفحص من قبل
            $table->date('date');               // التاريخ
            $table->string('maintenance_manager'); // مدير الصيانة
            $table->string('it_manager');          // مدير المعلوماتية
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
