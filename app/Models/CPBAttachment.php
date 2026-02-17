<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CPBAttachment extends Model
{
    use HasFactory;

    protected $table = 'cpb_attachments';

    protected $fillable = [
        'cpb_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'file_type',
        'description'
    ];

    public function cpb()
    {
        return $this->belongsTo(CPB::class, 'cpb_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
