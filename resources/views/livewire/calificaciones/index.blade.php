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
                        $nota      = $notas[$e->id] ?? '';
                        $locked    = !$esAdmin && $nota !== '';
                        $pct       = $nota !== '' && $tipoActivo->punteo_max > 0
                            ? round((float)$nota / (float)$tipoActivo->punteo_max * 100) : null;
                        $pctColor  = $pct === null ? 'text-gray-300' : ($pct >= 70 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500'));
                    @endphp
                    <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                        <td class="px-4 md:px-5 py-3 font-mono text-xs text-[#000b60] dark:text-[#bcc2ff]">{{ $e->carnet }}</td>
                        <td class="px-4 md:px-5 py-3 font-semibold text-sm">{{ $e->nombre }}</td>
                        <td class="px-4 md:px-5 py-3">
                            @if($locked)
                                <div class="flex items-center justify-center gap-1.5 text-sm font-bold text-[#000b60] dark:text-[#bcc2ff]">
                                    {{ $nota }}
                                    <span class="material-symbols-outlined text-gray-400 dark:text-gray-500" style="font-size:14px" title="Solo el administrador puede modificar esta nota">lock</span>
                                </div>
                            @else
                                <input wire:model.blur="notas.{{ $e->id }}"
                                       type="number"
                                       min="0" max="{{ $tipoActivo->punteo_max }}" step="0.01"
                                       placeholder="—"
                                       x-on:input="let v=parseFloat($el.value),mx={{ (float)$tipoActivo->punteo_max }};if(!isNaN(v)&&v>mx)$el.value=mx;if(!isNaN(v)&&v<0)$el.value=0;"
                                       class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                            @endif
                        </td>
                        <td class="px-4 md:px-5 py-3 text-center font-bold text-sm {{ $pctColor }}">
                            {{ $pct !== null ? $pct . '%' : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!$esAdmin && $notasGuardadas)
        <div class="px-5 py-3 bg-amber-50 dark:bg-amber-900/10 border-t border-amber-100 dark:border-amber-900/20 flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
            <span class="material-symbols-outlined" style="font-size:15px">lock</span>
            Notas guardadas y bloqueadas. Si hay un error, repórtalo al administrador del sistema para que realice las correcciones.
        </div>
        @else
        <div class="px-5 py-3 bg-gray-50 dark:bg-[#162a35] border-t border-gray-100 dark:border-[#1a2f3c] flex justify-end">
            <button wire:click="guardarNotas"
                    class="bg-[#000b60] text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:16px">save</span>
                Guardar notas
            </button>
        </div>
        @endif
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
                    <span class="text-xs font-normal bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 px-2 py-0.5 rounded-full">
                        Promedio (sobre 100)
                    </span>
                </span>
                @if($gradoCerrada)
                <span class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 font-semibold">
                    <span class="material-symbols-outlined" style="font-size:15px">lock</span>
                    Ciclo cerrado — no se pueden agregar más actividades
                </span>
                @else
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
                @endif
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
                            <th class="text-center px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">Tipo</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($actividades as $act)
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                            <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ $act->orden }}</td>
                            <td class="px-5 py-3 font-semibold">{{ $act->nombre }}</td>
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

        {{-- Tabla de notas individuales (incluye grupales como solo lectura) --}}
        @php
            $actividadesIndividuales = $actividades->filter(fn($a) => !$a->esGrupal());
            $actividadesGrupalesTabla = $actividades->filter(fn($a) => $a->esGrupal());
            $todasParaTabla = $actividades; // todas: individuales editables + grupales solo lectura
        @endphp
        @if($todasParaTabla->isNotEmpty() && $estudiantes->isNotEmpty())
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] px-5 py-3 flex items-center justify-between flex-wrap gap-2">
                <span class="font-bold text-[#000b60] dark:text-[#bcc2ff] flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">table_chart</span>
                    Notas individuales
                </span>
                @if($esAdmin || !$notasActsGuardadas)
                <button wire:click="guardarNotasActividades"
                        class="bg-[#000b60] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">save</span>
                    Guardar notas
                </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="text-sm" style="min-width: max-content; width: 100%;">
                    <thead class="bg-gray-50 dark:bg-[#162a35] border-b border-gray-100 dark:border-[#1a2f3c]">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs sticky left-0 bg-gray-50 dark:bg-[#162a35] min-w-[100px]">Carné</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs sticky left-[100px] bg-gray-50 dark:bg-[#162a35] min-w-[160px]">Nombre</th>
                            @foreach($todasParaTabla as $act)
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[130px]">
                                <p>{{ $act->nombre }}</p>
                                @if($act->esGrupal())
                                    <span class="inline-block text-[10px] bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 px-1.5 rounded-full font-semibold">Grupal</span>
                                @else
                                    <p class="font-normal text-gray-400">/100</p>
                                @endif
                            </th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[110px]">
                                Promedio / {{ $tipoActivo->punteo_max }} pts
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($estudiantes as $e)
                        @php
                            $sumaEst = 0; $sumaMax = 0;
                            foreach($todasParaTabla as $act) {
                                $n = $notasActs[$act->id][$e->id] ?? '';
                                if ($n !== '') $sumaEst += (float)$n;
                                $sumaMax += (float)$act->punteo_max;
                            }
                            $subtotal = $sumaMax > 0 ? round($sumaEst / $sumaMax * (float)$tipoActivo->punteo_max, 2) : null;
                            $subColor = $subtotal === null ? 'text-gray-300' : ($subtotal >= $tipoActivo->punteo_max * 0.7 ? 'text-green-600' : ($subtotal >= $tipoActivo->punteo_max * 0.5 ? 'text-orange-500' : 'text-red-500'));
                        @endphp
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]">
                            <td class="px-4 py-2.5 font-mono text-xs text-[#000b60] dark:text-[#bcc2ff] sticky left-0 bg-white dark:bg-[#1e333c]">{{ $e->carnet }}</td>
                            <td class="px-4 py-2.5 font-semibold sticky left-[100px] bg-white dark:bg-[#1e333c] text-sm">{{ $e->nombre }}</td>
                            @foreach($todasParaTabla as $act)
                            @php
                                $notaAct = $notasActs[$act->id][$e->id] ?? '';
                                $esGrupalAct = $act->esGrupal();
                                $lockedAct = $esGrupalAct || (!$esAdmin && $notaAct !== '');
                            @endphp
                            <td class="px-4 py-2.5 {{ $esGrupalAct ? 'bg-purple-50/40 dark:bg-purple-900/5' : '' }}">
                                @if($lockedAct)
                                    <div class="flex items-center justify-center gap-1 text-xs font-bold {{ $esGrupalAct ? 'text-purple-700 dark:text-purple-300' : 'text-[#000b60] dark:text-[#bcc2ff]' }}">
                                        {{ $notaAct !== '' ? $notaAct : '—' }}
                                        <span class="material-symbols-outlined {{ $esGrupalAct ? 'text-purple-400' : 'text-gray-400 dark:text-gray-500' }}" style="font-size:12px"
                                              title="{{ $esGrupalAct ? 'Nota propagada del grupo' : 'Solo el administrador puede modificar esta nota' }}">
                                            {{ $esGrupalAct ? 'hub' : 'lock' }}
                                        </span>
                                    </div>
                                @else
                                    <input wire:model.blur="notasActs.{{ $act->id }}.{{ $e->id }}"
                                           type="number"
                                           min="0" max="{{ $act->punteo_max }}" step="0.01"
                                           placeholder="—"
                                           x-on:input="let v=parseFloat($el.value),mx={{ (float)$act->punteo_max }};if(!isNaN(v)&&v>mx)$el.value=mx;if(!isNaN(v)&&v<0)$el.value=0;"
                                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-2 py-1 text-center text-xs focus:outline-none focus:ring-2 focus:ring-[#000b60]
                                                  @error('notaActs_'.$act->id.'_'.$e->id) border-red-400 @enderror">
                                    @error('notaActs_'.$act->id.'_'.$e->id)
                                        <p class="text-red-500 text-[10px] mt-0.5">{{ $message }}</p>
                                    @enderror
                                @endif
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

            @if(!$esAdmin && $notasActsGuardadas)
            <div class="px-5 py-3 bg-amber-50 dark:bg-amber-900/10 border-t border-amber-100 dark:border-amber-900/20 flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                <span class="material-symbols-outlined" style="font-size:15px">lock</span>
                Notas guardadas y bloqueadas. Si hay un error, repórtalo al administrador del sistema para que realice las correcciones.
            </div>
            @else
            <div class="px-5 py-3 bg-gray-50 dark:bg-[#162a35] border-t border-gray-100 dark:border-[#1a2f3c] text-xs text-gray-400 flex flex-wrap gap-3">
                <span><span class="text-blue-600 dark:text-blue-400 font-semibold">Promedio:</span> Cada actividad vale sobre 100. El promedio de todas se escala al punteo del tipo ({{ $tipoActivo->punteo_max }} pts).</span>
                @if($actividadesGrupalesTabla->isNotEmpty())
                <span class="flex items-center gap-1 text-purple-600 dark:text-purple-400">
                    <span class="material-symbols-outlined" style="font-size:13px">hub</span>
                    Las columnas Grupales muestran notas propagadas desde los grupos — solo lectura.
                </span>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- Tabla de notas grupales --}}
        @php $actividadesGrupales = $actividades->filter(fn($a) => $a->esGrupal()); @endphp
        @if($actividadesGrupales->isNotEmpty())
        @foreach($actividadesGrupales as $act)
        @php
            $gruposDeEstaAct = $gruposPorActividad[$act->id] ?? collect();
        @endphp

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

            <div class="bg-purple-600 px-5 py-3 flex items-center justify-between flex-wrap gap-2">
                <span class="font-bold text-white flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">hub</span>
                    {{ $act->nombre }}
                    <span class="text-purple-200 font-normal text-xs">(Grupal — Máx: {{ number_format($act->punteo_max, 0) }} pts)</span>
                </span>
                @if($esAdmin || !$notasGruposGuardadas)
                <button wire:click="guardarNotasGrupos"
                        class="bg-white text-purple-700 px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">save</span>
                    Guardar y propagar
                </button>
                @endif
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
                            @php $notaGrp = $notasGrupos[$act->id][$grupo->id] ?? ''; $lockedGrp = !$esAdmin && $notaGrp !== ''; @endphp
                            <td class="px-5 py-3">
                                @if($lockedGrp)
                                    <div class="flex items-center justify-center gap-1.5 text-sm font-bold text-[#000b60] dark:text-[#bcc2ff]">
                                        {{ $notaGrp }}
                                        <span class="material-symbols-outlined text-gray-400 dark:text-gray-500" style="font-size:14px" title="Solo el administrador puede modificar esta nota">lock</span>
                                    </div>
                                @else
                                    <input wire:model.blur="notasGrupos.{{ $act->id }}.{{ $grupo->id }}"
                                           type="number"
                                           min="0" max="{{ $act->punteo_max }}" step="0.01"
                                           placeholder="—"
                                           x-on:input="let v=parseFloat($el.value),mx={{ (float)$act->punteo_max }};if(!isNaN(v)&&v>mx)$el.value=mx;if(!isNaN(v)&&v<0)$el.value=0;"
                                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-purple-500
                                                  @error('notaGrupo_'.$act->id.'_'.$grupo->id) border-red-400 @enderror">
                                    @error('notaGrupo_'.$act->id.'_'.$grupo->id)
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(!$esAdmin && $notasGruposGuardadas)
            <div class="px-5 py-2 bg-amber-50 dark:bg-amber-900/10 border-t border-amber-100 dark:border-amber-900/20 text-xs text-amber-700 dark:text-amber-400 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px">lock</span>
                Notas guardadas y bloqueadas. Si hay un error, repórtalo al administrador del sistema para que realice las correcciones.
            </div>
            @else
            <div class="px-5 py-2 bg-purple-50 dark:bg-purple-900/10 border-t border-purple-100 dark:border-purple-900/20 text-xs text-purple-600 dark:text-purple-300 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px">info</span>
                Al guardar, la nota se propagará automáticamente a todos los integrantes de cada grupo.
            </div>
            @endif
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
            <div class="flex items-center gap-3">
                <span class="text-white/60 text-xs">Aprobación: ≥ 61 pts</span>
                <a href="{{ route('calificaciones.acta-pdf', $claseId) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 bg-white/15 hover:bg-white/25 border border-white/30 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                    <span class="material-symbols-outlined" style="font-size:15px">picture_as_pdf</span>
                    Descargar Acta PDF
                </a>
            </div>
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
                        $total      = $fila['total'];
                        $aprobado   = $fila['aprobado'];
                        $totalColor = $total >= 61 ? 'text-green-600 bg-green-50 dark:bg-green-900/20' : 'text-red-600 bg-red-50 dark:bg-red-900/20';
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
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Aprobado</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Reprobado</span>
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
                <p class="text-xs text-gray-400 flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">info</span>
                    Todas las actividades se califican sobre 100 puntos.
                </p>
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
                <p class="text-xs text-gray-400 flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">info</span>
                    La actividad se calificará sobre 100 puntos y propagará la nota a todos los integrantes.
                </p>
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
                                <th class="text-center px-3 py-2 font-semibold text-[#000b60] dark:text-[#bcc2ff] text-xs w-28">Punteo</th>
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
                                <td class="px-3 py-2 text-center text-xs font-bold text-[#000b60] dark:text-[#bcc2ff]">
                                    100 pts
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
