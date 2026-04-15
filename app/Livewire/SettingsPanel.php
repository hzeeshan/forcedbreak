<?php

namespace App\Livewire;

use App\Models\BreakSetting;
use App\Models\Challenge;
use Livewire\Component;
use Native\Laravel\Facades\App as NativeApp;
use Native\Laravel\Facades\MenuBar;

class SettingsPanel extends Component
{
    // General settings
    public int $intervalMinutes = 25;
    public bool $autoLaunch = false;
    public bool $skipPenalty = true;
    public bool $warningEnabled = true;
    public int $warningSeconds = 60;
    public array $activeCategories = ['physical', 'hydration', 'mental', 'movement'];
    public bool $saved = false;

    // Challenge editor state
    public string $activeTab = 'general'; // general | challenges
    public ?int $editingId = null;
    public string $editEmoji = '';
    public string $editTitle = '';
    public string $editSubtitle = '';
    public string $editCategory = 'physical';
    public bool $showAddForm = false;
    public string $newEmoji = '💪';
    public string $newTitle = '';
    public string $newSubtitle = '';
    public string $newCategory = 'physical';

    public array $intervalOptions = [];

    protected function getIntervalOptions(): array
    {
        $options = [
            ['value' => 25, 'label' => '25 min', 'sub' => 'Pomodoro'],
            ['value' => 30, 'label' => '30 min', 'sub' => ''],
            ['value' => 45, 'label' => '45 min', 'sub' => ''],
            ['value' => 60, 'label' => '60 min', 'sub' => '1 hour'],
            ['value' => 90, 'label' => '90 min', 'sub' => '1.5 hours'],
        ];

        if (app()->environment('local')) {
            array_unshift($options,
                ['value' => 2, 'label' => '2 min', 'sub' => 'Test'],
                ['value' => 5, 'label' => '5 min', 'sub' => 'Test'],
            );
        }

        return $options;
    }

    public array $warningOptions = [
        ['value' => 30,  'label' => '30 seconds'],
        ['value' => 60,  'label' => '1 minute'],
        ['value' => 120, 'label' => '2 minutes'],
        ['value' => 300, 'label' => '5 minutes'],
    ];

    public function mount(): void
    {
        $this->intervalOptions = $this->getIntervalOptions();

        $settings = BreakSetting::current();
        $this->intervalMinutes   = $settings->interval_minutes;
        $this->autoLaunch        = $settings->auto_launch;
        $this->skipPenalty       = $settings->skip_penalty;
        $this->warningEnabled    = $settings->warning_enabled ?? true;
        $this->warningSeconds    = $settings->warning_seconds ?? 60;
        $this->activeCategories  = $settings->activeCategories();
    }

    public function save(): void
    {
        $this->validate([
            'intervalMinutes'  => 'required|integer|in:' . implode(',', array_column($this->intervalOptions, 'value')),
            'warningSeconds'   => 'required|integer|in:30,60,120,300',
            'activeCategories' => 'required|array|min:1',
        ]);

        $settings = BreakSetting::current();
        $settings->update([
            'interval_minutes'  => $this->intervalMinutes,
            'auto_launch'       => $this->autoLaunch,
            'skip_penalty'      => $this->skipPenalty,
            'warning_enabled'   => $this->warningEnabled,
            'warning_seconds'   => $this->warningSeconds,
            'active_categories' => $this->activeCategories,
        ]);

        $totalSeconds = $this->intervalMinutes * 60;
        cache()->put('break_seconds_left', $totalSeconds, now()->addHours(2));
        cache()->put('on_break', false, now()->addHours(2));
        cache()->put('warning_sent', false, now()->addHours(2));
        MenuBar::label(sprintf('%02d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60));

        // Wire auto-launch via NativePHP App API (only works when running bundled)
        try {
            NativeApp::openAtLogin($this->autoLaunch);
        } catch (\Throwable) {
            // Silently ignore in dev mode
        }

        $this->saved = true;
    }

    public function updated($property): void
    {
        $this->saved = false;
    }

    public function toggleCategory(string $cat): void
    {
        $this->saved = false;
        if (in_array($cat, $this->activeCategories)) {
            if (count($this->activeCategories) === 1) {
                return; // must keep at least one
            }
            $this->activeCategories = array_values(array_filter(
                $this->activeCategories,
                fn ($c) => $c !== $cat
            ));
        } else {
            $this->activeCategories[] = $cat;
        }
    }

    // ── Challenge CRUD ──────────────────────────────────────────

    public function toggleChallenge(int $id): void
    {
        $challenge = Challenge::findOrFail($id);
        $challenge->update(['is_active' => !$challenge->is_active]);
    }

    public function startEdit(int $id): void
    {
        $challenge = Challenge::findOrFail($id);
        $this->editingId    = $id;
        $this->editEmoji    = $challenge->emoji;
        $this->editTitle    = $challenge->title;
        $this->editSubtitle = $challenge->subtitle ?? '';
        $this->editCategory = $challenge->category;
        $this->showAddForm  = false;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editTitle'    => 'required|string|max:80',
            'editEmoji'    => 'required|string|max:10',
            'editCategory' => 'required|in:physical,hydration,mental,movement',
        ]);

        Challenge::findOrFail($this->editingId)->update([
            'emoji'    => $this->editEmoji,
            'title'    => $this->editTitle,
            'subtitle' => $this->editSubtitle ?: null,
            'category' => $this->editCategory,
        ]);

        $this->editingId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function deleteChallenge(int $id): void
    {
        Challenge::findOrFail($id)->delete();
        if ($this->editingId === $id) {
            $this->editingId = null;
        }
    }

    public function addChallenge(): void
    {
        $this->validate([
            'newTitle'    => 'required|string|max:80',
            'newEmoji'    => 'required|string|max:10',
            'newCategory' => 'required|in:physical,hydration,mental,movement',
        ]);

        Challenge::create([
            'emoji'     => $this->newEmoji,
            'title'     => $this->newTitle,
            'subtitle'  => $this->newSubtitle ?: null,
            'category'  => $this->newCategory,
            'is_active' => true,
            'is_custom' => true,
        ]);

        $this->newEmoji    = '💪';
        $this->newTitle    = '';
        $this->newSubtitle = '';
        $this->newCategory = 'physical';
        $this->showAddForm = false;
    }

    public function render()
    {
        $challengesByCategory = Challenge::orderBy('category')
            ->orderBy('is_custom')
            ->orderBy('title')
            ->get()
            ->groupBy('category');

        return view('livewire.settings-panel', [
            'challengesByCategory' => $challengesByCategory,
            'allCategories'        => Challenge::$categories,
        ]);
    }
}
