<?php

namespace App\Console\Commands;

use App\Models\BreakSetting;
use Illuminate\Console\Command;

class CheckBreakTimer extends Command
{
    protected $signature = 'app:check-break-timer';
    protected $description = 'Periodic safety check — ticker process handles countdown, warnings, and overlay';

    public function handle(): void
    {
        // Safety net: if ticker process died and timer should have triggered,
        // ensure on_break gets set so the next ticker restart picks it up.
        if (cache()->get('on_break', false)) {
            return;
        }

        $settings    = BreakSetting::current();
        $secondsLeft = cache()->get('break_seconds_left', $settings->interval_minutes * 60);

        if ($secondsLeft <= 0) {
            cache()->put('on_break', true, now()->addMinutes(30));
        }
    }
}
