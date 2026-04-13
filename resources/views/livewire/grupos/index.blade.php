<div>

    {{-- Encabezado --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Grupos Aleatorios</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Formación automática de grupos con los estudiantes presentes</p>
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
            <span class="material-symbols-outlined" style="font-size:64px">groups</span>
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
            <span class="material-symbols-outlined" style="font-size:64px">groups</span>
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
            <p class="text-sm mt-1">Los estudiantes deben registrar asistencia para participar en grupos</p>
        </div>

    @else

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Panel de configuración --}}
            <div class="lg:col-span-1 flex flex-col gap-5">

                {{-- Info sesión --}}
                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-xl px-5 py-4">
                    <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-1">Sesión activa</p>
                    <p class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->fecha->translatedFormat('d/m/Y') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <span class="font-semibold text-green-600">{{ $presentes->count() }}</span> estudiantes presentes
                    </p>
                </div>

                {{-- Configuración --}}
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex flex-col gap-4">
                    <p class="font-bold text-[#000b60] dark:text-[#bcc2ff] text-sm uppercase tracking-wide">Configuración</p>

                    {{-- Modo --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Dividir por</label>
                        <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-[#2a3d4a]">
                            <button wire:click="$set('modo', 'grupos')"
                                    class="flex-1 py-2 text-sm font-semibold transition
                                           {{ $modo === 'grupos' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#1a2f3c]' }}">
                                N.° de grupos
                            </button>
                            <button wire:click="$set('modo', 'tamano')"
                                    class="flex-1 py-2 text-sm font-semibold transition border-l border-gray-200 dark:border-[#2a3d4a]
                                           {{ $modo === 'tamano' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#1a2f3c]' }}">
                                Tamaño
                            </button>
                        </div>
                    </div>

                    {{-- Cantidad --}}
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            @if($modo === 'grupos') Número de grupos @else Estudiantes por grupo @endif
                        </label>
                        <input wire:model="cantidad"
                               type="number" min="2" max="50"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('cantidad') border-red-400 @enderror">
                        @error('cantidad')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Estimación --}}
                        @php
                            $total = $presentes->count();
                            if ($modo === 'grupos') {
                                $numG = min(max((int)$cantidad, 1), $total);
                                $est  = $numG > 0 ? ceil($total / $numG) : 0;
                                $hint = "≈ {$est} estudiante(s) por grupo";
                            } else {
                                $tam  = max((int)$cantidad, 1);
                                $numG = (int)ceil($total / $tam);
                                $hint = "≈ {$numG} grupo(s)";
                            }
                        @endphp
                        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
                    </div>

                    {{-- Botón generar --}}
                    <button wire:click="generar"
                            class="w-full bg-[#000b60] text-white py-3 rounded-xl font-bold text-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">shuffle</span>
                        Generar grupos
                    </button>

                    <p class="text-xs text-gray-400 flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:13px">auto_awesome</span>
                        Minimiza repeticiones con historial anterior
                    </p>
                </div>

                {{-- Lista de presentes --}}
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                    <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                        <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size:18px">how_to_reg</span>
                            Presentes hoy
                        </span>
                        <span class="bg-green-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                            {{ $presentes->count() }}
                        </span>
                    </div>
                    <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c] max-h-56 overflow-y-auto">
                        @foreach($presentes as $i => $e)
                            <li class="px-4 py-2 flex items-center gap-3 text-sm">
                                <span class="text-gray-300 text-xs w-5 text-right">{{ $i + 1 }}</span>
                                <span>{{ $e->nombre }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            {{-- Panel principal: grupos --}}
            <div class="lg:col-span-2 flex flex-col gap-5">

                {{-- Preview: grupos generados (sin guardar) --}}
                @if($generado && !empty($preview))
                    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-100 dark:border-yellow-800/30 px-5 py-3 flex items-center justify-between">
                            <span class="font-bold text-yellow-700 dark:text-yellow-400 flex items-center gap-2 text-sm">
                                <span class="material-symbols-outlined" style="font-size:18px">pending</span>
                                Vista previa — sin guardar aún
                            </span>
                            <div class="flex gap-2">
                                <button wire:click="generar"
                                        class="text-xs border border-yellow-400 text-yellow-700 dark:text-yellow-400 px-3 py-1 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px">shuffle</span>
                                    Regenerar
                                </button>
                                <button wire:click="guardar"
                                        class="text-xs bg-[#000b60] text-white px-3 py-1 rounded-lg hover:opacity-90 transition font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px">save</span>
                                    Guardar grupos
                                </button>
                            </div>
                        </div>

                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($preview as $i => $grupo)
                                @php
                                    $colores = [
                                        'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
                                        'border-purple-400 bg-purple-50 dark:bg-purple-900/20',
                                        'border-green-400 bg-green-50 dark:bg-green-900/20',
                                        'border-orange-400 bg-orange-50 dark:bg-orange-900/20',
                                        'border-pink-400 bg-pink-50 dark:bg-pink-900/20',
                                        'border-teal-400 bg-teal-50 dark:bg-teal-900/20',
                                        'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
                                        'border-red-400 bg-red-50 dark:bg-red-900/20',
                                    ];
                                    $color = $colores[$i % count($colores)];
                                @endphp
                                <div class="border-2 {{ $color }} rounded-xl p-4">
                                    <p class="font-black text-[#000b60] dark:text-[#bcc2ff] text-sm mb-3 flex items-center gap-2">
                                        <span class="material-symbols-outlined" style="font-size:16px">group</span>
                                        {{ $grupo['nombre'] }}
                                        <span class="ml-auto bg-white/70 dark:bg-black/30 text-xs font-bold px-2 py-0.5 rounded-full text-gray-600 dark:text-gray-300">
                                            {{ count($grupo['miembros']) }}
                                        </span>
                                    </p>
                                    <ul class="space-y-1">
                                        @foreach($grupo['miembros'] as $m)
                                            <li class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                                                {{ $m['nombre'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endif

                {{-- Grupos guardados en DB --}}
                @if($guardados->isNotEmpty())
                    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

                        <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                            <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                                <span class="material-symbols-outlined" style="font-size:18px">groups</span>
                                Grupos guardados — sesión {{ $sesion->fecha->translatedFormat('d/m/Y') }}
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-[#000b60] text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $guardados->count() }} grupos
                                </span>
                                <a href="{{ route('calificaciones.index') }}"
                                   @click="
                                       sessionStorage.setItem('cal_sesion_grupal', '{{ $sesionId }}');
                                   "
                                   class="text-purple-600 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 text-xs font-semibold flex items-center gap-1 transition px-2 py-1 rounded-lg border border-purple-200 dark:border-purple-700">
                                    <span class="material-symbols-outlined" style="font-size:14px">grading</span>
                                    Calificar grupos
                                </a>
                                <button wire:click="eliminarGrupos"
                                        wire:confirm="¿Eliminar todos los grupos de esta sesión?"
                                        class="text-red-400 hover:text-red-600 text-xs font-semibold flex items-center gap-1 transition">
                                    <span class="material-symbols-outlined" style="font-size:16px">delete</span>
                                </button>
                            </div>
                        </div>

                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($guardados as $i => $grupo)
                                @php
                                    $colores = [
                                        'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
                                        'border-purple-400 bg-purple-50 dark:bg-purple-900/20',
                                        'border-green-400 bg-green-50 dark:bg-green-900/20',
                                        'border-orange-400 bg-orange-50 dark:bg-orange-900/20',
                                        'border-pink-400 bg-pink-50 dark:bg-pink-900/20',
                                        'border-teal-400 bg-teal-50 dark:bg-teal-900/20',
                                        'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
                                        'border-red-400 bg-red-50 dark:bg-red-900/20',
                                    ];
                                    $color = $colores[$i % count($colores)];
                                @endphp
                                <div class="border-2 {{ $color }} rounded-xl p-4">
                                    <p class="font-black text-[#000b60] dark:text-[#bcc2ff] text-sm mb-3 flex items-center gap-2">
                                        <span class="material-symbols-outlined" style="font-size:16px">group</span>
                                        {{ $grupo->nombre }}
                                        <span class="ml-auto bg-white/70 dark:bg-black/30 text-xs font-bold px-2 py-0.5 rounded-full text-gray-600 dark:text-gray-300">
                                            {{ $grupo->estudiantes->count() }}
                                        </span>
                                    </p>
                                    <ul class="space-y-1">
                                        @foreach($grupo->estudiantes as $e)
                                            <li class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                                                {{ $e->nombre }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @elseif(!$generado)

                    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
                        <span class="material-symbols-outlined" style="font-size:64px">group_add</span>
                        <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">Configura y genera los grupos</p>
                        <p class="text-sm mt-1">Los grupos se mostrarán aquí una vez generados</p>
                    </div>

                @endif

            </div>

        </div>

    @endif

</div>
