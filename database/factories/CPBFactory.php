<?php
// database/factories/CPBFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CPB;
use App\Models\User;

class CPBFactory extends Factory
{
    protected $model = CPB::class;
    
    public function definition()
    {
        $types = ['pengolahan', 'pengemasan'];
        $statuses = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final'];
        
        $rndUser = User::where('role', 'rnd')->first();
        $currentStatus = $this->faker->randomElement($statuses);
        
        // Get a user in the current status department
        $currentDepartment = User::where('role', $currentStatus)->first();
        
        $enteredAt = $this->faker->dateTimeBetween('-30 days', 'now');
        $duration = $this->faker->numberBetween(1, 100);
        $isOverdue = $this->faker->boolean(20); // 20% chance of being overdue
        
        return [
            'batch_number' => 'CPB-' . $this->faker->unique()->numberBetween(1000, 9999),
            'type' => $this->faker->randomElement($types),
            'product_name' => 'Produk ' . $this->faker->word() . ' ' . $this->faker->numberBetween(1, 100),
            'schedule_duration' => $this->faker->numberBetween(24, 168),
            'status' => $currentStatus,
            'created_by' => $rndUser ? $rndUser->id : User::factory()->create(['role' => 'rnd'])->id,
            'current_department_id' => $currentDepartment ? $currentDepartment->id : User::factory()->create(['role' => $currentStatus])->id,
            'entered_current_status_at' => $enteredAt,
            'duration_in_current_status' => $duration,
            'is_overdue' => $isOverdue,
            'overdue_since' => $isOverdue ? $this->faker->dateTimeBetween($enteredAt, 'now') : null,
            'created_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
    
    public function pengolahan()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'pengolahan',
            ];
        });
    }
    
    public function pengemasan()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'pengemasan',
            ];
        });
    }
    
    public function withStatus($status)
    {
        return $this->state(function (array $attributes) use ($status) {
            $user = User::where('role', $status)->first();
            
            if (!$user) {
                $user = User::factory()->create(['role' => $status]);
            }
            
            return [
                'status' => $status,
                'current_department_id' => $user->id,
                'entered_current_status_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }
    
    public function overdue()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_overdue' => true,
                'overdue_since' => $this->faker->dateTimeBetween('-5 days', '-1 day'),
            ];
        });
    }
    
    public function released()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'released',
                'entered_current_status_at' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
                'is_overdue' => false,
                'overdue_since' => null,
            ];
        });
    }
}