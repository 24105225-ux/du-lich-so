<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const UPDATED_AT = null;

    protected $table = 'users';

    protected $fillable = [
        'email', 'password_hash', 'role', 'status', 'last_login_at',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    public function getAuthPasswordName(): string
    { return 'password_hash'; }

    public function getAuthPassword(): string
    { return (string) $this->password_hash; }
}
