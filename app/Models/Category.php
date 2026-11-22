<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'is_sensitive', 'koordinator_role'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_sensitive' => 'boolean',
        ];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Koordinator yang ditugaskan menerima laporan kategori ini (null = lewat admin). */
    public function koordinatorRole(): ?string
    {
        return $this->koordinator_role;
    }
}
