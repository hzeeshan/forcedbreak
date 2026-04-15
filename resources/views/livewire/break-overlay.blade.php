<div
    class="flex flex-col items-center justify-center min-h-screen bg-[#1a1a24] px-8"
    x-data="{ played: false }"
    x-on:close-overlay-window.window="setTimeout(() => { fetch('{{ route('close.overlays') }}').catch(() => {}); window.close(); }, 1500)"
    x-init="
        if (!played) {
            played = true;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                // Gentle chime: three ascending tones
                [[440, 0], [554, 0.15], [659, 0.30]].forEach(([freq, when]) => {
                    const osc  = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = freq;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0, ctx.currentTime + when);
                    gain.gain.linearRampToValueAtTime(0.18, ctx.currentTime + when + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + when + 0.6);
                    osc.start(ctx.currentTime + when);
                    osc.stop(ctx.currentTime + when + 0.7);
                });
            } catch(e) {}
        }
    "
>
    @if(!$done)
        {{-- Background glow --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-blue-500/5 rounded-full blur-3xl animate-pulse"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center text-center max-w-lg w-full">

            {{-- Category badge --}}
            <div class="mb-8 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/[0.05] border border-white/[0.08]">
                <span class="text-sm">{{ \App\Models\Challenge::$categories[$challengeCategory]['emoji'] ?? '⚡' }}</span>
                <span class="text-[11px] font-medium tracking-widest uppercase text-white/35">
                    {{ \App\Models\Challenge::$categories[$challengeCategory]['label'] ?? ucfirst($challengeCategory) }}
                </span>
            </div>

            {{-- Emoji --}}
            <div class="text-[96px] leading-none mb-6 drop-shadow-2xl select-none" style="filter: drop-shadow(0 0 48px rgba(96,165,250,0.2))">
                {{ $challengeEmoji }}
            </div>

            {{-- Text --}}
            <h1 class="text-[42px] font-bold text-white/90 leading-tight mb-3 tracking-tight">
                {{ $challengeTitle }}
            </h1>
            @if($challengeSubtitle)
                <p class="text-white/40 text-xl mb-12">{{ $challengeSubtitle }}</p>
            @else
                <div class="mb-12"></div>
            @endif

            {{-- Done button --}}
            <button
                wire:click="markDone"
                wire:loading.attr="disabled"
                class="group inline-flex items-center gap-3 bg-blue-500 hover:bg-blue-400 active:bg-blue-600 disabled:opacity-50 text-white font-semibold text-xl px-12 py-5 rounded-2xl transition-all duration-150 shadow-xl shadow-blue-900/20 hover:shadow-blue-500/20 hover:scale-[1.03] active:scale-[0.97] cursor-pointer select-none"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span wire:loading.remove wire:target="markDone">I Did It!</span>
                <span wire:loading wire:target="markDone">Done!</span>
            </button>

            {{-- Skip --}}
            <button
                wire:click="skipBreak"
                @if($skipPenalty) onclick="return confirm('Skip this break? Timer will restart at 5 minutes instead of your full interval.')" @endif
                class="mt-6 text-white/15 hover:text-white/40 text-sm transition-colors cursor-pointer"
            >
                {{ $skipPenalty ? 'Skip (penalty applies)' : 'Skip this break' }}
            </button>
        </div>

    @else
        <div class="flex flex-col items-center text-center">
            <div class="text-[96px] leading-none mb-6">🎉</div>
            <h1 class="text-4xl font-bold text-white/90 mb-3">Great work!</h1>
            <p class="text-white/35 text-lg">Streak updated. Timer restarting...</p>
        </div>
    @endif

</div>
