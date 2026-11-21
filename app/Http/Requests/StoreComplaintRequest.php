<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'priority' => ['required', Rule::in(Complaint::PRIORITIES)],
            'sensitivity' => ['required', Rule::in(Complaint::SENSITIVITIES)],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'Kategori',
            'location_id' => 'Lokasi',
            'title' => 'Judul',
            'description' => 'Deskripsi',
            'priority' => 'Prioritas',
            'sensitivity' => 'Klasifikasi',
            'photos' => 'Foto',
        ];
    }
}
