<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ============================================================
    // عرض قائمة المستخدمين (بما فيهم المجمدين)
    // ============================================================
    public function index()
    {
        $users = User::withTrashed()->get();
        return view('users.index', compact('users'));
    }

    // ============================================================
    // عرض نموذج إنشاء مستخدم جديد
    // ============================================================
    public function create()
    {
        return view('users.create');
    }

    // ============================================================
    // حفظ مستخدم جديد
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:employee,manager',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    // ============================================================
    // عرض نموذج تعديل مستخدم
    // ============================================================
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // ============================================================
    // تحديث بيانات مستخدم
    // ============================================================
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:employee,manager',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        // $data['updated_by'] = auth()->id();
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    // ============================================================
    // تجميد مستخدم (Soft Delete)
    // ============================================================
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكن تجميد حسابك الخاص');
        }

        // عدد الكشوفات المرتبطة (ستبقى محتفظة بمنشئها)
        $reportsCount = $user->maintenanceReports()->count();
        $deliveriesCount = \App\Models\WarehouseDelivery::where('created_by', $user->id)->count();

        $user->delete(); // Soft delete

        $message = 'تم تجميد المستخدم بنجاح';
        if ($reportsCount > 0 || $deliveriesCount > 0) {
            $message .= " مع الاحتفاظ بـ {$reportsCount} كشف و {$deliveriesCount} تسليم مستودع.";
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    // ============================================================
    // تفعيل مستخدم مجمد (Restore)
    // ============================================================
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            return back()->with('error', 'المستخدم غير مجمد');
        }

        $user->restore();

        return redirect()->route('users.index')->with('success', 'تم تفعيل المستخدم بنجاح');
    }
}
