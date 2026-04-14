<div class="w-full max-w-sm mx-auto"
     x-data="registroApp()"
     x-init="init()">

    {{-- Logo / Branding --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-12 h-12 rounded-xl bg-[#000b60] flex items-center justify-center text-white font-black text-2xl">C</div>
        <div>
            <h2 class="font-black text-xl text-[#000b60] dark:text-[#bcc2ff]">ClassAssist Pro</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Registro de Asistencia</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg p-6">

        @if($claseNombre)
            <div class="text-center mb-6">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:36px">school</span>
                <h1 class="text-xl font-extrabold text-[#000b60] dark:text-[#bcc2ff] mt-1">{{ $claseNombre }}</h1>
                <p class="text-sm text-gray-400 dark:text-gray-500">{{ $fecha }}</p>
            </div>
        @endif

        @if($invalido)

            <div class="text-center py-4">
                <span class="material-symbols-outlined text-{{ $tipo === 'warning' ? 'amber' : 'red' }}-400" style="font-size:48px">
                    {{ $tipo === 'warning' ? 'schedule' : 'error' }}
                </span>
                <p class="mt-3 font-semibold text-gray-700 dark:text-gray-300">{{ $mensaje }}</p>
            </div>

        @elseif($registrado)

            <div class="text-center py-6">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-green-500" style="font-size:36px">check_circle</span>
                </div>
                <p class="font-bold text-green-700 dark:text-green-400 text-lg">{{ $mensaje }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Puedes cerrar esta página.</p>
            </div>

        @else

            <div class="space-y-5">

                {{-- ── Ubicación GPS ─────────────────────────────────────────── --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] mb-2">
                        Ubicación GPS
                        <span class="text-red-500">*</span>
                    </label>

                    {{-- Obteniendo... --}}
                    <div x-show="gpsEstado === 'obteniendo'"
                         class="flex items-center gap-3 rounded-xl border-2 border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-900/20 px-4 py-3">
                        <svg class="h-5 w-5 animate-spin text-blue-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Obteniendo tu ubicación…</p>
                    </div>

                    {{-- Éxito --}}
                    <div x-show="gpsEstado === 'ok'"
                         class="flex items-center gap-3 rounded-xl border-2 border-green-300 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3">
                        <span class="material-symbols-outlined text-green-500 shrink-0" style="font-size:22px">location_on</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-green-700 dark:text-green-400">Ubicación obtenida</p>
                            <p class="text-xs text-green-600 dark:text-green-500 font-mono truncate"
                               x-text="latitudStr + ', ' + longitudStr"></p>
                        </div>
                        <span class="material-symbols-outlined text-green-400 shrink-0" style="font-size:20px">check_circle</span>
                    </div>

                    {{-- Error --}}
                    <div x-show="gpsEstado === 'error'"
                         class="rounded-xl border-2 border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-500 shrink-0" style="font-size:22px">location_off</span>
                            <p class="text-sm font-bold text-red-700 dark:text-red-400">No se pudo obtener la ubicación</p>
                        </div>
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="gpsError"></p>
                        <p class="text-xs text-red-500 dark:text-red-400 font-semibold">
                            Debes permitir el acceso a tu ubicación para registrar asistencia.
                        </p>
                        <button @click="pedirUbicacion()"
                                class="w-full mt-1 border border-red-300 dark:border-red-700 bg-white dark:bg-[#162a35] text-red-600 dark:text-red-400 text-xs font-bold py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:15px">refresh</span>
                            Intentar de nuevo
                        </button>
                    </div>

                    @error('latitud')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Carné ─────────────────────────────────────────────────── --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] mb-2">
                        Ingresa tu Carné
                        <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="carnet"
                           x-on:keydown.enter="enviarRegistro()"
                           type="text"
                           placeholder="Ej. 8590-21-16653"
                           autofocus
                           @class([
                               'w-full border-2 rounded-xl px-4 py-3 text-lg text-center font-mono focus:outline-none tracking-widest bg-white dark:bg-[#162a35] dark:text-[#dff4ff] placeholder-gray-400 dark:placeholder-gray-600',
                               'border-gray-200 dark:border-[#2a3d4a] focus:border-[#000b60] dark:focus:border-[#bcc2ff]' => !$errors->has('carnet'),
                               'border-red-400 dark:border-red-500'                                                          => $errors->has('carnet'),
                           ])>
                    @error('carnet')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Selfie ────────────────────────────────────────────────── --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] mb-2">
                        Selfie de verificación
                        <span class="text-gray-400 dark:text-gray-500 font-normal">(opcional)</span>
                    </label>

                    <div x-show="selfieDataUrl" class="relative mb-3">
                        <img :src="selfieDataUrl"
                             class="w-full rounded-xl border-2 border-green-400 object-cover"
                             style="max-height: 200px;">
                        <button @click="descartarSelfie()"
                                class="absolute top-2 right-2 bg-red-500 text-white w-7 h-7 rounded-full flex items-center justify-center shadow">
                            <span class="material-symbols-outlined" style="font-size:16px">close</span>
                        </button>
                        <div class="absolute bottom-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:12px">check</span>
                            Selfie lista
                        </div>
                    </div>

                    <div x-show="camaraActiva && !selfieDataUrl" class="relative mb-3">
                        <video x-ref="video" autoplay playsinline muted
                               class="w-full rounded-xl border-2 border-[#000b60] dark:border-[#bcc2ff] object-cover"
                               style="max-height: 200px; transform: scaleX(-1);"></video>
                        <div class="absolute inset-0 flex items-end justify-center pb-3">
                            <button @click="tomarFoto()"
                                    class="bg-white dark:bg-[#1e333c] text-[#000b60] dark:text-[#bcc2ff] font-black px-5 py-2 rounded-full shadow-lg flex items-center gap-2 text-sm border-2 border-[#000b60] dark:border-[#bcc2ff]">
                                <span class="material-symbols-outlined" style="font-size:18px">camera</span>
                                Tomar foto
                            </button>
                        </div>
                    </div>

                    <canvas x-ref="canvas" class="hidden"></canvas>

                    <div x-show="!camaraActiva && !selfieDataUrl">
                        <button @click="activarCamara()"
                                class="w-full border-2 border-dashed border-gray-300 dark:border-[#2a3d4a] rounded-xl py-4 text-gray-400 dark:text-gray-500 hover:border-[#000b60] dark:hover:border-[#bcc2ff] hover:text-[#000b60] dark:hover:text-[#bcc2ff] transition flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:28px">add_a_photo</span>
                            <span class="text-sm font-semibold">Tomar selfie</span>
                        </button>
                    </div>

                    <p x-show="errorCamara" x-text="errorCamara"
                       class="text-xs text-amber-600 dark:text-amber-400 mt-1 text-center"></p>
                </div>

                {{-- Mensaje de error/alerta --}}
                @if($mensaje)
                    <div @class([
                        'rounded-lg px-4 py-3 text-sm text-center',
                        'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400'         => $tipo === 'error',
                        'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' => $tipo === 'warning',
                        'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' => $tipo === 'success',
                    ])>
                        {{ $mensaje }}
                    </div>
                @endif

                {{-- Botón registrar --}}
                <button @click="enviarRegistro()"
                        :disabled="gpsEstado !== 'ok'"
                        wire:loading.attr="disabled"
                        :class="gpsEstado !== 'ok'
                            ? 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                            : 'bg-[#000b60] dark:bg-[#bcc2ff] text-white dark:text-[#000b60] hover:opacity-90'"
                        class="w-full py-3 rounded-xl font-bold text-base transition disabled:opacity-60 flex items-center justify-center gap-2">
                    <span wire:loading.remove>
                        <span x-show="gpsEstado !== 'ok'" class="flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size:18px">location_off</span>
                            Esperando ubicación…
                        </span>
                        <span x-show="gpsEstado === 'ok'">Registrar Asistencia</span>
                    </span>
                    <span wire:loading style="display:none">Registrando...</span>
                </button>

            </div>

        @endif

    </div>

    <p class="text-center text-xs text-gray-300 dark:text-gray-600 mt-6">ClassAssist Pro &copy; {{ date('Y') }}</p>

</div>

<script>
function registroApp() {
    return {
        // GPS
        gpsEstado:   'obteniendo', // obteniendo | ok | error
        gpsError:    '',
        latitudStr:  '',
        longitudStr: '',

        // Cámara
        camaraActiva: false,
        selfieDataUrl: '',
        errorCamara:  '',
        _stream:      null,

        init() {
            this.pedirUbicacion();
        },

        pedirUbicacion() {
            this.gpsEstado = 'obteniendo';
            this.gpsError  = '';

            if (!navigator.geolocation) {
                this.gpsEstado = 'error';
                this.gpsError  = 'Tu navegador no soporta geolocalización.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude.toFixed(7);
                    const lng = pos.coords.longitude.toFixed(7);
                    this.latitudStr  = lat;
                    this.longitudStr = lng;
                    this.$wire.set('latitud',  lat);
                    this.$wire.set('longitud', lng);
                    this.gpsEstado = 'ok';
                },
                (err) => {
                    this.gpsEstado = 'error';
                    this.gpsError  = {
                        1: 'Permiso denegado. Ve a la configuración de tu navegador y permite el acceso a la ubicación.',
                        2: 'No se pudo determinar la ubicación. Asegúrate de tener GPS activo.',
                        3: 'Tiempo de espera agotado. Verifica tu señal GPS e intenta de nuevo.',
                    }[err.code] || 'Error desconocido al obtener la ubicación.';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        async activarCamara() {
            this.errorCamara = '';
            try {
                this._stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                this.camaraActiva = true;
                await this.$nextTick();
                this.$refs.video.srcObject = this._stream;
            } catch (e) {
                this.errorCamara = 'No se pudo acceder a la cámara. Puedes continuar sin selfie.';
            }
        },

        tomarFoto() {
            const video  = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width  = video.videoWidth  || 320;
            canvas.height = video.videoHeight || 240;
            const ctx = canvas.getContext('2d');
            ctx.save();
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);
            ctx.restore();
            this.selfieDataUrl = canvas.toDataURL('image/jpeg', 0.8);
            this.detenerCamara();
        },

        descartarSelfie() {
            this.selfieDataUrl = '';
            this.errorCamara   = '';
        },

        detenerCamara() {
            if (this._stream) {
                this._stream.getTracks().forEach(t => t.stop());
                this._stream = null;
            }
            this.camaraActiva = false;
        },

        async enviarRegistro() {
            if (this.gpsEstado !== 'ok') return;
            if (this.selfieDataUrl) {
                await this.$wire.set('selfieData', this.selfieDataUrl);
            }
            this.$wire.registrar();
        },
    };
}
</script>
