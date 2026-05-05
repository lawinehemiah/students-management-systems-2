<?php
// app/Models/ImpersonateLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpersonateLog extends Model
{
    use HasFactory;
    
    protected $table = 'impersonate_logs';
    
    protected $fillable = [
        'admin_id', 'admin_name', 'admin_email', 'admin_role',
        'target_user_id', 'target_user_name', 'target_user_email', 'target_user_role',
        'ip_address', 'user_agent', 'impersonated_at', 'stopped_at', 'duration_seconds'
    ];
    
    protected $casts = [
        'impersonated_at' => 'datetime',
        'stopped_at' => 'datetime',
        'duration_seconds' => 'integer'
    ];
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}