<?php

use App\Http\Controllers\MaintenanceReportController;
use App\Http\Controllers\MaintenanceStatusController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseDeliveryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
// use App\Models\User;

Route::get('/hash-first-user', function () {
    $password = '12345678'; // غيرها إلى كلمة المرور التي تريدها
    $hashed = bcrypt($password);
    
    // تحديث المستخدم الذي بريده admin@admin.com
    $user = \App\Models\User::where('email', 'admin@admin.com')->first();
    if ($user) {
        $user->password = $hashed;
        $user->save();
        return "تم تحديث كلمة مرور المستخدم admin@admin.com بنجاح. كلمة المرور الأصلية: {$password}";
    } else {
        return "لم يتم العثور على مستخدم بهذا البريد. قم بإنشائه أولاً.";
    }
});

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // توجيه صفحات index المنفصلة إلى الصفحة الرئيسية
    Route::get('/warehouse-deliveries', function () {
        return redirect()->route('home', ['type' => 'warehouse']);
    })->name('warehouse-deliveries.index');
    
    Route::get('/maintenance-reports', function () {
        return redirect()->route('home', ['type' => 'maintenance']);
    })->name('maintenance-reports.index');

    // باقي المسارات للإنشاء والعرض والتعديل والحذف
    Route::resource('warehouse-deliveries', WarehouseDeliveryController::class)->except(['index']);
    Route::resource('maintenance-reports', MaintenanceReportController::class)->except(['index']);

    // تغيير حالة الكشف (مدير فقط)
    Route::patch('/maintenance-reports/{report}/status', [MaintenanceStatusController::class, 'update'])
        ->name('maintenance.status.update')
        ->middleware('role:manager');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // إدارة المستخدمين (مدير فقط)
    Route::middleware('role:manager')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::patch('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    });
});