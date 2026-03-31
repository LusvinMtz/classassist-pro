<x-app-layout>
<div x-data="medidorRuido()" x-init="init()">

    <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Medidor de Ruido</h1>
        <p class="text-sm text-gray-500">Monitoreo del nivel de sonido en tiempo real usando el micrófono</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- Panel principal --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-8 flex flex-col items-center">

            {{-- Sin permiso / inactivo --}}
            <div x-show="!activo && !error" class="text-center py-8">
                <span class="material-symbols-outlined text-gray-300" style="font-size:80px">mic_off</span>
                <p class="mt-4 font-semibold text-gray-500 text-lg">Micrófono inactivo</p>
                <p class="text-sm text-gray-400 mb-6">Haz clic en "Activar" para comenzar a medir el ruido</p>
                <button @click="activar()"
                        class="bg-[#000b60] text-white px-8 py-3 rounded-xl font-bold text-base hover:opacity-90 transition flex items-center gap-2 mx-auto">
                    <span class="material-symbols-outlined">mic</span>
                    Activar micrófono
                </button>
            </div>

            {{-- Error de permiso --}}
            <div x-show="error" class="text-center py-8">
                <span class="material-symbols-outlined text-red-400" style="font-size:64px">mic_off</span>
                <p class="mt-3 font-semibold text-red-500" x-text="errorMsg"></p>
                <button @click="activar()"
                        class="mt-4 border border-gray-200 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    Reintentar
                </button>
            </div>

            {{-- Medidor activo --}}
            <div x-show="activo" class="w-full flex flex-col items-center gap-8">

                {{-- Nivel dB y etiqueta --}}
                <div class="text-center">
                    <p class="text-xs font-bold uppercase tracking-widest mb-1"
                       :class="{
                           'text-green-600':  nivel === 'silencio' || nivel === 'bajo',
                           'text-yellow-600': nivel === 'moderado',
                           'text-orange-500': nivel === 'alto',
                           'text-red-600':    nivel === 'muy_alto'
                       }"
                       x-text="etiqueta">
                    </p>
                    <p class="font-black tabular-nums leading-none"
                       :class="{
                           'text-7xl text-green-500':  nivel === 'silencio' || nivel === 'bajo',
                           'text-7xl text-yellow-500': nivel === 'moderado',
                           'text-7xl text-orange-500': nivel === 'alto',
                           'text-7xl text-red-500':    nivel === 'muy_alto'
                       }"
                       x-text="db + ' dB'">
                    </p>
                </div>

                {{-- Barras de nivel --}}
                <div class="w-full max-w-lg">
                    <div class="flex items-end justify-center gap-1.5 h-32">
                        <template x-for="(bar, i) in bars" :key="i">
                            <div class="flex-1 rounded-t-md transition-all duration-75"
                                 :style="'height: ' + bar + '%; opacity: ' + (bar > 5 ? 1 : 0.15)"
                                 :class="{
                                     'bg-green-400':  bar <= 40,
                                     'bg-yellow-400': bar > 40 && bar <= 65,
                                     'bg-orange-400': bar > 65 && bar <= 85,
                                     'bg-red-500':    bar > 85
                                 }">
                            </div>
                        </template>
                    </div>
                    {{-- Escala --}}
                    <div class="flex justify-between text-xs text-gray-300 mt-1 px-0.5">
                        <span>0 dB</span>
                        <span>silencio</span>
                        <span>conversación</span>
                        <span>ruido</span>
                        <span>100 dB</span>
                    </div>
                </div>

                {{-- Barra de umbral --}}
                <div class="w-full max-w-lg">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-gray-500">Umbral de alerta</label>
                        <span class="text-xs font-bold text-[#000b60]" x-text="umbral + ' dB'"></span>
                    </div>
                    <input x-model.number="umbral" type="range" min="30" max="90" step="5"
                           class="w-full accent-[#000b60]">
                    <div x-show="superaUmbral"
                         class="mt-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3 text-red-600 font-semibold text-sm animate-pulse">
                        <span class="material-symbols-outlined">warning</span>
                        ¡Nivel de ruido supera el umbral establecido!
                    </div>
                </div>

                {{-- Botón detener --}}
                <button @click="detener()"
                        class="border-2 border-gray-200 text-gray-500 px-6 py-2.5 rounded-xl font-semibold text-sm hover:border-red-300 hover:text-red-500 transition flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">mic_off</span>
                    Detener
                </button>

            </div>

        </div>

        {{-- Panel lateral --}}
        <div class="flex flex-col gap-4">

            {{-- Niveles de referencia --}}
            <div class="bg-white rounded-2xl shadow p-5">
                <p class="text-xs font-bold text-[#000b60] uppercase tracking-widest mb-4">Niveles de referencia</p>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-green-400 flex-shrink-0"></span>
                        <span class="text-gray-600"><span class="font-bold">0–40 dB</span> — Silencio / muy tranquilo</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                        <span class="text-gray-600"><span class="font-bold">40–55 dB</span> — Clase en orden</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0"></span>
                        <span class="text-gray-600"><span class="font-bold">55–70 dB</span> — Conversación normal</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-orange-400 flex-shrink-0"></span>
                        <span class="text-gray-600"><span class="font-bold">70–85 dB</span> — Ruido elevado</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"></span>
                        <span class="text-gray-600"><span class="font-bold">85+ dB</span> — Muy ruidoso</span>
                    </li>
                </ul>
            </div>

            {{-- Estadísticas de sesión --}}
            <div class="bg-white rounded-2xl shadow p-5" x-show="activo">
                <p class="text-xs font-bold text-[#000b60] uppercase tracking-widest mb-4">Esta sesión</p>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Máximo</span>
                        <span class="font-black text-red-500" x-text="maxDb + ' dB'"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Mínimo</span>
                        <span class="font-black text-green-600" x-text="minDb + ' dB'"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Promedio</span>
                        <span class="font-black text-[#000b60]" x-text="avgDb + ' dB'"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Alertas</span>
                        <span class="font-black text-orange-500" x-text="alertas"></span>
                    </div>
                </div>
                <button @click="resetStats()"
                        class="mt-4 w-full text-xs text-gray-400 hover:text-gray-600 transition">
                    Reiniciar estadísticas
                </button>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
function medidorRuido() {
    return {
        activo:       false,
        error:        false,
        errorMsg:     '',
        db:           0,
        nivel:        'silencio',
        etiqueta:     'Silencio',
        umbral:       65,
        superaUmbral: false,
        bars:         Array(32).fill(0),
        maxDb:        0,
        minDb:        999,
        avgDb:        0,
        alertas:      0,
        _samples:     [],
        _stream:      null,
        _ctx:         null,
        _analyser:    null,
        _raf:         null,

        init() {},

        async activar() {
            this.error = false;
            try {
                this._stream   = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                this._ctx      = new (window.AudioContext || window.webkitAudioContext)();
                const source   = this._ctx.createMediaStreamSource(this._stream);
                this._analyser = this._ctx.createAnalyser();
                this._analyser.fftSize = 256;
                this._analyser.smoothingTimeConstant = 0.6;
                source.connect(this._analyser);
                this.activo    = true;
                this.resetStats();
                this.loop();
            } catch (e) {
                this.error    = true;
                this.errorMsg = 'No se pudo acceder al micrófono. Verifica los permisos del navegador.';
            }
        },

        loop() {
            const data = new Uint8Array(this._analyser.frequencyBinCount);
            const tick = () => {
                this._analyser.getByteFrequencyData(data);

                // RMS → dB
                const rms = Math.sqrt(data.reduce((s, v) => s + v * v, 0) / data.length);
                const raw = rms > 0 ? Math.round(20 * Math.log10(rms / 255) + 100) : 0;
                this.db   = Math.max(0, Math.min(100, raw));

                // Barras (32 bandas)
                const chunk = Math.floor(data.length / 32);
                this.bars = Array.from({ length: 32 }, (_, i) => {
                    const slice = data.slice(i * chunk, (i + 1) * chunk);
                    const avg   = slice.reduce((s, v) => s + v, 0) / slice.length;
                    return Math.min(100, Math.round(avg / 255 * 100));
                });

                // Nivel / etiqueta
                if      (this.db < 40)  { this.nivel = 'silencio';  this.etiqueta = 'Silencio'; }
                else if (this.db < 55)  { this.nivel = 'bajo';      this.etiqueta = 'Tranquilo'; }
                else if (this.db < 70)  { this.nivel = 'moderado';  this.etiqueta = 'Moderado'; }
                else if (this.db < 85)  { this.nivel = 'alto';      this.etiqueta = 'Ruidoso'; }
                else                    { this.nivel = 'muy_alto';  this.etiqueta = '¡Muy ruidoso!'; }

                // Umbral
                const prev = this.superaUmbral;
                this.superaUmbral = this.db >= this.umbral;
                if (this.superaUmbral && !prev) this.alertas++;

                // Stats
                this._samples.push(this.db);
                if (this._samples.length > 600) this._samples.shift();
                this.maxDb = Math.max(...this._samples);
                this.minDb = Math.min(...this._samples);
                this.avgDb = Math.round(this._samples.reduce((a, b) => a + b, 0) / this._samples.length);

                this._raf = requestAnimationFrame(tick);
            };
            this._raf = requestAnimationFrame(tick);
        },

        detener() {
            this.activo = false;
            if (this._raf) cancelAnimationFrame(this._raf);
            if (this._stream) this._stream.getTracks().forEach(t => t.stop());
            if (this._ctx) this._ctx.close();
            this.db   = 0;
            this.bars = Array(32).fill(0);
        },

        resetStats() {
            this._samples  = [];
            this.maxDb     = 0;
            this.minDb     = 999;
            this.avgDb     = 0;
            this.alertas   = 0;
        },
    };
}
</script>
@endpush
</x-app-layout>
