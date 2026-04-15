<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakSetting extends Model
{
    protected $fillable = [
        'interval_minutes',
        'auto_launch',
        'skip_penalty',
        'warning_enabled',
        'warning_seconds',
        'active_categories',
    ];

    protected $casts = [
        'auto_launch'       => 'boolean',
        'skip_penalty'      => 'boolean',
        'warning_enabled'   => 'boolean',
        'warning_seconds'   => 'integer',
        'active_categories' => 'array',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'interval_minutes'  => 25,
            'auto_launch'       => false,
            'skip_penalty'      => true,
            'warning_enabled'   => true,
            'warning_seconds'   => 60,
            'active_categories' => ['physical', 'hydration', 'mental', 'movement'],
        ]);
    }

    public function activeCategories(): array
    {
        return $this->active_categories ?? ['physical', 'hydration', 'mental', 'movement'];
    }
}
