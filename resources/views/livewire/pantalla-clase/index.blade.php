<div wire:poll.5s x-data="pantallaClaseApp()" x-init="init()"
     @iniciar-ruleta-pantalla.window="iniciarAnimacion($event.detail)">

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- CABECERA                                                        --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">

        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold">Pantalla de Clase</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Vista centralizada para proyección</p>
            </div>

            {{-- Selector de clase (solo admin sin sesión activa) --}}
            @if(!$esCatedratico && !$sesion)
            <select wire:model.live="claseId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[220px]">
                <option value="">— Selecciona una clase —</option>
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
            @endif
        </div>

        {{-- Tabs --}}
        @if($sesion)
        <div class="flex items-center gap-1 bg-white dark:bg-[#1e333c] rounded-xl shadow p-1">
            @foreach([
                ['qr',        'qr_code_2',   'QR'],
                ['ruleta',    'casino',       'Ruleta'],
                ['grupos',    'groups',       'Grupos'],
                ['timer',     'timer',        'Timer'],
                ['medidor',   'graphic_eq',   'Ruido'],
            ] as [$id, $icon, $label])
            <button wire:click="$set('tab','{{ $id }}')"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-bold transition
                           {{ $tab === $id
                               ? 'bg-[#000b60] text-white shadow'
                               : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
                <span class="material-symbols-outlined" style="font-size:18px">{{ $icon }}</span>
                {{ $label }}
            </button>
            @endforeach
        </div>
        @endif

    </div>

    {{-- Sin sesión activa (catedrático) --}}
    @if($sinSesionActiva)
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-32 text-gray-400 dark:text-gray-500">
        <span class="material-symbols-outlined" style="font-size:72px">cast_for_education</span>
        <p class="mt-4 font-semibold text-gray-500 text-xl">No tienes una sesión activa</p>
        <p class="text-sm mt-1">Ve a Sesiones, crea la sesión de hoy y luego regresa aquí.</p>
        <a href="{{ route('sesiones.index') }}"
           class="mt-6 bg-[#000b60] text-white px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
            <span class="material-symbols-outlined" style="font-size:18px">calendar_add_on</span>
            Ir a Sesiones
        </a>
    </div>

    {{-- Sin clase seleccionada (admin) --}}
    @elseif(!$esCatedratico && !$claseId)
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-32 text-gray-400 dark:text-gray-500">
        <span class="material-symbols-outlined" style="font-size:72px">cast_for_education</span>
        <p class="mt-4 font-semibold text-gray-500 text-xl">Selecciona una clase para comenzar</p>
        <p class="text-sm mt-1">Todas las herramientas de clase estarán disponibles aquí</p>
    </div>

    {{-- Sin sesión hoy --}}
    @elseif(!$sesion)
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-32 text-gray-400 dark:text-gray-500">
        <span class="material-symbols-outlined" style="font-size:72px">event_busy</span>
        <p class="mt-4 font-semibold text-gray-500 text-xl">No hay sesión activa para hoy</p>
        <p class="text-sm mt-1">Crea una sesión desde el módulo de Sesiones para activar la pantalla</p>
        <a href="{{ route('sesiones.index') }}"
           class="mt-6 bg-[#000b60] text-white px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
            <span class="material-symbols-outlined" style="font-size:18px">calendar_add_on</span>
            Ir a Sesiones
        </a>
    </div>

    @else

    {{-- Barra de estado de sesión --}}
    <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-xl px-5 py-2.5 mb-4 flex items-center justify-between text-sm">
        <div class="flex items-center gap-3 text-[#000b60] dark:text-[#bcc2ff] font-bold">
            <span class="material-symbols-outlined" style="font-size:17px">calendar_today</span>
            {{ $sesion->clase->nombre }} — Sesión {{ $sesion->fecha->translatedFormat('d/m/Y') }}
            @if($sesion->finalizada)
                <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">Finalizada</span>
            @else
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">Activa</span>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 font-bold text-[#000b60] dark:text-[#bcc2ff]">
                <span class="material-symbols-outlined text-green-600" style="font-size:17px">how_to_reg</span>
                {{ $asistentes->count() }} / {{ $totalEstudiantes }} presentes
            </div>
            @if(!$sesion->finalizada)
            <button wire:click="finalizarSesion"
                    wire:confirm="¿Finalizar esta sesión? Ya no se podrá registrar asistencia ni participación."
                    class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                <span class="material-symbols-outlined" style="font-size:15px">lock</span>
                Finalizar sesión
            </button>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: QR                                                         --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($tab === 'qr')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Panel QR --}}
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg p-8 flex flex-col items-center">

            @if($qrSvg && $sesion->expiracion > now())
                @php
                    $segsIniciales = $sesion->expiracion->gt(now())
                        ? (int) now()->diffInSeconds($sesion->expiracion) : 0;
                @endphp

                <p class="text-xs font-black text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">
                    Escanea para registrar asistencia
                </p>

                <div class="border-4 border-[#000b60] rounded-2xl p-2 mb-4">
                    {!! $qrSvg !!}
                </div>

                <div class="text-center mb-5"
                     x-data="{
                         r: {{ $segsIniciales }},
                         get display() {
                             const m = Math.floor(this.r / 60);
                             const s = this.r % 60;
                             return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
                         },
                         init() {
                             const t = setInterval(() => {
                                 if (this.r > 0) this.r--;
                                 else clearInterval(t);
                             }, 1000);
                         }
                     }"
                     wire:key="qr-countdown-{{ $sesion->token }}">
                    <p class="text-xs text-gray-400 mb-1">Tiempo restante</p>
                    <p class="text-5xl font-black font-mono"
                       :class="r <= 60 ? 'text-red-500' : 'text-[#000b60] dark:text-[#bcc2ff]'"
                       x-text="display"></p>
                    <p x-show="r <= 60 && r > 0" class="text-xs text-red-500 font-semibold mt-1">⚠ Menos de 1 minuto</p>
                    <p x-show="r > 60" class="text-xs text-green-600 font-semibold">QR activo</p>
                </div>

                <button wire:click="generarQR"
                        class="w-full border-2 border-[#000b60] text-[#000b60] py-3 rounded-xl font-bold hover:bg-blue-50 transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">refresh</span>
                    Renovar QR (5 min)
                </button>

            @else
                <span class="material-symbols-outlined text-gray-200 mb-4" style="font-size:100px">qr_code_2</span>
                <p class="font-semibold text-gray-500 text-lg mb-1">
                    {{ $sesion->token ? 'QR expirado' : 'Sin QR activo' }}
                </p>
                <p class="text-sm text-gray-400 mb-6 text-center">
                    Genera un código para que los estudiantes registren su asistencia
                </p>
                <button wire:click="generarQR"
                        class="bg-[#000b60] text-white px-8 py-3 rounded-xl font-bold hover:opacity-90 transition flex items-center gap-2 text-lg">
                    <span class="material-symbols-outlined">qr_code</span>
                    Generar QR (5 min)
                </button>
            @endif

        </div>

        {{-- Panel: lista de todos los inscritos --}}
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg overflow-hidden flex flex-col">

            <div class="bg-[#000b60] px-5 py-4 flex items-center justify-between">
                <span class="font-black text-white text-base flex items-center gap-2">
                    <span class="material-symbols-outlined">format_list_bulleted</span>
                    Lista de asistencia
                </span>
                <div class="flex items-center gap-2">
                    <span class="bg-green-400 text-[#000b60] text-xs font-black px-2.5 py-1 rounded-full">
                        {{ $asistentes->count() }} presentes
                    </span>
                    <span class="bg-white/20 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ $totalEstudiantes - $asistentes->count() }} ausentes
                    </span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto" style="max-height: 480px;">
                @if($todosInscritos->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-gray-300">
                        <span class="material-symbols-outlined" style="font-size:48px">group</span>
                        <p class="mt-3 font-semibold text-gray-400">Sin estudiantes inscritos</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($todosInscritos as $est)
                        <li class="flex items-center gap-3 px-4 py-2.5
                                   {{ $est->ya_asistio
                                       ? 'bg-green-50/60 dark:bg-green-900/10'
                                       : 'hover:bg-gray-50 dark:hover:bg-[#1a2f3c]' }}">

                            {{-- Ícono estado --}}
                            @if($est->ya_asistio)
                                <span class="material-symbols-outlined text-green-500 shrink-0" style="font-size:20px">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 shrink-0" style="font-size:20px">radio_button_unchecked</span>
                            @endif

                            {{-- Nombre --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm truncate
                                          {{ $est->ya_asistio ? 'text-gray-800 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $est->nombre }}
                                </p>
                                @if($est->ya_asistio && $est->hora_registro)
                                    <p class="text-xs text-green-600 font-mono">
                                        {{ \Carbon\Carbon::parse($est->hora_registro)->format('H:i') }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-300 dark:text-gray-600 font-mono">{{ $est->carne }}</p>
                                @endif
                            </div>

                            {{-- Botón acción --}}
                            @if(!$sesion->finalizada)
                                @if($est->ya_asistio)
                                    <button wire:click="quitarAsistencia({{ $est->id }})"
                                            title="Quitar asistencia"
                                            class="text-red-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded-lg transition shrink-0">
                                        <span class="material-symbols-outlined" style="font-size:16px">remove_circle</span>
                                    </button>
                                @else
                                    <button wire:click="registrarManual({{ $est->id }})"
                                            title="Registrar asistencia manual"
                                            class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] p-1 rounded-lg transition shrink-0">
                                        <span class="material-symbols-outlined" style="font-size:16px">person_add</span>
                                    </button>
                                @endif
                            @endif

                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: RULETA                                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'ruleta')

    {{-- Panel actividad de sesión --}}
    @if($actSesionId)
    <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-5 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
            <span class="material-symbols-outlined" style="font-size:20px">task_alt</span>
            <span class="text-sm font-semibold">Actividad activa:</span>
            <span class="font-bold">{{ $actSesionNombre }}</span>
            <span class="text-xs font-normal opacity-70">(sobre 100 pts — cada spin guarda la nota)</span>
        </div>
        <button wire:click="limpiarActSesion"
                class="text-green-600 dark:text-green-400 hover:text-red-500 transition p-1 rounded-lg"
                title="Quitar actividad de sesión">
            <span class="material-symbols-outlined" style="font-size:18px">close</span>
        </button>
    </div>
    @else
    <div class="mb-4 bg-[#e6f6ff] dark:bg-[#0d2535] border border-blue-100 dark:border-[#1a2f3c] rounded-xl px-5 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:20px">casino</span>
            <span class="text-sm">Crea una actividad para que cada estudiante seleccionado reciba su nota sobre 100 pts.</span>
        </div>
        <button wire:click="abrirModalCrearActSesion"
                class="shrink-0 bg-[#000b60] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
            <span class="material-symbols-outlined" style="font-size:14px">add</span>
            Crear actividad
        </button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Display ruleta --}}
        <div class="lg:col-span-2 flex flex-col items-center">

            <div class="w-full bg-[#000b60] rounded-2xl shadow-2xl flex flex-col items-center justify-center py-16 px-8 mb-6 relative overflow-hidden"
                 style="min-height: 340px;">
                <div class="absolute inset-0 opacity-5"
                     style="background: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>

                <div x-show="!girando && !ganadorMostrado" class="text-center z-10">
                    <span class="material-symbols-outlined text-white opacity-20" style="font-size:80px">casino</span>
                    <p class="text-white/40 mt-3 text-2xl font-semibold">Presiona Girar para comenzar</p>
                    <p class="text-white/25 mt-1 text-sm">{{ $presentes->count() }} estudiante(s) en la ruleta</p>
                </div>

                <div x-show="girando || ganadorMostrado" class="text-center z-10 w-full px-6">
                    <p class="text-white/50 text-sm uppercase tracking-widest mb-4"
                       x-text="girando ? 'Seleccionando...' : '¡Seleccionado!'"></p>
                    <p class="font-black leading-tight text-center transition-all duration-100"
                       :class="{
                           'text-white text-5xl md:text-6xl': girando,
                           'text-yellow-300 text-6xl md:text-7xl scale-110 drop-shadow-lg': ganadorMostrado && !girando
                       }"
                       style="text-shadow: 0 0 40px rgba(253,224,71,0.5);"
                       x-text="nombreActual"></p>
                    <div x-show="ganadorMostrado && !girando"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-5 flex items-center justify-center gap-3">
                        <span class="text-yellow-300 text-3xl">★</span>
                        <span class="text-white/60 text-lg">Estudiante seleccionado</span>
                        <span class="text-yellow-300 text-3xl">★</span>
                    </div>
                </div>
            </div>

            <button wire:click="girar"
                    :disabled="girando"
                    class="w-full max-w-sm bg-yellow-400 hover:bg-yellow-300 text-[#000b60] font-black text-2xl py-5 rounded-2xl shadow-xl transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                <span class="material-symbols-outlined text-3xl" :class="girando ? 'animate-spin' : ''">casino</span>
                <span x-show="!girando">Girar</span>
                <span x-show="girando">Seleccionando...</span>
            </button>

            {{-- Historial --}}
            @if($historial->isNotEmpty())
            <div class="w-full mt-5 bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] text-sm flex items-center gap-2">
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
                            <th class="text-center px-4 py-2 font-semibold text-gray-500 dark:text-gray-400 text-xs">Nota /100</th>
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

        {{-- Lista presentes --}}
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow overflow-hidden h-fit">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">how_to_reg</span>
                    Presentes hoy
                </span>
                <span class="bg-green-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                    {{ $presentes->count() }}
                </span>
            </div>
            @if($presentes->isEmpty())
                <p class="text-center text-gray-400 py-8 text-sm">Sin asistencia registrada</p>
            @else
            <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c] max-h-[500px] overflow-y-auto">
                @foreach($presentes as $i => $e)
                <li class="px-4 py-2.5 flex items-center gap-3 text-sm hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]"
                    :class="nombreActual === '{{ $e->nombre }}' && (girando || ganadorMostrado) ? 'bg-yellow-50 dark:bg-yellow-900/20 font-bold' : ''">
                    <span class="text-gray-300 text-xs w-5 text-right">{{ $i + 1 }}</span>
                    <span>{{ $e->nombre }}</span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: GRUPOS                                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'grupos')
    @php
        $candidatosGrupo = $fuente === 'todos' ? $todosInscritos : $presentes;
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Panel de configuración --}}
        <div class="lg:col-span-1 flex flex-col gap-5">

            {{-- Info sesión --}}
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-xl px-5 py-4">
                <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-1">Sesión activa</p>
                <p class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->fecha->translatedFormat('d/m/Y') }}</p>
                <div class="flex gap-3 mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span><span class="font-semibold text-green-600">{{ $presentes->count() }}</span> presentes</span>
                    <span><span class="font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $todosInscritos->count() }}</span> inscritos</span>
                </div>
            </div>

            {{-- Configuración --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex flex-col gap-4">
                <p class="font-bold text-[#000b60] dark:text-[#bcc2ff] text-sm uppercase tracking-wide">Configuración</p>

                {{-- Fuente --}}
                <div>
                    <label class="block text-sm font-semibold mb-2">Incluir estudiantes</label>
                    <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-[#2a3d4a]">
                        <button wire:click="$set('fuente', 'presentes')"
                                class="flex-1 py-2 text-sm font-semibold transition flex items-center justify-center gap-1
                                       {{ $fuente === 'presentes' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#1a2f3c]' }}">
                            <span class="material-symbols-outlined" style="font-size:15px">how_to_reg</span>
                            Solo presentes
                        </button>
                        <button wire:click="$set('fuente', 'todos')"
                                class="flex-1 py-2 text-sm font-semibold transition border-l border-gray-200 dark:border-[#2a3d4a] flex items-center justify-center gap-1
                                       {{ $fuente === 'todos' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#1a2f3c]' }}">
                            <span class="material-symbols-outlined" style="font-size:15px">group</span>
                            Todos los inscritos
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:13px">info</span>
                        {{ $fuente === 'presentes'
                            ? $presentes->count() . ' estudiante(s) en la sesión de hoy'
                            : $todosInscritos->count() . ' estudiante(s) inscritos en la clase' }}
                    </p>
                </div>

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
                    @php
                        $total = $candidatosGrupo->count();
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

                {{-- Descripción --}}
                <div>
                    <label class="block text-sm font-semibold mb-1">
                        Descripción
                        <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">(opcional)</span>
                    </label>
                    <input wire:model="descripcionGrupos"
                           type="text"
                           placeholder="¿Qué actividad realizarán los grupos?"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                </div>

                {{-- Botón generar --}}
                <button wire:click="generar"
                        @if($candidatosGrupo->isEmpty()) disabled @endif
                        class="w-full py-3 rounded-xl font-bold text-sm transition flex items-center justify-center gap-2
                               {{ $candidatosGrupo->isEmpty() ? 'bg-gray-200 dark:bg-[#1a2f3c] text-gray-400 cursor-not-allowed' : 'bg-[#000b60] text-white hover:opacity-90' }}">
                    <span class="material-symbols-outlined" style="font-size:18px">shuffle</span>
                    {{ $candidatosGrupo->isEmpty() ? 'Sin estudiantes para agrupar' : 'Generar grupos' }}
                </button>

                <p class="text-xs text-gray-400 flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:13px">auto_awesome</span>
                    Minimiza repeticiones con historial anterior
                </p>
            </div>

            {{-- Lista de candidatos --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">{{ $fuente === 'presentes' ? 'how_to_reg' : 'group' }}</span>
                        {{ $fuente === 'presentes' ? 'Presentes hoy' : 'Inscritos en clase' }}
                    </span>
                    <span class="bg-[#000b60] text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                        {{ $candidatosGrupo->count() }}
                    </span>
                </div>
                <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c] max-h-56 overflow-y-auto">
                    @foreach($candidatosGrupo as $i => $e)
                        <li class="px-4 py-2 flex items-center gap-3 text-sm">
                            <span class="text-gray-300 text-xs w-5 text-right">{{ $i + 1 }}</span>
                            <span class="{{ $fuente === 'todos' && !($e->ya_asistio ?? false) ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $e->nombre }}
                            </span>
                            @if($fuente === 'todos' && ($e->ya_asistio ?? false))
                                <span class="ml-auto text-green-500 material-symbols-outlined" style="font-size:14px">check_circle</span>
                            @endif
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
                            <button wire:click="guardarGrupos"
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

            {{-- Grupos guardados --}}
            @if($grupos->isNotEmpty())
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                    <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                        <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined" style="font-size:18px">groups</span>
                            Grupos guardados — sesión {{ $sesion->fecha->translatedFormat('d/m/Y') }}
                        </span>
                        <div class="flex items-center gap-3">
                            <span class="bg-[#000b60] text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                                {{ $grupos->count() }} grupos
                            </span>
                            <button wire:click="eliminarGrupos"
                                    wire:confirm="¿Eliminar todos los grupos de esta sesión?"
                                    class="text-red-400 hover:text-red-600 text-xs font-semibold flex items-center gap-1 transition">
                                <span class="material-symbols-outlined" style="font-size:16px">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($grupos as $i => $grupo)
                            @php
                                $colores = [
                                    ['border-blue-400',   'bg-blue-50',   'bg-blue-500',   'text-blue-700'],
                                    ['border-purple-400', 'bg-purple-50', 'bg-purple-500', 'text-purple-700'],
                                    ['border-green-400',  'bg-green-50',  'bg-green-500',  'text-green-700'],
                                    ['border-orange-400', 'bg-orange-50', 'bg-orange-500', 'text-orange-700'],
                                    ['border-pink-400',   'bg-pink-50',   'bg-pink-500',   'text-pink-700'],
                                    ['border-teal-400',   'bg-teal-50',   'bg-teal-500',   'text-teal-700'],
                                    ['border-yellow-400', 'bg-yellow-50', 'bg-yellow-500', 'text-yellow-700'],
                                    ['border-red-400',    'bg-red-50',    'bg-red-500',    'text-red-700'],
                                ];
                                [$border, $bg, $badge, $text] = $colores[$i % count($colores)];
                            @endphp
                            <div class="border-2 {{ $border }} {{ $bg }} dark:bg-transparent rounded-xl overflow-hidden">
                                <div class="{{ $badge }} px-4 py-2.5 flex items-center justify-between">
                                    <span class="font-black text-white text-sm flex items-center gap-2">
                                        <span class="material-symbols-outlined" style="font-size:16px">group</span>
                                        {{ $grupo->nombre }}
                                    </span>
                                    <span class="bg-white/30 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ $grupo->estudiantes->count() }}
                                    </span>
                                </div>
                                <ul class="divide-y divide-white/40 dark:divide-white/10 px-4 py-2">
                                    @foreach($grupo->estudiantes as $e)
                                        <li class="py-2 flex items-center gap-2 text-sm {{ $text }} dark:text-gray-300 font-semibold">
                                            <span class="material-symbols-outlined" style="font-size:14px">person</span>
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

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: TEMPORIZADOR (Alpine.js puro)                              --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'timer')
    <div x-data="timerPantalla()" x-init="initTimer()">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Reloj --}}
            <div class="lg:col-span-2 bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg flex flex-col items-center py-10 px-6">

                <p class="text-sm font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-6 min-h-[20px]"
                   x-text="tLabel"></p>

                <div class="relative flex items-center justify-center mb-8">
                    <svg width="280" height="280" viewBox="0 0 280 280" class="-rotate-90">
                        <circle cx="140" cy="140" r="120" fill="none"
                                :stroke="tDark ? '#1e3d58' : '#e6f0ff'" stroke-width="14"/>
                        <circle cx="140" cy="140" r="120" fill="none"
                                :stroke="tRingColor" stroke-width="14" stroke-linecap="round"
                                :stroke-dasharray="tCircumference"
                                :stroke-dashoffset="tDashOffset"
                                style="transition: stroke-dashoffset 0.9s linear, stroke 0.5s ease;"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center"
                         :class="tFinished ? 'animate-pulse' : ''">
                        <span class="font-black tabular-nums leading-none"
                              :class="{
                                  'text-7xl': !tFinished,
                                  'text-5xl text-red-500': tFinished,
                                  'text-orange-500': !tFinished && tProgress <= 0.25 && tProgress > 0.1,
                                  'text-red-500': !tFinished && tProgress <= 0.1
                              }"
                              :style="(!tFinished && tProgress > 0.25) ? { color: tDark ? '#bcc2ff' : '#000b60' } : {}"
                              x-text="tFinished ? '¡Tiempo!' : tDisplay"></span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-semibold uppercase tracking-widest"
                              x-show="!tFinished"
                              x-text="tRunning ? 'en curso' : 'listo'"></span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button @click="tReset()"
                            class="w-12 h-12 rounded-full border-2 border-gray-200 dark:border-[#2a3d4a] flex items-center justify-center text-gray-400 hover:border-gray-400 dark:hover:border-[#bcc2ff] hover:text-gray-600 dark:hover:text-[#bcc2ff] transition">
                        <span class="material-symbols-outlined" style="font-size:22px">replay</span>
                    </button>
                    <button @click="tRunning ? tPause() : tStart()"
                            class="w-20 h-20 rounded-full flex items-center justify-center shadow-lg text-white transition"
                            :class="tFinished ? 'bg-red-500 hover:bg-red-600' : tRunning ? 'bg-orange-400 hover:bg-orange-500' : 'bg-[#000b60] hover:opacity-90'">
                        <span class="material-symbols-outlined" style="font-size:32px"
                              x-text="tRunning ? 'pause' : 'play_arrow'"></span>
                    </button>
                    <button @click="tToggleFullscreen()"
                            class="w-12 h-12 rounded-full border-2 border-gray-200 dark:border-[#2a3d4a] flex items-center justify-center text-gray-400 hover:border-gray-400 dark:hover:border-[#bcc2ff] hover:text-gray-600 dark:hover:text-[#bcc2ff] transition">
                        <span class="material-symbols-outlined" style="font-size:22px"
                              x-text="tIsFullscreen ? 'fullscreen_exit' : 'fullscreen'"></span>
                    </button>
                </div>
                <p class="text-xs text-gray-300 dark:text-gray-600 mt-5">Barra espaciadora para iniciar / pausar</p>
            </div>

            {{-- Configuración timer --}}
            <div class="flex flex-col gap-4">
                <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                    <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">Tiempos rápidos</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([1, 2, 3, 5, 10, 15, 20, 25, 30] as $min)
                        <button @click="tSetTime({{ $min }})"
                                :class="tTotal === {{ $min * 60 }} && !tRunning && !tFinished ? 'bg-[#000b60] text-white' : 'bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#162a35]'"
                                class="py-2.5 rounded-xl text-sm font-bold transition">
                            {{ $min }}<span class="font-normal text-xs opacity-70"> min</span>
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                    <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">Tiempo personalizado</p>
                    <div class="flex gap-2 mb-3">
                        <div class="flex-1">
                            <label class="text-xs text-gray-400 dark:text-gray-500 mb-1 block">Min</label>
                            <input x-model.number="tCustomMin"
                                   type="number" min="0" max="99"
                                   class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-center text-lg font-black focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        </div>
                        <div class="flex-1">
                            <label class="text-xs text-gray-400 dark:text-gray-500 mb-1 block">Seg</label>
                            <input x-model.number="tCustomSec"
                                   type="number" min="0" max="59"
                                   class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-center text-lg font-black focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        </div>
                    </div>
                    <button @click="tSetCustomTime()"
                            :disabled="tCustomMin === 0 && tCustomSec === 0"
                            class="w-full bg-[#000b60] text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition disabled:opacity-40">
                        Aplicar
                    </button>
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                    <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-3">Etiqueta (opcional)</p>
                    <input x-model="tLabel" type="text" maxlength="40"
                           placeholder="Ej. Examen parcial, Debate..."
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-[#000b60] dark:text-[#bcc2ff]">Sonido al finalizar</p>
                        <p class="text-xs text-gray-400">Alerta sonora al llegar a cero</p>
                    </div>
                    <button @click="tSoundOn = !tSoundOn"
                            :class="tSoundOn ? 'bg-[#000b60]' : 'bg-gray-200 dark:bg-[#2a3d4a]'"
                            class="relative w-12 h-6 rounded-full transition-colors flex-shrink-0">
                        <span :class="tSoundOn ? 'translate-x-6' : 'translate-x-1'"
                              class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform block"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Overlay pantalla completa timer --}}
    <div x-show="tIsFullscreen"
         x-transition:enter="transition duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-[#000b60] z-50 flex flex-col items-center justify-center"
         style="display:none;">
        <p class="text-white/50 text-sm font-bold uppercase tracking-widest mb-6" x-text="tLabel"></p>
        <span class="font-black tabular-nums leading-none"
              :class="{
                  'text-[12rem] text-white':                    !tFinished && tProgress > 0.25,
                  'text-[12rem] text-orange-400':               !tFinished && tProgress <= 0.25 && tProgress > 0.1,
                  'text-[10rem] text-red-400 animate-pulse':    !tFinished && tProgress <= 0.1,
                  'text-8xl text-red-400 animate-pulse':        tFinished
              }"
              x-text="tFinished ? '¡Tiempo!' : tDisplay"></span>
        <div class="flex items-center gap-6 mt-12">
            <button @click="tReset()"
                    class="w-16 h-16 rounded-full border-2 border-white/30 flex items-center justify-center text-white/60 hover:text-white hover:border-white transition">
                <span class="material-symbols-outlined" style="font-size:28px">replay</span>
            </button>
            <button @click="tRunning ? tPause() : tStart()"
                    class="w-24 h-24 rounded-full flex items-center justify-center text-white shadow-2xl transition"
                    :class="tRunning ? 'bg-orange-500' : 'bg-white/20 hover:bg-white/30'">
                <span class="material-symbols-outlined" style="font-size:40px"
                      x-text="tRunning ? 'pause' : 'play_arrow'"></span>
            </button>
            <button @click="tToggleFullscreen()"
                    class="w-16 h-16 rounded-full border-2 border-white/30 flex items-center justify-center text-white/60 hover:text-white hover:border-white transition">
                <span class="material-symbols-outlined" style="font-size:28px">fullscreen_exit</span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: MEDIDOR DE RUIDO (Alpine.js puro)                          --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'medidor')
    <div x-data="medidorPantalla()" x-init="initMedidor()">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <div class="lg:col-span-2 bg-white dark:bg-[#1e333c] rounded-2xl shadow-lg p-8 flex flex-col items-center">

                <div x-show="!mActivo && !mError" class="text-center py-8">
                    <span class="material-symbols-outlined text-gray-300" style="font-size:80px">mic_off</span>
                    <p class="mt-4 font-semibold text-gray-500 text-xl">Micrófono inactivo</p>
                    <p class="text-sm text-gray-400 mb-6">Haz clic en "Activar" para comenzar a medir el ruido</p>
                    <button @click="mActivar()"
                            class="bg-[#000b60] text-white px-8 py-3 rounded-xl font-bold text-base hover:opacity-90 transition flex items-center gap-2 mx-auto">
                        <span class="material-symbols-outlined">mic</span>
                        Activar micrófono
                    </button>
                </div>

                <div x-show="mError" class="text-center py-8">
                    <span class="material-symbols-outlined text-red-400" style="font-size:64px">mic_off</span>
                    <p class="mt-3 font-semibold text-red-500" x-text="mErrorMsg"></p>
                    <button @click="mActivar()"
                            class="mt-4 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-400 px-6 py-2 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#1a2f3c] transition">
                        Reintentar
                    </button>
                </div>

                <div x-show="mActivo" class="w-full flex flex-col items-center gap-8">

                    {{-- Nivel dB y etiqueta --}}
                    <div class="text-center">
                        <p class="text-xs font-bold uppercase tracking-widest mb-1"
                           :class="{
                               'text-green-600':  mNivel === 'silencio' || mNivel === 'bajo',
                               'text-yellow-600': mNivel === 'moderado',
                               'text-orange-500': mNivel === 'alto',
                               'text-red-600':    mNivel === 'muy_alto'
                           }"
                           x-text="mEtiqueta"></p>
                        <p class="font-black tabular-nums leading-none"
                           :class="{
                               'text-7xl text-green-500':  mNivel === 'silencio' || mNivel === 'bajo',
                               'text-7xl text-yellow-500': mNivel === 'moderado',
                               'text-7xl text-orange-500': mNivel === 'alto',
                               'text-7xl text-red-500':    mNivel === 'muy_alto'
                           }"
                           x-text="mDb + ' dB'"></p>
                    </div>

                    {{-- Barras de nivel --}}
                    <div class="w-full max-w-lg">
                        <div class="flex items-end justify-center gap-1.5 h-32">
                            <template x-for="(bar, idx) in mBars" :key="idx">
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
                        <div class="flex justify-between text-xs text-gray-300 dark:text-gray-600 mt-1 px-0.5">
                            <span>0 dB</span>
                            <span>silencio</span>
                            <span>conversación</span>
                            <span>ruido</span>
                            <span>100 dB</span>
                        </div>
                    </div>

                    {{-- Umbral de alerta --}}
                    <div class="w-full max-w-lg">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Umbral de alerta</label>
                            <span class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff]" x-text="mUmbral + ' dB'"></span>
                        </div>
                        <input x-model.number="mUmbral" type="range" min="30" max="90" step="5"
                               class="w-full accent-[#000b60]">
                    </div>

                    {{-- Botón detener --}}
                    <button @click="mDetener()"
                            class="border-2 border-gray-200 dark:border-[#2a3d4a] text-gray-500 dark:text-gray-400 px-6 py-2.5 rounded-xl font-semibold text-sm hover:border-red-300 hover:text-red-500 dark:hover:border-red-500/50 dark:hover:text-red-400 transition flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">mic_off</span>
                        Detener
                    </button>
                </div>

            </div>

            {{-- Panel info --}}
            <div class="flex flex-col gap-4">
                <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5">
                    <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">Niveles de referencia</p>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-green-400 flex-shrink-0"></span>
                            <span class="text-gray-600 dark:text-gray-400"><span class="font-bold">0–40 dB</span> — Silencio / muy tranquilo</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                            <span class="text-gray-600 dark:text-gray-400"><span class="font-bold">40–55 dB</span> — Clase en orden</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0"></span>
                            <span class="text-gray-600 dark:text-gray-400"><span class="font-bold">55–70 dB</span> — Conversación normal</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-orange-400 flex-shrink-0"></span>
                            <span class="text-gray-600 dark:text-gray-400"><span class="font-bold">70–85 dB</span> — Ruido elevado</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"></span>
                            <span class="text-gray-600 dark:text-gray-400"><span class="font-bold">85+ dB</span> — Muy ruidoso</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow p-5" x-show="mActivo">
                    <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-widest mb-4">Esta sesión</p>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Máximo</span>
                            <span class="font-black text-red-500" x-text="mMax + ' dB'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Mínimo</span>
                            <span class="font-black text-green-600" x-text="mMin + ' dB'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Promedio</span>
                            <span class="font-black text-[#000b60] dark:text-[#bcc2ff]" x-text="mAvg + ' dB'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Alertas</span>
                            <span class="font-black text-orange-500" x-text="mAlertas"></span>
                        </div>
                    </div>
                    <button @click="mResetStats()"
                            class="mt-4 w-full text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                        Reiniciar estadísticas
                    </button>
                </div>
            </div>

        </div>

        {{-- Alerta umbral (fuera de la card) --}}
        <div x-show="mSuperaUmbral"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-xl px-5 py-4 flex items-center gap-3 text-red-600 dark:text-red-400 font-semibold animate-pulse">
            <span class="material-symbols-outlined text-2xl flex-shrink-0">warning</span>
            <span>¡Nivel de ruido supera el umbral establecido!</span>
        </div>

    </div>
    @endif

    @endif {{-- fin sesión activa --}}

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Registrar participación (ruleta)                         --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="bg-[#000b60] px-6 py-5 text-center">
                <p class="text-yellow-300 text-xs font-bold uppercase tracking-widest mb-1">Estudiante seleccionado</p>
                <h2 class="text-white text-2xl font-black">{{ $ganadorNombre }}</h2>
            </div>
            <div class="p-6">
                @if($actSesionId)
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg px-4 py-2 flex items-center gap-2 text-green-700 dark:text-green-400 text-xs">
                    <span class="material-symbols-outlined" style="font-size:15px">task_alt</span>
                    La nota se guardará en <strong>{{ $actSesionNombre }}</strong>
                </div>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5">Registra la participación (opcional)</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Calificación <span class="text-gray-400 font-normal">(0 – 100)</span></label>
                        <input wire:model="calificacion" type="number" min="0" max="100" step="0.5"
                               placeholder="Ej. 85"
                               x-on:input="let v=parseFloat($el.value);if(!isNaN(v)&&v>100)$el.value=100;if(!isNaN(v)&&v<0)$el.value=0;"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('calificacion') border-red-400 @enderror">
                        @error('calificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Comentario</label>
                        <textarea wire:model="comentario" rows="3"
                                  placeholder="Observaciones sobre la participación..."
                                  class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] resize-none @error('comentario') border-red-400 @enderror"></textarea>
                        @error('comentario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Crear actividad de sesión (ruleta)                       --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showModalCrearActSesion)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="bg-[#000b60] px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-yellow-300" style="font-size:24px">casino</span>
                    <div>
                        <p class="text-yellow-300 text-xs font-bold uppercase tracking-widest">Ruleta</p>
                        <h2 class="text-white text-lg font-black leading-tight">Crear actividad de sesión</h2>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                    Todos los estudiantes seleccionados en esta sesión acumularán su nota en esta actividad (sobre 100 pts).
                </p>
                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre de la actividad</label>
                    <input wire:model="actSesionNombre" type="text"
                           placeholder="Ej. Pregunta oral U3"
                           wire:keydown.enter="crearActSesion"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actSesionNombre') border-red-400 @enderror">
                    @error('actSesionNombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 mt-6">
                    <button wire:click="$set('showModalCrearActSesion', false)"
                            class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-500 dark:text-gray-400 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                        Cancelar
                    </button>
                    <button wire:click="crearActSesion"
                            class="flex-1 bg-[#000b60] text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition text-sm">
                        Crear actividad
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Crear actividad grupal (post-guardar grupos)             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showModalActividad)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="bg-[#000b60] px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-yellow-300" style="font-size:24px">assignment</span>
                    <div>
                        <p class="text-yellow-300 text-xs font-bold uppercase tracking-widest">Grupos guardados</p>
                        <h2 class="text-white text-lg font-black leading-tight">¿Crear actividad grupal?</h2>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                    Puedes crear una actividad para calificar a estos grupos o hacerlo más tarde desde el panel de desempeño.
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Nombre de la actividad
                            <span class="text-red-400">*</span>
                        </label>
                        <input wire:model="actividadNombre"
                               type="text"
                               placeholder="Ej. Trabajo grupal U2"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actividadNombre') border-red-400 @enderror">
                        @error('actividadNombre')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Punteo máximo
                            <span class="text-red-400">*</span>
                        </label>
                        <input wire:model="actividadPunteo"
                               type="number"
                               min="1"
                               max="9999.99"
                               step="0.5"
                               placeholder="100"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actividadPunteo') border-red-400 @enderror">
                        @error('actividadPunteo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button wire:click="omitirActividad"
                            class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-500 dark:text-gray-400 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                        Omitir
                    </button>
                    <button wire:click="crearActividadGrupal"
                            class="flex-1 bg-[#000b60] text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition text-sm flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">add_task</span>
                        Crear actividad
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
/* ── Alpine: Ruleta ─────────────────────────────────────────── */
function pantallaClaseApp() {
    return {
        nombreActual:    '',
        girando:         false,
        ganadorMostrado: false,

        init() {},

        iniciarAnimacion({ nombres, ganadorNombre }) {
            if (this.girando) return;

            this.girando         = true;
            this.ganadorMostrado = false;
            this.nombreActual    = nombres[0] ?? '';

            const totalMs = 4000;
            let elapsed   = 0;
            let delay     = 60;
            let idx       = 0;

            const paso = () => {
                idx++;
                this.nombreActual = nombres[idx % nombres.length];
                elapsed += delay;

                if (elapsed > totalMs * 0.6) {
                    delay = Math.min(Math.floor(delay * 1.18), 450);
                }

                if (elapsed < totalMs) {
                    setTimeout(paso, delay);
                } else {
                    this.nombreActual    = ganadorNombre;
                    this.girando         = false;
                    this.ganadorMostrado = true;
                    setTimeout(() => { this.$wire.seleccionarGanador(); }, 900);
                }
            };

            setTimeout(paso, delay);
        }
    };
}

/* ── Alpine: Temporizador ───────────────────────────────────── */
function timerPantalla() {
    return {
        tTotal:         300,
        tRemaining:     300,
        tRunning:       false,
        tFinished:      false,
        tInterval:      null,
        tLabel:         '',
        tCustomMin:     5,
        tCustomSec:     0,
        tSoundOn:       true,
        tIsFullscreen:  false,
        tDark:          window.matchMedia('(prefers-color-scheme: dark)').matches,
        tCircumference: 2 * Math.PI * 120,

        initTimer() {
            window.addEventListener('keydown', (e) => {
                if (e.code === 'Space' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    this.tRunning ? this.tPause() : this.tStart();
                }
            });
        },

        get tProgress()   { return this.tTotal > 0 ? this.tRemaining / this.tTotal : 0; },
        get tDashOffset() { return this.tCircumference * (1 - this.tProgress); },
        get tDisplay() {
            const m = Math.floor(this.tRemaining / 60);
            const s = this.tRemaining % 60;
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        },
        get tRingColor() {
            if (this.tFinished)          return '#ef4444';
            if (this.tProgress <= 0.1)   return '#ef4444';
            if (this.tProgress <= 0.25)  return '#f97316';
            return '#000b60';
        },

        tSetTime(minutes) {
            this.tStop();
            this.tTotal     = minutes * 60;
            this.tRemaining = this.tTotal;
            this.tFinished  = false;
        },

        tSetCustomTime() {
            const secs = (this.tCustomMin * 60) + this.tCustomSec;
            if (secs <= 0) return;
            this.tStop();
            this.tTotal     = secs;
            this.tRemaining = secs;
            this.tFinished  = false;
        },

        tToggleFullscreen() {
            this.tIsFullscreen = !this.tIsFullscreen;
        },

        tStart() {
            if (this.tFinished) { this.tReset(); return; }
            if (this.tRemaining <= 0) return;
            this.tRunning  = true;
            this.tFinished = false;
            this.tInterval = setInterval(() => {
                if (this.tRemaining > 0) {
                    this.tRemaining--;
                } else {
                    this.tStop();
                    this.tFinished = true;
                    if (this.tSoundOn) this.tPlaySound();
                }
            }, 1000);
        },

        tPause() {
            this.tRunning = false;
            clearInterval(this.tInterval);
            this.tInterval = null;
        },

        tStop() {
            this.tRunning = false;
            if (this.tInterval) { clearInterval(this.tInterval); this.tInterval = null; }
        },

        tReset() {
            this.tStop();
            this.tRemaining = this.tTotal;
            this.tFinished  = false;
        },

        tPlaySound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const beep = (delay, freq, dur) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.type = 'sine'; osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.6, ctx.currentTime + delay);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + dur);
                    osc.start(ctx.currentTime + delay);
                    osc.stop(ctx.currentTime + delay + dur + 0.05);
                };
                beep(0.0, 880, 0.25); beep(0.3, 880, 0.25); beep(0.6, 1100, 0.5);
            } catch (e) {}
        },
    };
}

/* ── Alpine: Medidor ────────────────────────────────────────── */
function medidorPantalla() {
    return {
        mActivo:       false,
        mError:        false,
        mErrorMsg:     '',
        mDb:           0,
        mNivel:        'silencio',
        mEtiqueta:     'Silencio',
        mUmbral:       65,
        mSuperaUmbral: false,
        mAlertas:      0,
        mBars:         Array(32).fill(0),
        mStream:       null,
        mContext:      null,
        mAnimId:       null,
        _mSamples:     [],
        mMin:          0,
        mMax:          0,
        mAvg:          0,
        _mIniciadoEn:  null,
        _mNiveles:     {},   // contador por nivel para calcular predominante

        initMedidor() {},

        mNivelInfo(db) {
            if (db < 40)  return ['silencio', 'Silencio'];
            if (db < 55)  return ['bajo',     'Tranquilo'];
            if (db < 70)  return ['moderado', 'Moderado'];
            if (db < 85)  return ['alto',     'Ruidoso'];
            return              ['muy_alto',  '¡Muy ruidoso!'];
        },

        async mActivar() {
            this.mError = false;
            try {
                this.mStream   = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                this.mContext  = new (window.AudioContext || window.webkitAudioContext)();
                const source   = this.mContext.createMediaStreamSource(this.mStream);
                const analyser = this.mContext.createAnalyser();
                analyser.fftSize = 256;
                analyser.smoothingTimeConstant = 0.6;
                source.connect(analyser);
                const data = new Uint8Array(analyser.frequencyBinCount);

                this.mActivo     = true;
                this._mIniciadoEn = new Date().toISOString();
                this.mResetStats();

                const loop = () => {
                    analyser.getByteFrequencyData(data);

                    // RMS → dB
                    const rms = Math.sqrt(data.reduce((s, v) => s + v * v, 0) / data.length);
                    const raw = rms > 0 ? Math.round(20 * Math.log10(rms / 255) + 100) : 0;
                    this.mDb  = Math.max(0, Math.min(100, raw));

                    // Barras (32 bandas)
                    const chunk = Math.floor(data.length / 32);
                    this.mBars = Array.from({ length: 32 }, (_, i) => {
                        const slice = data.slice(i * chunk, (i + 1) * chunk);
                        const avg   = slice.reduce((s, v) => s + v, 0) / slice.length;
                        return Math.min(100, Math.round(avg / 255 * 100));
                    });

                    [this.mNivel, this.mEtiqueta] = this.mNivelInfo(this.mDb);

                    // Umbral
                    const prev = this.mSuperaUmbral;
                    this.mSuperaUmbral = this.mDb >= this.mUmbral;
                    if (this.mSuperaUmbral && !prev) this.mAlertas++;

                    // Stats
                    this._mSamples.push(this.mDb);
                    if (this._mSamples.length > 3000) this._mSamples.shift();
                    this.mMax = Math.max(...this._mSamples);
                    this.mMin = Math.min(...this._mSamples);
                    this.mAvg = Math.round(this._mSamples.reduce((a, b) => a + b, 0) / this._mSamples.length);

                    // Nivel predominante
                    this._mNiveles[this.mNivel] = (this._mNiveles[this.mNivel] ?? 0) + 1;

                    this.mAnimId = requestAnimationFrame(loop);
                };
                this.mAnimId = requestAnimationFrame(loop);
            } catch (e) {
                this.mError    = true;
                this.mErrorMsg = 'No se pudo acceder al micrófono. Verifica los permisos del navegador.';
            }
        },

        mDetener() {
            if (!this.mActivo) return;

            if (this.mAnimId) cancelAnimationFrame(this.mAnimId);
            if (this.mStream) this.mStream.getTracks().forEach(t => t.stop());
            if (this.mContext) this.mContext.close();

            // Guardar estadísticas si hubo medición significativa
            const finalizadoEn = new Date().toISOString();
            const durSeg = this._mIniciadoEn
                ? Math.round((new Date(finalizadoEn) - new Date(this._mIniciadoEn)) / 1000)
                : 0;
            const nivelPred = Object.entries(this._mNiveles)
                .sort((a, b) => b[1] - a[1])[0]?.[0] ?? 'silencio';

            if (durSeg >= 5 && this._mSamples.length > 0) {
                this.$wire.guardarEstadisticasRuido(
                    this.mMin,
                    this.mMax,
                    this.mAvg,
                    this.mAlertas,
                    this.mUmbral,
                    durSeg,
                    nivelPred,
                    this._mIniciadoEn,
                    finalizadoEn
                );
            }

            this.mActivo       = false;
            this.mDb           = 0;
            this.mNivel        = 'silencio';
            this.mSuperaUmbral = false;
            this.mBars         = Array(32).fill(0);
        },

        mResetStats() {
            this._mSamples  = [];
            this._mNiveles  = {};
            this._mIniciadoEn = new Date().toISOString();
            this.mMax       = 0;
            this.mMin       = 0;
            this.mAvg       = 0;
            this.mAlertas   = 0;
        },
    };
}
</script>
@endpush
