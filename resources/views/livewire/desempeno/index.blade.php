<div>

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Desempeño de Estudiantes</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Ranking basado en asistencia, participación y calificaciones</p>
    </div>

    {{-- ── Filtros ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-6">
        @if($esAdmin)
            {{-- Admin: Sede → Carrera → Clase --}}
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">Sede</label>
                    <select wire:model.live="filterSede"
                            class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        <option value="">— Todas las sedes —</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">Carrera</label>
                    <select wire:model.live="filterCarrera"
                            class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        <option value="">— Todas las carreras —</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">Clase (opcional)</label>
                    <select wire:model.live="claseId"
                            class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        <option value="">— Vista global —</option>
                        @foreach($clases as $clase)
                            <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @if($filterSede || $filterCarrera || $claseId)
                    <button wire:click="$set('filterSede', null); $set('filterCarrera', null); $set('claseId', null)"
                            class="text-xs text-gray-400 hover:text-red-500 flex items-center gap-1 pb-1 transition">
                        <span class="material-symbols-outlined" style="font-size:15px">restart_alt</span>
                        Limpiar
                    </button>
                @endif
            </div>

            {{-- Chip indicador de contexto --}}
            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                @if($filterSede)
                    <span class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] px-2.5 py-1 rounded-full font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:13px">location_city</span>
                        {{ $sedes->firstWhere('id', $filterSede)?->nombre }}
                    </span>
                @endif
                @if($filterCarrera)
                    <span class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] px-2.5 py-1 rounded-full font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:13px">school</span>
                        {{ $carreras->firstWhere('id', $filterCarrera)?->nombre }}
                    </span>
                @endif
                @if($claseId && $claseActual)
                    <span class="bg-[#000b60] dark:bg-[#303c9a] text-white px-2.5 py-1 rounded-full font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:13px">menu_book</span>
                        {{ $claseActual->nombre }}
                        @if($claseActual->catedratico) — {{ $claseActual->catedratico->nombre }} @endif
                    </span>
                @endif
            </div>

        @else
            {{-- Catedrático: solo selector de clase --}}
            <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]">school</span>
                <div class="flex-1">
                    <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">Clase</label>
                    <select wire:model.live="claseId"
                            class="w-full md:w-80 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        <option value="">— Selecciona una clase —</option>
                        @foreach($clases as $clase)
                            <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </div>

    {{-- ── VISTA AGREGADA POR CLASE (admin sin clase específica) ─────────────── --}}
    @if($esAdmin && $modoVista === 'clases')

        @if($resumenClases->isEmpty())
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
                <span class="material-symbols-outlined" style="font-size:64px">leaderboard</span>
                <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay clases con los filtros seleccionados</p>
            </div>
        @else
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between">
                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined" style="font-size:18px">leaderboard</span>
                        Resumen por clase
                    </span>
                    <span class="text-xs text-gray-400">{{ $resumenClases->count() }} clase(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                            <tr>
                                <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Clase</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Carrera</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Catedrático</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Estudiantes</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Sesiones</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[160px]">% Asistencia</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Participaciones</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Notas prom.</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs w-28">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                            @foreach($resumenClases as $c)
                            @php if(!isset($c['id'])) continue; @endphp
                                @php
                                    $pct = $c['pct_asistencia'];
                                    $barColor  = $pct >= 75 ? 'bg-green-500' : ($pct >= 50 ? 'bg-orange-400' : 'bg-red-400');
                                    $textColor = $pct >= 75 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500');
                                @endphp
                                <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                                    <td class="px-5 py-3 font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $c['nombre'] }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $c['carrera'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 text-xs">{{ $c['catedratico'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $c['total_estudiantes'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ $c['total_sesiones'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 dark:bg-[#2a3d4a] rounded-full h-2 overflow-hidden">
                                                <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $textColor }} w-10 text-right">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-blue-100 text-blue-700 font-bold text-xs px-2 py-0.5 rounded-full">
                                            {{ $c['total_participaciones'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($c['promedio_notas'] !== null)
                                            @php $nc = $c['promedio_notas'] >= 70 ? 'bg-green-100 text-green-700' : ($c['promedio_notas'] >= 50 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'); @endphp
                                            <span class="font-bold text-xs px-2 py-0.5 rounded-full {{ $nc }}">{{ $c['promedio_notas'] }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button wire:click="$set('claseId', {{ $c['id'] }})"
                                                class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] bg-[#e6f6ff] dark:bg-[#0d2535] px-3 py-1 rounded-full hover:bg-[#000b60] hover:text-white dark:hover:bg-[#303c9a] transition">
                                            Ver ranking
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación de clases --}}
                @if($clasesPaginator)
                @php
                    $cur  = $clasesPaginator->currentPage();
                    $last = $clasesPaginator->lastPage();
                    $pages = collect();
                    for ($p = 1; $p <= $last; $p++) {
                        if ($p === 1 || $p === $last || abs($p - $cur) <= 2) $pages->push($p);
                    }
                    $pages = $pages->unique()->sort()->values();
                @endphp
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1a2f3c] flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Página {{ $cur }} de {{ $last }} &nbsp;·&nbsp; {{ $clasesPaginator->total() }} clases</span>
                    @if($last > 1)
                    <div class="flex items-center gap-1">
                        {{-- Anterior --}}
                        @if($clasesPaginator->onFirstPage())
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-[#162a35] text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">‹</span>
                        @else
                            <button wire:click="previousPage" class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] transition">‹</button>
                        @endif

                        {{-- Números con elipsis --}}
                        @php $prev = null; @endphp
                        @foreach($pages as $p)
                            @if($prev !== null && $p - $prev > 1)
                                <span class="px-1 text-gray-300 select-none">…</span>
                            @endif
                            <button wire:click="gotoPage({{ $p }})"
                                    class="min-w-[28px] px-2 py-1 rounded-lg transition {{ $cur === $p ? 'bg-[#000b60] text-white font-bold' : 'bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535]' }}">
                                {{ $p }}
                            </button>
                            @php $prev = $p; @endphp
                        @endforeach

                        {{-- Siguiente --}}
                        @if($clasesPaginator->hasMorePages())
                            <button wire:click="nextPage" class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] transition">›</button>
                        @else
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-[#162a35] text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">›</span>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
        @endif

    {{-- ── VISTA RANKING POR ESTUDIANTE ──────────────────────────────────────── --}}
    @elseif($modoVista === 'estudiantes')

        @if(!$claseId)
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
                <span class="material-symbols-outlined" style="font-size:64px">leaderboard</span>
                <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">Selecciona una clase para ver el ranking</p>
            </div>

        @elseif($ranking->isEmpty())
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
                <span class="material-symbols-outlined" style="font-size:64px">person_off</span>
                <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay estudiantes en esta clase</p>
            </div>

        @else
            {{-- KPIs --}}
            @php
                $totalEstudiantes = $rankingTotal->count();
                $pctPromedio      = $rankingTotal->avg('pct_asistencia');
                $totalParticip    = $rankingTotal->sum('participaciones');
                $promedioCalif    = $rankingTotal->whereNotNull('promedio')->avg('promedio');
            @endphp

            <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wide mb-1">Estudiantes</p>
                    <p class="text-3xl font-black text-[#000b60] dark:text-[#bcc2ff]">{{ $totalEstudiantes }}</p>
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wide mb-1">Sesiones totales</p>
                    <p class="text-3xl font-black text-[#000b60] dark:text-[#bcc2ff]">{{ $totalSesiones }}</p>
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wide mb-1">Asistencia promedio</p>
                    <p class="text-3xl font-black {{ $pctPromedio >= 75 ? 'text-green-600' : ($pctPromedio >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                        {{ round($pctPromedio) }}%
                    </p>
                </div>
                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wide mb-1">Participaciones</p>
                    <p class="text-3xl font-black text-[#000b60] dark:text-[#bcc2ff]">{{ $totalParticip }}</p>
                    @if($promedioCalif !== null)
                        <p class="text-xs text-gray-400 mt-0.5">Calif. prom. <span class="font-bold text-blue-600">{{ number_format($promedioCalif, 1) }}</span></p>
                    @endif
                </div>
            </div>

            {{-- Tabla ranking --}}
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined" style="font-size:18px">leaderboard</span>
                        Ranking de estudiantes
                    </span>
                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span>Ordenar por:</span>
                        <button wire:click="$set('ordenar','asistencia')"
                                class="px-3 py-1 rounded-full transition {{ $ordenar === 'asistencia' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
                            Asistencia
                        </button>
                        <button wire:click="$set('ordenar','participaciones')"
                                class="px-3 py-1 rounded-full transition {{ $ordenar === 'participaciones' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
                            Participaciones
                        </button>
                        <button wire:click="$set('ordenar','calificacion')"
                                class="px-3 py-1 rounded-full transition {{ $ordenar === 'calificacion' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
                            Calif. participación
                        </button>
                        <button wire:click="$set('ordenar','notas')"
                                class="px-3 py-1 rounded-full transition {{ $ordenar === 'notas' ? 'bg-[#000b60] text-white' : 'bg-white dark:bg-[#162a35] hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
                            Notas
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                            <tr>
                                <th class="text-center px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs w-12">#</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Estudiante</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Asistencias</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[160px]">% Asistencia</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Participaciones</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Calif. participación</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Notas (prom.)</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Rendimiento</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                            @foreach($ranking as $pos => $e)
                                @php
                                    $pct = $e['pct_asistencia'];
                                    $barColor = $pct >= 75 ? 'bg-green-500' : ($pct >= 50 ? 'bg-orange-400' : 'bg-red-400');
                                    $textColor = $pct >= 75 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500');
                                    $scoreAsist = $pct;
                                    $scoreCalif = $e['promedio'] !== null ? (float) $e['promedio'] : null;
                                    $scoreNotas = $e['prom_notas'] !== null ? (float) $e['prom_notas'] : null;
                                    $rendimiento = $scoreAsist * 0.5;
                                    if ($scoreCalif !== null) { $rendimiento += $scoreCalif * 0.2; }
                                    if ($scoreNotas !== null) { $rendimiento += $scoreNotas * 0.3; }
                                    $rendimiento = round($rendimiento);
                                    $rendColor = $rendimiento >= 75 ? 'text-green-600 bg-green-50' : ($rendimiento >= 50 ? 'text-orange-500 bg-orange-50' : 'text-red-500 bg-red-50');
                                @endphp
                                <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition {{ $pos === 0 ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}">

                                    <td class="px-4 py-3 text-center">
                                        @if($pos === 0) <span class="text-yellow-500 text-lg">🥇</span>
                                        @elseif($pos === 1) <span class="text-gray-400 text-lg">🥈</span>
                                        @elseif($pos === 2) <span class="text-amber-600 text-lg">🥉</span>
                                        @else <span class="text-gray-300 font-bold text-xs">{{ $pos + 1 }}</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <p class="font-bold {{ $pos === 0 ? 'text-[#000b60] dark:text-[#bcc2ff]' : '' }}">{{ $e['nombre'] }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $e['carnet'] }}</p>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $e['asistencias'] }}</span>
                                        <span class="text-gray-400 text-xs"> / {{ $totalSesiones }}</span>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 dark:bg-[#2a3d4a] rounded-full h-2 overflow-hidden">
                                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $textColor }} w-10 text-right">{{ $pct }}%</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($e['participaciones'] > 0)
                                            <span class="bg-blue-100 text-blue-700 font-bold text-xs px-2 py-0.5 rounded-full">{{ $e['participaciones'] }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($e['promedio'] !== null)
                                            @php $califColor = $e['promedio'] >= 7 ? 'bg-green-100 text-green-700' : ($e['promedio'] >= 5 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'); @endphp
                                            <span class="font-bold text-xs px-2 py-0.5 rounded-full {{ $califColor }}">{{ number_format($e['promedio'], 1) }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($e['prom_notas'] !== null)
                                            @php $notaColor = $e['prom_notas'] >= 70 ? 'bg-green-100 text-green-700' : ($e['prom_notas'] >= 50 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'); @endphp
                                            <span class="font-bold text-xs px-2 py-0.5 rounded-full {{ $notaColor }}">{{ number_format($e['prom_notas'], 1) }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span class="font-black text-xs px-2.5 py-1 rounded-full {{ $rendColor }}">{{ $rendimiento }}%</span>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 bg-gray-50 dark:bg-[#162a35] border-t border-gray-100 dark:border-[#1a2f3c] flex flex-wrap gap-4 text-xs text-gray-400 dark:text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> ≥ 75%</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span> 50–74%</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span> &lt; 50%</span>
                    <span class="ml-auto">Rendimiento = 50% asistencia + 20% calif. participación + 30% notas</span>
                </div>

                {{-- Paginación del ranking --}}
                @if($lastPageRank > 1)
                @php
                    $rPages = collect();
                    for ($p = 1; $p <= $lastPageRank; $p++) {
                        if ($p === 1 || $p === $lastPageRank || abs($p - $pageRank) <= 2) $rPages->push($p);
                    }
                    $rPages = $rPages->unique()->sort()->values();
                @endphp
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1a2f3c] flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Página {{ $pageRank }} de {{ $lastPageRank }} &nbsp;·&nbsp; {{ $rankingTotal->count() }} estudiantes</span>
                    <div class="flex items-center gap-1">
                        @if($pageRank <= 1)
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-[#162a35] text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">‹</span>
                        @else
                            <button wire:click="$set('pageRank', {{ $pageRank - 1 }})" class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] transition">‹</button>
                        @endif

                        @php $prev = null; @endphp
                        @foreach($rPages as $p)
                            @if($prev !== null && $p - $prev > 1)
                                <span class="px-1 text-gray-300 select-none">…</span>
                            @endif
                            <button wire:click="$set('pageRank', {{ $p }})"
                                    class="min-w-[28px] px-2 py-1 rounded-lg transition {{ $pageRank === $p ? 'bg-[#000b60] text-white font-bold' : 'bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535]' }}">
                                {{ $p }}
                            </button>
                            @php $prev = $p; @endphp
                        @endforeach

                        @if($pageRank >= $lastPageRank)
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-[#162a35] text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">›</span>
                        @else
                            <button wire:click="$set('pageRank', {{ $pageRank + 1 }})" class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] transition">›</button>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        @endif

    @endif

</div>
