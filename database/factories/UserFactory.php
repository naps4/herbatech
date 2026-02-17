<?php
// database/factories/UserFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;
    
    public function definition()
    {
        $roles = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc'];
        $role = $this->faker->randomElement($roles);
        
        $departments = [
            'rnd' => 'Research & Development',
            'qa' => 'Quality Assurance',
            'ppic' => 'PPIC',
            'wh' => 'Warehouse',
            'produksi' => 'Production',
            'qc' => 'Quality Control',
        ];
        
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password123'), // password
            'remember_token' => Str::random(10),
            'role' => $role,
            'department' => $departments[$role],
        ];
    }
    
    public function superadmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'superadmin',
                'department' => 'Administration',
            ];
        });
    }
    
    public function rnd()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'rnd',
                'department' => 'Research & Development',
            ];
        });
    }
    
    public function qa()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'qa',
                'department' => 'Quality Assurance',
            ];
        });
    }
    
    public function ppic()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'ppic',
                'department' => 'PPIC',
            ];
        });
    }
    
    public function wh()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'wh',
                'department' => 'Warehouse',
            ];
        });
    }
    
    public function production()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'produksi',
                'department' => 'Production',
            ];
        });
    }
    
    public function qc()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'qc',
                'department' => 'Quality Control',
            ];
        });
    }
    
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}