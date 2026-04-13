<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Bitácora del sistema</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Registro detallado de todas las acciones realizadas en la plataforma</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">

            {{-- Búsqueda --}}
            <div class="xl:col-span-2 flex items-center gap-2 border border-gray-200 dark:border-[#2a3d4a] rounded-lg px-3 py-2 bg-white dark:bg-[#162a35]">
                <span class="material-symbols-outlined text-gray-400" style="font-size:18px">search</span>
                <input wire:model.live.debounce.300ms="busqueda" type="text"
                    placeholder="Buscar descripción, usuario, IP…"
                    class="flex-1 text-sm focus:outline-none bg-transparent dark:text-[#dff4ff] dark:placeholder-gray-500">
            </div>

            {{-- Módulo --}}
            <select wire:model.live="modulo"
                class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todos los módulos</option>
                @foreach ($modulos as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>

            {{-- Acción --}}
            <select wire:model.live="accion"
                class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todas las acciones</option>
                @foreach ($acciones as $a)
                    <option value="{{ $a }}">{{ ucfirst($a) }}</option>
                @endforeach
            </select>

            {{-- Nivel --}}
            <select wire:model.live="nivel"
                class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todos los niveles</option>
                <option value="info">Info</option>
                <option value="advertencia">Advertencia</option>
                <option value="error">Error</option>
            </select>

            {{-- Fechas + limpiar --}}
            <div class="flex gap-2">
                <input wire:model.live="fechaDesde" type="date" title="Desde"
                    class="flex-1 min-w-0 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <input wire:model.live="fechaHasta" type="date" title="Hasta"
                    class="flex-1 min-w-0 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                @if ($busqueda || $modulo || $accion || $nivel || $fechaDesde || $fechaHasta)
                    <button wire:click="limpiarFiltros" title="Limpiar filtros"
                        class="shrink-0 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] px-3 rounded-lg text-gray-400 hover:text-red-500 transition">
                        <span class="material-symbols-outlined" style="font-size:18px">close</span>
                    </button>
                @endif
            </div>

        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

        {{-- Contador + loading --}}
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-[#1a2f3c]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                {{ number_format($registros->total()) }} registro{{ $registros->total() !== 1 ? 's' : '' }}
            </p>
            <div wire:loading class="flex items-center gap-1.5 text-xs text-[#000b60] dark:text-[#bcc2ff]">
                <svg class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Actualizando…
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                    <tr>
                        <th class="text-left px-5 py-3 font-bold">Fecha y hora</th>
                        <th class="text-left px-5 py-3 font-bold">Nivel</th>
                        <th class="text-left px-5 py-3 font-bold">Acción</th>
                        <th class="text-left px-5 py-3 font-bold">Módulo</th>
                        <th class="text-left px-5 py-3 font-bold">Usuario</th>
                        <th class="text-left px-5 py-3 font-bold">Descripción</th>
                        <th class="text-left px-5 py-3 font-bold">IP</th>
                        <th class="text-center px-4 py-3 font-bold w-16">+</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1a2f3c]" x-data>
                    @forelse ($registros as $reg)
                        @php
                            $badgeNivel = match($reg->nivel) {
                                'advertencia' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'error'       => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                default       => 'bg-[#e6f6ff] text-[#000b60] dark:bg-[#0d2535] dark:text-[#bcc2ff]',
                            };
                            $rowBg = match($reg->nivel) {
                                'error'       => 'bg-red-50/60 dark:bg-red-950/20',
                                'advertencia' => 'bg-amber-50/60 dark:bg-amber-950/20',
                                default       => '',
                            };
                            $tieneDetalle = $reg->datos_anteriores || $reg->datos_nuevos;
                        @endphp

                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition {{ $rowBg }}"
                            x-data="{ open: false }">
                            <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ $reg->created_at->format('d/m/Y') }}<br>
                                <span class="font-mono font-semibold">{{ $reg->created_at->format('H:i:s') }}</span>
                            </td>

                            <td class="px-5 py-3">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $badgeNivel }}">
                                    {{ ucfirst($reg->nivel) }}
                                </span>
                            </td>

                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 border border-gray-200 dark:border-[#2a3d4a] bg-white dark:bg-[#162a35] px-2.5 py-1 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-[#000b60] dark:text-[#bcc2ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $reg->accionIcono() }}"/>
                                    </svg>
                                    {{ ucfirst($reg->accion) }}
                                </span>
                            </td>

                            <td class="px-5 py-3 font-semibold text-[#000b60] dark:text-[#bcc2ff]">
                                {{ $reg->modulo }}
                                @if ($reg->entidad_id)
                                    <span class="text-xs text-gray-400 font-normal">#{{ $reg->entidad_id }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-3 text-xs">
                                @if ($reg->usuario)
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $reg->usuario->nombre }}</span><br>
                                    <span class="text-gray-400">{{ $reg->usuario->email }}</span>
                                @else
                                    <span class="italic text-gray-400">Sistema</span>
                                @endif
                            </td>

                            <td class="px-5 py-3 max-w-xs text-gray-600 dark:text-gray-300">
                                {{ $reg->descripcion }}
                            </td>

                            <td class="px-5 py-3 font-mono text-xs text-gray-400">
                                {{ $reg->ip ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($tieneDetalle)
                                    <button @click="open = !open"
                                        class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-[#e6f6ff] dark:hover:bg-[#0d2535] p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined transition-transform duration-200"
                                            :class="open ? 'rotate-180' : ''"
                                            style="font-size:18px">expand_more</span>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        {{-- Fila expandida --}}
                        @if ($tieneDetalle)
                            <tr x-show="open" style="display:none"
                                class="bg-[#f3faff] dark:bg-[#0d2535]">
                                <td colspan="8" class="px-5 py-4">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        @if ($reg->datos_anteriores)
                                            <div>
                                                <p class="mb-1.5 text-xs font-black uppercase tracking-widest text-[#000b60]/50 dark:text-[#bcc2ff]/50">Antes</p>
                                                <pre class="overflow-x-auto rounded-lg bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] p-3 text-xs text-gray-700 dark:text-[#dff4ff]">{{ json_encode($reg->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                        @if ($reg->datos_nuevos)
                                            <div>
                                                <p class="mb-1.5 text-xs font-black uppercase tracking-widest text-[#000b60]/50 dark:text-[#bcc2ff]/50">Después</p>
                                                <pre class="overflow-x-auto rounded-lg bg-white dark:bg-[#162a35] border border-gray-200 dark:border-[#2a3d4a] p-3 text-xs text-gray-700 dark:text-[#dff4ff]">{{ json_encode($reg->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                    @if ($reg->user_agent)
                                        <p class="mt-3 text-xs text-gray-400">
                                            <span class="font-semibold text-gray-500 dark:text-gray-300">User-Agent:</span>
                                            {{ $reg->user_agent }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                                <span class="material-symbols-outlined" style="font-size:40px">policy</span>
                                <p class="mt-2 text-sm">No hay registros que coincidan con los filtros</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registros->hasPages())
            <div class="border-t border-gray-100 dark:border-[#1a2f3c] px-5 py-3">
                {{ $registros->links() }}
            </div>
        @endif
    </div>

</div>
