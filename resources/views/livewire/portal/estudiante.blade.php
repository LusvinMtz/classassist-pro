<div>
    {{-- ── Formulario de búsqueda ─────────────────────────────────────────── --}}
    @if (! $buscado || $error)
        <form wire:submit.prevent="buscar" class="space-y-5">

            @if ($error)
                <div class="flex items-start gap-3 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    {{ $error }}
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold tracking-widest uppercase mb-1" style="color: var(--guest-subtitle);">
                    Carnet
                </label>
                <input wire:model="carnet" type="text" placeholder="Ej. 202300001"
                    class="w-full rounded-xl px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-400"
                    style="background-color: var(--guest-input-bg); border: 1px solid var(--guest-input-border); color: var(--guest-title);">
                @error('carnet')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold tracking-widest uppercase mb-1" style="color: var(--guest-subtitle);">
                    Correo electrónico
                </label>
                <input wire:model="correo" type="email" placeholder="tucorreo@ejemplo.com"
                    class="w-full rounded-xl px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-400"
                    style="background-color: var(--guest-input-bg); border: 1px solid var(--guest-input-border); color: var(--guest-title);">
                @error('correo')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full rounded-xl py-3 text-sm font-bold tracking-wide text-white transition hover:opacity-90 active:scale-95"
                style="background-color: var(--guest-logo-bg);">
                <span wire:loading.remove wire:target="buscar">Consultar mi información</span>
                <span wire:loading wire:target="buscar" style="display:none">Buscando…</span>
            </button>
        </form>

    {{-- ── Resultados del estudiante ──────────────────────────────────────── --}}
    @elseif ($estudiante)
        <div class="space-y-6">

            {{-- Encabezado estudiante --}}
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-lg font-bold text-white"
                    style="background-color: var(--guest-logo-bg);">
                    {{ strtoupper(substr($estudiante->nombre, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-base leading-tight" style="color: var(--guest-title);">
                        {{ $estudiante->nombre }}
                    </p>
                    <p class="text-xs" style="color: var(--guest-subtitle);">
                        Carnet: {{ $estudiante->carnet }}
                    </p>
                </div>
                <button wire:click="limpiar" class="ml-auto text-xs underline" style="color: var(--guest-subtitle);">
                    Nueva búsqueda
                </button>
            </div>

            {{-- Cursos --}}
            @forelse ($estudiante->clases as $clase)
                @php
                    $sesiones       = $clase->sesiones;
                    $totalSes       = $sesiones->count();
                    $asistidas      = $estudiante->asistencias->filter(fn($a) => $a->sesion?->clase_id === $clase->id)->count();
                    $pctAsist       = $totalSes > 0 ? round(($asistidas / $totalSes) * 100) : null;
                    $califs         = $estudiante->calificaciones->where('clase_id', $clase->id);
                    $actividadNotas = $estudiante->actividadNotas->filter(fn($an) => $an->actividad?->clase_id === $clase->id);
                    $gruposClase    = $estudiante->grupos->filter(fn($g) => $g->sesion?->clase_id === $clase->id);
                @endphp

                <div class="rounded-2xl border overflow-hidden" style="border-color: var(--guest-input-border);"
                     x-data="{ open: false }">

                    {{-- Cabecera del curso (clic para expandir) --}}
                    <button type="button" @click="open = !open"
                            class="w-full text-left px-5 py-4 flex items-start justify-between gap-2 transition"
                            style="background-color: var(--guest-input-bg);">
                        <div>
                            <p class="font-bold text-sm" style="color: var(--guest-title);">{{ $clase->nombre }}</p>
                            @if ($clase->codigo)
                                <p class="text-xs mt-0.5" style="color: var(--guest-subtitle);">
                                    Código: {{ $clase->codigo }}
                                    @if ($clase->ciclo) · Ciclo {{ $clase->ciclo }} @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($clase->catedratico)
                                <span class="text-xs px-2 py-1 rounded-full" style="background-color: var(--guest-card-bg); color: var(--guest-subtitle);">
                                    {{ $clase->catedratico->name }}
                                </span>
                            @endif
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-200 shrink-0"
                                 :class="open ? 'rotate-180' : ''"
                                 style="color: var(--guest-subtitle);"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" class="px-5 py-4 space-y-4"
                         style="display:none">

                        {{-- Asistencia --}}
                        <div>
                            <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color: var(--guest-subtitle);">Asistencia</p>
                            @if ($totalSes === 0)
                                <p class="text-xs" style="color: var(--guest-muted);">Sin sesiones registradas.</p>
                            @else
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 rounded-full overflow-hidden" style="background-color: var(--guest-input-bg);">
                                        <div class="h-2 rounded-full transition-all duration-500"
                                            style="width: {{ $pctAsist }}%; background-color: {{ $pctAsist >= 75 ? '#22c55e' : ($pctAsist >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                                    </div>
                                    <span class="text-xs font-bold whitespace-nowrap" style="color: var(--guest-title);">
                                        {{ $asistidas }}/{{ $totalSes }} ({{ $pctAsist }}%)
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Calificaciones --}}
                        @if ($califs->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color: var(--guest-subtitle);">Calificaciones</p>
                                <div class="space-y-1.5">
                                    @foreach ($califs as $cal)
                                        <div class="flex items-center justify-between rounded-lg px-3 py-2 text-sm"
                                            style="background-color: var(--guest-input-bg);">
                                            <span style="color: var(--guest-muted);">
                                                {{ $cal->tipoCalificacion?->nombre ?? 'Sin tipo' }}
                                            </span>
                                            <span class="font-bold" style="color: var(--guest-title);">
                                                {{ number_format($cal->nota, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Actividades --}}
                        @if ($actividadNotas->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color: var(--guest-subtitle);">Actividades</p>
                                <div class="space-y-1.5">
                                    @foreach ($actividadNotas as $an)
                                        <div class="flex items-center justify-between rounded-lg px-3 py-2 text-sm"
                                            style="background-color: var(--guest-input-bg);">
                                            <div>
                                                <span style="color: var(--guest-muted);">
                                                    {{ $an->actividad?->nombre ?? 'Actividad' }}
                                                </span>
                                                @if ($an->actividad?->esGrupal())
                                                    <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full font-semibold"
                                                          style="background-color: var(--guest-card-bg); color: var(--guest-subtitle);">
                                                        Grupal
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span class="font-bold" style="color: var(--guest-title);">
                                                    {{ number_format($an->nota, 2) }}
                                                </span>
                                                @if ($an->actividad?->punteo_max)
                                                    <span class="text-xs" style="color: var(--guest-muted);">
                                                        / {{ number_format($an->actividad->punteo_max, 0) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Grupos --}}
                        @if ($gruposClase->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color: var(--guest-subtitle);">Grupos</p>
                                <div class="space-y-1.5">
                                    @foreach ($gruposClase as $grupo)
                                        <div class="rounded-lg px-3 py-2 text-sm"
                                            style="background-color: var(--guest-input-bg);">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold" style="color: var(--guest-title);">
                                                    {{ $grupo->nombre }}
                                                </span>
                                                @if ($grupo->sesion)
                                                    <span class="text-xs" style="color: var(--guest-muted);">
                                                        · {{ $grupo->sesion->fecha->translatedFormat('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($grupo->descripcion)
                                                <p class="text-xs mt-0.5" style="color: var(--guest-muted);">
                                                    {{ $grupo->descripcion }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

            @empty
                <div class="rounded-xl border px-5 py-6 text-center text-sm" style="border-color: var(--guest-input-border); color: var(--guest-muted);">
                    No tienes cursos asignados actualmente.
                </div>
            @endforelse

        </div>
    @endif
</div>
