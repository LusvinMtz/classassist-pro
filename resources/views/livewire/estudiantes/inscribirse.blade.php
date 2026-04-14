<div class="w-full max-w-sm mx-auto">

    {{-- Logo / Branding --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-12 h-12 rounded-xl bg-[#000b60] flex items-center justify-center text-white font-black text-2xl">C</div>
        <div>
            <h2 class="font-black text-xl text-[#000b60] dark:text-[#bcc2ff]">ClassAssist Pro</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Inscripción a Clase</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg p-6">

        @if($claseNombre)
            <div class="text-center mb-6">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:36px">school</span>
                <h1 class="text-xl font-extrabold text-[#000b60] dark:text-[#bcc2ff] mt-1">{{ $claseNombre }}</h1>
                <p class="text-sm text-gray-400 dark:text-gray-500">Completa el formulario para inscribirte</p>
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

            <div class="space-y-4">

                {{-- Carné --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">
                        Carné <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="carnet"
                           type="text"
                           placeholder="Ej. 8590-21-16653"
                           autocomplete="off"
                           @class([
                               'w-full border-2 rounded-xl px-4 py-3 font-mono focus:outline-none tracking-widest text-center text-lg bg-white dark:bg-[#162a35] dark:text-[#dff4ff] placeholder-gray-400 dark:placeholder-gray-600',
                               'border-gray-200 dark:border-[#2a3d4a] focus:border-[#000b60] dark:focus:border-[#bcc2ff]' => !$errors->has('carnet'),
                               'border-red-400 dark:border-red-500'                                                          => $errors->has('carnet'),
                           ])>
                    @error('carnet')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Formato: 0000-00-0000 (ej. 8590-21-16653)</p>
                </div>

                {{-- Nombre --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="nombre"
                           type="text"
                           placeholder="Tu nombre completo"
                           @class([
                               'w-full border-2 rounded-xl px-4 py-3 focus:outline-none bg-white dark:bg-[#162a35] dark:text-[#dff4ff] placeholder-gray-400 dark:placeholder-gray-600',
                               'border-gray-200 dark:border-[#2a3d4a] focus:border-[#000b60] dark:focus:border-[#bcc2ff]' => !$errors->has('nombre'),
                               'border-red-400 dark:border-red-500'                                                          => $errors->has('nombre'),
                           ])>
                    @error('nombre')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo --}}
                <div>
                    <label class="block text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">
                        Correo institucional <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="correo"
                           type="email"
                           placeholder="tu.correo@miumg.edu.gt"
                           @class([
                               'w-full border-2 rounded-xl px-4 py-3 focus:outline-none bg-white dark:bg-[#162a35] dark:text-[#dff4ff] placeholder-gray-400 dark:placeholder-gray-600',
                               'border-gray-200 dark:border-[#2a3d4a] focus:border-[#000b60] dark:focus:border-[#bcc2ff]' => !$errors->has('correo'),
                               'border-red-400 dark:border-red-500'                                                          => $errors->has('correo'),
                           ])>
                    @error('correo')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Debe terminar en @miumg.edu.gt</p>
                </div>

                {{-- Mensaje de error/alerta --}}
                @if($mensaje)
                    <div @class([
                        'rounded-lg px-4 py-3 text-sm text-center',
                        'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400'    => $tipo === 'error',
                        'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' => $tipo === 'warning',
                        'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' => $tipo === 'success',
                    ])>
                        {{ $mensaje }}
                    </div>
                @endif

                {{-- Botón inscribirse --}}
                <button wire:click="inscribirse"
                        wire:loading.attr="disabled"
                        class="w-full bg-[#000b60] dark:bg-[#bcc2ff] text-white dark:text-[#000b60] py-3 rounded-xl font-bold text-base hover:opacity-90 transition disabled:opacity-60 flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="inscribirse" class="flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">how_to_reg</span>
                        Inscribirme
                    </span>
                    <span wire:loading wire:target="inscribirse" style="display:none">Procesando...</span>
                </button>

            </div>

        @endif

    </div>

    <p class="text-center text-xs text-gray-300 dark:text-gray-600 mt-6">ClassAssist Pro &copy; {{ date('Y') }}</p>

</div>
