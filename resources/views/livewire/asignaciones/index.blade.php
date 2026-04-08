<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Asignaciones</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Consulta de estudiantes asignados a clases por año</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Buscar estudiante</label>
            <input wire:model.live.debounce.300ms="busqueda" type="text" placeholder="Nombre o carné..."
                   class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
        </div>
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Carrera</label>
            <select wire:model.live="filtroCarrera"
                    class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todas</option>
                @foreach($carreras as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Clase</label>
            <select wire:model.live="filtroClase"
                    class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todas</option>
                @foreach($clasesDeFiltro as $c)
                    <option value="{{ $c->id }}">{{ $c->codigo ? $c->codigo.' – ' : '' }}{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-28">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Año</label>
            <input wire:model.live="filtroAnio" type="number" placeholder="{{ now()->year }}" min="2000" max="2099"
                   class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                <tr>
                    <th class="text-left px-6 py-3 font-bold">Estudiante</th>
                    <th class="text-left px-4 py-3 font-bold">Carné</th>
                    <th class="text-left px-6 py-3 font-bold">Clase</th>
                    <th class="text-left px-4 py-3 font-bold">Carrera</th>
                    <th class="text-center px-3 py-3 font-bold w-16">Ciclo</th>
                    <th class="text-center px-3 py-3 font-bold w-20">Año</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-[#1a2f3c]">
                @forelse($asignaciones as $a)
                <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                    <td class="px-6 py-3 font-semibold dark:text-gray-200">{{ $a->estudiante->nombre }}</td>
                    <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400">{{ $a->estudiante->carnet }}</td>
                    <td class="px-6 py-3 dark:text-gray-200">{{ $a->clase->nombre }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $a->clase->carrera?->nombre ?? '—' }}</td>
                    <td class="px-3 py-3 text-center">
                        @if($a->clase->ciclo)
                            <span class="bg-[#000b60] text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $a->clase->ciclo }}°</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-center font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $a->anio }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                        <span class="material-symbols-outlined" style="font-size:40px">assignment_ind</span>
                        <p class="mt-2 text-sm">No hay asignaciones registradas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-[#1a2f3c]">
            {{ $asignaciones->links() }}
        </div>
    </div>

</div>
