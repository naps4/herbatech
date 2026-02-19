<?php
// app/Policies/CPBPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\CPB;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class CPBPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar CPB.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah user dapat melihat detail CPB tertentu.
     */
    public function view(User $user, CPB $cpb): bool
    {
        // Super admin, QA, dan RND memiliki akses penuh untuk memantau semua batch
        if ($user->role === 'superadmin' || $user->role === 'qa' || $user->role === 'rnd') {
            return true;
        }
        
        // User dapat melihat CPB yang mereka buat sendiri
        if ($cpb->created_by === $user->id) {
            return true;
        }
        
        // User dapat melihat CPB yang sedang aktif di departemen/tangan mereka
        if ($cpb->current_department_id === $user->id) {
            return true;
        }
        
        // Aturan riwayat: User dapat melihat CPB yang statusnya sudah melewati departemen mereka
        // Alur menggunakan 7 role sesuai keterbatasan database
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa', 'released'];
        $userIndex = array_search($user->role, $flow);
        $cpbIndex = array_search($cpb->status, $flow);
        
        return $userIndex !== false && $cpbIndex !== false && $cpbIndex <= $userIndex;
    }

    /**
     * Tentukan apakah user dapat membuat CPB baru.
     */
    public function create(User $user): bool
    {
        // Hanya departemen RND atau Super Admin yang diizinkan memulai batch baru
        return $user->role === 'rnd' || $user->role === 'superadmin';
    }

    /**
     * Tentukan apakah user dapat memperbarui data CPB.
     */
    public function update(User $user, CPB $cpb): bool
    {
        // Update hanya diizinkan di tahap RND oleh pembuatnya atau Admin
        if ($user->role === 'rnd' && $cpb->status === 'rnd') {
            return $cpb->created_by === $user->id || $user->role === 'superadmin';
        }
        return false;
    }

    /**
     * Tentukan apakah user dapat menghapus CPB.
     */
    public function delete(User $user, CPB $cpb): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Logika Inti Handover (Serah Terima Dokumen)
     */
    public function handover(User $user, CPB $cpb): bool
    {
        // 1. Super admin selalu diizinkan untuk bypass alur jika diperlukan
        if ($user->role === 'superadmin') {
            return true;
        }
        
        // 2. Keamanan: User haruslah pemegang dokumen saat ini (Current Department ID)
        if ($cpb->current_department_id !== $user->id) {
            return false;
        }
        
        // 3. Keamanan: Role user harus sesuai dengan status sistem CPB saat ini
        if ($cpb->status !== $user->role) {
            return false;
        }
        
        // 4. Ambil departemen tujuan berdasarkan logika workflow di Model
        $nextStatus = $cpb->getNextDepartment();
        
        // Pastikan bukan status akhir yang sudah tidak bisa dipindahkan
        if (empty($nextStatus) || $cpb->status === 'released') {
            return false;
        }

        /**
         * LOGIKA BEST PRACTICE INDUSTRI (Checkpoint QC)
         * Mengecek apakah batch ini sudah pernah melewati departemen QC sebelumnya.
         */
        $hasPassedQC = $cpb->handoverLogs()->where('from_status', 'qc')->exists();
        
        // --- VALIDASI ALUR KERJA ---

        // A. Alur RND -> QA (Review Dokumen Awal)
        if ($user->role === 'rnd' && $cpb->status === 'rnd') {
            return $nextStatus === 'qa';
        }

        // B. Alur QA (Awal) -> PPIC
        // QA hanya boleh mengirim ke PPIC jika belum pernah melewati QC (tahap awal)
        if ($user->role === 'qa' && $cpb->status === 'qa' && !$hasPassedQC) {
            return $nextStatus === 'ppic';
        }
        
        // C. Alur PPIC -> Warehouse (WH)
        if ($user->role === 'ppic' && $cpb->status === 'ppic') {
            return $nextStatus === 'wh';
        }
        
        // D. Alur Warehouse -> Produksi
        if ($user->role === 'wh' && $cpb->status === 'wh') {
            return $nextStatus === 'produksi';
        }
        
        // E. Alur Produksi -> QC
        if ($user->role === 'produksi' && $cpb->status === 'produksi') {
            return $nextStatus === 'qc';
        }
        
        // F. Alur QC -> QA (Final Review)
        if ($user->role === 'qc' && $cpb->status === 'qc') {
            return $nextStatus === 'qa';
        }

        // G. Alur QA (Final) -> Released
        // QA hanya boleh mengirim ke Released (selesai) jika sudah melewati QC
        if ($user->role === 'qa' && $cpb->status === 'qa' && $hasPassedQC) {
            return $nextStatus === 'released';
        }
        
        return false;
    }

    /**
     * Tentukan apakah user dapat melakukan pelulusan produk (Release Product).
     */
    public function release(User $user, CPB $cpb): bool
    {
        // 1. Hanya personil QA atau Superadmin yang memiliki wewenang rilis
        if (!($user->role === 'qa' || $user->role === 'superadmin')) {
            return false;
        }

        // 2. Status dokumen harus berada di departemen QA
        if ($cpb->status !== 'qa') {
            return false;
        }

        /**
         * Produk TIDAK BOLEH dirilis jika belum pernah melalui QC.
         * Ini mencegah QA menekan tombol 'Release' saat tahap review awal (setelah RND).
         */
        $hasPassedQC = $cpb->handoverLogs()->where('from_status', 'qc')->exists();

        return $hasPassedQC;
    }

    /**
     * Tentukan apakah user dapat melihat/menerima notifikasi handover.
     */
    public function receive(User $user, CPB $cpb): bool
    {
        $nextStatus = $cpb->getNextDepartment();
        return $nextStatus === $user->role;
    }
}