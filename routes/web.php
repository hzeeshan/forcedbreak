<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('menubar');
});

Route::get('/menubar', function () {
    return view('menubar');
})->name('menubar');

Route::get('/settings', function () {
    return view('settings');
})->name('settings');

Route::get('/open-settings', function () {
    \Native\Laravel\Facades\Window::open('settings')
        ->route('settings')
        ->width(420)
        ->height(620)
        ->titleBarHidden()
        ->resizable(false)
        ->vibrancy('under-window');
    return 'ok';
})->name('open.settings');

Route::get('/break-overlay', function () {
    return view('break-overlay');
})->name('break.overlay');

// Called by the overlay window itself to close all overlays
Route::get('/close-overlays', function () {
    \Native\Laravel\Facades\Window::close('break-overlay');
    foreach (range(1, 5) as $i) {
        try { \Native\Laravel\Facades\Window::close("break-overlay-{$i}"); } catch (\Throwable) {}
    }
    return 'ok';
})->name('close.overlays');

// Dev-only routes
if (app()->environment('local')) {
    Route::get('/dev/migrate', function () {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--seed' => true, '--force' => true]);
        return '<pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    });

    // Immediately open break overlay on all screens
    Route::get('/dev/force-break', function () {
        cache()->put('break_seconds_left', 0, now()->addHours(2));
        cache()->put('on_break', true, now()->addMinutes(30));

        try {
            $displays = \Native\Laravel\Facades\Screen::displays();
        } catch (\Throwable) {
            $displays = [];
        }

        if (empty($displays)) {
            \Native\Laravel\Facades\Window::open('break-overlay')
                ->route('break.overlay')
                ->fullscreen()
                ->alwaysOnTop()
                ->titleBarHidden()
                ->resizable(false)
                ->movable(false)
                ->minimizable(false);
        } else {
            foreach ($displays as $i => $display) {
                $bounds = $display['bounds'];
                $id     = 'break-overlay' . ($i === 0 ? '' : "-{$i}");

                \Native\Laravel\Facades\Window::open($id)
                    ->route('break.overlay')
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

        return 'Break overlay triggered on ' . max(1, count($displays)) . ' screen(s)!';
    });
}
