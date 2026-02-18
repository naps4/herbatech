<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function cpbsCreated()
    {
        return $this->hasMany(CPB::class, 'created_by');
    }

    public function cpbsCurrent()
    {
        return $this->hasMany(CPB::class, 'current_department_id');
    }

    public function handoversGiven()
    {
        return $this->hasMany(HandoverLog::class, 'handed_by');
    }

    public function handoversReceived()
    {
        return $this->hasMany(HandoverLog::class, 'received_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    // Helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isQA()
    {
        return $this->role === 'qa';
    }

    public function isRND()
    {
        return $this->role === 'rnd';
    }

    /**
     * Get users who can receive handover from this user
     */
    public function getNextDepartmentUsers()
    {
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'];
        $currentIndex = array_search($this->role, $flow);
        
        if ($currentIndex !== false && isset($flow[$currentIndex + 1])) {
            $nextRole = $flow[$currentIndex + 1];
            return User::where('role', $nextRole)->get();
        }
        
        return collect();
    }

    /**
     * Check if user can handover to specific department
     */
    public function canHandoverTo($department)
    {
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'];
        $currentIndex = array_search($this->role, $flow);
        $targetIndex = array_search($department, $flow);
        
        return $currentIndex !== false && $targetIndex !== false && $targetIndex === $currentIndex + 1;
    }

    /**
     * Get CPBs that user can handover
     */
    public function cpbsForHandover()
    {
        return $this->cpbsCurrent()
            ->where('status', $this->role)
            ->where('status', '!=', 'released')
            ->get();
    }

    public function scopeByRole($query, $role)
{
    if ($role && $role !== 'all') {
        return $query->where('role', $role);
    }
    return $query;
}

public function getRoleBadgeAttribute()
{
    $roles = [
        'superadmin' => ['label' => 'Super Admin', 'color' => 'danger'],
        'rnd' => ['label' => 'RND', 'color' => 'primary'],
        'qa' => ['label' => 'QA', 'color' => 'info'],
        'ppic' => ['label' => 'PPIC', 'color' => 'secondary'],
        'wh' => ['label' => 'Warehouse', 'color' => 'dark'],
        'produksi' => ['label' => 'Produksi', 'color' => 'warning'],
        'qc' => ['label' => 'QC', 'color' => 'success']
    ];
    
    $role = $roles[$this->role] ?? ['label' => ucfirst($this->role), 'color' => 'secondary'];
    
    return '<span class="badge bg-' . $role['color'] . '">' . $role['label'] . '</span>';
}

public function getDepartmentOptions()
{
    return [
        'superadmin' => 'Administration',
        'rnd' => 'Research & Development',
        'qa' => 'Quality Assurance',
        'ppic' => 'PPIC',
        'wh' => 'Warehouse',
        'produksi' => 'Production',
        'qc' => 'Quality Control'
    ];
}

}