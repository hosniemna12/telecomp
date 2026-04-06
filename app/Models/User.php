<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // Helpers de rôle
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperateur(): bool
    {
        return $this->role === 'operateur';
    }

    public function isSuperviseur(): bool
    {
        return $this->role === 'superviseur';
    }
}