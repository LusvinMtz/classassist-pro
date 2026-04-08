<div
    x-data="ruletaApp()"
    x-init="init()"
    @iniciar-ruleta.window="iniciarAnimacion($event.detail)"
>

    {{-- Encabezado --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Ruleta de Participación</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Selección aleatoria entre estudiantes presentes</p>
        </div>
        @if(!$esCatedratico)
        <div class="flex items-center gap-3">
            <label class="text-sm font-semibold text-[#000b60] dark:text-[#bcc2ff]">Clase:</label>
            <select wire:model.live="claseId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[220px]">
                <option value="">— Selecciona una clase —</option>
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    @if($sinSesionActiva)

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">casino</span>
            <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No tienes una sesión activa</p>
            <p class="text-sm mt-1">Ve a Sesiones, crea la sesión de hoy y luego regresa aquí.</p>
            <a href="{{ route('sesiones.index') }}"
               class="mt-6 bg-[#000b60] text-white px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px">calendar_add_on</span>
                Ir a Sesiones
            </a>
        </div>

    @elseif(!$esCatedratico && !$claseId)

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">casino</span>
            <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">Selecciona una clase para comenzar</p>
        </div>

    @elseif(!$sesion)

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">event_busy</span>
            <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay sesión activa para hoy</p>
            <p class="text-sm mt-1">Inicia una sesión desde el módulo de Asistencia primero</p>
        </div>

    @elseif($presentes->isEmpty())

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">person_off</span>
            <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay estudiantes con asistencia registrada</p>
            <p class="text-sm mt-1">Los estudiantes deben registrar asistencia para participar en la ruleta</p>
        </div>

    @else

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Panel principal: Ruleta --}}
            <div class="lg:col-span-2 flex flex-col items-center">

                {{-- Display de la ruleta --}}
                <div class="w-full bg-[#000b60] rounded-2xl shadow-xl flex flex-col items-center justify-center py-12 px-6 mb-6 relative overflow-hidden"
                     style="min-height: 280px;">

                    {{-- Decoración fondo --}}
                    <div class="absolute inset-0 opacity-5"
                         style="background: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>

                    {{-- Estado inicial --}}
                    <div x-show="!girando && !ganadorMostrado" class="text-center z-10">
                        <span class="material-symbols-outlined text-white opacity-30" style="font-size:64px">casino</span>
                        <p class="text-white opacity-40 mt-2 text-lg">Presiona Girar para comenzar</p>
                    </div>

                    {{-- Nombre girando --}}
                    <div x-show="girando || ganadorMostrado"
                         class="text-center z-10 w-full px-4">

                        <p class="text-white opacity-50 text-xs uppercase tracking-widest mb-3"
                           x-text="girando ? 'Seleccionando...' : '¡Seleccionado!'"></p>

                        <p class="font-black leading-tight text-center transition-all duration-100"
                           :class="{
                               'text-white text-4xl md:text-5xl': girando,
                               'text-yellow-300 text-5xl md:text-6xl scale-110 drop-shadow-lg': ganadorMostrado && !girando
                           }"
                           style="text-shadow: 0 0 30px rgba(253,224,71,0.5);"
                           x-text="nombreActual">
                        </p>

                        <div x-show="ganadorMostrado && !girando"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mt-4 flex items-center justify-center gap-2">
                            <span class="text-yellow-300 text-2xl">★</span>
                            <span class="text-white opacity-60 text-sm">Estudiante seleccionado</span>
                            <span class="text-yellow-300 text-2xl">★</span>
                        </div>

                    </div>
                </div>

                {{-- Botón girar --}}
                <button wire:click="girar"
                        :disabled="girando"
                        class="w-full max-w-xs bg-yellow-400 hover:bg-yellow-300 text-[#000b60] font-black text-xl py-4 rounded-2xl shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                    <span class="material-symbols-outlined text-2xl"
                          :class="girando ? 'animate-spin' : ''">casino</span>
                    <span x-show="!girando">Girar</span>
                    <span x-show="girando">Seleccionando...</span>
                </button>

                <p class="text-xs text-gray-400 mt-3 text-center">
                    {{ $presentes->count() }} estudiante(s) en la ruleta
                </p>

                {{-- Historial de participaciones --}}
                @if($historial->isNotEmpty())
                    <div class="w-full mt-6 bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                        <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                            <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                                <span class="material-symbols-outlined" style="font-size:18px">history</span>
                                Participaciones de esta sesión
                            </span>
                            <span class="bg-[#000b60] text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                                {{ $historial->count() }}
                            </span>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-[#162a35]">
                                <tr>
                                    <th class="text-left px-4 py-2 font-semibold text-gray-500 dark:text-gray-400 text-xs">Estudiante</th>
                                    <th class="text-center px-4 py-2 font-semibold text-gray-500 dark:text-gray-400 text-xs">Nota</th>
                                    <th class="text-left px-4 py-2 font-semibold text-gray-500 dark:text-gray-400 text-xs">Comentario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                                @foreach($historial as $p)
                                    <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                                        <td class="px-4 py-2.5 font-semibold">{{ $p->estudiante->nombre }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if($p->calificacion !== null)
                                                <span class="bg-blue-100 text-blue-700 font-bold text-xs px-2 py-0.5 rounded-full">
                                                    {{ number_format($p->calificacion, 1) }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 text-xs">{{ $p->comentario ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>

            {{-- Panel lateral: Lista de presentes --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden h-fit">
                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined" style="font-size:18px">how_to_reg</span>
                        Presentes hoy
                    </span>
                    <span class="bg-green-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                        {{ $presentes->count() }}
                    </span>
                </div>
                <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c] max-h-96 overflow-y-auto">
                    @foreach($presentes as $i => $e)
                        <li class="px-4 py-2.5 flex items-center gap-3 hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] text-sm"
                            :class="nombreActual === '{{ $e->nombre }}' && (girando || ganadorMostrado)
                                ? 'bg-yellow-50 dark:bg-yellow-900/20 font-bold' : ''">
                            <span class="text-gray-300 text-xs w-5 text-right">{{ $i + 1 }}</span>
                            <span>{{ $e->nombre }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

    @endif


    {{-- MODAL: Registrar participación --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

                {{-- Header dorado --}}
                <div class="bg-[#000b60] px-6 py-5 text-center">
                    <p class="text-yellow-300 text-xs font-bold uppercase tracking-widest mb-1">Estudiante seleccionado</p>
                    <h2 class="text-white text-2xl font-black">{{ $ganadorNombre }}</h2>
                </div>

                <div class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5">
                        Registra la participación del estudiante (opcional)
                    </p>

                    <div class="space-y-4">

                        <div>
                            <label class="block text-sm font-semibold mb-1">
                                Calificación <span class="text-gray-400 font-normal">(0 – 10)</span>
                            </label>
                            <input wire:model="calificacion"
                                   type="number"
                                   min="0" max="10" step="0.5"
                                   placeholder="Ej. 8.5"
                                   class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('calificacion') border-red-400 @enderror">
                            @error('calificacion')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1">Comentario</label>
                            <textarea wire:model="comentario"
                                      rows="3"
                                      placeholder="Observaciones sobre la participación..."
                                      class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] resize-none @error('comentario') border-red-400 @enderror"></textarea>
                            @error('comentario')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="omitir"
                                class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-500 dark:text-gray-400 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                            Omitir
                        </button>
                        <button wire:click="guardarParticipacion"
                                class="flex-1 bg-[#000b60] text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition text-sm">
                            Guardar participación
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
function ruletaApp() {
    return {
        nombreActual: '',
        girando: false,
        ganadorMostrado: false,

        init() {},

        iniciarAnimacion({ nombres, ganadorNombre }) {
            if (this.girando) return;

            this.girando       = true;
            this.ganadorMostrado = false;
            this.nombreActual  = nombres[0] ?? '';

            const totalMs = 4000;
            let elapsed   = 0;
            let delay     = 60;
            let idx       = 0;

            const paso = () => {
                idx++;
                this.nombreActual = nombres[idx % nombres.length];
                elapsed += delay;

                // Desaceleración progresiva en el último 40%
                if (elapsed > totalMs * 0.6) {
                    delay = Math.min(Math.floor(delay * 1.18), 450);
                }

                if (elapsed < totalMs) {
                    setTimeout(paso, delay);
                } else {
                    // Mostrar ganador
                    this.nombreActual  = ganadorNombre;
                    this.girando       = false;
                    this.ganadorMostrado = true;

                    // Avisar a Livewire después de la animación de reveal
                    setTimeout(() => {
                        this.$wire.seleccionarGanador();
                    }, 900);
                }
            };

            setTimeout(paso, delay);
        }
    }
}
</script>
@endpush
