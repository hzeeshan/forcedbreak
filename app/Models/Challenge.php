<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'emoji', 'title', 'subtitle', 'category', 'is_active', 'is_custom',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_custom' => 'boolean',
    ];

    public static array $categories = [
        'physical'  => ['label' => 'Physical',   'emoji' => '💪'],
        'hydration' => ['label' => 'Hydration',  'emoji' => '💧'],
        'mental'    => ['label' => 'Mental',      'emoji' => '🧘'],
        'movement'  => ['label' => 'Movement',    'emoji' => '🚶'],
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategories($query, array $categories)
    {
        return $query->whereIn('category', $categories);
    }
}
