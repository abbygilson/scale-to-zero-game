<?php

use App\Models\Round;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Leaderboard — Guess the Scale to Zero')] class extends Component
{
    /**
     * Seconds a guess stays in the recent feed before it fades away entirely.
     */
    public const int RECENT_WINDOW_SECONDS = 300;

    /**
     * The round holding the #1 spot when the board last checked.
     */
    public ?int $leaderRoundId = null;

    public function mount(): void
    {
        $this->leaderRoundId = $this->leaderboard->first()?->id;
    }

    /**
     * Celebrate with snow on the booth TV whenever a new round takes the #1 spot.
     */
    public function checkLeader(): void
    {
        $leader = $this->leaderboard->first();

        if ($leader !== null && $leader->id !== $this->leaderRoundId) {
            $this->dispatch('new-leader', name: $leader->player_name, deltaMs: $leader->delta_ms);
        }

        $this->leaderRoundId = $leader?->id;
    }

    #[Computed]
    public function leaderboard(): Collection
    {
        return Round::closest()->limit(10)->get();
    }

    #[Computed]
    public function recentGuesses(): Collection
    {
        return Round::query()
            ->where('created_at', '>=', now()->subSeconds(self::RECENT_WINDOW_SECONDS))
            ->latest()
            ->limit(8)
            ->get();
    }
};
?>

<div wire:poll.3s.keep-alive="checkLeader" class="relative isolate h-dvh overflow-hidden">
    {{-- Full-bleed dot-grid backdrop --}}
    <div class="absolute inset-0 bg-[radial-gradient(var(--color-slate-200)_1px,transparent_1px)] [background-size:8px_8px]"></div>

    {{-- Snowfall and a "new #1" callout whenever someone takes the top spot --}}
    <div
        wire:ignore
        x-data="{
            leader: null,
            showing: false,
            celebrate(detail) {
                this.leader = detail;
                this.showing = true;
                const field = this.$refs.snow;
                for (let i = 0; i < 140; i++) {
                    const flake = document.createElement('span');
                    const size = 0.8 + Math.random() * 1.8;
                    const fall = 4 + Math.random() * 4;
                    flake.textContent = '❄';
                    flake.className = 'board-flake';
                    flake.style.left = Math.random() * 100 + 'vw';
                    flake.style.fontSize = size + 'rem';
                    flake.style.opacity = 0.5 + Math.random() * 0.5;
                    flake.style.setProperty('--drift', (Math.random() * 16 - 8) + 'vw');
                    flake.style.animationDuration = fall + 's';
                    flake.style.animationDelay = Math.random() * 3 + 's';
                    field.appendChild(flake);
                    setTimeout(() => flake.remove(), (fall + 3.5) * 1000);
                }
                clearTimeout(this.hide);
                this.hide = setTimeout(() => this.showing = false, 7000);
            },
        }"
        x-on:new-leader.window="celebrate($event.detail)"
        class="pointer-events-none absolute inset-0 z-30"
    >
        <div x-ref="snow" class="absolute inset-0 overflow-hidden"></div>

        <div
            x-show="showing"
            x-transition:enter="transition duration-500 ease-out"
            x-transition:enter-start="scale-75 opacity-0"
            x-transition:leave="transition duration-500 ease-in"
            x-transition:leave-end="scale-90 opacity-0"
            x-cloak
            class="absolute inset-0 grid place-items-center"
        >
            <div class="flex flex-col items-center gap-3 rounded-3xl bg-cloud px-14 py-10 text-center text-white shadow-2xl ring-8 ring-white/60 xl:px-20 xl:py-14">
                <p class="font-mono text-lg tracking-widest uppercase text-white/70 xl:text-2xl">❄ New #1 ❄</p>
                <p class="text-6xl font-semibold tracking-tight xl:text-8xl" x-text="leader?.name"></p>
                <p class="font-mono text-2xl text-white/80 xl:text-4xl">±<span x-text="leader?.deltaMs?.toLocaleString()"></span> ms</p>
            </div>
        </div>
    </div>

    <div class="relative z-10 flex h-full gap-8 p-8 xl:gap-10 xl:p-12">
        {{-- Left: the big leaderboard --}}
        <main class="flex min-w-0 flex-[3] flex-col gap-8">
            <h1 class="text-4xl font-semibold tracking-tight xl:text-6xl">Guess the <span class="font-serif italic">scale to zero</span> 💤</h1>

            {{-- Prize banner: a looping wake stopwatch beside rotating calls to action. wire:ignore keeps the poll from restarting it. --}}
            <div
                wire:ignore
                x-data="{
                    ms: 0,
                    awake: false,
                    word: 0,
                    words: ['Guess the wake time.', 'Closest guess wins…', 'a Laravel Cloud beanie!'],
                    init() {
                        const run = () => {
                            const target = 300 + Math.round(Math.random() * 200);
                            const start = performance.now();
                            this.awake = false;
                            const tick = (now) => {
                                const t = Math.min((now - start) / 1400, 1);
                                this.ms = Math.round(target * (1 - Math.pow(1 - t, 3)));
                                if (t < 1) { requestAnimationFrame(tick); } else { this.awake = true; setTimeout(run, 2200); }
                            };
                            requestAnimationFrame(tick);
                        };
                        run();
                        setInterval(() => this.word = (this.word + 1) % this.words.length, 2600);
                    },
                }"
                class="flex shrink-0 items-center gap-8 overflow-hidden rounded-3xl bg-cloud px-8 py-6 text-white shadow-2xl xl:gap-10 xl:px-10 xl:py-8"
            >
                <div class="flex shrink-0 flex-col items-center gap-1">
                    <div class="w-[6.5ch] text-right font-mono text-5xl font-semibold tracking-tight tabular-nums xl:text-7xl"><span x-text="ms">0</span><span class="text-white/60"> ms</span></div>
                    <div class="font-mono text-xs tracking-widest uppercase transition-colors duration-300 xl:text-sm" :class="awake ? 'text-emerald-300' : 'text-white/60'" x-text="awake ? '● awake' : '○ waking…'"></div>
                </div>

                <div class="h-16 w-px shrink-0 bg-white/25 xl:h-20"></div>

                <div class="relative h-[1.25em] min-w-0 flex-1 overflow-hidden text-4xl font-semibold tracking-tight xl:text-6xl">
                    <template x-for="(text, index) in words" :key="index">
                        <p
                            class="absolute inset-0 truncate transition-all duration-300 ease-out"
                            :class="index === word ? 'translate-y-0 opacity-100 delay-300' : (index === (word + words.length - 1) % words.length ? '-translate-y-full opacity-0' : 'translate-y-full opacity-0')"
                            x-text="text"
                        ></p>
                    </template>
                </div>
            </div>

            <div class="flex min-h-0 flex-1 flex-col rounded-3xl bg-white/85 p-8 shadow-2xl ring-1 ring-black/5 backdrop-blur-md xl:p-10">
                <h2 class="font-mono text-sm font-medium tracking-wide text-slate-400 uppercase xl:text-base">Leaderboard</h2>

                @if ($this->leaderboard->isEmpty())
                    <p class="mt-6 text-2xl text-slate-400 xl:text-3xl">No rounds yet — be the first!</p>
                @else
                    <ol role="list" class="mt-6 flex min-h-0 flex-1 flex-col gap-4 overflow-hidden xl:gap-5">
                        @foreach ($this->leaderboard as $round)
                            <li wire:key="board-leader-{{ $round->id }}" class="flex items-baseline gap-6 text-2xl xl:text-4xl">
                                <div class="w-10 shrink-0 font-mono text-xl text-slate-400 tabular-nums xl:w-14 xl:text-3xl">{{ $loop->iteration }}</div>
                                <div class="flex-1 truncate font-semibold text-slate-700">{{ $round->player_name }}</div>
                                <div class="shrink-0 font-mono text-cloud tabular-nums">±{{ number_format($round->delta_ms) }} ms</div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </main>

        {{-- Right: the recent-guess feed and the QR code to join --}}
        <aside class="flex w-96 shrink-0 flex-col gap-8 xl:w-[28rem]">
            <div class="flex min-h-0 flex-1 flex-col rounded-3xl bg-white/85 p-7 shadow-2xl ring-1 ring-black/5 backdrop-blur-md xl:p-8">
                <h2 class="font-mono text-sm font-medium tracking-wide text-slate-400 uppercase xl:text-base">Recent guesses</h2>

                @if ($this->recentGuesses->isEmpty())
                    <p class="mt-5 text-lg text-slate-400 xl:text-xl">Waiting for the next brave guess…</p>
                @else
                    <ul role="list" class="mt-5 flex min-h-0 flex-1 flex-col gap-4 overflow-hidden xl:gap-5">
                        @foreach ($this->recentGuesses as $round)
                            @php $ageSeconds = min((int) $round->created_at->diffInSeconds(), $this::RECENT_WINDOW_SECONDS); @endphp
                            <li
                                wire:key="recent-guess-{{ $round->id }}"
                                class="flex flex-col gap-1 rounded-xl bg-cloud/4 px-5 py-4 ring-1 ring-cloud/15 transition-opacity duration-1000 motion-safe:animate-[board-enter_0.5s_ease-out]"
                                style="opacity: {{ round(max(0.15, 1 - $ageSeconds / $this::RECENT_WINDOW_SECONDS), 2) }}"
                            >
                                <p class="text-sm font-medium text-cloud xl:text-base">{{ $round->verdict }}</p>
                                <p class="text-lg font-semibold text-slate-700 xl:text-xl">{{ $round->player_name }} <span class="font-normal text-slate-400">was off by</span> {{ number_format($round->delta_ms) }} ms</p>
                                <p class="text-sm text-slate-500 xl:text-base">Guessed {{ number_format($round->guess_ms) }} ms — the wake took {{ number_format($round->actual_ms) }} ms</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-6 rounded-3xl bg-white/85 p-6 shadow-2xl ring-1 ring-black/5 backdrop-blur-md xl:p-7">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode(url('/')) }}"
                    alt="QR code linking to the game"
                    class="size-28 shrink-0 rounded-xl xl:size-36"
                />
                <div class="flex flex-col gap-1">
                    <p class="text-xl font-semibold tracking-tight xl:text-2xl">Scan to play</p>
                    <p class="text-sm text-pretty text-slate-500 xl:text-base">Guess the wake time on your phone — closest guess wins.</p>
                </div>
            </div>

            <p class="self-end rounded-full bg-white px-3 py-1.5 font-mono text-xs tracking-wide text-slate-500 uppercase shadow-xs ring-1 ring-black/5">Powered by Laravel Cloud</p>
        </aside>
    </div>
</div>

@assets
<style>
    @keyframes board-enter {
        from {
            opacity: 0;
            transform: translateY(-0.5rem);
        }
    }
</style>
@endassets

@script
<script>
    // The booth TV runs unattended: if an update fails (a deploy, an expired
    // session, a network blip), reload the page instead of showing a dialog.
    // Wait until the board answers first, so a reload never lands on an error page.
    let reloading = false;

    Livewire.hook('request', ({ fail }) => {
        fail(({ preventDefault }) => {
            preventDefault();

            if (reloading) {
                return;
            }

            reloading = true;

            const reloadWhenReachable = () => fetch(window.location.href, { method: 'HEAD', cache: 'no-store' })
                .then((response) => response.ok ? window.location.reload() : setTimeout(reloadWhenReachable, 5000))
                .catch(() => setTimeout(reloadWhenReachable, 5000));

            setTimeout(reloadWhenReachable, 3000);
        });
    });
</script>
@endscript
