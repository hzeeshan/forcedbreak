<?php

namespace App\Providers;

use App\Models\BreakSetting;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\ChildProcess;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        // Ensure NativePHP's database has all tables + seed data
        \Artisan::call('migrate', ['--force' => true]);
        \Artisan::call('db:seed', ['--class' => 'ChallengesSeeder', '--force' => true]);

        // Ensure storage subdirectories exist (NativePHP doesn't create all of them)
        $appStoragePath = storage_path('app');
        if (! is_dir($appStoragePath)) {
            mkdir($appStoragePath, 0755, true);
        }

        // Store the server base URL in a file so the scheduler child process can read it
        $port = $_SERVER['SERVER_PORT'] ?? 8100;
        $baseUrl = "http://127.0.0.1:{$port}";
        file_put_contents(storage_path('app/server_url.txt'), $baseUrl);

        // Read saved settings for initial label and cache
        $settings = BreakSetting::current();
        $totalSeconds = $settings->interval_minutes * 60;
        $secondsLeft = cache()->get('break_seconds_left', $totalSeconds);
        $initialLabel = sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60);

        // Ensure cache has a value for the ticker to pick up
        if (cache()->get('break_seconds_left') === null) {
            cache()->put('break_seconds_left', $totalSeconds, now()->addHours(2));
        }

        MenuBar::create()
            ->label($initialLabel)
            ->route('menubar')
            ->width(380)
            ->height(480)
            ->resizable(false)
            ->withContextMenu(
                Menu::make(
                    Menu::label('ForcedBreak'),
                    Menu::separator(),
                    Menu::link(route('open.settings'), 'Settings...'),
                    Menu::separator(),
                    Menu::quit()
                )
            );

        // Run the scheduler as a persistent background process (kill any existing one first)
        ChildProcess::stop('scheduler');
        ChildProcess::artisan('schedule:work', 'scheduler', persistent: true);

        // Run the menu bar label ticker (updates countdown every second)
        ChildProcess::stop('menubar-ticker');
        ChildProcess::artisan('app:tick-menubar-label', 'menubar-ticker', persistent: true);
    }

    public function phpIni(): array
    {
        return [];
    }
}
