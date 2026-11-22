<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\HasDatabaseNotifications;
// Trait ini WAJIB: tanpa ini `$user->notifications()` tidak ada dan semua notifikasi
// (status laporan, penugasan, triase sensitif) tidak pernah tersimpan.
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'phone', 'position', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasDatabaseNotifications, HasFactory, Notifiable;

    public const ROLES = ['pelapor', 'koordinator_keamanan', 'koordinator_logistik', 'admin', 'pimpinan'];

    /** Role yang boleh dipilih admin saat membuat/mengubah akun. */
    public const STAFF_ROLES = ['koordinator_keamanan', 'koordinator_logistik', 'admin', 'pimpinan', 'pelapor'];

    public const ROLE_LABELS = [
        'pelapor' => 'Pelapor',
        'koordinator_keamanan' => 'Koordinator Keamanan',
        'koordinator_logistik' => 'Koordinator Logistik',
        'admin' => 'Admin',
        'pimpinan' => 'Pimpinan',
    ];

    /** Role koordinator = penanggung jawab lapangan per divisi. */
    public const KOORDINATOR_ROLES = ['koordinator_keamanan', 'koordinator_logistik'];

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

    public function isKoordinator(): bool
    {
        return $this->isRole(...self::KOORDINATOR_ROLES);
    }

    /** Mendapatkan koordinator aktif untuk sebuah divisi (mis. koordinator_logistik). */
    public static function koordinatorFor(string $role)
    {
        return self::where('role', $role)->where('is_active', true)->get();
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst((string) $this->role);
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'pelapor' => 'pelapor.dashboard',
            'koordinator_keamanan', 'koordinator_logistik' => 'koordinator.dashboard',
            'pimpinan' => 'pimpinan.dashboard',
            default => 'admin.dashboard',
        };
    }
}
