<div class="w-full max-w-sm mx-auto"
     x-data="selfieApp()"
     x-init="init()">

    {{-- Logo / Branding --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-12 h-12 rounded-xl bg-[#000b60] flex items-center justify-center text-white font-black text-2xl">
            C
        </div>
        <div>
            <h2 class="font-black text-xl text-[#000b60]">ClassAssist Pro</h2>
            <p class="text-xs text-gray-500">Registro de Asistencia</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6">

        @if($claseNombre)
            <div class="text-center mb-6">
                <span class="material-symbols-outlined text-[#000b60]" style="font-size:36px">school</span>
                <h1 class="text-xl font-extrabold text-[#000b60] mt-1">{{ $claseNombre }}</h1>
                <p class="text-sm text-gray-400">{{ $fecha }}</p>
            </div>
        @endif

        @if($invalido)

            <div class="text-center py-4">
                <span class="material-symbols-outlined text-{{ $tipo === 'warning' ? 'amber' : 'red' }}-400" style="font-size:48px">
                    {{ $tipo === 'warning' ? 'schedule' : 'error' }}
                </span>
                <p class="mt-3 font-semibold text-gray-700">{{ $mensaje }}</p>
            </div>

        @elseif($registrado)

            <div class="text-center py-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-green-500" style="font-size:36px">check_circle</span>
                </div>
                <p class="font-bold text-green-700 text-lg">{{ $mensaje }}</p>
                <p class="text-sm text-gray-400 mt-2">Puedes cerrar esta página.</p>
            </div>

        @else

            <div class="space-y-5">

                {{-- Carné --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] mb-2">Ingresa tu Carné</label>
                    <input wire:model="carnet"
                           x-on:keydown.enter="$wire.registrar()"
                           type="text"
                           placeholder="Ej. 202300001"
                           autofocus
                           @class([
                               'w-full border-2 rounded-xl px-4 py-3 text-lg text-center font-mono focus:outline-none tracking-widest',
                               'border-gray-200 focus:border-[#000b60]' => !$errors->has('carnet'),
                               'border-red-400'                          => $errors->has('carnet'),
                           ])>
                    @error('carnet')
                        <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Selfie --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] mb-2">
                        Selfie de verificación
                        <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>

                    {{-- Vista previa de selfie tomada --}}
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

                    {{-- Cámara en vivo --}}
                    <div x-show="camaraActiva && !selfieDataUrl" class="relative mb-3">
                        <video x-ref="video"
                               autoplay playsinline muted
                               class="w-full rounded-xl border-2 border-[#000b60] object-cover"
                               style="max-height: 200px; transform: scaleX(-1);"></video>
                        <div class="absolute inset-0 flex items-end justify-center pb-3">
                            <button @click="tomarFoto()"
                                    class="bg-white text-[#000b60] font-black px-5 py-2 rounded-full shadow-lg flex items-center gap-2 text-sm border-2 border-[#000b60]">
                                <span class="material-symbols-outlined" style="font-size:18px">camera</span>
                                Tomar foto
                            </button>
                        </div>
                    </div>

                    {{-- Canvas oculto para captura --}}
                    <canvas x-ref="canvas" class="hidden"></canvas>

                    {{-- Botón activar cámara --}}
                    <div x-show="!camaraActiva && !selfieDataUrl">
                        <button @click="activarCamara()"
                                class="w-full border-2 border-dashed border-gray-300 rounded-xl py-4 text-gray-400 hover:border-[#000b60] hover:text-[#000b60] transition flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:28px">add_a_photo</span>
                            <span class="text-sm font-semibold">Tomar selfie</span>
                        </button>
                    </div>

                    {{-- Error de cámara --}}
                    <p x-show="errorCamara" x-text="errorCamara"
                       class="text-xs text-amber-600 mt-1 text-center"></p>
                </div>

                {{-- Mensaje de error/alerta --}}
                @if($mensaje)
                    <div class="rounded-lg px-4 py-3 text-sm text-center
                        {{ $tipo === 'error'   ? 'bg-red-50 text-red-600'
                         : ($tipo === 'warning' ? 'bg-amber-50 text-amber-700'
                         : 'bg-green-50 text-green-700') }}">
                        {{ $mensaje }}
                    </div>
                @endif

                {{-- Botón registrar --}}
                <button @click="enviarRegistro()"
                        wire:loading.attr="disabled"
                        class="w-full bg-[#000b60] text-white py-3 rounded-xl font-bold text-base hover:opacity-90 transition disabled:opacity-60">
                    <span wire:loading.remove>Registrar Asistencia</span>
                    <span wire:loading>Registrando...</span>
                </button>

            </div>

        @endif

    </div>

    <p class="text-center text-xs text-gray-300 mt-6">ClassAssist Pro &copy; {{ date('Y') }}</p>

</div>

<script>
function selfieApp() {
    return {
        camaraActiva: false,
        selfieDataUrl: '',
        errorCamara:  '',
        _stream:      null,

        init() {},

        async activarCamara() {
            this.errorCamara = '';
            try {
                this._stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });
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
            if (this.selfieDataUrl) {
                await this.$wire.set('selfieData', this.selfieDataUrl);
            }
            this.$wire.registrar();
        },
    };
}
</script>
