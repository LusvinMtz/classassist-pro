<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Gestión de Usuarios</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Administra los usuarios y sus roles</p>
        </div>
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90 transition">
            <span class="material-symbols-outlined" style="font-size:18px">person_add</span>
            Nuevo usuario
        </button>
    </div>

    {{-- Buscador --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-5 flex items-center gap-3">
        <span class="material-symbols-outlined text-gray-400">search</span>
        <input wire:model.live.debounce.300ms="buscar"
               type="text"
               placeholder="Buscar por nombre o correo..."
               class="flex-1 text-sm focus:outline-none bg-transparent dark:text-[#dff4ff] dark:placeholder-gray-500">
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                <tr>
                    <th class="text-left px-6 py-3 font-bold">Usuario</th>
                    <th class="text-left px-6 py-3 font-bold">Correo</th>
                    <th class="text-center px-4 py-3 font-bold">Rol</th>
                    <th class="text-left px-4 py-3 font-bold">Clases asignadas</th>
                    <th class="text-center px-4 py-3 font-bold">Estado</th>
                    <th class="text-center px-4 py-3 font-bold w-24">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-[#1a2f3c]">
                @forelse($usuarios as $usuario)
                @php $rol = $usuario->roles->first(); @endphp
                <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#000b60] flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                            </div>
                            <span class="font-semibold">{{ $usuario->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $usuario->email }}</td>
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
                    <td class="px-4 py-4">
                        @php $clasesU = $usuario->clasesImpartidas ?? collect(); @endphp
                        @if($clasesU->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($clasesU as $cl)
                                    <span class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] text-xs font-semibold px-2 py-0.5 rounded-full">{{ $cl->nombre }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
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
                                    class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#0d2535] p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                            </button>
                            @if($usuario->id !== auth()->id())
                            <button wire:click="delete({{ $usuario->id }})"
                                    wire:confirm="¿Eliminar a '{{ addslashes($usuario->nombre) }}'? Esta acción no se puede deshacer."
                                    title="Eliminar"
                                    class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-gray-400">
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
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow-xl w-full mx-4 p-6 overflow-y-auto max-h-[92vh]
                    {{ $rolId == $rolCatedraticoId ? 'max-w-3xl' : 'max-w-md' }}">

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">
                    {{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Datos básicos --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

                <div>
                    <label class="block text-sm font-semibold mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="nombre" type="text" placeholder="Nombre completo"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('nombre') border-red-400 @enderror">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Correo <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" placeholder="correo@ejemplo.com"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('email') border-red-400 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        Contraseña {{ $editingId ? '(vacío = sin cambio)' : '*' }}
                    </label>
                    <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('password') border-red-400 @enderror">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Rol <span class="text-red-500">*</span></label>
                    <select wire:model.live="rolId"
                            class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('rolId') border-red-400 @enderror">
                        <option value="0">— Selecciona un rol —</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }}</option>
                        @endforeach
                    </select>
                    @error('rolId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="flex items-center justify-between bg-gray-50 dark:bg-[#162a35] rounded-lg px-4 py-3 mb-4">
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Estado</p>
                    <p class="text-xs text-gray-400">El usuario puede o no iniciar sesión</p>
                </div>
                <button type="button" wire:click="$set('estado', !$estado)"
                        class="relative w-12 h-6 rounded-full transition-colors duration-200 {{ $estado ? 'bg-[#000b60]' : 'bg-gray-300 dark:bg-[#2a3d4a]' }}">
                    <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block {{ $estado ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>

            {{-- ── Sección catedrático: selección directa de clases ──────────── --}}
            @if($rolId == $rolCatedraticoId)
            <div class="border-t border-gray-100 dark:border-[#2a3d4a] pt-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-black text-[#000b60] dark:text-[#bcc2ff]">Asignación de clases</p>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                 {{ count($clasesSeleccionadas) >= 6 ? 'bg-red-100 text-red-600' : 'bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]' }}">
                        {{ count($clasesSeleccionadas) }} / 6 clases
                    </span>
                </div>

                {{-- Buscador --}}
                <div class="relative mb-2">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400" style="font-size:14px">search</span>
                    <input wire:model.live.debounce.200ms="buscarClase"
                           type="text" placeholder="Buscar clase..."
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg pl-7 pr-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                </div>

                {{-- Lista de clases --}}
                <div class="border border-gray-200 dark:border-[#2a3d4a] rounded-lg p-2 space-y-0.5 h-36 overflow-y-auto">
                    @forelse($clasesDisponibles as $clase)
                    @php
                        $seleccionada = in_array((string)$clase->id, $clasesSeleccionadas);
                        $ocupadaPor   = $clasesOcupadas[$clase->id] ?? null;
                        $bloqueada    = $ocupadaPor !== null;
                        $lleno        = count($clasesSeleccionadas) >= 6 && !$seleccionada;
                        $disabled     = $bloqueada || $lleno;
                    @endphp
                    <label class="flex items-center gap-2 rounded px-2 py-1.5 transition
                                  {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c]' }}">
                        <input type="checkbox"
                               wire:model.live="clasesSeleccionadas"
                               value="{{ $clase->id }}"
                               class="w-4 h-4 rounded border-gray-300 text-[#000b60] focus:ring-[#000b60]"
                               {{ $disabled ? 'disabled' : '' }}>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold leading-tight block
                                         {{ $bloqueada ? 'text-gray-400 dark:text-gray-500' : 'text-[#000b60] dark:text-[#bcc2ff]' }}">
                                {{ $clase->nombre }}
                            </span>
                            <span class="text-[10px] font-mono {{ $bloqueada ? 'text-red-400 dark:text-red-500' : 'text-gray-400' }}">
                                @if($bloqueada)
                                    Asignada a {{ $ocupadaPor }}
                                @elseif($clase->codigo)
                                    Ciclo {{ $clase->ciclo }} · {{ $clase->codigo }}
                                @endif
                            </span>
                        </div>
                    </label>
                    @empty
                    <p class="text-gray-400 text-xs text-center py-6">Sin resultados</p>
                    @endforelse
                </div>
                @error('clasesSeleccionadas')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                {{-- Resumen de clases seleccionadas --}}
                @if(count($clasesSeleccionadas) > 0)
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($clasesDisponibles->whereIn('id', $clasesSeleccionadas) as $cl)
                        <span class="bg-[#000b60] text-white text-xs font-semibold px-2 py-0.5 rounded-full">{{ $cl->nombre }}</span>
                    @endforeach
                </div>
                @endif

            </div>
            @endif
            {{-- /sección catedrático --}}

            <div class="flex gap-3 mt-6">
                <button wire:click="closeModal"
                        class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition">
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
