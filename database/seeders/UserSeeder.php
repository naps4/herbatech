<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'department' => 'Administration'
        ]);
        
        // RND
        User::create([
            'name' => 'RND Manager',
            'email' => 'rnd@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'rnd',
            'department' => 'Research & Development'
        ]);
        
        // QA
        User::create([
            'name' => 'QA Manager',
            'email' => 'qa@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'qa',
            'department' => 'Quality Assurance'
        ]);
        
        // PPIC
        User::create([
            'name' => 'PPIC Officer',
            'email' => 'ppic@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'ppic',
            'department' => 'PPIC'
        ]);
        
        // Warehouse
        User::create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'wh',
            'department' => 'Warehouse'
        ]);
        
        // Production
        User::create([
            'name' => 'Production Head',
            'email' => 'production@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'produksi',
            'department' => 'Production'
        ]);
        
        // QC
        User::create([
            'name' => 'QC Inspector',
            'email' => 'qc@cpb.com',
            'password' => Hash::make('password'),
            'role' => 'qc',
            'department' => 'Quality Control'
        ]);
    }
}