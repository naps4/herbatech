<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandoverLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpb_id',
        'from_status',
        'to_status',
        'handed_by',
        'received_by',
        'handed_at',
        'received_at',
        'duration_in_hours',
        'was_overdue',
        'notes'
    ];

    protected $casts = [
        'handed_at' => 'datetime',
        'received_at' => 'datetime',
        'was_overdue' => 'boolean'
    ];

    // Relationships
      public function cpb()
    {
        return $this->belongsTo(CPB::class, 'cpb_id'); // Tentukan column name
    }

    public function sender() 
    {
        return $this->belongsTo(User::class, 'handed_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Accessors
    public function getDurationFormattedAttribute()
    {
        $hours = $this->duration_in_hours;
        
        if ($hours < 1) {
            return '< 1 jam';
        } elseif ($hours < 24) {
            return $hours . ' jam';
        } else {
            $days = floor($hours / 24);
            $remaining = $hours % 24;
            
            if ($remaining > 0) {
                return $days . ' hari ' . $remaining . ' jam';
            }
            
            return $days . ' hari';
        }
    }
       /**
     * Get the table name for the model.
     */
    public function getTable()
    {
        return 'handover_logs';
    }
}