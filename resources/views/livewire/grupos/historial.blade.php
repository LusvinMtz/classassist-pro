<div>

    {{-- ════ ENCABEZADO ════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Historial de Grupos</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Grupos formados en sesiones anteriores</p>
        </div>

        {{-- Selector de clase (solo catedrático) --}}
        @if(!$esAdmin)
        <div class="flex items-center gap-3">
            <label class="text-sm font-semibold text-[#000b60] dark:text-[#bcc2ff]">Clase:</label>
            <select wire:model.live="claseId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[220px]">
                <option value="">— Todas mis clases —</option>
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- ════ FILTROS ADMIN ══════════════════════════════════════════════════════ --}}
    @if($esAdmin)
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow px-5 py-4 mb-5 flex flex-wrap items-end gap-4">

        {{-- Sede --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide">Sede</label>
            <select wire:model.live="filterSedeId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[140px]">
                <option value="">Todas</option>
                @foreach($sedes as $sede)
                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- Carrera --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide">Carrera</label>
            <select wire:model.live="filterCarreraId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[170px]">
                <option value="">Todas</option>
                @foreach($carreras as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>

        {{-- Clase --}}
        @if($clasesFilter->isNotEmpty())
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide">Clase</label>
            <select wire:model.live="filterClaseId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[200px]">
                <option value="">Todas</option>
                @foreach($clasesFilter as $cl)
                    <option value="{{ $cl->id }}">{{ $cl->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Catedrático --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide">Catedrático</label>
            <input wire:model.live.debounce.400ms="filterCatedratico"
                   type="text"
                   placeholder="Buscar por nombre…"
                   class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[180px]">
        </div>

        {{-- Limpiar --}}
        @if($filterSedeId || $filterCarreraId || $filterClaseId || $filterCatedratico)
        <button wire:click="$set('filterSedeId', null); $set('filterCarreraId', null); $set('filterClaseId', null); $set('filterCatedratico', '')"
                class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold pb-0.5">
            <span class="material-symbols-outlined" style="font-size:16px">close</span>
            Limpiar filtros
        </button>
        @endif

    </div>
    @endif

    {{-- ════ CONTENIDO ═════════════════════════════════════════════════════════ --}}
    @if($sesiones->isEmpty())

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">group_off</span>
            @if($esAdmin && !$filterSedeId && !$filterCarreraId && !$filterClaseId && !$filterCatedratico)
                <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">Usa los filtros para buscar grupos</p>
                <p class="text-sm mt-1">Filtra por sede, carrera, clase o catedrático</p>
            @else
                <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay grupos registrados</p>
                <p class="text-sm mt-1">Genera grupos desde la Pantalla de Clase</p>
            @endif
        </div>

    @else

        <div class="space-y-5">
            @foreach($sesiones as $sesion)
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
                    $totalMiembros    = $sesion->grupos->sum(fn($g) => $g->estudiantes->count());
                    $activsVinculadas = $actividadesPorSesion[$sesion->id] ?? collect();
                    $descripcion      = $sesion->grupos->first()?->descripcion;
                @endphp

                <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

                    {{-- Header sesión --}}
                    <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-start justify-between gap-3 flex-wrap">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff] mt-0.5" style="font-size:20px">calendar_today</span>
                            <div>
                                <p class="font-bold text-[#000b60] dark:text-[#bcc2ff]">
                                    {{ $sesion->fecha->translatedFormat('l, d \d\e F Y') }}
                                    @if($sesion->fecha->isToday())
                                        <span class="ml-2 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-0.5 rounded-full font-semibold">Hoy</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $sesion->grupos->count() }} grupos · {{ $totalMiembros }} estudiantes
                                    @if($esAdmin && $sesion->clase)
                                        · <span class="font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->clase->nombre }}</span>
                                    @endif
                                </p>
                                @if($descripcion)
                                    <p class="text-xs text-[#000b60] dark:text-[#bcc2ff] mt-0.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined" style="font-size:13px">label</span>
                                        {{ $descripcion }}
                                    </p>
                                @endif
                                {{-- Actividades vinculadas --}}
                                @if($activsVinculadas->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($activsVinculadas as $act)
                                            <span class="inline-flex items-center gap-1 bg-[#000b60] dark:bg-[#bcc2ff] text-white dark:text-[#000b60] text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                <span class="material-symbols-outlined" style="font-size:11px">task_alt</span>
                                                {{ $act->nombre }}
                                                <span class="opacity-70">({{ $act->punteo_max }} pts)</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($sesion->finalizada)
                            <span class="text-xs bg-gray-100 dark:bg-[#162a35] text-gray-500 dark:text-gray-400 px-2.5 py-1 rounded-full font-semibold flex items-center gap-1 flex-shrink-0">
                                <span class="material-symbols-outlined" style="font-size:13px">lock</span>
                                Finalizada
                            </span>
                        @endif
                    </div>

                    {{-- Grid de grupos --}}
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($sesion->grupos as $i => $grupo)
                            <div class="border-2 {{ $colores[$i % count($colores)] }} rounded-xl p-4">
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
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                                            {{ $e->nombre }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                </div>
            @endforeach
        </div>

    @endif

</div>
