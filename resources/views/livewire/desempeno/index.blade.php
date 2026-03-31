<div>

    {{-- Encabezado --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Desempeño de Estudiantes</h1>
            <p class="text-sm text-gray-500">Ranking basado en asistencia, participación y calificaciones</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-sm font-semibold text-[#000b60]">Clase:</label>
            <select wire:model.live="claseId"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[220px]">
                <option value="">— Selecciona una clase —</option>
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(!$claseId)

        <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="material-symbols-outlined" style="font-size:64px">leaderboard</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">Selecciona una clase para ver el ranking</p>
        </div>

    @elseif($ranking->isEmpty())

        <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="material-symbols-outlined" style="font-size:64px">person_off</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">No hay estudiantes en esta clase</p>
        </div>

    @else

        {{-- Métricas globales --}}
        @php
            $totalEstudiantes  = $ranking->count();
            $pctPromedio       = $ranking->avg('pct_asistencia');
            $totalParticip     = $ranking->sum('participaciones');
            $promedioCalif     = $ranking->whereNotNull('promedio')->avg('promedio');
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <div class="bg-white rounded-xl shadow px-5 py-4">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Estudiantes</p>
                <p class="text-3xl font-black text-[#000b60]">{{ $totalEstudiantes }}</p>
            </div>

            <div class="bg-white rounded-xl shadow px-5 py-4">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Sesiones totales</p>
                <p class="text-3xl font-black text-[#000b60]">{{ $totalSesiones }}</p>
            </div>

            <div class="bg-white rounded-xl shadow px-5 py-4">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Asistencia promedio</p>
                <p class="text-3xl font-black {{ $pctPromedio >= 75 ? 'text-green-600' : ($pctPromedio >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                    {{ round($pctPromedio) }}%
                </p>
            </div>

            <div class="bg-white rounded-xl shadow px-5 py-4">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Participaciones</p>
                <p class="text-3xl font-black text-[#000b60]">{{ $totalParticip }}</p>
                @if($promedioCalif !== null)
                    <p class="text-xs text-gray-400 mt-0.5">Calif. prom. <span class="font-bold text-blue-600">{{ number_format($promedioCalif, 1) }}</span></p>
                @endif
            </div>

        </div>

        {{-- Tabla ranking --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            {{-- Toolbar --}}
            <div class="bg-[#e6f6ff] px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                <span class="font-bold text-[#000b60] flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined" style="font-size:18px">leaderboard</span>
                    Ranking de estudiantes
                </span>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                    <span>Ordenar por:</span>
                    <button wire:click="$set('ordenar','asistencia')"
                            class="px-3 py-1 rounded-full transition {{ $ordenar === 'asistencia' ? 'bg-[#000b60] text-white' : 'bg-white hover:bg-gray-100' }}">
                        Asistencia
                    </button>
                    <button wire:click="$set('ordenar','participaciones')"
                            class="px-3 py-1 rounded-full transition {{ $ordenar === 'participaciones' ? 'bg-[#000b60] text-white' : 'bg-white hover:bg-gray-100' }}">
                        Participaciones
                    </button>
                    <button wire:click="$set('ordenar','calificacion')"
                            class="px-3 py-1 rounded-full transition {{ $ordenar === 'calificacion' ? 'bg-[#000b60] text-white' : 'bg-white hover:bg-gray-100' }}">
                        Calificación
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-center px-4 py-3 font-semibold text-gray-400 text-xs w-12">#</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs">Estudiante</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs">Asistencias</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs min-w-[160px]">% Asistencia</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs">Participaciones</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs">Calif. prom.</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500 text-xs">Rendimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($ranking as $pos => $e)
                            @php
                                $pct = $e['pct_asistencia'];
                                $barColor = $pct >= 75 ? 'bg-green-500' : ($pct >= 50 ? 'bg-orange-400' : 'bg-red-400');
                                $textColor = $pct >= 75 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500');

                                // Rendimiento general (0-100): 60% asistencia + 40% calificación
                                $scoreAsist  = $pct;
                                $scoreCalif  = $e['promedio'] !== null ? ($e['promedio'] / 10 * 100) : null;
                                if ($scoreCalif !== null) {
                                    $rendimiento = round($scoreAsist * 0.6 + $scoreCalif * 0.4);
                                } else {
                                    $rendimiento = round($scoreAsist * 0.6);
                                }
                                $rendColor = $rendimiento >= 75 ? 'text-green-600 bg-green-50' : ($rendimiento >= 50 ? 'text-orange-500 bg-orange-50' : 'text-red-500 bg-red-50');
                            @endphp
                            <tr class="hover:bg-[#f3faff] transition {{ $pos === 0 ? 'bg-yellow-50/50' : '' }}">

                                {{-- Posición --}}
                                <td class="px-4 py-3 text-center">
                                    @if($pos === 0)
                                        <span class="text-yellow-500 text-lg">🥇</span>
                                    @elseif($pos === 1)
                                        <span class="text-gray-400 text-lg">🥈</span>
                                    @elseif($pos === 2)
                                        <span class="text-amber-600 text-lg">🥉</span>
                                    @else
                                        <span class="text-gray-300 font-bold text-xs">{{ $pos + 1 }}</span>
                                    @endif
                                </td>

                                {{-- Estudiante --}}
                                <td class="px-4 py-3">
                                    <p class="font-bold {{ $pos === 0 ? 'text-[#000b60]' : '' }}">{{ $e['nombre'] }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $e['carnet'] }}</p>
                                </td>

                                {{-- Asistencias --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-[#000b60]">{{ $e['asistencias'] }}</span>
                                    <span class="text-gray-400 text-xs"> / {{ $totalSesiones }}</span>
                                </td>

                                {{-- Barra % asistencia --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500"
                                                 style="width: {{ $pct }}%">
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold {{ $textColor }} w-10 text-right">{{ $pct }}%</span>
                                    </div>
                                </td>

                                {{-- Participaciones --}}
                                <td class="px-4 py-3 text-center">
                                    @if($e['participaciones'] > 0)
                                        <span class="bg-blue-100 text-blue-700 font-bold text-xs px-2 py-0.5 rounded-full">
                                            {{ $e['participaciones'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Calificación promedio --}}
                                <td class="px-4 py-3 text-center">
                                    @if($e['promedio'] !== null)
                                        @php
                                            $califColor = $e['promedio'] >= 7
                                                ? 'bg-green-100 text-green-700'
                                                : ($e['promedio'] >= 5 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700');
                                        @endphp
                                        <span class="font-bold text-xs px-2 py-0.5 rounded-full {{ $califColor }}">
                                            {{ number_format($e['promedio'], 1) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Rendimiento --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="font-black text-xs px-2.5 py-1 rounded-full {{ $rendColor }}">
                                        {{ $rendimiento }}%
                                    </span>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Leyenda --}}
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Asistencia ≥ 75%</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span> Asistencia 50–74%</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span> Asistencia &lt; 50%</span>
                <span class="ml-auto">Rendimiento = 60% asistencia + 40% calificación promedio</span>
            </div>

        </div>

    @endif

</div>
