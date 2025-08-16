<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function canManageUsers()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canManageContent()
    {
        return in_array($this->role, ['super_admin', 'admin', 'content_manager']);
    }
}
