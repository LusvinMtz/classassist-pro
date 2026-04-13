<div>

    {{-- ════ ENCABEZADO ════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('sesiones.index') }}"
               class="inline-flex items-center gap-1 text-sm text-[#000b60] dark:text-[#bcc2ff] hover:underline mb-2">
                <span class="material-symbols-outlined" style="font-size:16px">arrow_back</span>
                Volver a sesiones
            </a>
            <h1 class="text-3xl font-extrabold">{{ $sesion->clase->nombre }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $sesion->fecha->translatedFormat('l d \d\e F \d\e Y') }}
                @if($sesion->clase->catedratico)
                    &mdash; {{ $sesion->clase->catedratico->nombre }}
                @endif
                @if($sesion->clase->carrera)
                    &mdash; {{ $sesion->clase->carrera->nombre }}
                @endif
            </p>
        </div>
        <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-[#2a3d4a] text-gray-600 dark:text-gray-300 text-xs font-bold px-3 py-1.5 rounded-full">
            <span class="material-symbols-outlined" style="font-size:14px">lock</span>
            Sesión finalizada
        </span>
    </div>

    {{-- ════ KPIs ════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Inscritos</p>
            <p class="text-3xl font-extrabold text-[#000b60] dark:text-[#dff4ff] mt-1">{{ $totalInscritos }}</p>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Presentes</p>
            <p class="text-3xl font-extrabold text-green-600 mt-1">{{ $asistencias->count() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $pctAsistencia }}% de asistencia</p>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Ausentes</p>
            <p class="text-3xl font-extrabold text-red-500 mt-1">{{ $ausentes->count() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $totalInscritos > 0 ? round($ausentes->count() / $totalInscritos * 100, 1) : 0 }}% inasistencia</p>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Participaciones</p>
            <p class="text-3xl font-extrabold text-[#000b60] dark:text-[#bcc2ff] mt-1">{{ $participaciones->count() }}</p>
            @if($promedioRuleta !== null)
                <p class="text-xs text-gray-400 mt-0.5">Prom. {{ number_format($promedioRuleta, 1) }} pts</p>
            @endif
        </div>

    </div>

    {{-- Segunda fila KPI: grupos y ruido --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Grupos</p>
            <p class="text-3xl font-extrabold text-[#000b60] dark:text-[#dff4ff] mt-1">{{ $grupos->count() }}</p>
            @if($grupos->isNotEmpty())
                <p class="text-xs text-gray-400 mt-0.5">{{ $grupos->sum(fn($g) => $g->miembros->count()) }} est. distribuidos</p>
            @endif
        </div>

        @if($ruidoResumen)
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Ruido Promedio</p>
                <p class="text-3xl font-extrabold text-amber-500 mt-1">{{ $ruidoResumen->db_promedio }} <span class="text-base font-semibold">dB</span></p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $ruidoResumen->total_alertas }} alerta(s)</p>
            </div>

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4 col-span-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Nivel Predominante</p>
                @php
                    $nivelColor = match($ruidoResumen->nivel_predominante) {
                        'silencio'  => 'text-blue-400',
                        'bajo'      => 'text-green-500',
                        'moderado'  => 'text-yellow-500',
                        'alto'      => 'text-orange-500',
                        'muy_alto'  => 'text-red-600',
                        default     => 'text-gray-400',
                    };
                    $durMin = intdiv($ruidoResumen->duracion_segundos, 60);
                    $durSeg = $ruidoResumen->duracion_segundos % 60;
                @endphp
                <p class="text-2xl font-extrabold {{ $nivelColor }} mt-1 capitalize">
                    {{ str_replace('_', ' ', $ruidoResumen->nivel_predominante ?? 'N/D') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Duración: {{ $durMin }}m {{ $durSeg }}s &nbsp;|&nbsp;
                    Min: {{ $ruidoResumen->db_minimo }} dB &nbsp;|&nbsp;
                    Máx: {{ $ruidoResumen->db_maximo }} dB
                </p>
            </div>
        @endif

    </div>

    {{-- ════ ASISTENCIA ══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- Presentes --}}
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-[#2a3d4a]">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                <h2 class="font-extrabold text-sm uppercase tracking-wide text-[#000b60] dark:text-[#dff4ff]">
                    Presentes <span class="text-gray-400 font-normal">({{ $asistencias->count() }})</span>
                </h2>
            </div>
            @if($asistencias->isEmpty())
                <div class="py-10 text-center text-gray-400 text-sm">Sin registros</div>
            @else
                <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                    @foreach($asistencias as $a)
                        <li class="flex items-center justify-between px-5 py-2.5 hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                            <div class="flex items-center gap-3">
                                {{-- Selfie o avatar inicial --}}
                                @if($a->selfie)
                                    <button
                                        x-data
                                        @click="$dispatch('open-selfie', { src: '{{ asset('storage/' . $a->selfie) }}', nombre: '{{ addslashes($a->nombre) }}' })"
                                        class="flex-shrink-0 focus:outline-none"
                                        title="Ver selfie"
                                    >
                                        <img src="{{ asset('storage/' . $a->selfie) }}"
                                             alt="{{ $a->nombre }}"
                                             class="w-9 h-9 rounded-full object-cover border-2 border-green-400 hover:border-[#000b60] transition cursor-zoom-in">
                                    </button>
                                @else
                                    <div class="w-9 h-9 rounded-full bg-[#000b60] dark:bg-[#303c9a] flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                                        {{ strtoupper(substr($a->nombre, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="text-sm font-semibold text-[#000b60] dark:text-[#dff4ff]">{{ $a->nombre }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($a->selfie)
                                    <span class="text-xs text-green-500 flex items-center gap-0.5">
                                        <span class="material-symbols-outlined" style="font-size:13px">add_a_photo</span>
                                    </span>
                                @endif
                                @if($a->fecha_hora)
                                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($a->fecha_hora)->format('H:i') }}</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Ausentes --}}
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-[#2a3d4a]">
                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                <h2 class="font-extrabold text-sm uppercase tracking-wide text-[#000b60] dark:text-[#dff4ff]">
                    Ausentes <span class="text-gray-400 font-normal">({{ $ausentes->count() }})</span>
                </h2>
            </div>
            @if($ausentes->isEmpty())
                <div class="py-10 text-center text-gray-400 text-sm">¡Todos presentes!</div>
            @else
                <ul class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                    @foreach($ausentes as $e)
                        <li class="flex items-center gap-3 px-5 py-2.5 hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                            <div class="w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-500 text-xs font-black flex-shrink-0">
                                {{ strtoupper(substr($e->nombre, 0, 1)) }}
                            </div>
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $e->nombre }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

    {{-- ════ PARTICIPACIONES (RULETA) ════════════════════════════════════════════ --}}
    @if($participaciones->isNotEmpty())
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden mb-6">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#2a3d4a]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:18px">casino</span>
                    <h2 class="font-extrabold text-sm uppercase tracking-wide text-[#000b60] dark:text-[#dff4ff]">
                        Participaciones &mdash; Ruleta
                    </h2>
                </div>
                @if($promedioRuleta !== null)
                    <span class="text-xs font-bold text-gray-400">
                        Promedio: <span class="text-[#000b60] dark:text-[#bcc2ff]">{{ number_format($promedioRuleta, 1) }} pts</span>
                    </span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                            <th class="text-left px-5 py-2.5 font-bold text-xs uppercase tracking-wide">#</th>
                            <th class="text-left px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Estudiante</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Calificación</th>
                            <th class="text-left px-4 py-2.5 font-bold text-xs uppercase tracking-wide hidden md:table-cell">Comentario</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide hidden sm:table-cell">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($participaciones as $i => $p)
                            <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                                <td class="px-5 py-2.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-[#000b60] dark:text-[#dff4ff]">{{ $p->nombre }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    @if($p->calificacion !== null)
                                        @php
                                            $cal = (float) $p->calificacion;
                                            $color = $cal >= 7 ? 'bg-green-100 text-green-700' : ($cal >= 5 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600');
                                        @endphp
                                        <span class="inline-block {{ $color }} font-bold text-xs px-2.5 py-1 rounded-full dark:bg-opacity-20">
                                            {{ number_format($cal, 1) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 text-sm hidden md:table-cell">
                                    {{ $p->comentario ?: '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-xs text-gray-400 hidden sm:table-cell">
                                    {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ════ GRUPOS ══════════════════════════════════════════════════════════════ --}}
    @if($grupos->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:18px">hub</span>
                <h2 class="font-extrabold text-sm uppercase tracking-wide text-[#000b60] dark:text-[#dff4ff]">
                    Grupos generados <span class="text-gray-400 font-normal">({{ $grupos->count() }})</span>
                </h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($grupos as $grupo)
                    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4">
                        <p class="text-xs font-extrabold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-3">
                            {{ $grupo->nombre ?: 'Grupo ' . $loop->iteration }}
                            <span class="text-gray-400 font-normal normal-case ml-1">({{ $grupo->miembros->count() }})</span>
                        </p>
                        <ul class="space-y-1.5">
                            @foreach($grupo->miembros as $miembro)
                                <li class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="w-5 h-5 rounded-full bg-[#000b60] dark:bg-[#303c9a] flex items-center justify-center text-white text-[9px] font-black flex-shrink-0">
                                        {{ strtoupper(substr($miembro, 0, 1)) }}
                                    </div>
                                    {{ $miembro }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ════ ESTADÍSTICAS DE RUIDO (detalle por medición) ═══════════════════════ --}}
    @if($ruidoRegistros->isNotEmpty())
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden mb-6">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-[#2a3d4a]">
                <span class="material-symbols-outlined text-amber-500" style="font-size:18px">graphic_eq</span>
                <h2 class="font-extrabold text-sm uppercase tracking-wide text-[#000b60] dark:text-[#dff4ff]">
                    Mediciones de Ruido
                    <span class="text-gray-400 font-normal">({{ $ruidoRegistros->count() }} sesión/es)</span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                            <th class="text-left px-5 py-2.5 font-bold text-xs uppercase tracking-wide">Inicio</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Duración</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Mín dB</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Prom dB</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Máx dB</th>
                            <th class="text-center px-4 py-2.5 font-bold text-xs uppercase tracking-wide">Alertas</th>
                            <th class="text-left px-4 py-2.5 font-bold text-xs uppercase tracking-wide hidden sm:table-cell">Nivel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($ruidoRegistros as $r)
                            @php
                                $min = intdiv($r->duracion_segundos, 60);
                                $seg = $r->duracion_segundos % 60;
                                $nivelColor = match($r->nivel_predominante) {
                                    'silencio' => 'text-blue-400',
                                    'bajo'     => 'text-green-500',
                                    'moderado' => 'text-yellow-500',
                                    'alto'     => 'text-orange-500',
                                    'muy_alto' => 'text-red-600',
                                    default    => 'text-gray-400',
                                };
                            @endphp
                            <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                                <td class="px-5 py-2.5 text-gray-600 dark:text-gray-300">
                                    {{ $r->iniciado_en ? $r->iniciado_en->format('H:i') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-center text-gray-600 dark:text-gray-300">
                                    {{ $min }}m {{ $seg }}s
                                </td>
                                <td class="px-4 py-2.5 text-center font-semibold text-blue-500">{{ $r->db_minimo }}</td>
                                <td class="px-4 py-2.5 text-center font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $r->db_promedio }}</td>
                                <td class="px-4 py-2.5 text-center font-semibold text-red-500">{{ $r->db_maximo }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="font-bold {{ $r->total_alertas > 0 ? 'text-amber-500' : 'text-gray-400' }}">
                                        {{ $r->total_alertas }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 hidden sm:table-cell capitalize {{ $nivelColor }} font-semibold">
                                    {{ str_replace('_', ' ', $r->nivel_predominante ?? '—') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Estado vacío: sesión sin datos --}}
    @if($asistencias->isEmpty() && $participaciones->isEmpty() && $grupos->isEmpty() && $ruidoRegistros->isEmpty())
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:56px">info</span>
            <p class="mt-3 font-semibold text-lg">Esta sesión no tiene datos registrados</p>
        </div>
    @endif

    {{-- ════ LIGHTBOX SELFIE ═══════════════════════════════════════════════════ --}}
    <div
        x-data="{ open: false, src: '', nombre: '' }"
        @open-selfie.window="open = true; src = $event.detail.src; nombre = $event.detail.nombre"
        @keydown.escape.window="open = false"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/75"
        style="display:none"
        @click.self="open = false"
    >
        <div class="relative max-w-sm w-full" @click.stop>
            <button @click="open = false"
                    class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-white dark:bg-[#1e333c] shadow flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-red-500 transition z-10">
                <span class="material-symbols-outlined" style="font-size:18px">close</span>
            </button>
            <img :src="src" :alt="nombre"
                 class="w-full rounded-2xl shadow-2xl object-cover border-4 border-white dark:border-[#2a3d4a]">
            <p class="text-center text-white text-sm font-semibold mt-3 drop-shadow" x-text="nombre"></p>
        </div>
    </div>

</div>
