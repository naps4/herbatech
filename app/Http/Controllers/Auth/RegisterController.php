<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function showRegistrationForm()
    {
        $roles = [
            'rnd' => 'Research & Development',
            'qa' => 'Quality Assurance',
            'ppic' => 'PPIC',
            'wh' => 'Warehouse',
            'produksi' => 'Production',
            'qc' => 'Quality Control',
            'superadmin' => 'Super Admin'
        ];
        
        return view('auth.register', compact('roles'));
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:superadmin,rnd,qa,ppic,wh,produksi,qc'],
            'department' => ['required', 'string', 'max:100'],
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'department' => $data['department'],
        ]);

        // Log registration activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Created new user account');

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        return redirect()->route('dashboard')
            ->with('success', 'User berhasil didaftarkan!');
    }
}