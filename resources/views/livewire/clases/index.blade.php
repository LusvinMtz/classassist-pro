<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold">{{ $esAdmin ? 'Gestión de Clases' : 'Mis Clases' }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $esAdmin ? 'Administra los cursos que impartes' : 'Clases asignadas a tu perfil' }}
            </p>
        </div>
        @if($esAdmin)
        <button wire:click="openCreate"
                class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90">
            <span class="material-symbols-outlined" style="font-size:18px">add</span>
            Nueva Clase
        </button>
        @endif
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
        @if($clases->isEmpty())
            <div class="text-center py-20 text-gray-400 dark:text-gray-500">
                <span class="material-symbols-outlined" style="font-size:56px">school</span>
                @if($esAdmin)
                    <p class="mt-3 font-semibold text-gray-500 dark:text-gray-400">No tienes clases registradas</p>
                    <p class="text-sm mt-1">Crea tu primera clase para comenzar</p>
                @else
                    <p class="mt-3 font-semibold text-gray-500 dark:text-gray-400">No tienes clases asignadas</p>
                    <p class="text-sm mt-1">Contacta al administrador para que te asigne clases</p>
                @endif
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                    <tr>
                        <th class="text-left px-6 py-3 font-bold">Nombre</th>
                        <th class="text-left px-6 py-3 font-bold">Carrera</th>
                        <th class="text-left px-6 py-3 font-bold">Descripción</th>
                        <th class="text-center px-6 py-3 font-bold">Estudiantes</th>
                        <th class="text-center px-6 py-3 font-bold">Sesiones</th>
                        <th class="text-center px-6 py-3 font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1a2f3c]">
                    @foreach($clases as $clase)
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition {{ !$esAdmin ? 'cursor-pointer' : '' }}"
                            @if(!$esAdmin) wire:click="verResumen({{ $clase->id }})" @endif>
                            <td class="px-6 py-4 font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $clase->nombre }}</td>
                            <td class="px-6 py-4">
                                @if($clase->carrera)
                                    <span class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff] text-xs font-semibold px-2 py-0.5 rounded-full">
                                        {{ $clase->carrera->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $clase->descripcion ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-bold px-2 py-1 rounded-full">
                                    {{ $clase->estudiantes_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 text-xs font-bold px-2 py-1 rounded-full">
                                    {{ $clase->sesiones_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    @if($esAdmin)
                                        <button wire:click.stop="openEdit({{ $clase->id }})"
                                                title="Editar"
                                                class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#1a2f3c] p-1.5 rounded-lg transition">
                                            <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                                        </button>
                                        <button wire:click.stop="delete({{ $clase->id }})"
                                                wire:confirm="¿Eliminar '{{ addslashes($clase->nombre) }}'? Se eliminarán también sus sesiones y registros asociados."
                                                title="Eliminar"
                                                class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition">
                                            <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                                        </button>
                                    @else
                                        <button wire:click="verResumen({{ $clase->id }})"
                                                title="Ver resumen"
                                                class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#1a2f3c] p-1.5 rounded-lg transition">
                                            <span class="material-symbols-outlined" style="font-size:18px">open_in_new</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- Modal RESUMEN (catedrático)                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showResumen && $resumenClase)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-[#1e333c] rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">

                {{-- Cabecera --}}
                <div class="bg-[#000b60] text-white px-6 py-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black">{{ $resumenClase->nombre }}</h2>
                        @if($resumenClase->carrera)
                            <p class="text-sm opacity-70 mt-0.5">{{ $resumenClase->carrera->nombre }}</p>
                        @endif
                    </div>
                    <button wire:click="cerrarResumen" class="opacity-60 hover:opacity-100 transition mt-0.5">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    {{-- Descripción --}}
                    @if($resumenClase->descripcion)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $resumenClase->descripcion }}</p>
                    @endif

                    {{-- Estadísticas --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl px-4 py-3 flex items-center gap-3">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">groups</span>
                            <div>
                                <p class="text-2xl font-black text-blue-800 dark:text-blue-300">{{ $resumenClase->estudiantes_count }}</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Estudiantes inscritos</p>
                            </div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl px-4 py-3 flex items-center gap-3">
                            <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">calendar_month</span>
                            <div>
                                <p class="text-2xl font-black text-purple-800 dark:text-purple-300">{{ $resumenClase->sesiones_count }}</p>
                                <p class="text-xs text-purple-600 dark:text-purple-400 font-semibold">Sesiones totales</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sesiones recientes --}}
                    @if($sesionesRecientes->isNotEmpty())
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">Sesiones recientes</p>
                            <div class="space-y-1.5">
                                @foreach($sesionesRecientes as $sesion)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-[#162a35] rounded-lg px-4 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $sesion->finalizada ? 'bg-gray-400' : 'bg-green-500' }}"></span>
                                            <div>
                                                <p class="text-sm font-semibold dark:text-gray-200">{{ $sesion->fecha->translatedFormat('l d \d\e F Y') }}</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ $sesion->asistencias_count }} asistentes · {{ $sesion->participaciones_count }} participaciones
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($sesion->finalizada)
                                                <span class="text-xs bg-gray-100 dark:bg-[#2a3d4a] text-gray-500 dark:text-gray-400 font-bold px-2 py-0.5 rounded-full">Finalizada</span>
                                            @else
                                                <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold px-2 py-0.5 rounded-full">Activa</span>
                                            @endif
                                            <a href="{{ route('pantalla-clase.index', ['sesionId' => $sesion->id]) }}"
                                               title="Pantalla de Clase"
                                               class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#1a2f3c] p-1 rounded-lg transition">
                                                <span class="material-symbols-outlined" style="font-size:16px">cast_for_education</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">No hay sesiones registradas aún</p>
                    @endif

                    {{-- Acciones rápidas --}}
                    <div class="flex gap-3 pt-1">
                        <a href="{{ route('sesiones.index') }}"
                           class="flex-1 bg-[#000b60] text-white py-2.5 rounded-xl font-bold text-sm text-center hover:opacity-90 transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined" style="font-size:18px">calendar_month</span>
                            Ver sesiones
                        </a>
                        <a href="{{ route('asistencia.index') }}"
                           class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-700 dark:text-gray-300 py-2.5 rounded-xl font-bold text-sm text-center hover:bg-gray-50 dark:hover:bg-[#1a2f3c] transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined" style="font-size:18px">how_to_reg</span>
                            Asistencia
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- Modal CREAR / EDITAR (solo admin)                               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">
                        {{ $editingId ? 'Editar Clase' : 'Nueva Clase' }}
                    </h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="nombre"
                               type="text"
                               placeholder="Ej. Programación Web I"
                               class="w-full border dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] {{ $errors->has('nombre') ? 'border-red-400' : 'border-gray-200' }}">
                        @error('nombre')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Carrera <span class="text-red-500">*</span></label>
                        <select wire:model="carreraId"
                                class="w-full border dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] {{ $errors->has('carreraId') ? 'border-red-400' : 'border-gray-200' }}">
                            <option value="">— Selecciona una carrera —</option>
                            @foreach($carreras as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        @error('carreraId')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Descripción</label>
                        <textarea wire:model="descripcion"
                                  rows="3"
                                  placeholder="Descripción opcional..."
                                  class="w-full border dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] resize-none {{ $errors->has('descripcion') ? 'border-red-400' : 'border-gray-200' }}"></textarea>
                        @error('descripcion')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeModal"
                            class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-400 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition">
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
