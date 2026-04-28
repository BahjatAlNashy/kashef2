<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التحقق من عدم وجود المستخدم مسبقاً
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'مدير النظام',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role' => 'manager',
            ]);

            $this->command->info('تم إنشاء مستخدم المدير بنجاح');
            $this->command->info('البريد الإلكتروني: admin@example.com');
            $this->command->info('كلمة المرور: admin123');
        } else {
            $this->command->info('مستخدم المدير موجود بالفعل');
        }
    }
}
