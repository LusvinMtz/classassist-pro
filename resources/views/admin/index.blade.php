<x-app-layout>

    <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Administración</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Panel de control del administrador del sistema</p>
    </div>

    @php
        $totalUsuarios      = \App\Models\User::count();
        $totalCatedraticos  = \App\Models\User::whereHas('roles', fn($q) => $q->where('nombre', 'catedratico'))->count();
        $totalClases        = \App\Models\Clase::count();
        $totalTipos         = \App\Models\TipoCalificacion::count();
    @endphp

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">manage_accounts</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $totalUsuarios }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Usuarios totales</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">person_book</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $totalCatedraticos }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Catedráticos</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">school</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $totalClases }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Clases registradas</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">grading</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $totalTipos }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Tipos de calificación</p>
            </div>
        </div>

    </div>

    {{-- Accesos rápidos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <a href="{{ route('admin.usuarios') }}"
           class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-6 flex items-center gap-5 hover:shadow-md transition group">
            <div class="bg-[#000b60] rounded-xl p-4">
                <span class="material-symbols-outlined text-white" style="font-size:32px">manage_accounts</span>
            </div>
            <div>
                <h3 class="text-lg font-black text-[#000b60] dark:text-[#bcc2ff] group-hover:underline">Gestión de Usuarios</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Crear, editar y asignar roles a los usuarios del sistema</p>
            </div>
            <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 ml-auto" style="font-size:24px">chevron_right</span>
        </a>

        <a href="{{ route('admin.tipos-calificacion') }}"
           class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-6 flex items-center gap-5 hover:shadow-md transition group">
            <div class="bg-[#000b60] rounded-xl p-4">
                <span class="material-symbols-outlined text-white" style="font-size:32px">grading</span>
            </div>
            <div>
                <h3 class="text-lg font-black text-[#000b60] dark:text-[#bcc2ff] group-hover:underline">Tipos de Calificación</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Administrar categorías de evaluación disponibles</p>
            </div>
            <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 ml-auto" style="font-size:24px">chevron_right</span>
        </a>

        <a href="{{ route('clases.index') }}"
           class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-6 flex items-center gap-5 hover:shadow-md transition group">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-xl p-4">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:32px">school</span>
            </div>
            <div>
                <h3 class="text-lg font-black text-[#000b60] dark:text-[#bcc2ff] group-hover:underline">Clases</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ver y gestionar todas las clases del sistema</p>
            </div>
            <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 ml-auto" style="font-size:24px">chevron_right</span>
        </a>

        <a href="{{ route('dashboard') }}"
           class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-6 flex items-center gap-5 hover:shadow-md transition group">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-xl p-4">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:32px">dashboard</span>
            </div>
            <div>
                <h3 class="text-lg font-black text-[#000b60] dark:text-[#bcc2ff] group-hover:underline">Dashboard General</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Estadísticas y gráficas del sistema</p>
            </div>
            <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 ml-auto" style="font-size:24px">chevron_right</span>
        </a>

    </div>

</x-app-layout>
