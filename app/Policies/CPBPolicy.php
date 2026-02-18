<?php
// app/Policies/CPBPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\CPB;
use Illuminate\Auth\Access\Response;

class CPBPolicy
{
     public function viewAny(User $user): bool
    {
        Log::info('CPBPolicy viewAny check', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'result' => true
        ]);
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CPB $cpb): bool
    {
        Log::info('CPBPolicy view check START', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'cpb_id' => $cpb->id,
            'cpb_status' => $cpb->status,
            'cpb_created_by' => $cpb->created_by,
            'cpb_current_dept' => $cpb->current_department_id,
        ]);
        
        // Super admin dan QA bisa melihat semua
        if ($user->isSuperAdmin() || $user->isQA()|| $user->isRND()) {
            Log::info('CPBPolicy view check: SuperAdmin or QA or RND - ALLOWED');
            return true;
        }
        
        // User bisa melihat CPB yang mereka buat
        if ($cpb->created_by === $user->id) {
            Log::info('CPBPolicy view check: Creator - ALLOWED');
            return true;
        }
        
        // User bisa melihat CPB di departemen mereka saat ini
        if ($cpb->current_department_id === $user->id) {
            Log::info('CPBPolicy view check: Current Department - ALLOWED');
            return true;
        }
        
        // User bisa melihat CPB yang sudah lewat departemen mereka
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'];
        $userIndex = array_search($user->role, $flow);
        $cpbIndex = array_search($cpb->status, $flow);
        
        $result = $userIndex !== false && $cpbIndex !== false && $cpbIndex <= $userIndex;
        
        Log::info('CPBPolicy view check FINAL', [
            'user_role' => $user->role,
            'cpb_status' => $cpb->status,
            'user_index' => $userIndex,
            'cpb_index' => $cpbIndex,
            'result' => $result
        ]);
        
        return $result;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya RND dan super admin yang bisa membuat CPB
        return $user->isRND() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CPB $cpb): bool
    {
        if ($user->isRND() && $cpb->status === 'rnd') {
        return $cpb->created_by === $user->id || $user->isSuperAdmin();
        }
        return false;
    }

    public function delete(User $user, CPB $cpb): bool
    {
        // Hanya super admin yang bisa delete
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CPB $cpb): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CPB $cpb): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can handover the CPB.
     */
    public function handover(User $user, CPB $cpb): bool
    {
        // 1. Super admin bisa handover semua CPB ✅
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // 2. User harus di departemen yang sesuai dengan status CPB
        if ($cpb->current_department_id !== $user->id) {
            return false;
        }
        
        // 3. Check if user's role matches current status
        if ($cpb->status !== $user->role) {
            return false;
        }
        
        // 4. Check if CPB has next department
        $nextStatus = $cpb->getNextDepartment();
        
        // Pastikan CPB belum released dan ada next department
        if (empty($nextStatus) || $nextStatus === 'released') {
            return false;
        }
        
        // 5. QA bisa handover dari QA ke PPIC
        if ($user->role === 'qa' && $cpb->status === 'qa') {
            return $nextStatus === 'ppic';
        }
        
        // 6. PPIC bisa handover ke WH
        if ($user->role === 'ppic' && $cpb->status === 'ppic') {
            return $nextStatus === 'wh';
        }
        
        // 7. WH bisa handover ke Produksi
        if ($user->role === 'wh' && $cpb->status === 'wh') {
            return $nextStatus === 'produksi';
        }
        
        // 8. Produksi bisa handover ke QC
        if ($user->role === 'produksi' && $cpb->status === 'produksi') {
            return $nextStatus === 'qc';
        }
        
        // 9. QC bisa handover ke QA Final
        if ($user->role === 'qc' && $cpb->status === 'qc') {
            return $nextStatus === 'qa_final';
        }
        
        // 10. RND bisa handover ke QA (awal)
        if ($user->role === 'rnd' && $cpb->status === 'rnd') {
            return $nextStatus === 'qa';
        }
        
        return false;
    }

    /**
     * Determine whether the user can release the CPB.
     */
    public function release(User $user, CPB $cpb): bool
    {
        // Hanya QA dan super admin yang bisa release
        return ($user->role === 'qa' || $user->isSuperAdmin()) && $cpb->status === 'qa_final';
    }

    /**
     * Determine whether the user can receive handover.
     */
    public function receive(User $user, CPB $cpb): bool
    {
        // User bisa menerima jika CPB akan masuk ke departemen mereka
        $nextStatus = $cpb->getNextDepartment();
        return $nextStatus === $user->role;
    }
}