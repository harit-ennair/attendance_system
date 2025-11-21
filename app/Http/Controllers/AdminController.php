<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of the admins.
     */
    public function index()
    {
        $admins = Admin::with('user')->paginate(15);
        return response()->json($admins);
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        return response()->json(['message' => 'Create admin form']);
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_um6p' => 'required|email|unique:admins,email_um6p',
            'department' => 'required|string|max:255',
            'program' => 'required|string|max:255',
        ]);

        // Create user account for admin
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email_um6p'],
            'password' => Hash::make('password123'), // Default password
            'role_id' => 2, // Assuming admin role ID is 2
        ]);

        // Create admin record
        $admin = Admin::create([
            'email_um6p' => $validated['email_um6p'],
            'department' => $validated['department'],
            'program' => $validated['program'],
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admin->load('user')
        ], 201);
    }

    /**
     * Display the specified admin.
     */
    public function show(Admin $admin)
    {
        return response()->json($admin->load('user'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        return response()->json($admin->load('user'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email_um6p' => ['sometimes', 'email', Rule::unique('admins', 'email_um6p')->ignore($admin->id)],
            'department' => 'sometimes|string|max:255',
            'program' => 'sometimes|string|max:255',
        ]);

        // Update user if name or email changed
        if (isset($validated['name']) || isset($validated['email_um6p'])) {
            $admin->user->update([
                'name' => $validated['name'] ?? $admin->user->name,
                'email' => $validated['email_um6p'] ?? $admin->user->email,
            ]);
        }

        // Update admin record
        $admin->update($validated);

        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => $admin->load('user')
        ]);
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(Admin $admin)
    {
        // Delete associated user account
        $admin->user->delete();
        
        // Admin will be deleted automatically due to cascade
        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
