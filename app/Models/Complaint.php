<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use SoftDeletes;

    public const PRIORITIES = ['rendah', 'sedang', 'tinggi', 'mendesak'];

    public const SENSITIVITIES = ['biasa', 'sensitif'];

    /**
     * Prioritas BAKU per klasifikasi. Pelapor tidak lagi memilih prioritas sendiri:
     * nilainya diturunkan dari klasifikasi agar laporan tidak bisa "dinaikkan" sepihak
     * demi didahulukan. Klasifikasi sensitif otomatis dianggap mendesak.
     *
     * @var array<string, string>
     */
    public const PRIORITY_BY_SENSITIVITY = [
        'biasa' => 'sedang',
        'sensitif' => 'mendesak',
    ];

    public const STATUSES = [
        'draft',
        'menunggu_verifikasi',
        'terverifikasi',
        'menunggu_penugasan',
        'ditugaskan',
        'sedang_ditangani',
        'menunggu_pemeriksaan',
        'selesai',
        'ditolak',
        'ditutup',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'menunggu_penugasan' => 'Menunggu Penugasan',
        'ditugaskan' => 'Ditugaskan',
        'sedang_ditangani' => 'Sedang Ditangani',
        'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'ditutup' => 'Ditutup',
    ];

    /** Transisi status yang diizinkan (backend controlled). */
    public const TRANSITIONS = [
        'draft' => ['menunggu_verifikasi'],
        'menunggu_verifikasi' => ['terverifikasi', 'ditolak'],
        'terverifikasi' => ['menunggu_penugasan', 'ditolak'],
        'menunggu_penugasan' => ['ditugaskan', 'ditolak'],
        'ditugaskan' => ['sedang_ditangani', 'menunggu_penugasan', 'menunggu_pemeriksaan'],
        'sedang_ditangani' => ['menunggu_pemeriksaan'],
        'menunggu_pemeriksaan' => ['selesai', 'sedang_ditangani'],
        'selesai' => ['ditutup'],
        'ditolak' => ['ditutup'],
        'ditutup' => [],
    ];

    protected $fillable = [
        'ticket_number',
        'user_id',
        'reporter_name',
        'reporter_contact',
        'is_anonymous',
        'category_id',
        'location_id',
        'title',
        'description',
        'priority',
        'sensitivity',
        'status',
        'assigned_to',
        'verified_by',
        'verification_note',
        'verified_at',
        'rejection_reason',
        'rejected_at',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_anonymous' => 'boolean',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ComplaintPhoto::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ComplaintStatusHistory::class)->latest();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->latest();
    }

    public function handlingLogs(): HasMany
    {
        return $this->hasMany(HandlingLog::class)->latest();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class)->latest();
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(ComplaintAccess::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function isSensitive(): bool
    {
        return $this->sensitivity === 'sensitif';
    }

    /**
     * Prioritas yang berlaku untuk sebuah klasifikasi.
     * Klasifikasi tak dikenal jatuh ke prioritas teraman ('sedang'), bukan 'mendesak'.
     */
    public static function priorityForSensitivity(?string $sensitivity): string
    {
        return self::PRIORITY_BY_SENSITIVITY[$sensitivity] ?? 'sedang';
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['selesai', 'ditolak', 'ditutup'], true);
    }

    public function canTransitionTo(string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$this->status] ?? [], true);
    }
}
