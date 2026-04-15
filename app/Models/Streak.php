<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    protected $fillable = [
        'current_streak', 'longest_streak', 'total_completed',
        'total_skipped', 'last_completed_date',
    ];

    protected $casts = [
        'last_completed_date' => 'date',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'current_streak'   => 0,
            'longest_streak'   => 0,
            'total_completed'  => 0,
            'total_skipped'    => 0,
            'last_completed_date' => null,
        ]);
    }

    public function recordCompleted(): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($this->last_completed_date?->toDateString() === $today) {
            // Already recorded today — just increment total
            $this->increment('total_completed');
            return;
        }

        $newStreak = ($this->last_completed_date?->toDateString() === $yesterday)
            ? $this->current_streak + 1
            : 1;

        $this->update([
            'current_streak'      => $newStreak,
            'longest_streak'      => max($this->longest_streak, $newStreak),
            'total_completed'     => $this->total_completed + 1,
            'last_completed_date' => $today,
        ]);
    }

    public function recordSkipped(): void
    {
        $this->update([
            'current_streak'  => 0,
            'total_skipped'   => $this->total_skipped + 1,
        ]);
    }
}
