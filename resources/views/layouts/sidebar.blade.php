<aside class="h-screen w-64 fixed left-0 top-0 hidden md:flex flex-col bg-[#e6f6ff] text-[#000b60] font-semibold py-6 space-y-4">

    <div class="px-4 mb-2">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-[#000b60] flex items-center justify-center text-white font-black text-xl">
                C
            </div>
            <div>
                <h2 class="font-black text-xl">ClassAssist</h2>
                @php $rolNombre = auth()->user()?->loadMissing('roles')->roles->first()?->nombre; @endphp
                <p class="text-xs opacity-70">{{ ucfirst($rolNombre ?? 'Usuario') }}</p>
            </div>
        </div>
    </div>

    @php
        $active   = 'bg-white rounded-lg mx-2 my-0.5 px-4 py-2.5 shadow flex items-center gap-3';
        $inactive = 'px-4 py-2.5 mx-2 hover:bg-blue-100 rounded-lg flex items-center gap-3 transition';
        $user     = auth()->user();
    @endphp

    <nav class="flex-grow overflow-y-auto">

        {{-- ── Módulo Admin (solo administradores) ─────────────────── --}}
        @if($user?->isAdmin())
        <div class="px-4 pt-1 pb-1">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#000b60]/40">Administración</p>
        </div>

        <a href="{{ route('admin.index') }}"
           class="{{ request()->routeIs('admin.index') ? $active : $inactive }}">
            <span class="material-symbols-outlined">admin_panel_settings</span>
            Panel Admin
        </a>

        <a href="{{ route('admin.usuarios') }}"
           class="{{ request()->routeIs('admin.usuarios') ? $active : $inactive }}">
            <span class="material-symbols-outlined">manage_accounts</span>
            Usuarios
        </a>

        <a href="{{ route('admin.tipos-calificacion') }}"
           class="{{ request()->routeIs('admin.tipos-calificacion') ? $active : $inactive }}">
            <span class="material-symbols-outlined">grading</span>
            Tipos Calificación
        </a>

        <div class="mx-4 my-2 border-t border-[#000b60]/10"></div>
        <div class="px-4 pb-1">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#000b60]/40">Académico</p>
        </div>
        @endif

        {{-- ── Sección académica (admin + catedrático) ─────────────── --}}
        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? $active : $inactive }}">
            <span class="material-symbols-outlined">dashboard</span>
            Dashboard
        </a>

        <a href="{{ route('clases.index') }}"
           class="{{ request()->routeIs('clases.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">school</span>
            Clases
        </a>

        <a href="{{ route('estudiantes.index') }}"
           class="{{ request()->routeIs('estudiantes.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">groups</span>
            Estudiantes
        </a>

        <a href="{{ route('sesiones.index') }}"
           class="{{ request()->routeIs('sesiones.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">calendar_month</span>
            Sesiones
        </a>

        <a href="{{ route('asistencia.index') }}"
           class="{{ request()->routeIs('asistencia.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">how_to_reg</span>
            Asistencia
        </a>

        <a href="{{ route('ruleta.index') }}"
           class="{{ request()->routeIs('ruleta.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">casino</span>
            Ruleta
        </a>

        <a href="{{ route('grupos.index') }}"
           class="{{ request()->routeIs('grupos.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">hub</span>
            Grupos
        </a>

        <a href="{{ route('temporizador.index') }}"
           class="{{ request()->routeIs('temporizador.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">timer</span>
            Temporizador
        </a>

        <a href="{{ route('desempeno.index') }}"
           class="{{ request()->routeIs('desempeno.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">leaderboard</span>
            Desempeño
        </a>

        <a href="{{ route('historial-grupos.index') }}"
           class="{{ request()->routeIs('historial-grupos.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">history</span>
            Historial Grupos
        </a>

        <a href="{{ route('medidor.index') }}"
           class="{{ request()->routeIs('medidor.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">graphic_eq</span>
            Medidor de Ruido
        </a>

        <a href="{{ route('exportacion.index') }}"
           class="{{ request()->routeIs('exportacion.*') ? $active : $inactive }}">
            <span class="material-symbols-outlined">download</span>
            Exportar Excel
        </a>

    </nav>

    <div class="px-4 pt-2">
        <p class="text-xs text-center text-[#000b60]/50 mb-2 truncate">{{ auth()->user()?->nombre }}</p>
        <button onclick="doLogout()"
                class="w-full bg-[#000b60] text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
            Cerrar sesión
        </button>
    </div>

    <script>
    function doLogout() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                   || document.querySelector('input[name="_token"]')?.value
                   || '';
        fetch('{{ route('logout') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
        }).then(() => { window.location.href = '/login'; });
    }
    </script>

</aside>
