<div wire:poll.5s>

    {{-- Encabezado --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Asistencia con QR</h1>
            <p class="text-sm text-gray-500">Control de asistencia por sesión</p>
        </div>

        {{-- Selector de clase --}}
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

        {{-- Sin clase seleccionada --}}
        <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="material-symbols-outlined" style="font-size:64px">qr_code_2</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">Selecciona una clase para comenzar</p>
        </div>

    @elseif(!$sesion)

        {{-- Sin sesión hoy --}}
        <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="material-symbols-outlined" style="font-size:64px">event_available</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">No hay sesión activa para hoy</p>
            <p class="text-sm mt-1">{{ now()->translatedFormat('l, d \d\e F Y') }}</p>
            <a href="{{ route('sesiones.index') }}"
               class="mt-6 bg-[#000b60] text-white px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px">calendar_add_on</span>
                Crear sesión en Módulo de Sesiones
            </a>
        </div>

    @elseif($sesion->finalizada)

        {{-- Sesión finalizada --}}
        <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="material-symbols-outlined" style="font-size:64px">lock</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">La sesión de hoy está finalizada</p>
            <p class="text-sm mt-1">No se puede registrar asistencia en una sesión finalizada</p>
            <a href="{{ route('sesiones.index') }}"
               class="mt-6 border border-gray-300 text-gray-600 px-6 py-2.5 rounded-lg font-semibold hover:bg-gray-50 transition flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined" style="font-size:18px">lock_open</span>
                Reabrir desde Módulo de Sesiones
            </a>
        </div>

    @else

        {{-- Sesión activa --}}
        <div class="bg-[#e6f6ff] rounded-xl px-5 py-3 mb-5 flex items-center justify-between">
            <div class="flex items-center gap-2 text-[#000b60]">
                <span class="material-symbols-outlined" style="font-size:18px">calendar_today</span>
                <span class="font-bold">Sesión del {{ $sesion->fecha->translatedFormat('d/m/Y') }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="font-bold text-[#000b60]">{{ $asistentes->count() }}/{{ $totalEstudiantes }}</span>
                <span class="text-gray-500">presentes</span>
                @if($totalEstudiantes > 0)
                    <span class="bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full text-xs">
                        {{ round(($asistentes->count() / $totalEstudiantes) * 100) }}%
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Panel QR --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">

                @if($qrSvg && $sesion->expiracion > now())

                    <p class="text-xs font-bold text-[#000b60] uppercase tracking-wide mb-3">Código QR de Asistencia</p>

                    <div class="border-4 border-[#000b60] rounded-xl p-2 mb-4">
                        {!! $qrSvg !!}
                    </div>

                    <div class="text-center mb-4">
                        @php
                            $segsRestantes = $sesion->expiracion->gt(now())
                                ? (int) now()->diffInSeconds($sesion->expiracion)
                                : 0;
                            $minsDisplay = floor($segsRestantes / 60);
                            $segsDisplay = $segsRestantes % 60;
                        @endphp
                        <p class="text-xs text-gray-400 mb-1">Tiempo restante</p>
                        <p class="text-4xl font-black font-mono {{ $segsRestantes <= 60 ? 'text-red-500' : 'text-[#000b60]' }}">
                            {{ sprintf('%02d:%02d', $minsDisplay, $segsDisplay) }}
                        </p>
                        @if($segsRestantes <= 60)
                            <p class="text-xs text-red-500 font-semibold mt-1">⚠ Menos de 1 minuto</p>
                        @else
                            <p class="text-xs text-green-600 font-semibold mt-1">QR activo</p>
                        @endif
                    </div>

                    <div class="w-full bg-gray-50 rounded-lg px-3 py-2 text-xs text-gray-400 text-center break-all mb-4">
                        {{ $qrUrl }}
                    </div>

                    <button wire:click="generarQR"
                            class="w-full border border-[#000b60] text-[#000b60] py-2 rounded-lg font-semibold hover:bg-blue-50 transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">refresh</span>
                        Renovar QR (5 min)
                    </button>

                @else

                    <span class="material-symbols-outlined text-gray-300 mb-3" style="font-size:80px">qr_code_2</span>
                    <p class="font-semibold text-gray-500 mb-1">
                        {{ $sesion->token ? 'QR expirado' : 'Sin QR generado' }}
                    </p>
                    <p class="text-sm text-gray-400 mb-5">Genera un código para que los estudiantes registren su asistencia</p>
                    <button wire:click="generarQR"
                            class="bg-[#000b60] text-white px-6 py-2.5 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">qr_code</span>
                        Generar QR (5 min)
                    </button>

                @endif

            </div>

            {{-- Panel Asistencia --}}
            <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col">

                {{-- Presentes --}}
                <div class="bg-[#e6f6ff] px-5 py-3 flex items-center justify-between">
                    <span class="font-bold text-[#000b60] flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">how_to_reg</span>
                        Presentes
                    </span>
                    <span class="bg-green-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                        {{ $asistentes->count() }}
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto max-h-64">
                    @if($asistentes->isEmpty())
                        <p class="text-center text-gray-400 py-8 text-sm">Aún no hay registros</p>
                    @else
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="text-left px-4 py-2 font-semibold text-gray-500 text-xs">Carné</th>
                                    <th class="text-left px-4 py-2 font-semibold text-gray-500 text-xs">Estudiante</th>
                                    <th class="text-center px-4 py-2 font-semibold text-gray-500 text-xs">Hora</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($asistentes as $a)
                                    <tr class="hover:bg-[#f3faff]">
                                        <td class="px-4 py-2.5 font-mono text-xs text-[#000b60]">{{ $a->estudiante->carnet }}</td>
                                        <td class="px-4 py-2.5 font-semibold text-sm">{{ $a->estudiante->nombre }}</td>
                                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($a->fecha_hora)->format('H:i') }}
                                        </td>
                                        <td class="px-2 py-2.5">
                                            <button wire:click="quitarAsistencia({{ $a->id }})"
                                                    wire:confirm="¿Quitar asistencia de {{ addslashes($a->estudiante->nombre) }}?"
                                                    class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1 rounded transition">
                                                <span class="material-symbols-outlined" style="font-size:16px">close</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Ausentes --}}
                @if($ausentes->isNotEmpty())
                    <div class="border-t border-gray-100">
                        <div class="bg-gray-50 px-5 py-3 flex items-center justify-between">
                            <span class="font-bold text-gray-600 text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined" style="font-size:18px">person_off</span>
                                Ausentes
                            </span>
                            <span class="bg-gray-300 text-gray-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                {{ $ausentes->count() }}
                            </span>
                        </div>
                        <div class="overflow-y-auto max-h-48">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($ausentes as $e)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 font-mono text-xs text-gray-400">{{ $e->carnet }}</td>
                                            <td class="px-4 py-2 text-gray-600">{{ $e->nombre }}</td>
                                            <td class="px-2 py-2 text-right">
                                                <button wire:click="marcarManual({{ $e->id }})"
                                                        title="Marcar presente"
                                                        class="text-green-600 hover:bg-green-50 p-1 rounded transition text-xs font-semibold flex items-center gap-1">
                                                    <span class="material-symbols-outlined" style="font-size:16px">person_add</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    @endif

</div>
