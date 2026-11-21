<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    public const AREAS = [
        'putra' => 'Area Putra',
        'putri' => 'Area Putri',
        'umum' => 'Umum / Bersama',
    ];

    protected $fillable = ['name', 'code', 'area', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArea($query, ?string $area)
    {
        if (! in_array($area, ['putra', 'putri', 'umum'], true)) {
            return $query;
        }

        return $query->whereIn('area', [$area, 'umum']);
    }

    public function areaLabel(): string
    {
        return self::AREAS[$this->area] ?? $this->area;
    }
}
