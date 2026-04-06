<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Tipos de Calificación</h1>
            <p class="text-sm text-gray-500">Categorías de evaluación disponibles en el sistema</p>
        </div>
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90 transition">
            <span class="material-symbols-outlined" style="font-size:18px">add</span>
            Nuevo tipo
        </button>
    </div>

    {{-- Lista --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[#e6f6ff] text-[#000b60]">
                <tr>
                    <th class="text-center px-4 py-3 font-bold w-16">Orden</th>
                    <th class="text-left px-6 py-3 font-bold">Nombre</th>
                    <th class="text-left px-6 py-3 font-bold">Descripción</th>
                    <th class="text-center px-4 py-3 font-bold">Punteo máx.</th>
                    <th class="text-center px-4 py-3 font-bold">Calificaciones</th>
                    <th class="text-center px-4 py-3 font-bold w-24">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tipos as $tipo)
                <tr class="hover:bg-[#f3faff] transition">
                    <td class="px-4 py-4 text-center text-gray-500">{{ $tipo->orden }}</td>
                    <td class="px-6 py-4 font-semibold text-[#000b60]">{{ $tipo->nombre }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $tipo->descripcion ?? '—' }}</td>
                    <td class="px-4 py-4 text-center font-semibold">{{ number_format($tipo->punteo_max, 0) }} pts</td>
                    <td class="px-4 py-4 text-center font-semibold">{{ $tipo->calificaciones_count }}</td>
                    <td class="px-4 py-4">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openEdit({{ $tipo->id }})" title="Editar"
                                    class="text-[#000b60] hover:bg-blue-100 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                            </button>
                            <button wire:click="delete({{ $tipo->id }})"
                                    wire:confirm="¿Eliminar '{{ addslashes($tipo->nombre) }}'? Se eliminarán también sus calificaciones asociadas."
                                    title="Eliminar"
                                    class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                        <span class="material-symbols-outlined" style="font-size:40px">grading</span>
                        <p class="mt-2 text-sm">No hay tipos de calificación registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60]">
                    {{ $editingId ? 'Editar tipo' : 'Nuevo tipo' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="nombre" type="text" placeholder="Ej. Examen parcial"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('nombre') border-red-400 @enderror">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Descripción</label>
                    <textarea wire:model="descripcion" rows="3" placeholder="Descripción opcional..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] resize-none @error('descripcion') border-red-400 @enderror"></textarea>
                    @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Punteo máximo <span class="text-red-500">*</span></label>
                        <input wire:model="punteoMax" type="number" min="0.01" max="9999" step="0.01" placeholder="100"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('punteoMax') border-red-400 @enderror">
                        @error('punteoMax') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Orden</label>
                        <input wire:model="orden" type="number" min="0" step="1" placeholder="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('orden') border-red-400 @enderror">
                        @error('orden') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="closeModal"
                        class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg hover:bg-gray-50 font-semibold transition">
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
