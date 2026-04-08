<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Sedes</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de sedes y sus carreras asignadas</p>
        </div>
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90 transition">
            <span class="material-symbols-outlined" style="font-size:18px">add</span>
            Nueva sede
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                <tr>
                    <th class="text-left px-6 py-3 font-bold">Código</th>
                    <th class="text-left px-6 py-3 font-bold">Nombre</th>
                    <th class="text-left px-6 py-3 font-bold">Dirección</th>
                    <th class="text-left px-6 py-3 font-bold">Carreras</th>
                    <th class="text-center px-4 py-3 font-bold w-24">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-[#1a2f3c]">
                @forelse($sedes as $sede)
                <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                    <td class="px-6 py-4 font-mono font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $sede->codigo }}</td>
                    <td class="px-6 py-4 font-semibold dark:text-gray-200">{{ $sede->nombre }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $sede->direccion ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($sede->carreras as $c)
                                <span class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] text-xs font-semibold px-2 py-0.5 rounded-full">
                                    {{ $c->nombre }}
                                </span>
                            @empty
                                <span class="text-gray-400 text-xs">Sin carreras</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openEdit({{ $sede->id }})" title="Editar"
                                    class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#1a2f3c] p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                            </button>
                            <button wire:click="delete({{ $sede->id }})"
                                    wire:confirm="¿Eliminar la sede '{{ addslashes($sede->nombre) }}'?"
                                    title="Eliminar"
                                    class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                        <span class="material-symbols-outlined" style="font-size:40px">location_city</span>
                        <p class="mt-2 text-sm">No hay sedes registradas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">
                    {{ $editingId ? 'Editar sede' : 'Nueva sede' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-4">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="nombre" type="text" placeholder="Ej. Campus Central"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('nombre') border-red-400 @enderror">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Código <span class="text-red-500">*</span></label>
                        <input wire:model="codigo" type="text" placeholder="Ej. 85" maxlength="10"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] uppercase @error('codigo') border-red-400 @enderror">
                        @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Dirección</label>
                    <input wire:model="direccion" type="text" placeholder="Dirección opcional"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2 dark:text-gray-300">Carreras que ofrece esta sede</label>
                    <div class="border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] rounded-lg p-3 space-y-1 max-h-52 overflow-y-auto">
                        @forelse($carreras as $c)
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] rounded-lg px-2 py-1.5 transition">
                            <input type="checkbox"
                                   wire:model="carrerasSeleccionadas"
                                   value="{{ $c->id }}"
                                   class="w-4 h-4 rounded border-gray-300 text-[#000b60] focus:ring-[#000b60]">
                            <span class="text-sm font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $c->nombre }}</span>
                            @if($c->codigo)
                                <span class="text-xs text-gray-400 font-mono">({{ $c->codigo }})</span>
                            @endif
                        </label>
                        @empty
                            <p class="text-gray-400 text-sm text-center py-3">No hay carreras registradas</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="closeModal"
                        class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-400 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition">
                    Cancelar
                </button>
                <button wire:click="save"
                        class="flex-1 bg-[#000b60] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                    {{ $editingId ? 'Guardar cambios' : 'Crear' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
