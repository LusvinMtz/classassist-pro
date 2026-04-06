<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold">Gestión de Clases</h1>
            <p class="text-sm text-gray-500">Administra los cursos que impartes</p>
        </div>
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90">
            <span class="material-symbols-outlined" style="font-size:18px">add</span>
            Nueva Clase
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        @if($clases->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <span class="material-symbols-outlined" style="font-size:56px">school</span>
                <p class="mt-3 font-semibold text-gray-500">No tienes clases registradas</p>
                <p class="text-sm mt-1">Crea tu primera clase para comenzar</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-[#e6f6ff] text-[#000b60]">
                    <tr>
                        <th class="text-left px-6 py-3 font-bold">Nombre</th>
                        <th class="text-left px-6 py-3 font-bold">Descripción</th>
                        <th class="text-center px-6 py-3 font-bold">Estudiantes</th>
                        <th class="text-center px-6 py-3 font-bold">Sesiones</th>
                        <th class="text-center px-6 py-3 font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($clases as $clase)
                        <tr class="hover:bg-[#f3faff] transition">
                            <td class="px-6 py-4 font-semibold text-[#000b60]">{{ $clase->nombre }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $clase->descripcion ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full">
                                    {{ $clase->estudiantes_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-purple-100 text-purple-800 text-xs font-bold px-2 py-1 rounded-full">
                                    {{ $clase->sesiones_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="openEdit({{ $clase->id }})"
                                            title="Editar"
                                            class="text-[#000b60] hover:bg-blue-100 p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                                    </button>
                                    <button wire:click="delete({{ $clase->id }})"
                                            wire:confirm="¿Eliminar '{{ addslashes($clase->nombre) }}'? Se eliminarán también sus sesiones y registros asociados."
                                            title="Eliminar"
                                            class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Modal crear / editar --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-black text-[#000b60]">
                        {{ $editingId ? 'Editar Clase' : 'Nueva Clase' }}
                    </h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="nombre"
                               type="text"
                               placeholder="Ej. Programación Web I"
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] {{ $errors->has('nombre') ? 'border-red-400' : 'border-gray-200' }}">
                        @error('nombre')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Descripción</label>
                        <textarea wire:model="descripcion"
                                  rows="3"
                                  placeholder="Descripción opcional..."
                                  class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] resize-none {{ $errors->has('descripcion') ? 'border-red-400' : 'border-gray-200' }}"></textarea>
                        @error('descripcion')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeModal"
                            class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg hover:bg-gray-50 font-semibold transition">
                        Cancelar
                    </button>
                    <button wire:click="save"
                            class="flex-1 bg-[#000b60] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                        {{ $editingId ? 'Guardar cambios' : 'Crear clase' }}
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
