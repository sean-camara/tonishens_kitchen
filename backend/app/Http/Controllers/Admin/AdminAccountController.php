<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAccountController extends Controller
{
    public function index(): JsonResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            return $this->error('Only super admins can manage accounts', 403);
        }

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        return $this->success($admins);
    }

    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            return $this->error('Only super admins can create admin accounts', 403);
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $data['role'] = 'admin';

        $admin = User::create($data);

        return $this->success($admin, 'Admin created', 201);
    }

    public function destroy(User $user): JsonResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            return $this->error('Only super admins can delete admin accounts', 403);
        }

        if ($user->id === auth()->id()) {
            return $this->error('Cannot delete yourself', 422);
        }

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->error('User is not an admin', 422);
        }

        $user->delete();

        return $this->success(null, 'Admin deleted');
    }
}
