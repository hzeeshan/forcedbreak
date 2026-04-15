<?php

namespace App\Livewire;

use App\Models\BreakSetting;
use App\Models\Streak;
use Livewire\Component;
use Native\Laravel\Facades\Window;

class MenuBarTimer extends Component
{
    public int $secondsLeft;
    public int $totalSeconds;
    public bool $onBreak = false;

    // Streak display
    public int $currentStreak    = 0;
    public int $longestStreak    = 0;
    public int $totalCompleted   = 0;

    public function mount(): void
    {
        $settings = BreakSetting::current();
        $this->totalSeconds = $settings->interval_minutes * 60;
        $this->secondsLeft  = cache()->get('break_seconds_left', $this->totalSeconds);
        $this->onBreak      = cache()->get('on_break', false);

        $this->syncStreak();
    }

    public function tick(): void
    {
        // Sync back when overlay closes or settings changed
        if (cache()->pull('break_completed') || cache()->pull('break_skipped')) {
            $settings           = BreakSetting::current();
            $this->totalSeconds = $settings->interval_minutes * 60;
            $this->secondsLeft  = cache()->get('break_seconds_left', $this->totalSeconds);
            $this->onBreak      = false;
            $this->syncStreak();
            return;
        }

        $this->onBreak = cache()->get('on_break', false);
        if ($this->onBreak) {
            return;
        }

        // Read the current countdown from cache (ticker process keeps it updated)
        $settings = BreakSetting::current();
        $this->totalSeconds = $settings->interval_minutes * 60;
        $this->secondsLeft = cache()->get('break_seconds_left', $this->totalSeconds);
    }

    public function triggerBreak(): void
    {
        $this->onBreak = true;
        cache()->put('on_break', true, now()->addMinutes(30));

        $urlFile = storage_path('app/server_url.txt');
        $baseUrl = file_exists($urlFile) ? trim(file_get_contents($urlFile)) : request()->getSchemeAndHttpHost();
        $overlayUrl = $baseUrl . '/break-overlay';

        Window::open('break-overlay')
            ->url($overlayUrl)
            ->fullscreen()
            ->alwaysOnTop()
            ->titleBarHidden()
            ->resizable(false)
            ->movable(false)
            ->minimizable(false);
    }

    protected function syncStreak(): void
    {
        $streak = Streak::current();
        $this->currentStreak  = $streak->current_streak;
        $this->longestStreak  = $streak->longest_streak;
        $this->totalCompleted = $streak->total_completed;
    }

    public function getProgressProperty(): float
    {
        if ($this->totalSeconds === 0) {
            return 0;
        }
        return (($this->totalSeconds - $this->secondsLeft) / $this->totalSeconds) * 100;
    }

    public function getFormattedTimeProperty(): string
    {
        return sprintf('%02d:%02d', intdiv($this->secondsLeft, 60), $this->secondsLeft % 60);
    }

    public function render()
    {
        return view('livewire.menu-bar-timer');
    }
}
