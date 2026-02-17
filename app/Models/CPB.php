<?php
// app/Models/CPB.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;

class CPB extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tentukan nama tabel secara eksplisit
     */
    protected $table = 'cpbs';

    protected $fillable = [
        'batch_number',
        'type',
        'product_name',
        'schedule_duration',
        'status',
        'created_by',
        'current_department_id',
        'entered_current_status_at',
        'is_overdue',
        'overdue_since'
    ];

    protected $casts = [
        'entered_current_status_at' => 'datetime',
        'overdue_since' => 'datetime',
        'is_overdue' => 'boolean'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentDepartment()
    {
        return $this->belongsTo(User::class, 'current_department_id');
    }

    public function handoverLogs()
    {
        return $this->hasMany(HandoverLog::class, 'cpb_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function attachments()
    {
        return $this->hasMany(CPBAttachment::class, 'cpb_id');
    }

    // Accessors
    public function getDurationInCurrentStatusAttribute()
    {
        return now()->diffInHours($this->entered_current_status_at);
    }

    public function getFormattedDurationAttribute()
    {
        $start = $this->entered_current_status_at;
        $now = now();
        $diff = $start->diff($now);

        $parts = [];
        
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' hari';
        }
        
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' jam';
        }
        
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' menit';
        }

        if (empty($parts)) {
            return 'Baru saja';
        }

        // Ambil 2 unit terbesar saja agar tidak terlalu panjang (misal: "2 hari 5 jam" instead of "2 hari 5 jam 30 menit")
        return implode(' ', array_slice($parts, 0, 2));
    }

    public function getTimeLimitAttribute()
    {
        $limits = [
            'rnd' => 24,
            'qa' => 24,
            'ppic' => 4,
            'wh' => 24,
            'produksi' => $this->schedule_duration,
            'qc' => 4,
            'qa_final' => 24,
            'released' => 0
        ];

        return $limits[$this->status] ?? 24;
    }

    public function getTimeRemainingAttribute()
    {
        $elapsed = $this->duration_in_current_status;
        $limit = $this->time_limit;
        
        return max(0, $limit - $elapsed);
    }

    public function getTimeStatusAttribute()
    {
        if ($this->is_overdue) {
            return 'overdue';
        }
        
        $elapsed = $this->duration_in_current_status;
        $limit = $this->time_limit;
        
        if ($elapsed >= $limit) {
            return 'overdue';
        } elseif ($elapsed >= $limit * 0.8) {
            return 'warning';
        }
        
        return 'ok';
    }

    public function getTimeStatusBadgeAttribute()
    {
        $status = $this->time_status;
        
        $badges = [
            'ok' => '<span class="badge bg-success">✅ OK</span>',
            'warning' => '<span class="badge bg-warning">⚠️ Warning</span>',
            'overdue' => '<span class="badge bg-danger">⚠️ Overdue</span>'
        ];
        
        return $badges[$status];
    }

    public function getStatusBadgeAttribute()
    {
        $statuses = [
            'rnd' => ['label' => 'RND', 'color' => 'primary'],
            'qa' => ['label' => 'QA Review', 'color' => 'info'],
            'ppic' => ['label' => 'PPIC', 'color' => 'secondary'],
            'wh' => ['label' => 'Warehouse', 'color' => 'dark'],
            'produksi' => ['label' => 'Production', 'color' => 'warning'],
            'qc' => ['label' => 'QC', 'color' => 'info'],
            'qa_final' => ['label' => 'QA Final', 'color' => 'success'],
            'released' => ['label' => 'Released', 'color' => 'success']
        ];
        
        $status = $statuses[$this->status] ?? ['label' => $this->status, 'color' => 'secondary'];
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $status['color'],
            $status['label']
        );
    }

    // Methods
    public function getNextDepartment()
    {
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'];
        $currentIndex = array_search($this->status, $flow);
        
        if ($currentIndex !== false && isset($flow[$currentIndex + 1])) {
            return $flow[$currentIndex + 1];
        }
        
        return null;
    }

    public function checkOverdue()
    {
        $elapsed = $this->duration_in_current_status;
        $limit = $this->time_limit;
        
        if ($elapsed > $limit && !$this->is_overdue) {
            $this->update([
                'is_overdue' => true,
                'overdue_since' => now()
            ]);
            
            // Trigger overdue event
            event(new \App\Events\CPBOverdue($this));
        }
        
        return $this->is_overdue;
    }

    public function canBeHandedOverBy(User $user)
    {
        return Gate::allows('handover', $this);
    }
}