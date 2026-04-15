<div
    wire:poll.1000ms="tick"
    class="flex flex-col h-full min-h-screen bg-[#1a1a24] text-white select-none overflow-hidden"
>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 pt-5 pb-2">
        <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full {{ $onBreak ? 'bg-amber-400 animate-pulse' : 'bg-green-400/80' }}"></div>
            <span class="text-[11px] font-medium text-white/40 tracking-widest uppercase">
                {{ $onBreak ? 'Break time' : 'Focus mode' }}
            </span>
        </div>
        <a
            href="{{ route('settings') }}"
            class="text-white/25 hover:text-white/50 transition-colors p-1"
            title="Settings"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </a>
    </div>

    {{-- Timer ring --}}
    <div class="flex flex-col items-center justify-center px-5 py-3">
        <div class="relative w-36 h-36">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 144 144">
                <circle cx="72" cy="72" r="62" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                <circle
                    cx="72" cy="72" r="62"
                    fill="none"
                    stroke="{{ $onBreak ? '#f59e0b' : '#60a5fa' }}"
                    stroke-width="8"
                    stroke-linecap="round"
                    stroke-dasharray="{{ round(2 * M_PI * 62, 2) }}"
                    stroke-dashoffset="{{ round(2 * M_PI * 62 * (1 - $this->progress / 100), 2) }}"
                    style="transition: stroke-dashoffset 0.9s linear; opacity: 0.8;"
                />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-[32px] font-bold font-mono tracking-tight leading-none text-white/90">
                    {{ $this->formattedTime }}
                </span>
                <span class="text-[10px] text-white/30 mt-1 uppercase tracking-wider">
                    {{ $onBreak ? 'break' : 'until break' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Streak row --}}
    <div class="mx-5 mb-3 rounded-lg bg-white/[0.04] border border-white/[0.06] px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-base leading-none">🔥</span>
            <div>
                <p class="text-[12px] font-semibold text-white/75">
                    {{ $currentStreak }} day streak
                </p>
                <p class="text-[10px] text-white/30 mt-0.5">Best: {{ $longestStreak }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[12px] font-semibold text-white/75">{{ $totalCompleted }}</p>
            <p class="text-[10px] text-white/30 mt-0.5">breaks done</p>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="px-5 pb-5">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[10px] text-white/25">Progress</span>
            <span class="text-[10px] font-mono text-white/35">{{ round($this->progress) }}%</span>
        </div>
        <div class="w-full bg-white/[0.06] rounded-full h-1">
            <div
                class="h-1 rounded-full {{ $onBreak ? 'bg-amber-400' : 'bg-blue-400/70' }}"
                style="width: {{ $this->progress }}%; transition: width 0.9s linear;"
            ></div>
        </div>
    </div>
</div>
