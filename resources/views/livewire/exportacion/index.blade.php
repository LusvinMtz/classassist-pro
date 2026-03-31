<div>

    <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Exportación a Excel</h1>
        <p class="text-sm text-gray-500">Descarga los datos de asistencia, participaciones y desempeño de tus clases</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Configuración --}}
        <div class="lg:col-span-1 flex flex-col gap-4">

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-xs font-bold text-[#000b60] uppercase tracking-widest mb-4">Selecciona la clase</p>
                <select wire:model.live="claseId"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                    <option value="">— Selecciona una clase —</option>
                    @foreach($clases as $clase)
                        <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                    @endforeach
                </select>

                @if($claseId)
                    <a href="{{ route('exportacion.download', $claseId) }}"
                       class="mt-5 w-full bg-[#000b60] text-white py-3 rounded-xl font-bold text-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">download</span>
                        Descargar Excel (.xlsx)
                    </a>
                @endif
            </div>

            {{-- Contenido del archivo --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-xs font-bold text-[#000b60] uppercase tracking-widest mb-4">El archivo incluye</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm">
                        <span class="material-symbols-outlined text-green-500 flex-shrink-0" style="font-size:18px">table_chart</span>
                        <div>
                            <p class="font-bold">Hoja 1: Resumen</p>
                            <p class="text-xs text-gray-400">Asistencia total, % y promedio de calificaciones por estudiante</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3 text-sm">
                        <span class="material-symbols-outlined text-blue-500 flex-shrink-0" style="font-size:18px">how_to_reg</span>
                        <div>
                            <p class="font-bold">Hoja 2: Detalle Asistencia</p>
                            <p class="text-xs text-gray-400">Registro por sesión con fecha y hora de cada asistencia</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3 text-sm">
                        <span class="material-symbols-outlined text-purple-500 flex-shrink-0" style="font-size:18px">casino</span>
                        <div>
                            <p class="font-bold">Hoja 3: Participaciones</p>
                            <p class="text-xs text-gray-400">Registros de la ruleta con calificación y comentario</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Vista previa de datos --}}
        <div class="lg:col-span-2">

            @if(!$claseId)

                <div class="bg-white rounded-2xl shadow flex flex-col items-center justify-center py-24 text-gray-400">
                    <span class="material-symbols-outlined" style="font-size:64px">table_chart</span>
                    <p class="mt-4 font-semibold text-gray-500 text-lg">Selecciona una clase para continuar</p>
                </div>

            @else

                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div class="bg-white rounded-xl shadow px-5 py-4 flex items-center gap-4">
                        <span class="material-symbols-outlined text-[#000b60]" style="font-size:32px">groups</span>
                        <div>
                            <p class="text-2xl font-black text-[#000b60]">{{ $stats['estudiantes'] }}</p>
                            <p class="text-xs text-gray-400 font-semibold">Estudiantes</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow px-5 py-4 flex items-center gap-4">
                        <span class="material-symbols-outlined text-[#000b60]" style="font-size:32px">calendar_month</span>
                        <div>
                            <p class="text-2xl font-black text-[#000b60]">{{ $stats['sesiones'] }}</p>
                            <p class="text-xs text-gray-400 font-semibold">Sesiones</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow px-5 py-4 flex items-center gap-4">
                        <span class="material-symbols-outlined text-green-500" style="font-size:32px">how_to_reg</span>
                        <div>
                            <p class="text-2xl font-black text-[#000b60]">{{ $stats['asistencias'] }}</p>
                            <p class="text-xs text-gray-400 font-semibold">Registros de asistencia</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow px-5 py-4 flex items-center gap-4">
                        <span class="material-symbols-outlined text-purple-500" style="font-size:32px">casino</span>
                        <div>
                            <p class="text-2xl font-black text-[#000b60]">{{ $stats['participaciones'] }}</p>
                            <p class="text-xs text-gray-400 font-semibold">Participaciones</p>
                        </div>
                    </div>
                </div>

                @if($stats['sesiones'] === 0)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-amber-700 text-sm flex items-center gap-3">
                        <span class="material-symbols-outlined flex-shrink-0">warning</span>
                        Esta clase no tiene sesiones registradas. El archivo se generará pero estará vacío.
                    </div>
                @else
                    <div class="bg-[#e6f6ff] rounded-xl px-5 py-4 text-[#000b60] text-sm flex items-center gap-3">
                        <span class="material-symbols-outlined flex-shrink-0">info</span>
                        El archivo Excel incluirá todos los datos históricos de esta clase en tres hojas separadas.
                    </div>
                @endif

            @endif

        </div>

    </div>

</div>
