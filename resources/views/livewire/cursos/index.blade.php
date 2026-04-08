<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Cursos</h1>
            <p class="text-sm text-gray-500">Malla curricular por carrera y ciclo</p>
        </div>
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90 transition">
            <span class="material-symbols-outlined" style="font-size:18px">add</span>
            Nuevo curso
        </button>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Facultad</label>
            <select wire:model.live="filtroFacultad"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todas las facultades</option>
                @foreach($facultades as $f)
                    <option value="{{ $f->id }}">{{ $f->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Carrera</label>
            <select wire:model.live="filtroCarrera"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todas las carreras</option>
                @foreach($carreras as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Ciclo</label>
            <select wire:model.live="filtroCiclo"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                <option value="">Todos</option>
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">{{ $i }}° Ciclo</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[#e6f6ff] text-[#000b60]">
                <tr>
                    <th class="text-left px-4 py-3 font-bold">Código</th>
                    <th class="text-center px-3 py-3 font-bold w-20">Ciclo</th>
                    <th class="text-left px-4 py-3 font-bold">Nombre</th>
                    <th class="text-left px-4 py-3 font-bold">Carrera</th>
                    <th class="text-left px-4 py-3 font-bold">Semestre</th>
                    <th class="text-center px-4 py-3 font-bold w-24">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cursos as $curso)
                <tr class="hover:bg-[#f3faff] transition">
                    <td class="px-4 py-3 font-mono font-semibold text-[#000b60]">{{ $curso->codigo }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="bg-[#000b60] text-white text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $curso->ciclo }}°
                        </span>
                    </td>
                    <td class="px-4 py-3 font-semibold">{{ $curso->nombre }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $curso->carrera?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $curso->semestre }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openEdit({{ $curso->id }})" title="Editar"
                                    class="text-[#000b60] hover:bg-blue-100 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                            </button>
                            <button wire:click="delete({{ $curso->id }})"
                                    wire:confirm="¿Eliminar '{{ addslashes($curso->nombre) }}'?"
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
                        <span class="material-symbols-outlined" style="font-size:40px">menu_book</span>
                        <p class="mt-2 text-sm">No hay cursos registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-2 text-xs text-gray-400 border-t border-gray-100">
            {{ $cursos->count() }} curso(s)
        </div>
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60]">
                    {{ $editingId ? 'Editar curso' : 'Nuevo curso' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="nombre" type="text" placeholder="Ej. Programación I"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('nombre') border-red-400 @enderror">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Código <span class="text-red-500">*</span></label>
                        <input wire:model="codigo" type="text" placeholder="Ej. 8590-15"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('codigo') border-red-400 @enderror">
                        @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Ciclo <span class="text-red-500">*</span></label>
                        <select wire:model="ciclo"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('ciclo') border-red-400 @enderror">
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}° Ciclo</option>
                            @endfor
                        </select>
                        @error('ciclo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Facultad <span class="text-red-500">*</span></label>
                    <select wire:model="facultadId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('facultadId') border-red-400 @enderror">
                        <option value="">Seleccionar facultad...</option>
                        @foreach($facultades as $f)
                            <option value="{{ $f->id }}">{{ $f->nombre }}</option>
                        @endforeach
                    </select>
                    @error('facultadId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Carrera <span class="text-red-500">*</span></label>
                    <select wire:model="carreraId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('carreraId') border-red-400 @enderror">
                        <option value="">Seleccionar carrera...</option>
                        @foreach($carreras as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                    @error('carreraId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
