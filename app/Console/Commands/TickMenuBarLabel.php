<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Screen;
use Native\Laravel\Facades\Window;

class TickMenuBarLabel extends Command
{
    protected $signature = 'app:tick-menubar-label';
    protected $description = 'Continuously update the menu bar label every second and trigger overlay at zero';

    public function handle(): void
    {
        $lastLabel = '';

        while (true) {
            $start = microtime(true);

            try {
                // Skip updating while on break
                if (cache()->get('on_break', false)) {
                    $this->sleepUntilNextSecond($start);
                    continue;
                }

                $secondsLeft = cache()->get('break_seconds_left');

                if ($secondsLeft === null) {
                    $this->sleepUntilNextSecond($start);
                    continue;
                }

                // Decrement by 1 second
                $secondsLeft = max(0, $secondsLeft - 1);
                cache()->put('break_seconds_left', $secondsLeft, now()->addHours(2));

                // Only update label when it actually changes (avoids unnecessary API calls)
                $label = sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60);
                if ($label !== $lastLabel) {
                    MenuBar::label($label);
                    $lastLabel = $label;
                }

                // Trigger break overlay instantly when timer hits zero
                if ($secondsLeft <= 0) {
                    cache()->put('on_break', true, now()->addMinutes(30));
                    cache()->put('warning_sent', false, now()->addHours(2));
                    $this->openOverlayOnAllScreens();
                }
            } catch (\Throwable $e) {
                \Log::warning('TickMenuBarLabel error: ' . $e->getMessage());
            }

            $this->sleepUntilNextSecond($start);
        }
    }

    /**
     * Sleep precisely until the next second boundary to avoid CPU spin.
     */
    protected function sleepUntilNextSecond(float $start): void
    {
        $elapsed = microtime(true) - $start;
        $remaining = max(0.1, 1.0 - $elapsed);
        usleep((int) ($remaining * 1_000_000));
    }

    protected function openOverlayOnAllScreens(): void
    {
        $urlFile = storage_path('app/server_url.txt');
        $baseUrl = file_exists($urlFile) ? trim(file_get_contents($urlFile)) : 'http://127.0.0.1:8100';
        $overlayUrl = $baseUrl . '/break-overlay';

        try {
            $displays = Screen::displays();
        } catch (\Throwable) {
            $displays = [];
        }

        if (empty($displays)) {
            Window::open('break-overlay')
                ->url($overlayUrl)
                ->fullscreen()
                ->alwaysOnTop()
                ->titleBarHidden()
                ->resizable(false)
                ->movable(false)
                ->minimizable(false);
            return;
        }

        foreach ($displays as $i => $display) {
            $bounds = $display['bounds'];
            $id     = 'break-overlay' . ($i === 0 ? '' : "-{$i}");

            Window::open($id)
                ->url($overlayUrl)
                ->alwaysOnTop()
                ->titleBarHidden()
                ->resizable(false)
                ->movable(false)
                ->minimizable(false)
                ->position($bounds['x'], $bounds['y'])
                ->width($bounds['width'])
                ->height($bounds['height']);
        }
    }
}
