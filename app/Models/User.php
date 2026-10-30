<?php

namespace App\Models;

use App\Domain\Identity\{Permission, Role};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements \Laravel\Passport\Contracts\OAuthenticatable
{
    use HasFactory, Notifiable, \Laravel\Passport\HasApiTokens;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $attributes = ['role' => 'user'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'role' => Role::class];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission, $this->role->permissions(), true);
    }
}
