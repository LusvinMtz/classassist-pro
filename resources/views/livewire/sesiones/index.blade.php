<div>

    {{-- ════ ENCABEZADO ════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Sesiones de Clase</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona las sesiones para habilitar asistencia, ruleta y grupos</p>
        </div>
        <button wire:click="abrirModal"
                class="bg-[#000b60] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition flex items-center gap-2">
            <span class="material-symbols-outlined" style="font-size:18px">add_circle</span>
            Nueva sesión — hoy {{ now()->translatedFormat('d/m/Y') }}
        </button>
    </div>

    {{-- ════ ALERTA: sesión activa (catedrático) ═══════════════════════════════ --}}
    @if(!$esAdmin && $sesionActiva)
        <div class="mb-5 flex items-start gap-3 bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-xl px-4 py-3 text-sm">
            <span class="material-symbols-outlined mt-0.5 text-green-500" style="font-size:18px">radio_button_checked</span>
            <div>
                <p class="font-bold">Sesión activa ahora</p>
                <p>{{ $sesionActiva->clase->nombre }} — {{ $sesionActiva->fecha->translatedFormat('d \d\e F') }}</p>
            </div>
        </div>
    @endif

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
                        class="text-xs text-gray-400 hover:text-red-500 transition flex items-center gap-1 pb-0.5">
                    <span class="material-symbols-outlined" style="font-size:15px">close</span>
                    Limpiar
                </button>
            @endif

        </div>
    @endif

    {{-- ════ TABLA DE SESIONES ══════════════════════════════════════════════════ --}}

    @if($sesiones->isEmpty())

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">event_note</span>
            <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay sesiones registradas</p>
            <p class="text-sm mt-1">Crea la primera sesión con el botón de arriba</p>
        </div>

    @else

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#2a3d4a]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesiones->count() }}</span> sesión(es)
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                            <th class="text-left px-5 py-3 font-bold text-xs uppercase tracking-wide">Fecha</th>
                            <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wide">Clase</th>
                            @if($esAdmin)
                                <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wide hidden md:table-cell">Catedrático</th>
                            @endif
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wide">Estado</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wide hidden sm:table-cell">Asist.</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wide hidden sm:table-cell">Partic.</th>
                            <th class="text-right px-5 py-3 font-bold text-xs uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($sesiones as $sesion)
                            @php
                                $esHoy     = $sesion->fecha->isToday();
                                $esPasada  = $sesion->fecha->lt(today());
                                $operativa = $esHoy && !$sesion->finalizada;
                            @endphp
                            <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition {{ $esPasada ? 'opacity-70' : '' }}">

                                {{-- Fecha --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $operativa ? 'bg-green-500' : ($esHoy ? 'bg-gray-400' : 'bg-gray-300 dark:bg-gray-600') }}"></span>
                                        <div>
                                            <p class="font-bold">{{ $sesion->fecha->translatedFormat('d/m/Y') }}</p>
                                            <p class="text-xs text-gray-400">{{ $sesion->fecha->translatedFormat('l') }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Clase --}}
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-[#000b60] dark:text-[#dff4ff]">{{ $sesion->clase->nombre }}</p>
                                    @if($sesion->clase->carrera)
                                        <p class="text-xs text-gray-400">{{ $sesion->clase->carrera->nombre }}</p>
                                    @endif
                                </td>

                                {{-- Catedrático (admin) --}}
                                @if($esAdmin)
                                    <td class="px-4 py-3.5 hidden md:table-cell text-gray-600 dark:text-gray-300">
                                        {{ $sesion->clase->catedratico?->nombre ?? '—' }}
                                    </td>
                                @endif

                                {{-- Estado --}}
                                <td class="px-4 py-3.5 text-center">
                                    @if($sesion->finalizada)
                                        <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-[#2a3d4a] text-gray-500 dark:text-gray-400 text-xs font-bold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined" style="font-size:13px">lock</span>
                                            Finalizada
                                        </span>
                                    @elseif($esHoy)
                                        <span class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined" style="font-size:13px">radio_button_checked</span>
                                            Activa
                                        </span>
                                    @else
                                        {{-- Sesión pasada sin finalizar (scheduler pendiente) --}}
                                        <span class="inline-flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined" style="font-size:13px">schedule</span>
                                            Vencida
                                        </span>
                                    @endif
                                </td>

                                {{-- Asistentes --}}
                                <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->asistencias_count }}</span>
                                </td>

                                {{-- Participaciones --}}
                                <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->participaciones_count }}</span>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">

                                        @if($sesion->finalizada || $esPasada)
                                            {{-- Sesión cerrada: solo estadísticas --}}
                                            <a href="{{ route('sesiones.detalle', $sesion->id) }}"
                                               class="inline-flex items-center gap-1 bg-[#000b60] text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:opacity-80 transition">
                                                <span class="material-symbols-outlined" style="font-size:14px">bar_chart</span>
                                                Estadísticas
                                            </a>

                                            {{-- Reabrir solo si fue hoy (admin) --}}
                                            @if($sesion->finalizada && $esAdmin && $esHoy)
                                                <button wire:click="reabrir({{ $sesion->id }})"
                                                        wire:confirm="¿Reabrir esta sesión?"
                                                        class="inline-flex items-center gap-1 border border-gray-300 dark:border-[#3a4d5a] text-gray-600 dark:text-gray-300 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] transition">
                                                    <span class="material-symbols-outlined" style="font-size:14px">lock_open</span>
                                                    Reabrir
                                                </button>
                                            @endif

                                            {{-- Forzar cierre si vencida (admin) --}}
                                            @if($esPasada && !$sesion->finalizada && $esAdmin)
                                                <button wire:click="finalizar({{ $sesion->id }})"
                                                        wire:confirm="¿Cerrar esta sesión vencida?"
                                                        class="inline-flex items-center gap-1 bg-amber-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-600 transition">
                                                    <span class="material-symbols-outlined" style="font-size:14px">lock</span>
                                                    Cerrar
                                                </button>
                                            @endif

                                        @else
                                            {{-- Sesión activa de hoy: solo ir a Pantalla Clase --}}
                                            <a href="{{ route('pantalla-clase.index', ['sesionId' => $sesion->id]) }}"
                                               title="Pantalla de Clase"
                                               class="inline-flex items-center gap-1 bg-[#000b60] text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:opacity-80 transition">
                                                <span class="material-symbols-outlined" style="font-size:14px">cast_for_education</span>
                                                Pantalla de Clase
                                            </a>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

    {{-- ════ MODAL: NUEVA SESIÓN ════════════════════════════════════════════════ --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4"
             x-data
             x-on:keydown.escape.window="$wire.cerrarModal()">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50" wire:click="cerrarModal"></div>

            {{-- Card --}}
            <div class="relative bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md p-6 z-10"
                 x-on:click.stop>

                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-extrabold text-[#000b60] dark:text-[#dff4ff]">Nueva Sesión</h2>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                    Fecha: <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ now()->translatedFormat('l d \d\e F Y') }}</span>
                </p>

                @if($esAdmin)

                    {{-- ADMIN: cascade sede → carrera → clase --}}
                    <div class="space-y-4">

                        <div>
                            <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-1">1. Sede</label>
                            <select wire:model.live="modalSedeId"
                                    class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                                <option value="">— Selecciona sede —</option>
                                @foreach($sedesModal as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($modalSedeId)
                            <div>
                                <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-1">2. Carrera</label>
                                <select wire:model.live="modalCarreraId"
                                        class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                                    <option value="">— Selecciona carrera —</option>
                                    @foreach($carrerasModal as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($modalSedeId && $modalCarreraId)
                            <div>
                                <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-1">3. Curso</label>
                                <select wire:model.live="modalClaseId"
                                        class="w-full border {{ $errors->has('modalClaseId') ? 'border-red-400' : 'border-gray-200 dark:border-[#2a3d4a]' }} dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                                    <option value="">— Selecciona curso —</option>
                                    @foreach($clasesModal as $clase)
                                        <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('modalClaseId')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                    </div>

                @else

                    {{-- CATEDRÁTICO: directo a selección de curso --}}
                    <div>
                        <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide mb-1">Curso</label>
                        <select wire:model.live="modalClaseId"
                                class="w-full border {{ $errors->has('modalClaseId') ? 'border-red-400' : 'border-gray-200 dark:border-[#2a3d4a]' }} dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                            <option value="">— Selecciona curso —</option>
                            @foreach($clasesModal as $clase)
                                <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                            @endforeach
                        </select>
                        @error('modalClaseId')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                @endif

                <div class="flex gap-3 mt-6">
                    <button wire:click="cerrarModal"
                            class="flex-1 border border-gray-300 dark:border-[#3a4d5a] text-gray-600 dark:text-gray-300 py-2.5 rounded-xl font-semibold text-sm hover:bg-gray-50 dark:hover:bg-[#1a2f3c] transition">
                        Cancelar
                    </button>
                    <button wire:click="crear"
                            class="flex-1 bg-[#000b60] text-white py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition">
                        Iniciar sesión
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
