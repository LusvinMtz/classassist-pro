<div>

    {{-- Encabezado --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-3xl font-extrabold">Sesiones de Clase</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona las sesiones para habilitar asistencia, ruleta y grupos</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-sm font-semibold text-[#000b60] dark:text-[#bcc2ff]">Clase:</label>
            <select wire:model.live="claseId"
                    class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] min-w-[220px]">
                <option value="">— Selecciona una clase —</option>
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(!$claseId)

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:64px">calendar_month</span>
            <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">Selecciona una clase para ver sus sesiones</p>
        </div>

    @else

        {{-- Advertencia sesión activa en otra clase --}}
        @error('sesion')
            <div class="mb-4 flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 text-sm">
                <span class="material-symbols-outlined mt-0.5" style="font-size:18px">warning</span>
                <span>{{ $message }}</span>
            </div>
        @enderror

        {{-- Barra de acción --}}
        <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesiones->count() }}</span> sesión(es) registradas
            </p>

            @if(!$hoyTiene)
                <button wire:click="crear"
                        class="bg-[#000b60] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">add_circle</span>
                    Nueva sesión — hoy {{ now()->translatedFormat('d/m/Y') }}
                </button>
            @else
                <span class="text-xs text-green-600 font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:16px">check_circle</span>
                    Ya existe una sesión para hoy
                </span>
            @endif
        </div>

        @if($sesiones->isEmpty())

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-24 text-gray-400 dark:text-gray-500">
                <span class="material-symbols-outlined" style="font-size:64px">event_note</span>
                <p class="mt-4 font-semibold text-gray-500 dark:text-gray-400 text-lg">No hay sesiones registradas</p>
                <p class="text-sm mt-1">Crea la primera sesión para comenzar con la clase de hoy</p>
            </div>

        @else

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                            <th class="text-left px-5 py-3 font-bold text-xs uppercase tracking-wide">Fecha</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wide">Estado</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wide">Asistentes</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wide">Participaciones</th>
                            <th class="text-right px-5 py-3 font-bold text-xs uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1a2f3c]">
                        @foreach($sesiones as $sesion)
                            <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">

                                {{-- Fecha --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        @if($sesion->fecha->isToday())
                                            <span class="w-2 h-2 rounded-full {{ $sesion->finalizada ? 'bg-gray-400' : 'bg-green-500' }} flex-shrink-0"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 flex-shrink-0"></span>
                                        @endif
                                        <div>
                                            <p class="font-bold">{{ $sesion->fecha->translatedFormat('l') }}</p>
                                            <p class="text-xs text-gray-400">{{ $sesion->fecha->translatedFormat('d \d\e F Y') }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-3.5 text-center">
                                    @if($sesion->finalizada)
                                        <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-[#2a3d4a] text-gray-600 dark:text-gray-300 text-xs font-bold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined" style="font-size:13px">lock</span>
                                            Finalizada
                                        </span>
                                    @elseif($sesion->fecha->isToday())
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined" style="font-size:13px">radio_button_checked</span>
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-[#0d2535] text-blue-600 dark:text-[#bcc2ff] text-xs font-bold px-2.5 py-1 rounded-full">
                                            <span class="material-symbols-outlined" style="font-size:13px">history</span>
                                            Pasada
                                        </span>
                                    @endif
                                </td>

                                {{-- Asistentes --}}
                                <td class="px-4 py-3.5 text-center">
                                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->asistencias_count }}</span>
                                </td>

                                {{-- Participaciones --}}
                                <td class="px-4 py-3.5 text-center">
                                    <span class="font-bold text-[#000b60] dark:text-[#bcc2ff]">{{ $sesion->participaciones_count }}</span>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">

                                        @if(!$sesion->finalizada)
                                            <button wire:click="finalizar({{ $sesion->id }})"
                                                    wire:confirm="¿Finalizar esta sesión? Ya no se podrá registrar asistencia ni usar la ruleta o grupos."
                                                    class="inline-flex items-center gap-1 bg-[#000b60] text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:opacity-80 transition">
                                                <span class="material-symbols-outlined" style="font-size:14px">lock</span>
                                                Finalizar
                                            </button>
                                        @elseif(auth()->user()->isAdmin())
                                            <button wire:click="reabrir({{ $sesion->id }})"
                                                    wire:confirm="¿Reabrir esta sesión?"
                                                    class="inline-flex items-center gap-1 border border-gray-300 dark:border-[#3a4d5a] text-gray-600 dark:text-gray-300 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] transition">
                                                <span class="material-symbols-outlined" style="font-size:14px">lock_open</span>
                                                Reabrir
                                            </button>
                                        @endif

                                        <a href="{{ route('pantalla-clase.index', ['sesionId' => $sesion->id]) }}"
                                           title="Abrir Pantalla de Clase"
                                           class="inline-flex items-center gap-1 border border-[#000b60]/30 dark:border-[#bcc2ff]/30 text-[#000b60] dark:text-[#bcc2ff] text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] transition">
                                            <span class="material-symbols-outlined" style="font-size:14px">cast_for_education</span>
                                            Pantalla
                                        </a>

                                        @if($sesion->asistencias_count === 0)
                                            <button wire:click="eliminar({{ $sesion->id }})"
                                                    wire:confirm="¿Eliminar esta sesión? Esta acción no se puede deshacer."
                                                    class="text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition">
                                                <span class="material-symbols-outlined" style="font-size:16px">delete</span>
                                            </button>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @endif

    @endif

</div>
