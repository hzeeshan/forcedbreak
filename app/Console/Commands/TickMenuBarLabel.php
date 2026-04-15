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
        while (true) {
            try {
                // Skip updating while on break
                if (cache()->get('on_break', false)) {
                    sleep(1);
                    continue;
                }

                $secondsLeft = cache()->get('break_seconds_left');

                if ($secondsLeft === null) {
                    sleep(1);
                    continue;
                }

                // Decrement by 1 second
                $secondsLeft = max(0, $secondsLeft - 1);
                cache()->put('break_seconds_left', $secondsLeft, now()->addHours(2));

                // Update the menu bar label
                $label = sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60);
                MenuBar::label($label);

                // TODO v2: Pre-break warning notification
                // NativePHP Notification facade doesn't work from child processes.
                // Tried HTTP bridge approach but it also didn't deliver reliably.
                // Revisit when NativePHP adds event-based notification support.

                // Trigger break overlay instantly when timer hits zero
                if ($secondsLeft <= 0) {
                    cache()->put('on_break', true, now()->addMinutes(30));
                    cache()->put('warning_sent', false, now()->addHours(2));
                    $this->openOverlayOnAllScreens();
                }
            } catch (\Throwable $e) {
                // Log but don't crash — keep the loop alive
                \Log::warning('TickMenuBarLabel error: ' . $e->getMessage());
            }

            sleep(1);
        }
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
