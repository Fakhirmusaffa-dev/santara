<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Trait ini WAJIB: tanpa ini `$user->notifications()` tidak ada dan semua notifikasi
// (status laporan, penugasan, triase sensitif) tidak pernah tersimpan.
use Illuminate\Notifications\HasDatabaseNotifications;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'phone', 'position', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasDatabaseNotifications, HasFactory, Notifiable;

    public const ROLES = ['petugas', 'admin', 'pimpinan'];

    public const ROLE_LABELS = [
        'pelapor' => 'Pelapor',
        'petugas' => 'Petugas',
        'admin' => 'Admin',
        'pimpinan' => 'Pimpinan',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'assigned_to');
    }

    public function handlingLogs(): HasMany
    {
        return $this->hasMany(HandlingLog::class);
    }

    public function isRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst((string) $this->role);
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'pelapor' => 'pelapor.dashboard',
            'petugas' => 'petugas.dashboard',
            'pimpinan' => 'pimpinan.dashboard',
            default => 'admin.dashboard',
        };
    }
}
