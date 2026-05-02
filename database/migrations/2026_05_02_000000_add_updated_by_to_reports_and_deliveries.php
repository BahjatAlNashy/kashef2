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
        // إضافة updated_by لجدول الكشوفات الفنية
        Schema::table('maintenance_reports', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_at');
        });

        // إضافة updated_by لجدول تسليمات المستودع
        Schema::table('warehouse_deliveries', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف updated_by من جدول الكشوفات الفنية
        Schema::table('maintenance_reports', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });

        // حذف updated_by من جدول تسليمات المستودع
        Schema::table('warehouse_deliveries', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};
