<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminAdminAccountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = User::where('role', 'admin')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('is_active', $status === 'Active');
        }

        $admins = $query->paginate(10);

        $offset = ($admins->currentPage() - 1) * $admins->perPage();
        $admins->getCollection()->transform(function ($admin, $index) use ($offset) {
            $admin->display_no       = $offset + $index + 1;
            $admin->display_username = strstr($admin->email, '@', true);
            $admin->display_status   = $admin->is_active ? 'Active' : 'Inactive';
            return $admin;
        });

        $totalAdmins    = User::where('role', 'admin')->count();
        $activeAdmins   = User::where('role', 'admin')->where('is_active', true)->count();
        $inactiveAdmins = $totalAdmins - $activeAdmins;

        return view('admin.admin-accounts', compact(
            'admins', 'search', 'status',
            'totalAdmins', 'activeAdmins', 'inactiveAdmins'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'is_active'       => 'required|boolean',
            'password'        => 'required|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $profilePicture = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('avatars', 'public')
            : null;

        User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'is_active'       => $request->is_active,
            'password'        => Hash::make($request->password),
            'role'            => 'admin',
            'profile_picture' => $profilePicture,
        ]);

        return redirect()->route('admin.adminAccounts')->with('success', 'Admin account added successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'is_active'       => 'required|boolean',
            'password'        => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_avatar'   => 'nullable|boolean',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $data['profile_picture'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.adminAccounts')->with('success', 'Admin account updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting their own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.adminAccounts')->with('error', 'You cannot delete your own account.');
        }

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return redirect()->route('admin.adminAccounts')->with('success', 'Admin account deleted successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.adminAccounts')->with('success', "Password for {$user->name} has been reset successfully.");
    }
}
