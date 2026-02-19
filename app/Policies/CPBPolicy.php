<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CPB;
use Illuminate\Auth\Access\HandlesAuthorization;

class CPBPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CPB $cpb): bool
    {
        // Akses penuh untuk pemantauan
        if ($user->role === 'superadmin' || $user->role === 'qa' || $user->role === 'rnd') {
            return true;
        }
        
        if ($cpb->current_department_id === $user->id || $cpb->created_by === $user->id) {
            return true;
        }
        
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa', 'released'];
        $userIndex = array_search($user->role, $flow);
        $cpbIndex = array_search($cpb->status, $flow);
        
        return $userIndex !== false && $cpbIndex !== false && $cpbIndex <= $userIndex;
    }

public function create(User $user): bool
{
    return in_array($user->role, ['rnd', 'superadmin','qa']);
}

    public function update(User $user, CPB $cpb): bool
    {
        if ($user->role === 'superadmin') return true;

        return $cpb->current_department_id === $user->id && $cpb->status === $user->role;
    }

    public function handover(User $user, CPB $cpb): bool
    {
        if ($user->role === 'superadmin') return true;
        
        // Validasi kepemilikan dokumen
        if ($cpb->current_department_id !== $user->id || $cpb->status !== $user->role) {
            return false;
        }
        
        $nextStatus = $cpb->getNextDepartment();
        if (empty($nextStatus) || $cpb->status === 'released') return false;

        // Identifikasi QA Tahap 1 atau Tahap 2
        $hasPassedQC = $cpb->handoverLogs()->where('from_status', 'qc')->exists();
        
        // Validasi Alur Spesifik
        if ($user->role === 'rnd' && $cpb->status === 'rnd') return $nextStatus === 'qa';
        if ($user->role === 'qa' && $cpb->status === 'qa' && !$hasPassedQC) return $nextStatus === 'ppic';
        if ($user->role === 'ppic' && $cpb->status === 'ppic') return $nextStatus === 'wh';
        if ($user->role === 'wh' && $cpb->status === 'wh') return $nextStatus === 'produksi';
        if ($user->role === 'produksi' && $cpb->status === 'produksi') return $nextStatus === 'qc';
        if ($user->role === 'qc' && $cpb->status === 'qc') return $nextStatus === 'qa';
        if ($user->role === 'qa' && $cpb->status === 'qa' && $hasPassedQC) return $nextStatus === 'released';
        
        return false;
    }

    public function release(User $user, CPB $cpb): bool
    {
        if (!($user->role === 'qa' || $user->role === 'superadmin')) return false;
        if ($cpb->status !== 'qa') return false;

        // Hanya boleh rilis jika sudah melewati QC (QA Final)
        return $cpb->handoverLogs()->where('from_status', 'qc')->exists();
    }
}