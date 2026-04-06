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

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- CABECERA                                                   --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap justify-between items-center gap-3 mb-5">
        <div>
            <h1 class="text-3xl font-extrabold">Calificaciones</h1>
            <p class="text-sm text-gray-500">Gestión de notas por tipo de evaluación</p>
        </div>

        <select wire:model.live="claseId"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[240px]">
            <option value="">— Selecciona una clase —</option>
            @foreach($clases as $clase)
                <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
            @endforeach
        </select>
    </div>

    @if(!$claseId)
    <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-32 text-gray-400">
        <span class="material-symbols-outlined" style="font-size:72px">grading</span>
        <p class="mt-4 font-semibold text-gray-500 text-xl">Selecciona una clase para gestionar calificaciones</p>
    </div>

    @else

    {{-- Pestañas --}}
    <div class="flex flex-wrap gap-1 bg-white rounded-xl shadow p-1 mb-5 overflow-x-auto">

        {{-- Tipos (en orden) --}}
        @foreach($tipos as $tipo)
        <button wire:click="$set('tab','{{ $tipo->id }}')"
                class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap
                       {{ $tab == $tipo->id
                           ? 'bg-[#000b60] text-white shadow'
                           : 'text-gray-500 hover:bg-gray-100' }}">
            @if($tipo->esActividades())
                <span class="material-symbols-outlined" style="font-size:16px">task_alt</span>
            @else
                <span class="material-symbols-outlined" style="font-size:16px">edit_note</span>
            @endif
            {{ $tipo->nombre }}
            <span class="text-xs opacity-70 font-normal">({{ $tipo->punteo_max }} pts)</span>
        </button>
        @endforeach

        {{-- Resumen --}}
        <button wire:click="$set('tab','resumen')"
                class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap ml-auto
                       {{ $tab === 'resumen'
                           ? 'bg-[#000b60] text-white shadow'
                           : 'text-gray-500 hover:bg-gray-100' }}">
            <span class="material-symbols-outlined" style="font-size:16px">leaderboard</span>
            Resumen Final
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: TIPO FIJO (Parcial 1, Parcial 2, Proyecto, Examen Final) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if($tipoActivo && !$tipoActivo->esActividades())

    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="bg-[#e6f6ff] px-5 py-3 flex items-center justify-between">
            <span class="font-bold text-[#000b60] flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px">edit_note</span>
                {{ $tipoActivo->nombre }} — Punteo máximo: {{ $tipoActivo->punteo_max }} pts
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
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs w-32">Carné</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs">Nombre</th>
                        <th class="text-center px-5 py-3 font-semibold text-gray-500 text-xs w-48">
                            Nota (0 – {{ $tipoActivo->punteo_max }})
                        </th>
                        <th class="text-center px-5 py-3 font-semibold text-gray-500 text-xs w-28">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($estudiantes as $e)
                    @php
                        $nota     = $notas[$e->id] ?? '';
                        $pct      = $nota !== '' && $tipoActivo->punteo_max > 0
                            ? round((float)$nota / (float)$tipoActivo->punteo_max * 100) : null;
                        $pctColor = $pct === null ? 'text-gray-300' : ($pct >= 70 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500'));
                    @endphp
                    <tr class="hover:bg-[#f3faff]">
                        <td class="px-5 py-3 font-mono text-xs text-[#000b60]">{{ $e->carnet }}</td>
                        <td class="px-5 py-3 font-semibold">{{ $e->nombre }}</td>
                        <td class="px-5 py-3">
                            <input wire:model="notas.{{ $e->id }}"
                                   type="number"
                                   min="0" max="{{ $tipoActivo->punteo_max }}" step="0.01"
                                   placeholder="—"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                        </td>
                        <td class="px-5 py-3 text-center font-bold text-sm {{ $pctColor }}">
                            {{ $pct !== null ? $pct . '%' : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button wire:click="guardarNotas"
                    class="bg-[#000b60] text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:16px">save</span>
                Guardar notas
            </button>
        </div>
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: ACTIVIDADES                                               --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @elseif($tipoActivo && $tipoActivo->esActividades())

    <div class="space-y-5">

        {{-- Panel superior: gestión de actividades --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <div class="bg-[#e6f6ff] px-5 py-3 flex items-center justify-between">
                <span class="font-bold text-[#000b60] flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">task_alt</span>
                    Actividades definidas — Punteo total: {{ $tipoActivo->punteo_max }} pts
                </span>
                <div class="flex gap-2">
                    <button wire:click="abrirNuevaActividad"
                            class="border border-[#000b60] text-[#000b60] px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-50 transition flex items-center gap-1">
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
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <span class="material-symbols-outlined" style="font-size:48px">assignment</span>
                <p class="mt-3 font-semibold text-gray-500">No hay actividades configuradas</p>
                <p class="text-sm mt-1">Agrega actividades o descarga la plantilla para importarlas masivamente</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs">#</th>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs">Nombre</th>
                            <th class="text-center px-5 py-3 font-semibold text-gray-500 text-xs">Punteo máx.</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($actividades as $act)
                        <tr class="hover:bg-[#f3faff]">
                            <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ $act->orden }}</td>
                            <td class="px-5 py-3 font-semibold">{{ $act->nombre }}</td>
                            <td class="px-5 py-3 text-center font-bold text-[#000b60]">
                                {{ number_format($act->punteo_max, 0) }} pts
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex gap-1 justify-end">
                                    <button wire:click="abrirEditarActividad({{ $act->id }})"
                                            class="text-[#000b60] hover:bg-blue-50 p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:16px">edit</span>
                                    </button>
                                    <button wire:click="eliminarActividad({{ $act->id }})"
                                            wire:confirm="¿Eliminar la actividad '{{ addslashes($act->nombre) }}' y todas sus notas?"
                                            class="text-red-400 hover:bg-red-50 p-1.5 rounded-lg transition">
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

        {{-- Tabla de notas por actividad --}}
        @if($actividades->isNotEmpty() && $estudiantes->isNotEmpty())
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <div class="bg-[#e6f6ff] px-5 py-3 flex items-center justify-between">
                <span class="font-bold text-[#000b60] flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">table_chart</span>
                    Registro de notas por actividad
                </span>
                <button wire:click="guardarNotasActividades"
                        class="bg-[#000b60] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">save</span>
                    Guardar notas
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="text-sm" style="min-width: max-content; width: 100%;">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs sticky left-0 bg-gray-50 min-w-[120px]">Carné</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs sticky left-[120px] bg-gray-50 min-w-[200px]">Nombre</th>
                            @foreach($actividades as $act)
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs min-w-[140px]">
                                <p>{{ $act->nombre }}</p>
                                <p class="font-normal text-gray-400">(Max: {{ number_format($act->punteo_max, 0) }})</p>
                            </th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs min-w-[100px]">
                                Subtotal / {{ $tipoActivo->punteo_max }} pts
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($estudiantes as $e)
                        @php
                            $sumaEst = 0;
                            $sumaMax = 0;
                            foreach($actividades as $act) {
                                $n = $notasActs[$act->id][$e->id] ?? '';
                                if ($n !== '') $sumaEst += (float)$n;
                                $sumaMax += (float)$act->punteo_max;
                            }
                            $subtotal = $sumaMax > 0 ? round($sumaEst / $sumaMax * (float)$tipoActivo->punteo_max, 2) : null;
                            $subColor = $subtotal === null ? 'text-gray-300' : ($subtotal >= $tipoActivo->punteo_max * 0.7 ? 'text-green-600' : ($subtotal >= $tipoActivo->punteo_max * 0.5 ? 'text-orange-500' : 'text-red-500'));
                        @endphp
                        <tr class="hover:bg-[#f3faff]">
                            <td class="px-4 py-2.5 font-mono text-xs text-[#000b60] sticky left-0 bg-white">{{ $e->carnet }}</td>
                            <td class="px-4 py-2.5 font-semibold sticky left-[120px] bg-white">{{ $e->nombre }}</td>
                            @foreach($actividades as $act)
                            <td class="px-4 py-2.5">
                                <input wire:model="notasActs.{{ $act->id }}.{{ $e->id }}"
                                       type="number"
                                       min="0" max="{{ $act->punteo_max }}" step="0.01"
                                       placeholder="—"
                                       class="w-full border border-gray-200 rounded-lg px-2 py-1 text-center text-xs focus:outline-none focus:ring-2 focus:ring-[#000b60]">
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

            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400">
                Subtotal = (suma de notas del estudiante / suma de punteos máx.) × {{ $tipoActivo->punteo_max }} pts
            </div>
        </div>
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: RESUMEN FINAL                                             --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'resumen')

    @if($estudiantes->isEmpty())
    <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
        <span class="material-symbols-outlined" style="font-size:64px">person_off</span>
        <p class="mt-4 font-semibold text-gray-500">No hay estudiantes en esta clase</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="bg-[#000b60] px-5 py-3 flex items-center justify-between">
            <span class="font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px">leaderboard</span>
                Resumen de calificaciones — Total sobre 100 pts
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="text-sm" style="min-width: max-content; width: 100%;">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs sticky left-0 bg-gray-50 min-w-[120px]">Carné</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs sticky left-[120px] bg-gray-50 min-w-[200px]">Nombre</th>
                        @foreach($tipos as $tipo)
                        <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs min-w-[130px]">
                            <p>{{ $tipo->nombre }}</p>
                            <p class="text-gray-400 font-normal">/{{ $tipo->punteo_max }} pts</p>
                        </th>
                        @endforeach
                        <th class="text-center px-5 py-3 font-semibold text-gray-700 text-xs min-w-[120px] bg-blue-50">
                            TOTAL / 100
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($resumen as $fila)
                    @php
                        $total      = $fila['total'];
                        $totalColor = $total >= 70 ? 'text-green-600 bg-green-50' : ($total >= 50 ? 'text-orange-500 bg-orange-50' : 'text-red-600 bg-red-50');
                    @endphp
                    <tr class="hover:bg-[#f3faff]">
                        <td class="px-5 py-3 font-mono text-xs text-[#000b60] sticky left-0 bg-white">{{ $fila['carnet'] }}</td>
                        <td class="px-5 py-3 font-semibold sticky left-[120px] bg-white">{{ $fila['nombre'] }}</td>
                        @foreach($tipos as $tipo)
                        @php
                            $d    = $fila['tipos'][$tipo->id] ?? ['pts' => null, 'max' => 0];
                            $pts  = $d['pts'];
                            $max  = $d['max'];
                            $pct  = $pts !== null && $max > 0 ? round($pts / $max * 100) : null;
                            $c    = $pct === null ? 'text-gray-300' : ($pct >= 70 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500'));
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
                        <td class="px-5 py-3 text-center">
                            <span class="font-black text-sm px-3 py-1 rounded-full {{ $totalColor }}">
                                {{ number_format($total, 2) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400 flex flex-wrap gap-4">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> ≥ 70 pts — Aprobado</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span> 50–69 pts — En riesgo</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> &lt; 50 pts — Reprobado</span>
        </div>
    </div>
    @endif

    @endif {{-- fin tab --}}
    @endif {{-- fin claseId --}}

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Agregar / Editar Actividad                               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showActModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60]">
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
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actNombre') border-red-400 @enderror">
                    @error('actNombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Punteo máximo <span class="text-red-500">*</span></label>
                    <input wire:model="actPunteo" type="number" min="0.01" max="9999" step="0.01"
                           placeholder="Ej. 100"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actPunteo') border-red-400 @enderror">
                    @error('actPunteo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showActModal', false)"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-lg hover:bg-gray-50 font-semibold transition text-sm">
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

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Wizard Plantilla Excel                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showPlantillaModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden">

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

                {{-- Número de actividades --}}
                <div class="flex items-center gap-4 mb-5">
                    <label class="text-sm font-semibold text-[#000b60] whitespace-nowrap">¿Cuántas actividades?</label>
                    <input wire:model.live="numActs"
                           type="number" min="1" max="20"
                           class="w-24 border border-gray-200 rounded-lg px-3 py-2 text-center font-bold focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                    <p class="text-xs text-gray-400">Mín: 1 · Máx: 20</p>
                </div>

                {{-- Tabla de definición --}}
                <div class="overflow-y-auto max-h-72 border border-gray-100 rounded-xl">
                    <table class="w-full text-sm">
                        <thead class="bg-[#e6f6ff] sticky top-0">
                            <tr>
                                <th class="text-center px-3 py-2 font-semibold text-[#000b60] text-xs w-10">#</th>
                                <th class="text-left px-3 py-2 font-semibold text-[#000b60] text-xs">Nombre de la actividad</th>
                                <th class="text-center px-3 py-2 font-semibold text-[#000b60] text-xs w-40">Punteo máximo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($actsWizard as $i => $act)
                            <tr>
                                <td class="px-3 py-2 text-center text-gray-400 font-mono text-xs">{{ $i + 1 }}</td>
                                <td class="px-3 py-2">
                                    <input wire:model="actsWizard.{{ $i }}.nombre"
                                           type="text"
                                           placeholder="Ej. Tarea {{ $i + 1 }}, Quiz..."
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('actsWizard.'.$i.'.nombre') border-red-400 @enderror">
                                    @error('actsWizard.'.$i.'.nombre')
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input wire:model="actsWizard.{{ $i }}.punteo_max"
                                           type="number" min="0.01" step="0.01"
                                           placeholder="100"
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-400 mt-3 flex items-start gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">info</span>
                    Al guardar, las actividades actuales de esta clase serán reemplazadas por las definidas aquí.
                    Los estudiantes ingresarán notas de 0 hasta el punteo máximo de cada actividad.
                </p>

            </div>

            <div class="px-6 pb-6 flex gap-3">
                <button wire:click="$set('showPlantillaModal', false)"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl hover:bg-gray-50 font-semibold transition text-sm">
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

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Importar notas desde Excel                               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showImportModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60]">Importar notas de actividades</h2>
                <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-4">
                Sube el archivo Excel con las notas de los estudiantes. Usa la plantilla descargada para garantizar el formato correcto.
            </p>

            <div>
                <label class="block text-sm font-semibold mb-2">Archivo (.xlsx, .xls, .csv)</label>
                <input wire:model="archivoImport" type="file" accept=".xlsx,.xls,.csv"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none">
                @error('archivoImport') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showImportModal', false)"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-lg hover:bg-gray-50 font-semibold transition text-sm">
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
