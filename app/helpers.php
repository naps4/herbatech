<?php

/**
 * Menghitung Skor Performa Departemen berdasarkan rasio keterlambatan.
 */
if (!function_exists('calculatePerformanceScore')) {
    function calculatePerformanceScore($dept) {
        $score = 100;
        
        // Ambil data total handover dan overdue
        $total = $dept->total_handovers ?? 0;
        $overdue = $dept->overdue_count ?? 0;

        // Menghitung rasio keterlambatan (%)
        $overdueRate = $total > 0 ? ($overdue / $total) * 100 : 0;
        
        // Pengurangan skor berdasarkan ambang batas
        if ($overdueRate > 20) {
            $score -= 40; // Penalti berat jika keterlambatan > 20%
        } elseif ($overdueRate > 10) {
            $score -= 20; // Penalti sedang jika keterlambatan > 10%
        }
        
        return max(0, $score);
    }
}

/**
 * Menghitung Skor Performa Staf/User secara individu.
 */
if (!function_exists('calculateUserPerformanceScore')) {
    function calculateUserPerformanceScore($userPerf) {
        $score = 100;
        
        // Ambil data handover dan durasi rata-rata
        $totalHandovers = $userPerf->total_handovers_count ?? $userPerf->total_handovers ?? 0;
        $avgDuration = $userPerf->avg_duration ?? 0;
        
        // Bonus skor jika rajin (banyak handover)
        if ($totalHandovers >= 10) {
            $score += 10;
        }
        
        // Penalti jika rata-rata pengerjaan terlalu lama (> 24 jam)
        if ($avgDuration > 24) {
            $score -= 20;
        }
        
        return max(0, min(100, $score));
    }
}