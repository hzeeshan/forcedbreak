<div class="min-h-screen bg-[#1a1a24] text-white/90 flex flex-col" x-data>

    {{-- Header --}}
    <div class="flex items-center gap-3 px-5 pt-5 pb-4 border-b border-white/[0.08]">
        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-sm shrink-0">⏱</div>
        <div>
            <h1 class="text-[13px] font-semibold text-white/90 leading-tight">ForcedBreak</h1>
            <p class="text-[11px] text-white/35">Settings</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-white/[0.08] px-5">
        <button
            wire:click="$set('activeTab', 'general')"
            class="text-[12px] font-medium py-2.5 mr-6 border-b-2 transition-colors cursor-pointer
                {{ $activeTab === 'general' ? 'border-blue-400 text-white/90' : 'border-transparent text-white/40 hover:text-white/60' }}"
        >General</button>
        <button
            wire:click="$set('activeTab', 'challenges')"
            class="text-[12px] font-medium py-2.5 border-b-2 transition-colors cursor-pointer
                {{ $activeTab === 'challenges' ? 'border-blue-400 text-white/90' : 'border-transparent text-white/40 hover:text-white/60' }}"
        >Challenges</button>
    </div>

    <div class="flex-1 overflow-y-auto px-5 py-4">

        {{-- ── GENERAL TAB ── --}}
        @if($activeTab === 'general')
        <form wire:submit="save" class="space-y-5">

            {{-- Break Interval --}}
            <div>
                <label class="block text-[11px] font-medium text-white/45 uppercase tracking-wider mb-2">Break Interval</label>
                <div class="grid grid-cols-2 gap-1.5">
                    @foreach($intervalOptions as $opt)
                        <button
                            type="button"
                            wire:click="$set('intervalMinutes', {{ $opt['value'] }})"
                            class="flex items-center justify-between px-3 py-2.5 rounded-lg border text-[13px] font-medium transition-all cursor-pointer
                                {{ $intervalMinutes == $opt['value']
                                    ? 'bg-blue-600 border-blue-500 text-white/90'
                                    : 'bg-white/[0.04] border-white/[0.08] text-white/50 hover:bg-white/[0.07] hover:text-white/70' }}"
                        >
                            <span>{{ $opt['label'] }}</span>
                            @if($opt['sub'])
                                <span class="text-[10px] {{ $intervalMinutes == $opt['value'] ? 'text-blue-300/70' : 'text-white/25' }}">{{ $opt['sub'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Category Filters --}}
            <div>
                <label class="block text-[11px] font-medium text-white/45 uppercase tracking-wider mb-2">Challenge Types</label>
                <div class="grid grid-cols-2 gap-1.5">
                    @foreach($allCategories as $key => $cat)
                        <button
                            type="button"
                            wire:click="toggleCategory('{{ $key }}')"
                            class="flex items-center gap-2 px-3 py-2.5 rounded-lg border text-[13px] font-medium transition-all cursor-pointer
                                {{ in_array($key, $activeCategories)
                                    ? 'bg-blue-600/30 border-blue-500/60 text-white/80'
                                    : 'bg-white/[0.04] border-white/[0.08] text-white/35 hover:text-white/60' }}"
                        >
                            <span>{{ $cat['emoji'] }}</span>
                            <span>{{ $cat['label'] }}</span>
                            @if(in_array($key, $activeCategories))
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 ml-auto text-blue-400/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Pre-break Warning — hidden for v1, revisit in v2 (NativePHP notifications don't work from child processes) --}}

            {{-- Divider --}}
            <div class="h-px bg-white/[0.08]"></div>

            {{-- Toggles --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] text-white/70">Launch at startup</p>
                        <p class="text-[11px] text-white/30 mt-0.5">Start automatically when you log in</p>
                    </div>
                    <button type="button" wire:click="$toggle('autoLaunch')"
                        class="relative inline-flex h-[22px] w-[40px] items-center rounded-full transition-colors cursor-pointer shrink-0 {{ $autoLaunch ? 'bg-blue-500' : 'bg-white/20' }}">
                        <span class="inline-block h-[18px] w-[18px] transform rounded-full bg-white shadow transition-transform {{ $autoLaunch ? 'translate-x-[20px]' : 'translate-x-[2px]' }}"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] text-white/70">Skip penalty</p>
                        <p class="text-[11px] text-white/30 mt-0.5">Skipping sets a 5-minute timer instead</p>
                    </div>
                    <button type="button" wire:click="$toggle('skipPenalty')"
                        class="relative inline-flex h-[22px] w-[40px] items-center rounded-full transition-colors cursor-pointer shrink-0 {{ $skipPenalty ? 'bg-blue-500' : 'bg-white/20' }}">
                        <span class="inline-block h-[18px] w-[18px] transform rounded-full bg-white shadow transition-transform {{ $skipPenalty ? 'translate-x-[20px]' : 'translate-x-[2px]' }}"></span>
                    </button>
                </div>
            </div>

            {{-- Save --}}
            <button
                type="submit"
                class="w-full bg-blue-500 hover:bg-blue-400 active:bg-blue-600 text-white font-semibold py-2.5 rounded-lg transition-all cursor-pointer text-[13px]"
            >
                @if($saved)✓ Saved!
                @else Save Settings
                @endif
            </button>

        </form>
        @endif

        {{-- ── CHALLENGES TAB ── --}}
        @if($activeTab === 'challenges')
        <div class="space-y-4">

            {{-- Add custom challenge --}}
            @if(!$showAddForm)
            <button
                wire:click="$set('showAddForm', true)"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-dashed border-blue-400/30 text-blue-400/70 hover:border-blue-400/50 hover:text-blue-300 transition-all cursor-pointer text-[13px] font-medium"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Custom Challenge
            </button>
            @else
            <div class="bg-white/[0.04] border border-white/[0.08] rounded-lg p-4 space-y-3">
                <p class="text-[11px] font-medium text-white/45 uppercase tracking-wider">New Challenge</p>
                <div class="flex gap-2">
                    <input wire:model="newEmoji" type="text" maxlength="4" placeholder="💪"
                        class="w-14 text-center bg-white/[0.06] border border-white/[0.10] rounded-md px-2 py-2 text-lg focus:outline-none focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/20">
                    <input wire:model="newTitle" type="text" placeholder="Challenge title..." maxlength="80"
                        class="flex-1 bg-white/[0.06] border border-white/[0.10] rounded-md px-3 py-2 text-[13px] text-white placeholder-white/25 focus:outline-none focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/20">
                </div>
                <input wire:model="newSubtitle" type="text" placeholder="Subtitle (optional)..." maxlength="100"
                    class="w-full bg-white/[0.06] border border-white/[0.10] rounded-md px-3 py-2 text-[13px] text-white placeholder-white/25 focus:outline-none focus:border-blue-400/50 focus:ring-1 focus:ring-blue-400/20">
                <div class="flex gap-2">
                    @foreach($allCategories as $key => $cat)
                    <button type="button" wire:click="$set('newCategory', '{{ $key }}')"
                        class="flex-1 text-[11px] py-1.5 rounded-md border cursor-pointer transition-colors
                            {{ $newCategory === $key ? 'bg-blue-600 border-blue-500 text-white' : 'bg-white/[0.04] border-white/10 text-white/35 hover:text-white/60' }}">
                        {{ $cat['emoji'] }}
                    </button>
                    @endforeach
                </div>
                @error('newTitle') <p class="text-red-400 text-[11px]">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <button wire:click="addChallenge"
                        class="flex-1 bg-blue-500 hover:bg-blue-400 text-white text-[13px] font-medium py-2 rounded-md cursor-pointer transition-colors">
                        Add
                    </button>
                    <button wire:click="$set('showAddForm', false)"
                        class="px-4 bg-white/[0.06] hover:bg-white/10 text-white/50 text-[13px] py-2 rounded-md cursor-pointer transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
            @endif

            {{-- Challenges by category --}}
            @foreach($allCategories as $key => $cat)
                @if(isset($challengesByCategory[$key]) && $challengesByCategory[$key]->isNotEmpty())
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm">{{ $cat['emoji'] }}</span>
                        <span class="text-[11px] font-medium text-white/40 uppercase tracking-wider">{{ $cat['label'] }}</span>
                        <span class="text-[10px] text-white/20">({{ $challengesByCategory[$key]->count() }})</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($challengesByCategory[$key] as $challenge)
                            @if($editingId === $challenge->id)
                            {{-- Edit form --}}
                            <div class="bg-white/[0.04] border border-blue-400/25 rounded-lg p-3 space-y-2">
                                <div class="flex gap-2">
                                    <input wire:model="editEmoji" type="text" maxlength="4"
                                        class="w-12 text-center bg-white/[0.06] border border-white/10 rounded-md px-2 py-1.5 text-base focus:outline-none focus:border-blue-400/50">
                                    <input wire:model="editTitle" type="text" maxlength="80"
                                        class="flex-1 bg-white/[0.06] border border-white/10 rounded-md px-3 py-1.5 text-[13px] text-white placeholder-white/25 focus:outline-none focus:border-blue-400/50">
                                </div>
                                <input wire:model="editSubtitle" type="text" maxlength="100" placeholder="Subtitle..."
                                    class="w-full bg-white/[0.06] border border-white/10 rounded-md px-3 py-1.5 text-[13px] text-white placeholder-white/25 focus:outline-none focus:border-blue-400/50">
                                <div class="flex gap-1.5">
                                    @foreach($allCategories as $k => $c)
                                    <button type="button" wire:click="$set('editCategory', '{{ $k }}')"
                                        class="flex-1 text-[11px] py-1 rounded-md border cursor-pointer transition-colors
                                            {{ $editCategory === $k ? 'bg-blue-600 border-blue-500 text-white' : 'bg-white/[0.04] border-white/10 text-white/30 hover:text-white/55' }}">
                                        {{ $c['emoji'] }}
                                    </button>
                                    @endforeach
                                </div>
                                <div class="flex gap-1.5">
                                    <button wire:click="saveEdit" class="flex-1 bg-blue-500 hover:bg-blue-400 text-white text-[11px] font-medium py-1.5 rounded-md cursor-pointer">Save</button>
                                    <button wire:click="cancelEdit" class="px-3 bg-white/[0.06] hover:bg-white/10 text-white/45 text-[11px] py-1.5 rounded-md cursor-pointer">Cancel</button>
                                    @if($challenge->is_custom)
                                    <button wire:click="deleteChallenge({{ $challenge->id }})"
                                        wire:confirm="Delete this challenge?"
                                        class="px-3 bg-red-900/20 hover:bg-red-900/40 text-red-400/80 text-[11px] py-1.5 rounded-md cursor-pointer">Delete</button>
                                    @endif
                                </div>
                            </div>
                            @else
                            {{-- Normal row --}}
                            <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white/[0.03] hover:bg-white/[0.06] transition-colors">
                                <span class="text-base shrink-0">{{ $challenge->emoji }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] truncate {{ $challenge->is_active ? 'text-white/75' : 'line-through text-white/25' }}">
                                        {{ $challenge->title }}
                                    </p>
                                    @if($challenge->is_custom)
                                        <span class="text-[10px] text-blue-400/50">custom</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <button wire:click="startEdit({{ $challenge->id }})"
                                        class="p-1.5 text-white/25 hover:text-white/60 cursor-pointer transition-colors"
                                        title="Edit this challenge">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button wire:click="toggleChallenge({{ $challenge->id }})"
                                        class="p-1.5 cursor-pointer transition-colors {{ $challenge->is_active ? 'text-green-400/70 hover:text-white/40' : 'text-white/15 hover:text-green-400/70' }}"
                                        title="{{ $challenge->is_active ? 'Click to disable (won\'t show during breaks)' : 'Click to enable (will show during breaks)' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </button>
                                    @if($challenge->is_custom)
                                    <button wire:click="deleteChallenge({{ $challenge->id }})"
                                        wire:confirm="Delete this challenge?"
                                        class="p-1.5 text-white/15 hover:text-red-400 hover:bg-red-400/10 rounded-md cursor-pointer transition-all"
                                        title="Delete this challenge">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="px-5 pb-4 pt-2 border-t border-white/[0.08]">
        <p class="text-center text-[10px]">
            <span class="text-white/15">ForcedBreak v1.0.0</span>
            <span class="text-white/10 mx-1">|</span>
            <a href="https://hafiz.dev" class="text-white/20 hover:text-white/40 transition-colors" target="_blank">hafiz.dev</a>
        </p>
    </div>

</div>
