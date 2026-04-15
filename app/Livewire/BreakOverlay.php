<?php

namespace App\Livewire;

use App\Models\BreakSetting;
use App\Models\Challenge;
use App\Models\Streak;
use Livewire\Component;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;

class BreakOverlay extends Component
{
    public bool $done = false;

    public string $challengeEmoji    = '💪';
    public string $challengeTitle    = 'Do 10 push-ups';
    public string $challengeSubtitle = 'Get that blood pumping!';
    public string $challengeCategory = 'physical';
    public bool $skipPenalty = true;

    public function mount(): void
    {
        $this->skipPenalty = BreakSetting::current()->skip_penalty;
        // Use cached challenge so all screens show the same one
        $cached = cache()->get('current_challenge');

        if (! $cached) {
            $settings   = BreakSetting::current();
            $categories = $settings->activeCategories();

            $challenge = Challenge::active()
                ->forCategories($categories)
                ->inRandomOrder()
                ->first();

            if ($challenge) {
                $cached = [
                    'emoji'    => $challenge->emoji,
                    'title'    => $challenge->title,
                    'subtitle' => $challenge->subtitle ?? '',
                    'category' => $challenge->category,
                ];
                cache()->put('current_challenge', $cached, now()->addMinutes(30));
            }
        }

        if ($cached) {
            $this->challengeEmoji    = $cached['emoji'];
            $this->challengeTitle    = $cached['title'];
            $this->challengeSubtitle = $cached['subtitle'];
            $this->challengeCategory = $cached['category'];
        }
    }

    public function skipBreak(): void
    {
        $settings = BreakSetting::current();

        $totalSeconds = $settings->skip_penalty ? 5 * 60 : $settings->interval_minutes * 60;

        cache()->put('break_seconds_left', $totalSeconds, now()->addHours(2));
        cache()->put('on_break', false, now()->addHours(2));
        cache()->put('warning_sent', false, now()->addHours(2));
        cache()->put('break_skipped', true, now()->addSeconds(5));

        Streak::current()->recordSkipped();
        cache()->forget('current_challenge');

        // Immediately update menu bar label
        $label = sprintf('%02d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60);
        MenuBar::label($label);

        $this->closeAllOverlays();
        $this->dispatch('close-overlay-window');
    }

    public function markDone(): void
    {
        $this->done = true;

        $settings     = BreakSetting::current();
        $totalSeconds = $settings->interval_minutes * 60;

        cache()->put('break_seconds_left', $totalSeconds, now()->addHours(2));
        cache()->put('on_break', false, now()->addHours(2));
        cache()->put('warning_sent', false, now()->addHours(2));
        cache()->put('break_completed', true, now()->addSeconds(5));

        Streak::current()->recordCompleted();
        cache()->forget('current_challenge');

        // Immediately update menu bar label so user sees the timer restart
        $label = sprintf('%02d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60);
        MenuBar::label($label);

        $this->closeAllOverlays();

        // Dispatch browser event so Alpine can close the window after showing success briefly
        $this->dispatch('close-overlay-window');
    }

    protected function closeAllOverlays(): void
    {
        // Close primary + up to 5 additional display overlays
        Window::close('break-overlay');
        foreach (range(1, 5) as $i) {
            try {
                Window::close("break-overlay-{$i}");
            } catch (\Throwable) {
                // Window doesn't exist — that's fine
            }
        }
    }

    public function render()
    {
        return view('livewire.break-overlay');
    }
}
