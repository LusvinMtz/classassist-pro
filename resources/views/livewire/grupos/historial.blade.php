<div>

    {{-- Encabezado --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Historial de Grupos</h1>
            <p class="text-sm text-gray-500">Grupos formados en sesiones anteriores</p>
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
            <span class="material-symbols-outlined" style="font-size:64px">history</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">Selecciona una clase para ver el historial</p>
        </div>

    @elseif($sesiones->isEmpty())

        <div class="bg-white rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="material-symbols-outlined" style="font-size:64px">group_off</span>
            <p class="mt-4 font-semibold text-gray-500 text-lg">No hay grupos registrados para esta clase</p>
            <p class="text-sm mt-1">Genera grupos desde el módulo de Grupos Aleatorios</p>
        </div>

    @else

        <div class="space-y-5">
            @foreach($sesiones as $sesion)
                @php
                    $colores = [
                        'border-blue-400 bg-blue-50',
                        'border-purple-400 bg-purple-50',
                        'border-green-400 bg-green-50',
                        'border-orange-400 bg-orange-50',
                        'border-pink-400 bg-pink-50',
                        'border-teal-400 bg-teal-50',
                        'border-yellow-400 bg-yellow-50',
                        'border-red-400 bg-red-50',
                    ];
                    $totalMiembros = $sesion->grupos->sum(fn ($g) => $g->estudiantes->count());
                @endphp

                <div class="bg-white rounded-xl shadow overflow-hidden">

                    {{-- Header sesión --}}
                    <div class="bg-[#e6f6ff] px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[#000b60]" style="font-size:20px">calendar_today</span>
                            <div>
                                <p class="font-bold text-[#000b60]">
                                    {{ $sesion->fecha->translatedFormat('l, d \d\e F Y') }}
                                    @if($sesion->fecha->isToday())
                                        <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">Hoy</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $sesion->grupos->count() }} grupos · {{ $totalMiembros }} estudiantes</p>
                            </div>
                        </div>
                        @if($sesion->finalizada)
                            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:13px">lock</span>
                                Finalizada
                            </span>
                        @endif
                    </div>

                    {{-- Grid de grupos --}}
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($sesion->grupos as $i => $grupo)
                            <div class="border-2 {{ $colores[$i % count($colores)] }} rounded-xl p-4">
                                <p class="font-black text-[#000b60] text-sm mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined" style="font-size:16px">group</span>
                                    {{ $grupo->nombre }}
                                    <span class="ml-auto bg-white/70 text-xs font-bold px-2 py-0.5 rounded-full text-gray-600">
                                        {{ $grupo->estudiantes->count() }}
                                    </span>
                                </p>
                                <ul class="space-y-1">
                                    @foreach($grupo->estudiantes as $e)
                                        <li class="text-sm text-gray-700 flex items-center gap-2">
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
