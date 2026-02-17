<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CPB;
use App\Models\User;
use Carbon\Carbon;

class CPBSeeder extends Seeder
{
    public function run()
    {
        // Get users
        $rnd = User::where('role', 'rnd')->first();
        $qa = User::where('role', 'qa')->first();
        $ppic = User::where('role', 'ppic')->first();
        $wh = User::where('role', 'wh')->first();
        $produksi = User::where('role', 'produksi')->first();
        $qc = User::where('role', 'qc')->first();
        
        // Create some CPBs for testing
        $cpbs = [
            [
                'batch_number' => 'CPB-2024-001',
                'type' => 'pengolahan',
                'product_name' => 'Produk A',
                'schedule_duration' => 48,
                'created_by' => $rnd->id,
                'current_department_id' => $qa->id,
                'status' => 'qa',
                'entered_current_status_at' => Carbon::now()->subHours(30),
            ],
            [
                'batch_number' => 'CPB-2024-002',
                'type' => 'pengemasan',
                'product_name' => 'Produk B',
                'schedule_duration' => 24,
                'created_by' => $rnd->id,
                'current_department_id' => $ppic->id,
                'status' => 'ppic',
                'entered_current_status_at' => Carbon::now()->subHours(3),
            ],
            [
                'batch_number' => 'CPB-2024-003',
                'type' => 'pengolahan',
                'product_name' => 'Produk C',
                'schedule_duration' => 72,
                'created_by' => $rnd->id,
                'current_department_id' => $wh->id,
                'status' => 'wh',
                'entered_current_status_at' => Carbon::now()->subHours(48),
                'is_overdue' => true,
                'overdue_since' => Carbon::now()->subHours(24),
            ],
            [
                'batch_number' => 'CPB-2024-004',
                'type' => 'pengemasan',
                'product_name' => 'Produk D',
                'schedule_duration' => 36,
                'created_by' => $rnd->id,
                'current_department_id' => $produksi->id,
                'status' => 'produksi',
                'entered_current_status_at' => Carbon::now()->subHours(12),
            ],
            [
                'batch_number' => 'CPB-2024-005',
                'type' => 'pengolahan',
                'product_name' => 'Produk E',
                'schedule_duration' => 96,
                'created_by' => $rnd->id,
                'current_department_id' => $qc->id,
                'status' => 'qc',
                'entered_current_status_at' => Carbon::now()->subHours(2),
            ],
            [
                'batch_number' => 'CPB-2024-006',
                'type' => 'pengemasan',
                'product_name' => 'Produk F',
                'schedule_duration' => 24,
                'created_by' => $rnd->id,
                'current_department_id' => $qa->id,
                'status' => 'qa_final',
                'entered_current_status_at' => Carbon::now()->subHours(20),
            ],
            [
                'batch_number' => 'CPB-2023-100',
                'type' => 'pengolahan',
                'product_name' => 'Produk X',
                'schedule_duration' => 48,
                'created_by' => $rnd->id,
                'current_department_id' => $qa->id,
                'status' => 'released',
                'entered_current_status_at' => Carbon::now()->subDays(10),
            ],
        ];
        
        foreach ($cpbs as $cpbData) {
            CPB::create($cpbData);
        }
        
        $this->command->info('CPBs seeded successfully!');
    }
}