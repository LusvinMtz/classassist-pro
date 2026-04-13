<x-app-layout>
<div
    x-data="temporizador()"
    x-init="init()"
    @keydown.space.window.prevent="running ? pause() : start()"
>

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Temporizador</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Cuenta regresiva configurable para actividades en clase</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- Panel principal: reloj --}}
        <div class="lg:col-span-2 bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg flex flex-col items-center py-10 px-6">

            {{-- Etiqueta de actividad --}}
            <p class="text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-6 min-h-[20px]"
               x-text="label"></p>

            {{-- Círculo SVG --}}
            <div class="relative flex items-center justify-center mb-8">
                <svg width="280" height="280" viewBox="0 0 280 280" class="-rotate-90">
                    {{-- Track --}}
                    <circle cx="140" cy="140" r="120"
                            fill="none"
                            :stroke="dark ? '#1e3d58' : '#e6f0ff'"
                            stroke-width="14"/>
                    {{-- Progreso --}}
                    <circle cx="140" cy="140" r="120"
                            fill="none"
                            :stroke="ringColor"
                            stroke-width="14"
                            stroke-linecap="round"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="dashOffset"
                            style="transition: stroke-dashoffset 0.9s linear, stroke 0.5s ease;"/>
                </svg>

                {{-- Números --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center"
                     :class="finished ? 'animate-pulse' : ''">
                    <span class="font-black tabular-nums leading-none"
                          :class="{
                              'text-7xl': !finished,
                              'text-5xl text-red-500': finished,
                              'text-orange-500': !finished && progress <= 0.25 && progress > 0.1,
                              'text-red-500':    !finished && progress <= 0.1
                          }"
                          :style="(!finished && progress > 0.25) ? { color: dark ? '#bcc2ff' : '#000b60' } : {}"
                          x-text="finished ? '¡Tiempo!' : display">
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-semibold uppercase tracking-widest"
                          x-show="!finished"
                          x-text="running ? 'en curso' : 'listo'">
                    </span>
                </div>
            </div>

            {{-- Controles --}}
            <div class="flex items-center gap-4">

                {{-- Reset --}}
                <button @click="reset()"
                        class="w-12 h-12 rounded-full border-2 border-gray-200 dark:border-[#2a3d4a] flex items-center justify-center text-gray-400 hover:border-gray-400 dark:hover:border-[#bcc2ff] hover:text-gray-600 dark:hover:text-[#bcc2ff] transition">
                    <span class="material-symbols-outlined" style="font-size:22px">replay</span>
                </button>

                {{-- Play / Pause --}}
                <button @click="running ? pause() : start()"
                        class="w-20 h-20 rounded-full flex items-center justify-center shadow-lg text-white transition"
                        :class="finished
                            ? 'bg-red-500 hover:bg-red-600'
                            : running
                                ? 'bg-orange-400 hover:bg-orange-500'
                                : 'bg-[#000b60] hover:opacity-90'">
                    <span class="material-symbols-outlined" style="font-size:32px"
                          x-text="running ? 'pause' : 'play_arrow'">
                    </span>
                </button>

                {{-- Fullscreen --}}
                <button @click="toggleFullscreen()"
                        class="w-12 h-12 rounded-full border-2 border-gray-200 dark:border-[#2a3d4a] flex items-center justify-center text-gray-400 hover:border-gray-400 dark:hover:border-[#bcc2ff] hover:text-gray-600 dark:hover:text-[#bcc2ff] transition">
                    <span class="material-symbols-outlined" style="font-size:22px"
                          x-text="isFullscreen ? 'fullscreen_exit' : 'fullscreen'">
                    </span>
                </button>

            </div>

            <p class="text-xs text-gray-300 dark:text-gray-600 mt-5">Barra espaciadora para iniciar / pausar</p>

        </div>

        {{-- Panel lateral: configuración --}}
        <div class="flex flex-col gap-4">

            {{-- Presets --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">Tiempos rápidos</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach([1, 2, 3, 5, 10, 15, 20, 25, 30] as $min)
                        <button @click="setTime({{ $min }})"
                                :class="total === {{ $min * 60 }} && !running && !finished
                                    ? 'bg-[#000b60] text-white'
                                    : 'bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#162a35]'"
                                class="py-2.5 rounded-xl text-sm font-bold transition">
                            {{ $min }}<span class="font-normal text-xs opacity-70"> min</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tiempo personalizado --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">Tiempo personalizado</p>
                <div class="flex gap-2 mb-3">
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 dark:text-gray-500 mb-1 block">Min</label>
                        <input x-model.number="customMin"
                               type="number" min="0" max="99"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-center text-lg font-black focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                    </div>
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 dark:text-gray-500 mb-1 block">Seg</label>
                        <input x-model.number="customSec"
                               type="number" min="0" max="59"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-center text-lg font-black focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                    </div>
                </div>
                <button @click="setCustomTime()"
                        :disabled="customMin === 0 && customSec === 0"
                        class="w-full bg-[#000b60] text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition disabled:opacity-40">
                    Aplicar
                </button>
            </div>

            {{-- Etiqueta --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-3">Etiqueta (opcional)</p>
                <input x-model="label"
                       type="text"
                       maxlength="40"
                       placeholder="Ej. Examen parcial, Debate..."
                       class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
            </div>

            {{-- Sonido --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-[#000b60] dark:text-[#bcc2ff]">Sonido al finalizar</p>
                    <p class="text-xs text-gray-400">Alerta sonora al llegar a cero</p>
                </div>
                <button @click="soundOn = !soundOn"
                        :class="soundOn ? 'bg-[#000b60]' : 'bg-gray-200 dark:bg-[#2a3d4a]'"
                        class="relative w-12 h-6 rounded-full transition-colors duration-200 flex-shrink-0">
                    <span :class="soundOn ? 'translate-x-6' : 'translate-x-1'"
                          class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block">
                    </span>
                </button>
            </div>

        </div>

    </div>

    {{-- Overlay fullscreen --}}
    <div x-show="isFullscreen"
         x-transition:enter="transition duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-[#000b60] z-50 flex flex-col items-center justify-center"
         style="display:none;">

        <p class="text-white/50 text-sm font-bold uppercase tracking-widest mb-6"
           x-text="label"></p>

        <span class="font-black tabular-nums leading-none"
              :class="{
                  'text-[12rem] text-white':       !finished && progress > 0.25,
                  'text-[12rem] text-orange-400':  !finished && progress <= 0.25 && progress > 0.1,
                  'text-[10rem] text-red-400 animate-pulse': !finished && progress <= 0.1,
                  'text-8xl text-red-400 animate-pulse': finished
              }"
              x-text="finished ? '¡Tiempo!' : display">
        </span>

        <div class="flex items-center gap-6 mt-12">
            <button @click="reset()"
                    class="w-16 h-16 rounded-full border-2 border-white/30 flex items-center justify-center text-white/60 hover:text-white hover:border-white transition">
                <span class="material-symbols-outlined" style="font-size:28px">replay</span>
            </button>
            <button @click="running ? pause() : start()"
                    class="w-24 h-24 rounded-full flex items-center justify-center text-white shadow-2xl transition"
                    :class="running ? 'bg-orange-500' : 'bg-white/20 hover:bg-white/30'">
                <span class="material-symbols-outlined" style="font-size:40px"
                      x-text="running ? 'pause' : 'play_arrow'">
                </span>
            </button>
            <button @click="toggleFullscreen()"
                    class="w-16 h-16 rounded-full border-2 border-white/30 flex items-center justify-center text-white/60 hover:text-white hover:border-white transition">
                <span class="material-symbols-outlined" style="font-size:28px">fullscreen_exit</span>
            </button>
        </div>

    </div>

</div>

@push('scripts')
<script>
function temporizador() {
    return {
        total:        300,
        remaining:    300,
        running:      false,
        finished:     false,
        interval:     null,
        label:        '',
        customMin:    5,
        customSec:    0,
        soundOn:      true,
        isFullscreen: false,
        dark:         window.matchMedia('(prefers-color-scheme: dark)').matches,

        circumference: 2 * Math.PI * 120,

        init() {},

        get minutes()   { return Math.floor(this.remaining / 60); },
        get seconds()   { return this.remaining % 60; },
        get display()   {
            return String(this.minutes).padStart(2, '0') + ':' + String(this.seconds).padStart(2, '0');
        },
        get progress()  { return this.total > 0 ? this.remaining / this.total : 0; },
        get dashOffset(){ return this.circumference * (1 - this.progress); },
        get ringColor() {
            if (this.finished)          return '#ef4444';
            if (this.progress <= 0.1)   return '#ef4444';
            if (this.progress <= 0.25)  return '#f97316';
            return '#000b60';
        },

        setTime(minutes) {
            this.stop();
            this.total     = minutes * 60;
            this.remaining = this.total;
            this.finished  = false;
            document.title = 'ClassAssist Pro';
        },

        setCustomTime() {
            const secs = (this.customMin * 60) + this.customSec;
            if (secs <= 0) return;
            this.stop();
            this.total     = secs;
            this.remaining = secs;
            this.finished  = false;
            document.title = 'ClassAssist Pro';
        },

        start() {
            if (this.finished) { this.reset(); return; }
            if (this.remaining <= 0) return;
            this.running  = true;
            this.finished = false;
            this.interval = setInterval(() => {
                if (this.remaining > 0) {
                    this.remaining--;
                    document.title = this.display + ' · Temporizador';
                } else {
                    this.stop();
                    this.finished  = true;
                    document.title = '¡Tiempo! · Temporizador';
                    if (this.soundOn) this.playSound();
                }
            }, 1000);
        },

        pause() {
            this.running = false;
            clearInterval(this.interval);
            this.interval = null;
        },

        stop() {
            this.running = false;
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        reset() {
            this.stop();
            this.remaining = this.total;
            this.finished  = false;
            document.title = 'ClassAssist Pro';
        },

        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
        },

        playSound() {
            try {
                const ctx  = new (window.AudioContext || window.webkitAudioContext)();
                const beep = (delay, freq, dur) => {
                    const osc  = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type            = 'sine';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.6, ctx.currentTime + delay);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + dur);
                    osc.start(ctx.currentTime + delay);
                    osc.stop(ctx.currentTime + delay + dur + 0.05);
                };
                beep(0.0, 880, 0.25);
                beep(0.3, 880, 0.25);
                beep(0.6, 1100, 0.5);
            } catch (e) {}
        },
    };
}
</script>
@endpush
</x-app-layout>
