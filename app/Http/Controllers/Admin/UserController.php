<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        $query = User::query();
        
        // Filter by role
        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }
        
        // Filter by search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('name')->paginate(20);
        
        // Statistics
        $totalUsers = User::count();
        $roleCounts = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
        
        return view('admin.users.index', compact('users', 'totalUsers', 'roleCounts'));
    }

    public function create()
    {
        $roles = [
            'superadmin' => 'Super Admin',
            'rnd' => 'RND',
            'qa' => 'QA',
            'ppic' => 'PPIC',
            'wh' => 'Warehouse',
            'produksi' => 'Produksi',
            'qc' => 'QC'
        ];
        
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:superadmin,rnd,qa,ppic,wh,produksi,qc',
            'department' => 'nullable|string|max:100',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department' => $validated['department'] ?? $this->getDefaultDepartment($validated['role']),
        ]);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user)
    {
        $user->load(['cpbsCreated', 'cpbsCurrent', 'handoversGiven', 'handoversReceived']);
        
        return view('admin.users.show', compact('user'));
    }

    public function showProfile()
    {
        $user = auth()->user(); // Mengambil data user yang sedang login
        return view('admin.users.show', compact('user'));
    }

    

    public function edit(User $user)
    {
        $roles = [
            'superadmin' => 'Super Admin',
            'rnd' => 'RND',
            'qa' => 'QA',
            'ppic' => 'PPIC',
            'wh' => 'Warehouse',
            'produksi' => 'Produksi',
            'qc' => 'QC'
        ];
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:superadmin,rnd,qa,ppic,wh,produksi,qc',
            'department' => 'nullable|string|max:100',
        ]);
        
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? $this->getDefaultDepartment($validated['role']),
        ];
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }
        
        $user->update($updateData);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Cegah menghapus diri sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        
        // Cek apakah user memiliki data terkait
        if ($user->cpbsCreated()->exists() || $user->handoversGiven()->exists()) {
            return back()->with('error', 'User tidak dapat dihapus karena memiliki data terkait.');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
    
    private function getDefaultDepartment($role)
    {
        $departments = [
            'superadmin' => 'Administration',
            'rnd' => 'Research & Development',
            'qa' => 'Quality Assurance',
            'ppic' => 'PPIC',
            'wh' => 'Warehouse',
            'produksi' => 'Production',
            'qc' => 'Quality Control'
        ];
        
        return $departments[$role] ?? 'General';
    }

    public function editProfile()
    {
        $user = auth()->user();
        return view('admin.users.edit_profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui!');
    }
}