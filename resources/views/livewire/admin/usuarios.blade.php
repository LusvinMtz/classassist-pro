<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Gestión de Usuarios</h1>
            <p class="text-sm text-gray-500">Administra los usuarios y sus roles</p>
        </div>
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90 transition">
            <span class="material-symbols-outlined" style="font-size:18px">person_add</span>
            Nuevo usuario
        </button>
    </div>

    {{-- Buscador --}}
    <div class="bg-white rounded-xl shadow p-4 mb-5 flex items-center gap-3">
        <span class="material-symbols-outlined text-gray-400">search</span>
        <input wire:model.live.debounce.300ms="buscar"
               type="text"
               placeholder="Buscar por nombre o correo..."
               class="flex-1 text-sm focus:outline-none">
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[#e6f6ff] text-[#000b60]">
                <tr>
                    <th class="text-left px-6 py-3 font-bold">Usuario</th>
                    <th class="text-left px-6 py-3 font-bold">Correo</th>
                    <th class="text-center px-4 py-3 font-bold">Rol</th>
                    <th class="text-center px-4 py-3 font-bold">Estado</th>
                    <th class="text-center px-4 py-3 font-bold w-24">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($usuarios as $usuario)
                @php $rol = $usuario->roles->first(); @endphp
                <tr class="hover:bg-[#f3faff] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#000b60] flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                            </div>
                            <span class="font-semibold">{{ $usuario->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $usuario->email }}</td>
                    <td class="px-4 py-4 text-center">
                        @if($rol)
                            @php
                                $colores = ['admin' => 'bg-purple-100 text-purple-700', 'catedratico' => 'bg-blue-100 text-blue-700', 'estudiante' => 'bg-green-100 text-green-700'];
                                $colorRol = $colores[$rol->nombre] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $colorRol }}">
                                {{ ucfirst($rol->nombre) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">Sin rol</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($usuario->estado)
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">Activo</span>
                        @else
                            <span class="bg-red-100 text-red-600 text-xs font-semibold px-2.5 py-1 rounded-full">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openEdit({{ $usuario->id }})" title="Editar"
                                    class="text-[#000b60] hover:bg-blue-100 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                            </button>
                            @if($usuario->id !== auth()->id())
                            <button wire:click="delete({{ $usuario->id }})"
                                    wire:confirm="¿Eliminar a '{{ addslashes($usuario->nombre) }}'? Esta acción no se puede deshacer."
                                    title="Eliminar"
                                    class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                        <span class="material-symbols-outlined" style="font-size:40px">manage_accounts</span>
                        <p class="mt-2 text-sm">No se encontraron usuarios</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL: Crear / Editar --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60]">
                    {{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="nombre" type="text" placeholder="Nombre completo"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('nombre') border-red-400 @enderror">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Correo <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" placeholder="correo@ejemplo.com"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('email') border-red-400 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        Contraseña {{ $editingId ? '(dejar vacío para no cambiar)' : '*' }}
                    </label>
                    <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('password') border-red-400 @enderror">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Rol <span class="text-red-500">*</span></label>
                    <select wire:model="rolId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('rolId') border-red-400 @enderror">
                        <option value="0">— Selecciona un rol —</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }}</option>
                        @endforeach
                    </select>
                    @error('rolId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Estado</p>
                        <p class="text-xs text-gray-400">El usuario puede o no iniciar sesión</p>
                    </div>
                    <button type="button" wire:click="$set('estado', !$estado)"
                            class="relative w-12 h-6 rounded-full transition-colors duration-200 {{ $estado ? 'bg-[#000b60]' : 'bg-gray-300' }}">
                        <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block {{ $estado ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="closeModal"
                        class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg hover:bg-gray-50 font-semibold transition">
                    Cancelar
                </button>
                <button wire:click="save"
                        class="flex-1 bg-[#000b60] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                    {{ $editingId ? 'Guardar cambios' : 'Crear usuario' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
