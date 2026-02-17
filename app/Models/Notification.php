<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'cpb_id', // Pastikan ini sesuai dengan migration
        'is_read',
        'data',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    // Relationships - Tentukan foreign key secara eksplisit
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cpb()
    {
        return $this->belongsTo(CPB::class, 'cpb_id'); // Tentukan column name
    }

    // Methods
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Get the table name for the model.
     */
    public function getTable()
    {
        return 'notifications';
    }
}