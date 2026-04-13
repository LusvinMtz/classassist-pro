<div
    x-data="{ notif: '', notifShow: false }"
    @notify.window="notif = $event.detail.message; notifShow = true; setTimeout(() => notifShow = false, 3500)"
>

    {{-- Notificación flotante --}}
    <div x-show="notifShow" x-transition
         class="fixed top-4 right-4 z-50 bg-green-600 text-white px-5 py-3 rounded-xl shadow-xl font-semibold text-sm"
         style="display:none">
        <span x-text="notif"></span>
    </div>

    {{-- CABECERA --}}
    <div class="flex flex-wrap justify-between items-center gap-3 mb-5">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold">Calificaciones</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de notas por tipo de evaluación</p>
        </div>

        <select wire:model.live="claseId"
                class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] w-full sm:w-auto sm:min-w-[240px]">
            <option value="">— Selecciona una clase —</option>
            @foreach($clases as $clase)
                <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
            @endforeach
        </select>
    </div>

    @if(!$claseId)
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-32 text-gray-400 dark:text-gray-500">
        <span class="material-symbols-outlined" style="font-size:72px">grading</span>
        <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-xl text-center">Selecciona una clase para gestionar calificaciones</p>
    </div>

    @else

    {{-- ── Panel de configuración de evaluación ──────────────────────────── --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-4 flex flex-wrap gap-4 items-end">

        <div>
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px">tune</span>
                Método de evaluación de actividades
            </p>
            <div class="flex gap-2">
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition
                              {{ $metodoActividades === 'porcentaje'
                                 ? 'border-[#000b60] bg-[#000b60]/5 dark:border-[#bcc2ff] dark:bg-[#bcc2ff]/10'
                                 : 'border-gray-200 dark:border-[#2a3d4a] hover:border-gray-300' }}">
                    <input type="radio" wire:model.live="metodoActividades" value="porcentaje" class="accent-[#000b60]">
                    <span class="text-sm font-semibold">Por porcentaje</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition
                              {{ $metodoActividades === 'puntos'
                                 ? 'border-[#000b60] bg-[#000b60]/5 dark:border-[#bcc2ff] dark:bg-[#bcc2ff]/10'
                                 : 'border-gray-200 dark:border-[#2a3d4a] hover:border-gray-300' }}">
                    <input type="radio" wire:model.live="metodoActividades" value="puntos" class="accent-[#000b60]">
                    <span class="text-sm font-semibold">Por puntos directos</span>
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                @if($metodoActividades === 'puntos')
                    <span class="text-orange-500 font-semibold">Por puntos:</span> cada actividad vale exactamente su punteo; no se puede superar.
                @else
                    <span class="text-blue-600 font-semibold">Por porcentaje:</span> la suma se escala al punteo del tipo (comportamiento estándar).
                @endif
            </p>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Máx. puntos extra (ruleta)
            </label>
            <div class="flex items-center gap-2">
                <input wire:model="maxPuntosExtra" type="number" min="0" max="20" step="0.5"
                       class="w-20 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-center text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <span class="text-sm text-gray-400">pts</span>
            </div>
            @error('maxPuntosExtra') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="ml-auto">
            <button wire:click="guardarConfigEvaluacion"
                    class="bg-[#000b60] text-white px-4 py-2 rounded-lg text-sm font-bold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:16px">save</span>
                Guardar configuración
            </button>
        </div>
    </div>

    {{-- Pestañas --}}
    <div class="flex flex-wrap gap-1 bg-white dark:bg-[#1e333c] rounded-xl shadow p-1 mb-5 overflow-x-auto">

        @foreach($tipos as $tipo)
        <button wire:click="$set('tab','{{ $tipo->id }}')"
                class="flex items-center gap-1.5 px-3 md:px-4 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap
                       {{ $tab == $tipo->id
                           ? 'bg-[#000b60] text-white shadow'
                           : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
            @if($tipo->esActividades())
                <span class="material-symbols-outlined" style="font-size:16px">task_alt</span>
            @else
                <span class="material-symbols-outlined" style="font-size:16px">edit_note</span>
            @endif
            <span class="hidden sm:inline">{{ $tipo->nombre }}</span>
            <span class="sm:hidden">{{ Str::limit($tipo->nombre, 6, '') }}</span>
            <span class="text-xs opacity-70 font-normal">({{ $tipo->punteo_max }})</span>
        </button>
        @endforeach

        <button wire:click="$set('tab','resumen')"
                class="flex items-center gap-1.5 px-3 md:px-4 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap ml-auto
                       {{ $tab === 'resumen'
                           ? 'bg-[#000b60] text-white shadow'
                           : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a2f3c]' }}">
            <span class="material-symbols-outlined" style="font-size:16px">leaderboard</span>
            <span class="hidden sm:inline">Resumen Final</span>
            <span class="sm:hidden">Final</span>
        </button>
    </div>

    {{-- TAB: TIPO FIJO --}}
    @if($tipoActivo && !$tipoActivo->esActividades())

    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

        <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between flex-wrap gap-2">
            <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px">edit_note</span>
                {{ $tipoActivo->nombre }} — Máx: {{ $tipoActivo->punteo_max }} pts
            </span>
            <button wire:click="guardarNotas"
                    class="bg-[#000b60] text-white px-4 py-2 rounded-lg text-sm font-bold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:16px">save</span>
                Guardar todas
            </button>
        </div>

        @if($estudiantes->isEmpty())
            <p class="text-center text-gray-400 py-12 text-sm">No hay estudiantes en esta clase.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                    <tr>
                        <th class="text-left px-4 md:px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs w-28 md:w-32">Carné</th>
                        <th class="text-left px-4 md:px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Nombre</th>
                        <th class="text-center px-4 md:px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs w-36 md:w-48">
                            Nota (0 – {{ $tipoActivo->punteo_max }})
                        </th>
                        <th class="text-center px-4 md:px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs w-20 md:w-28">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                    @foreach($estudiantes as $e)
                    @php
                        $nota     = $notas[$e->id] ?? '';
                        $pct      = $nota !== '' && $tipoActivo->punteo_max > 0
                            ? round((float)$nota / (float)$tipoActivo->punteo_max * 100) : null;
                        $pctColor = $pct === null ? 'text-gray-300' : ($pct >= 70 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500'));
                    @endphp
                    <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                        <td class="px-4 md:px-5 py-3 font-mono text-xs text-[#000b60] dark:text-[#bcc2ff]">{{ $e->carnet }}</td>
                        <td class="px-4 md:px-5 py-3 font-semibold text-sm">{{ $e->nombre }}</td>
                        <td class="px-4 md:px-5 py-3">
                            <input wire:model="notas.{{ $e->id }}"
                                   type="number"
                                   min="0" max="{{ $tipoActivo->punteo_max }}" step="0.01"
                                   placeholder="—"
                                   class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        </td>
                        <td class="px-4 md:px-5 py-3 text-center font-bold text-sm {{ $pctColor }}">
                            {{ $pct !== null ? $pct . '%' : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 bg-gray-50 dark:bg-[#162a35] border-t border-gray-100 dark:border-[#1a2f3c] flex justify-end">
            <button wire:click="guardarNotas"
                    class="bg-[#000b60] text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:16px">save</span>
                Guardar notas
            </button>
        </div>
        @endif

    </div>

    {{-- TAB: ACTIVIDADES --}}
    @elseif($tipoActivo && $tipoActivo->esActividades())

    <div class="space-y-5">

        {{-- Lista de actividades definidas --}}
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">task_alt</span>
                    Actividades — Punteo total: {{ $tipoActivo->punteo_max }} pts
                    @if($metodoActividades === 'puntos')
                        <span class="text-xs font-normal bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 px-2 py-0.5 rounded-full">
                            Puntos directos
                        </span>
                    @else
                        <span class="text-xs font-normal bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 px-2 py-0.5 rounded-full">
                            Por porcentaje
                        </span>
                    @endif
                </span>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="abrirNuevaActividad"
                            class="border border-[#000b60] dark:border-[#bcc2ff] text-[#000b60] dark:text-[#bcc2ff] px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-50 dark:hover:bg-[#0d2535] transition flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:14px">add</span>
                        Agregar
                    </button>
                    <button wire:click="abrirPlantillaModal"
                            class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:14px">download</span>
                        Plantilla Excel
                    </button>
                    <button wire:click="$set('showImportModal', true)"
                            class="bg-[#000b60] text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:14px">upload_file</span>
                        Importar notas
                    </button>
                </div>
            </div>

            @if($actividades->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                <span class="material-symbols-outlined" style="font-size:48px">assignment</span>
                <p class="mt-3 font-semibold text-gray-500 dark:text-gray-400">No hay actividades configuradas</p>
                <p class="text-sm mt-1 text-center px-4">Agrega actividades o descarga la plantilla para importarlas masivamente</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">#</th>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Nombre</th>
                            <th class="text-center px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Punteo máx.</th>
                            <th class="text-center px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Tipo</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($actividades as $act)
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                            <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ $act->orden }}</td>
                            <td class="px-5 py-3 font-semibold">{{ $act->nombre }}</td>
                            <td class="px-5 py-3 text-center font-bold text-[#000b60] dark:text-[#bcc2ff]">
                                {{ number_format($act->punteo_max, 0) }} pts
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($act->esGrupal())
                                    <span class="text-xs bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 px-2 py-0.5 rounded-full font-semibold">
                                        Grupal
                                    </span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-full">
                                        Individual
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex gap-1 justify-end">
                                    @if(!$act->esGrupal())
                                    <button wire:click="abrirEditarActividad({{ $act->id }})"
                                            class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-50 dark:hover:bg-[#0d2535] p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:16px">edit</span>
                                    </button>
                                    @endif
                                    <button wire:click="eliminarActividad({{ $act->id }})"
                                            wire:confirm="¿Eliminar la actividad '{{ addslashes($act->nombre) }}' y todas sus notas?"
                                            class="text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>

        {{-- Tabla de notas individuales --}}
        @php $actividadesIndividuales = $actividades->filter(fn($a) => !$a->esGrupal()); @endphp
        @if($actividadesIndividuales->isNotEmpty() && $estudiantes->isNotEmpty())
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between flex-wrap gap-2">
                <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">table_chart</span>
                    Notas individuales
                </span>
                <button wire:click="guardarNotasActividades"
                        class="bg-[#000b60] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">save</span>
                    Guardar notas
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="text-sm" style="min-width: max-content; width: 100%;">
                    <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs sticky left-0 bg-gray-50 dark:bg-[#162a35] min-w-[100px]">Carné</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs sticky left-[100px] bg-gray-50 dark:bg-[#162a35] min-w-[160px]">Nombre</th>
                            @foreach($actividadesIndividuales as $act)
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[130px]">
                                <p>{{ $act->nombre }}</p>
                                <p class="font-normal text-gray-400">(Máx: {{ number_format($act->punteo_max, 0) }})</p>
                            </th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[110px]">
                                {{ $metodoActividades === 'puntos' ? 'Total directo' : 'Subtotal' }} / {{ $tipoActivo->punteo_max }} pts
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($estudiantes as $e)
                        @php
                            $sumaEst = 0; $sumaMax = 0;
                            foreach($actividadesIndividuales as $act) {
                                $n = $notasActs[$act->id][$e->id] ?? '';
                                if ($n !== '') $sumaEst += (float)$n;
                                $sumaMax += (float)$act->punteo_max;
                            }
                            if ($metodoActividades === 'puntos') {
                                $subtotal = $sumaMax > 0 ? min(round($sumaEst, 2), (float)$tipoActivo->punteo_max) : null;
                            } else {
                                $subtotal = $sumaMax > 0 ? round($sumaEst / $sumaMax * (float)$tipoActivo->punteo_max, 2) : null;
                            }
                            $subColor = $subtotal === null ? 'text-gray-300' : ($subtotal >= $tipoActivo->punteo_max * 0.7 ? 'text-green-600' : ($subtotal >= $tipoActivo->punteo_max * 0.5 ? 'text-orange-500' : 'text-red-500'));
                        @endphp
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                            <td class="px-4 py-2.5 font-mono text-xs text-[#000b60] dark:text-[#bcc2ff] sticky left-0 bg-white dark:bg-[#1e333c]">{{ $e->carnet }}</td>
                            <td class="px-4 py-2.5 font-semibold sticky left-[100px] bg-white dark:bg-[#1e333c] text-sm">{{ $e->nombre }}</td>
                            @foreach($actividadesIndividuales as $act)
                            <td class="px-4 py-2.5">
                                <input wire:model="notasActs.{{ $act->id }}.{{ $e->id }}"
                                       type="number"
                                       min="0" max="{{ $act->punteo_max }}" step="0.01"
                                       placeholder="—"
                                       class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-2 py-1 text-center text-xs focus:outline-none focus:ring-2 focus:ring-[#000b60]
                                              @error('notaActs_'.$act->id.'_'.$e->id) border-red-400 @enderror">
                                @error('notaActs_'.$act->id.'_'.$e->id)
                                    <p class="text-red-500 text-[10px] mt-0.5">{{ $message }}</p>
                                @enderror
                            </td>
                            @endforeach
                            <td class="px-4 py-2.5 text-center font-black text-sm {{ $subColor }}">
                                {{ $subtotal !== null ? number_format($subtotal, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 bg-gray-50 dark:bg-[#162a35] border-t border-gray-100 dark:border-[#1a2f3c] text-xs text-gray-400">
                @if($metodoActividades === 'puntos')
                    <span class="text-orange-500 font-semibold">Puntos directos:</span>
                    Total = suma de notas (cap. en {{ $tipoActivo->punteo_max }} pts). No se puede superar el punteo de cada actividad.
                @else
                    <span class="text-blue-600 font-semibold">Porcentaje:</span>
                    Subtotal = (suma notas / suma punteos máx.) × {{ $tipoActivo->punteo_max }} pts
                @endif
            </div>
        </div>
        @endif

        {{-- Tabla de notas grupales --}}
        @php $actividadesGrupales = $actividades->filter(fn($a) => $a->esGrupal()); @endphp
        @if($actividadesGrupales->isNotEmpty())
        @foreach($actividadesGrupales as $act)
        @php $gruposDeEstaAct = $gruposPorActividad[$act->id] ?? collect(); @endphp

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

            <div class="bg-purple-600 px-5 py-3 flex items-center justify-between flex-wrap gap-2">
                <span class="font-bold text-white flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">hub</span>
                    {{ $act->nombre }}
                    <span class="text-purple-200 font-normal text-xs">(Grupal — Máx: {{ number_format($act->punteo_max, 0) }} pts)</span>
                </span>
                <button wire:click="guardarNotasGrupos"
                        class="bg-white text-purple-700 px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">save</span>
                    Guardar y propagar
                </button>
            </div>

            @if($gruposDeEstaAct->isEmpty())
            <div class="py-8 text-center text-gray-400 text-sm">
                <span class="material-symbols-outlined" style="font-size:36px">group_off</span>
                <p class="mt-2">No se encontraron grupos para esta actividad.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Grupo</th>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Integrantes</th>
                            <th class="text-center px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs w-44">
                                Nota (0 – {{ number_format($act->punteo_max, 0) }})
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($gruposDeEstaAct as $grupo)
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                            <td class="px-5 py-3 font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $grupo->nombre }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $grupo->estudiantes->pluck('nombre')->join(', ') }}
                            </td>
                            <td class="px-5 py-3">
                                <input wire:model="notasGrupos.{{ $act->id }}.{{ $grupo->id }}"
                                       type="number"
                                       min="0" max="{{ $act->punteo_max }}" step="0.01"
                                       placeholder="—"
                                       class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                              @error('notaGrupo_'.$act->id.'_'.$grupo->id) border-red-400 @enderror">
                                @error('notaGrupo_'.$act->id.'_'.$grupo->id)
                                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-2 bg-purple-50 dark:bg-purple-900/10 border-t border-purple-100 dark:border-purple-900/20 text-xs text-purple-600 dark:text-purple-300 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px">info</span>
                Al guardar, la nota se propagará automáticamente a todos los integrantes de cada grupo.
            </div>
            @endif
        </div>
        @endforeach
        @endif

    </div>

    {{-- TAB: RESUMEN FINAL --}}
    @elseif($tab === 'resumen')

    @if($estudiantes->isEmpty())
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
        <span class="material-symbols-outlined" style="font-size:64px">person_off</span>
        <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400">No hay estudiantes en esta clase</p>
    </div>
    @else
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

        <div class="bg-[#000b60] px-5 py-3 flex flex-wrap items-center justify-between gap-2">
            <span class="font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px">leaderboard</span>
                Resumen de calificaciones
            </span>
            <span class="text-white/60 text-xs">
                Aprobación: ≥ 61 pts
                @if((float)$maxPuntosExtra > 0)
                    · Puntos extra ruleta: hasta {{ $maxPuntosExtra }} pts
                @endif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="text-sm" style="min-width: max-content; width: 100%;">
                <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                    <tr>
                        <th class="text-left px-4 md:px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs sticky left-0 bg-gray-50 dark:bg-[#162a35] min-w-[100px]">Carné</th>
                        <th class="text-left px-4 md:px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs sticky left-[100px] bg-gray-50 dark:bg-[#162a35] min-w-[160px]">Nombre</th>
                        @foreach($tipos as $tipo)
                        <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[120px]">
                            <p>{{ $tipo->nombre }}</p>
                            <p class="text-gray-400 font-normal">/{{ $tipo->punteo_max }} pts</p>
                        </th>
                        @endforeach
                        @if((float)$maxPuntosExtra > 0)
                        <th class="text-center px-4 py-3 font-semibold text-amber-600 dark:text-amber-400 text-xs min-w-[100px] bg-amber-50 dark:bg-amber-900/10">
                            <p>Extra</p>
                            <p class="font-normal opacity-70">(ruleta)</p>
                        </th>
                        @endif
                        <th class="text-center px-5 py-3 font-semibold text-gray-700 dark:text-gray-300 text-xs min-w-[130px] bg-blue-50 dark:bg-[#0d2535]">
                            TOTAL / 100
                        </th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[110px]">
                            Estado
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                    @foreach($resumen as $fila)
                    @php
                        $total    = $fila['total'];
                        $aprobado = $fila['aprobado'];
                        $extra    = $fila['puntos_extra'] ?? 0;
                        $totalColor = $total >= 61 ? 'text-green-600 bg-green-50 dark:bg-green-900/20' : ($total >= 45 ? 'text-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'text-red-600 bg-red-50 dark:bg-red-900/20');
                    @endphp
                    <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                        <td class="px-4 md:px-5 py-3 font-mono text-xs text-[#000b60] dark:text-[#bcc2ff] sticky left-0 bg-white dark:bg-[#1e333c]">{{ $fila['carnet'] }}</td>
                        <td class="px-4 md:px-5 py-3 font-semibold text-sm sticky left-[100px] bg-white dark:bg-[#1e333c]">{{ $fila['nombre'] }}</td>
                        @foreach($tipos as $tipo)
                        @php
                            $d   = $fila['tipos'][$tipo->id] ?? ['pts' => null, 'max' => 0];
                            $pts = $d['pts'];
                            $max = $d['max'];
                            $pct = $pts !== null && $max > 0 ? round($pts / $max * 100) : null;
                            $c   = $pct === null ? 'text-gray-300' : ($pct >= 70 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500'));
                        @endphp
                        <td class="px-4 py-3 text-center">
                            @if($pts !== null)
                                <span class="font-bold {{ $c }}">{{ number_format($pts, 2) }}</span>
                                <span class="text-gray-400 text-xs"> ({{ $pct }}%)</span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        @endforeach
                        @if((float)$maxPuntosExtra > 0)
                        <td class="px-4 py-3 text-center bg-amber-50/50 dark:bg-amber-900/5">
                            @if($extra > 0)
                                <span class="font-bold text-amber-600 dark:text-amber-400">+{{ number_format($extra, 1) }}</span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-5 py-3 text-center bg-blue-50/50 dark:bg-blue-900/5">
                            <span class="font-black text-sm px-3 py-1 rounded-full {{ $totalColor }}">
                                {{ number_format($total, 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($aprobado === true)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-300 px-2.5 py-1 rounded-full">
                                    <span class="material-symbols-outlined" style="font-size:13px">check_circle</span>
                                    Aprobado
                                </span>
                            @elseif($aprobado === false)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-300 px-2.5 py-1 rounded-full">
                                    <span class="material-symbols-outlined" style="font-size:13px">cancel</span>
                                    Reprobado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 px-2.5 py-1 rounded-full">
                                    <span class="material-symbols-outlined" style="font-size:13px">pending</span>
                                    Pendiente
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 bg-gray-50 dark:bg-[#162a35] border-t border-gray-100 dark:border-[#1a2f3c] text-xs text-gray-400 flex flex-wrap gap-4">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> ≥ 61 pts — Aprobado</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span> 45–60 pts — En riesgo</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> &lt; 45 pts — Reprobado</span>
            @if((float)$maxPuntosExtra > 0)
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> Columna Extra = puntos de participación (ruleta)</span>
            @endif
        </div>
    </div>
    @endif

    @endif {{-- fin tab --}}
    @endif {{-- fin claseId --}}

    {{-- MODAL: Agregar / Editar Actividad individual --}}
    @if($showActModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">
                    {{ $actEditId ? 'Editar actividad' : 'Nueva actividad' }}
                </h2>
                <button wire:click="$set('showActModal', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="actNombre" type="text" placeholder="Ej. Tarea 1, Quiz, Laboratorio..."
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actNombre') border-red-400 @enderror">
                    @error('actNombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">
                        Punteo máximo <span class="text-red-500">*</span>
                        @if($metodoActividades === 'puntos')
                            <span class="text-orange-500 font-normal text-xs">(valor fijo — no se puede superar)</span>
                        @endif
                    </label>
                    <input wire:model="actPunteo" type="number" min="0.01" max="9999" step="0.01"
                           placeholder="{{ $metodoActividades === 'puntos' ? 'Ej. 5' : 'Ej. 100' }}"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actPunteo') border-red-400 @enderror">
                    @error('actPunteo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showActModal', false)"
                        class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                    Cancelar
                </button>
                <button wire:click="guardarActividad"
                        class="flex-1 bg-[#000b60] text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition text-sm">
                    {{ $actEditId ? 'Guardar cambios' : 'Agregar actividad' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Actividad grupal --}}
    @if($showActGrupalModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-black text-purple-700 dark:text-purple-300 flex items-center gap-2">
                    <span class="material-symbols-outlined">hub</span>
                    Actividad grupal
                </h2>
                <button wire:click="$set('showActGrupalModal', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Crea una actividad vinculada a los grupos de la sesión. Al calificar, la nota se propagará a todos los integrantes de cada grupo.
            </p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="actGrupalNombre" type="text" placeholder="Ej. Proyecto grupal, Exposición..."
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('actGrupalNombre') border-red-400 @enderror">
                    @error('actGrupalNombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Punteo máximo <span class="text-red-500">*</span></label>
                    <input wire:model="actGrupalPunteo" type="number" min="0.01" max="9999" step="0.01"
                           placeholder="{{ $metodoActividades === 'puntos' ? 'Ej. 10' : 'Ej. 100' }}"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 @error('actGrupalPunteo') border-red-400 @enderror">
                    @error('actGrupalPunteo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showActGrupalModal', false)"
                        class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                    Cancelar
                </button>
                <button wire:click="guardarActividadGrupal"
                        class="flex-1 bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition text-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px">hub</span>
                    Crear actividad grupal
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Wizard Plantilla Excel --}}
    @if($showPlantillaModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">

            <div class="bg-[#000b60] px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-white font-black text-lg">Plantilla de Actividades</h2>
                    <p class="text-white/60 text-xs mt-0.5">Define las actividades y descarga la plantilla para llenar</p>
                </div>
                <button wire:click="$set('showPlantillaModal', false)" class="text-white/60 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6">

                <div class="flex items-center gap-4 mb-5">
                    <label class="text-sm font-semibold text-[#000b60] dark:text-[#bcc2ff] whitespace-nowrap">¿Cuántas actividades?</label>
                    <input wire:model.live="numActs"
                           type="number" min="1" max="20"
                           class="w-24 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-center font-bold focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                    <p class="text-xs text-gray-400">Mín: 1 · Máx: 20</p>
                </div>

                <div class="overflow-y-auto max-h-72 border border-gray-100 dark:border-[#2a3d4a] rounded-xl">
                    <table class="w-full text-sm">
                        <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] sticky top-0">
                            <tr>
                                <th class="text-center px-3 py-2 font-semibold text-[#000b60] dark:text-[#bcc2ff] text-xs w-10">#</th>
                                <th class="text-left px-3 py-2 font-semibold text-[#000b60] dark:text-[#bcc2ff] text-xs">Nombre de la actividad</th>
                                <th class="text-center px-3 py-2 font-semibold text-[#000b60] dark:text-[#bcc2ff] text-xs w-40">Punteo máximo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                            @foreach($actsWizard as $i => $act)
                            <tr>
                                <td class="px-3 py-2 text-center text-gray-400 font-mono text-xs">{{ $i + 1 }}</td>
                                <td class="px-3 py-2">
                                    <input wire:model="actsWizard.{{ $i }}.nombre"
                                           type="text"
                                           placeholder="Ej. Tarea {{ $i + 1 }}, Quiz..."
                                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actsWizard.'.$i.'.nombre') border-red-400 @enderror">
                                    @error('actsWizard.'.$i.'.nombre')
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input wire:model="actsWizard.{{ $i }}.punteo_max"
                                           type="number" min="0.01" step="0.01"
                                           placeholder="{{ $metodoActividades === 'puntos' ? '5' : '100' }}"
                                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-400 mt-3 flex items-start gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">info</span>
                    Al guardar, las actividades individuales actuales serán reemplazadas. Las actividades grupales no se ven afectadas.
                </p>

            </div>

            <div class="px-6 pb-6 flex gap-3">
                <button wire:click="$set('showPlantillaModal', false)"
                        class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                    Cancelar
                </button>
                <button wire:click="descargarPlantilla"
                        class="flex-1 bg-green-600 text-white py-2.5 rounded-xl font-bold hover:opacity-90 transition text-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">download</span>
                    Guardar actividades y descargar
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- MODAL: Importar notas desde Excel --}}
    @if($showImportModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">Importar notas de actividades</h2>
                <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Sube el archivo Excel con las notas de los estudiantes. Usa la plantilla descargada para garantizar el formato correcto.
            </p>

            <div>
                <label class="block text-sm font-semibold mb-2">Archivo (.xlsx, .xls, .csv)</label>
                <input wire:model="archivoImport" type="file" accept=".xlsx,.xls,.csv"
                       class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none">
                @error('archivoImport') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showImportModal', false)"
                        class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition text-sm">
                    Cancelar
                </button>
                <button wire:click="importarActividades"
                        class="flex-1 bg-[#000b60] text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition text-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px">upload_file</span>
                    Importar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
