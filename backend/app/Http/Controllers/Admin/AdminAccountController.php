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
        $admins = User::where('role', 'admin')->get();
        return $this->success($admins);
    }

    public function store(Request $request): JsonResponse
    {
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
        if ($user->id === auth()->id()) {
            return $this->error('Cannot delete yourself', 422);
        }

        if ($user->role !== 'admin') {
            return $this->error('User is not an admin', 422);
        }

        $user->delete();

        return $this->success(null, 'Admin deleted');
    }
}
