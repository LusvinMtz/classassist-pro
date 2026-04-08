<div>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-extrabold">Gestión de Estudiantes</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Administra los estudiantes por clase</p>
        </div>
        @if($claseId)
            <div class="flex gap-2">
                <button wire:click="openImport"
                        class="border border-[#000b60] dark:border-[#bcc2ff] text-[#000b60] dark:text-[#bcc2ff] px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:bg-blue-50 dark:hover:bg-[#0d2535] transition">
                    <span class="material-symbols-outlined" style="font-size:18px">upload_file</span>
                    Importar Excel
                </button>
                <button wire:click="openCreate"
                        class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:opacity-90 transition">
                    <span class="material-symbols-outlined" style="font-size:18px">person_add</span>
                    Agregar Estudiante
                </button>
            </div>
        @endif
    </div>

    {{-- Selector de clase --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-6 flex items-center gap-4">
        <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]">school</span>
        <div class="flex-1">
            <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1">Clase</label>
            <select wire:model.live="claseId"
                    class="w-full md:w-80 border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] text-sm">
                <option value="">— Selecciona una clase —</option>
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
        </div>
        @if($claseId)
            <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                <span class="font-bold text-2xl text-[#000b60] dark:text-[#bcc2ff]">{{ $estudiantes->count() }}</span><br>
                estudiante(s)
            </div>
        @endif
    </div>

    {{-- Contenido principal --}}
    @if(!$claseId)

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:56px">school</span>
            <p class="mt-3 font-semibold text-gray-500 dark:text-gray-400">Selecciona una clase para gestionar sus estudiantes</p>
        </div>

    @elseif($estudiantes->isEmpty())

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:56px">groups</span>
            <p class="mt-3 font-semibold text-gray-500 dark:text-gray-400">No hay estudiantes en esta clase</p>
            <p class="text-sm mt-1">Agrega individualmente o importa desde Excel</p>
            <div class="flex gap-3 mt-5">
                <button wire:click="openImport"
                        class="border border-[#000b60] dark:border-[#bcc2ff] text-[#000b60] dark:text-[#bcc2ff] px-4 py-2 rounded-lg font-semibold text-sm hover:bg-blue-50 dark:hover:bg-[#0d2535] transition">
                    Importar Excel
                </button>
                <button wire:click="openCreate"
                        class="bg-[#000b60] text-white px-4 py-2 rounded-lg font-semibold text-sm hover:opacity-90 transition">
                    Agregar Estudiante
                </button>
            </div>
        </div>

    @else

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[#e6f6ff] dark:bg-[#0d2535] text-[#000b60] dark:text-[#bcc2ff]">
                    <tr>
                        <th class="text-center px-4 py-3 font-bold w-12">No.</th>
                        <th class="text-left px-6 py-3 font-bold">Carné</th>
                        <th class="text-left px-6 py-3 font-bold">Estudiante</th>
                        <th class="text-left px-6 py-3 font-bold">Correo Electrónico</th>
                        <th class="text-center px-6 py-3 font-bold w-24">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1a2f3c]">
                    @foreach($estudiantes as $i => $e)
                        <tr class="hover:bg-[#f3faff] dark:hover:bg-[#1a2f3c] transition">
                            <td class="px-4 py-4 text-center text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 font-mono font-semibold text-[#000b60] dark:text-[#bcc2ff]">{{ $e->carnet }}</td>
                            <td class="px-6 py-4 font-semibold">{{ $e->nombre }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $e->correo ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-1">
                                    <button wire:click="openEdit({{ $e->id }})"
                                            title="Editar"
                                            class="text-[#000b60] dark:text-[#bcc2ff] hover:bg-blue-100 dark:hover:bg-[#0d2535] p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                                    </button>
                                    <button wire:click="delete({{ $e->id }})"
                                            wire:confirm="¿Quitar a '{{ addslashes($e->nombre) }}' de esta clase?"
                                            title="Quitar de clase"
                                            class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition">
                                        <span class="material-symbols-outlined" style="font-size:18px">person_remove</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif


    {{-- MODAL: Agregar / Editar Estudiante --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">
                        {{ $editingId ? 'Editar Estudiante' : 'Agregar Estudiante' }}
                    </h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Carné <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="carnet"
                               type="text"
                               placeholder="Ej. 202300001"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('carnet') border-red-400 @enderror">
                        @error('carnet')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Estudiante <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="nombre"
                               type="text"
                               placeholder="Nombre completo"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('nombre') border-red-400 @enderror">
                        @error('nombre')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Correo Electrónico</label>
                        <input wire:model="correo"
                               type="email"
                               placeholder="correo@universidad.edu"
                               class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('correo') border-red-400 @enderror">
                        @error('correo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeModal"
                            class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition">
                        Cancelar
                    </button>
                    <button wire:click="save"
                            class="flex-1 bg-[#000b60] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                        {{ $editingId ? 'Guardar cambios' : 'Agregar' }}
                    </button>
                </div>

            </div>
        </div>
    @endif


    {{-- MODAL: Importar Excel --}}
    @if($showImportModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-black text-[#000b60] dark:text-[#bcc2ff]">Importar desde Excel</h2>
                    <button wire:click="closeImportModal" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Formato requerido --}}
                <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-4 mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] uppercase tracking-wide">Formato requerido de columnas</p>
                        <a href="{{ route('estudiantes.plantilla') }}"
                           class="flex items-center gap-1 text-xs font-semibold text-[#000b60] dark:text-[#bcc2ff] bg-white dark:bg-[#162a35] border border-[#000b60] dark:border-[#bcc2ff] px-2.5 py-1 rounded-lg hover:bg-blue-50 dark:hover:bg-[#1a2f3c] transition"
                           title="Descargar plantilla Excel">
                            <span class="material-symbols-outlined" style="font-size:15px">download</span>
                            Descargar plantilla
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="text-[#000b60] dark:text-[#bcc2ff]">
                                    <th class="border border-blue-200 dark:border-[#1a3a50] bg-blue-100 dark:bg-[#0d2535] px-3 py-1.5 text-left">No.</th>
                                    <th class="border border-blue-200 dark:border-[#1a3a50] bg-blue-100 dark:bg-[#0d2535] px-3 py-1.5 text-left">Carné</th>
                                    <th class="border border-blue-200 dark:border-[#1a3a50] bg-blue-100 dark:bg-[#0d2535] px-3 py-1.5 text-left">Estudiante</th>
                                    <th class="border border-blue-200 dark:border-[#1a3a50] bg-blue-100 dark:bg-[#0d2535] px-3 py-1.5 text-left">Correo Electrónico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-gray-600 dark:text-gray-300">
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">1</td>
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">202300001</td>
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">Juan Pérez García</td>
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">juan@uni.edu</td>
                                </tr>
                                <tr class="text-gray-600 dark:text-gray-300">
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">2</td>
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">202300002</td>
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">María López</td>
                                    <td class="border border-blue-100 dark:border-[#1a2f3c] px-3 py-1.5">maria@uni.edu</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Formatos aceptados: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong> · Máx. 10 MB</p>
                </div>

                {{-- Input archivo --}}
                <div>
                    <label class="block text-sm font-semibold mb-1">Archivo</label>
                    <input wire:model="archivo"
                           type="file"
                           accept=".xlsx,.xls,.csv"
                           class="w-full border border-gray-200 dark:border-[#2a3d4a] dark:bg-[#162a35] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] @error('archivo') border-red-400 @enderror">
                    @error('archivo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <div wire:loading wire:target="archivo" class="text-xs text-blue-500 mt-1 flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Subiendo archivo...
                    </div>
                </div>

                {{-- Resultado importación --}}
                @if($importados > 0)
                    <div class="mt-4 bg-green-50 border border-green-200 text-green-700 rounded-lg p-3 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">check_circle</span>
                        <span>{{ $importados }} estudiante(s) importado(s) exitosamente.</span>
                    </div>
                @endif

                @if(!empty($erroresImport))
                    <div class="mt-4 bg-red-50 border border-red-200 text-red-600 rounded-lg p-3 text-sm">
                        <p class="font-bold mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size:16px">warning</span>
                            Errores encontrados:
                        </p>
                        <ul class="space-y-0.5 pl-2">
                            @foreach($erroresImport as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeImportModal"
                            class="flex-1 border border-gray-200 dark:border-[#2a3d4a] text-gray-600 dark:text-gray-300 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a2f3c] font-semibold transition">
                        Cerrar
                    </button>
                    <button wire:click="importar"
                            wire:loading.attr="disabled"
                            wire:target="importar"
                            class="flex-1 bg-[#000b60] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="importar">Importar</span>
                        <span wire:loading wire:target="importar">Importando...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
