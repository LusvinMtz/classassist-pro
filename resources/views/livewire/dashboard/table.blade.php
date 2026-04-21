<div>

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold">Dashboard</h1>
        <p class="text-sm text-gray-500">Resumen estadístico · {{ now()->translatedFormat('F Y') }}</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5 flex-shrink-0">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">class</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $kpis['totalClases'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Clases activas</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5 flex-shrink-0">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">groups</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $kpis['totalEstudiantes'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Estudiantes únicos</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="bg-[#e6f6ff] dark:bg-[#0d2535] rounded-lg p-2.5 flex-shrink-0">
                <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff]" style="font-size:22px">calendar_month</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#000b60] dark:text-[#bcc2ff] leading-none">{{ $kpis['sesionesMes'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Sesiones este mes</p>
            </div>
        </div>

        @php
            $avgColor = $kpis['avgAsistencia'] >= 75
                ? ['bg' => 'bg-green-50',  'icon' => 'text-green-600',  'val' => 'text-green-600']
                : ($kpis['avgAsistencia'] >= 50
                    ? ['bg' => 'bg-orange-50', 'icon' => 'text-orange-500', 'val' => 'text-orange-500']
                    : ['bg' => 'bg-red-50',    'icon' => 'text-red-500',    'val' => 'text-red-500']);
        @endphp
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5 flex items-start gap-3">
            <div class="{{ $avgColor['bg'] }} rounded-lg p-2.5 flex-shrink-0">
                <span class="material-symbols-outlined {{ $avgColor['icon'] }}" style="font-size:22px">how_to_reg</span>
            </div>
            <div>
                <p class="text-2xl font-black {{ $avgColor['val'] }} leading-none">{{ $kpis['avgAsistencia'] }}%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Asistencia promedio</p>
            </div>
        </div>

    </div>

    @if($clases->isEmpty() && !$esAdmin)
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:56px">school</span>
            <p class="mt-3 font-semibold text-gray-500">No tienes clases registradas aún</p>
            <a href="{{ route('clases.index') }}"
               class="mt-5 bg-[#000b60] text-white px-5 py-2 rounded-lg font-semibold text-sm hover:opacity-90 transition">
                Ir a Clases
            </a>
        </div>
    @else

    {{-- ── Filtros ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">

            {{-- Clase --}}
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1.5 uppercase tracking-wide">Clase</label>
                <select wire:model.live="claseId"
                    class="w-full border border-gray-200 dark:border-[#1a3040] dark:bg-[#162830] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
                    <option value="">Todas las clases</option>
                    @foreach($clases as $clase)
                        <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Desde --}}
            <div>
                <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1.5 uppercase tracking-wide">Desde</label>
                <input wire:model.live="fechaDesde" type="date"
                    class="border border-gray-200 dark:border-[#1a3040] dark:bg-[#162830] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
            </div>

            {{-- Hasta --}}
            <div>
                <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1.5 uppercase tracking-wide">Hasta</label>
                <input wire:model.live="fechaHasta" type="date"
                    class="border border-gray-200 dark:border-[#1a3040] dark:bg-[#162830] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60]">
            </div>

            {{-- Limpiar --}}
            @if($claseId || $fechaDesde)
            <div class="self-end">
                <button wire:click="limpiarFiltros"
                    class="flex items-center gap-1.5 border border-gray-200 dark:border-[#1a3040] text-gray-500 dark:text-gray-400 hover:text-red-500 hover:border-red-300 dark:hover:text-red-400 dark:hover:border-red-700 rounded-lg px-3 py-2 text-sm font-semibold transition">
                    <span class="material-symbols-outlined" style="font-size:16px">filter_alt_off</span>
                    Limpiar
                </button>
            </div>
            @endif

            @if($claseNombre)
            <div class="ml-auto text-right hidden lg:block">
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Clase seleccionada</p>
                <p class="text-sm font-black text-[#000b60] dark:text-[#bcc2ff]">{{ $claseNombre }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $totalEstClase }} estudiante(s)</p>
            </div>
            @endif

        </div>
    </div>

    {{-- Datos para las gráficas: se re-ejecuta en cada re-render de Livewire --}}
    <script>
        window.__dashboardData = {!! json_encode($chartData) !!};
        // Si los gráficos ya están inicializados (re-render por filtros), actualizarlos
        if (typeof window.__dashboardRender === 'function') {
            window.__dashboardRender(window.__dashboardData);
        }
    </script>

    {{-- Gráficas --}}
    <div wire:ignore>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff] text-base leading-tight">Asistencia por sesión</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Presentes vs. ausentes en el período</p>
                    </div>
                    <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff] opacity-20" style="font-size:28px">bar_chart</span>
                </div>
                <div style="position:relative; height:230px;">
                    <canvas id="chartAsistenciaSesion"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff] text-base leading-tight">Asistencia por estudiante</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">% en el período seleccionado</p>
                    </div>
                    <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff] opacity-20" style="font-size:28px">person_check</span>
                </div>
                <div id="chartAEWrap" style="position:relative; height:230px; overflow-y:auto;">
                    <canvas id="chartAsistenciaEstudiante"></canvas>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff] text-base leading-tight">Participaciones por sesión</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Cantidad y promedio de calificación</p>
                    </div>
                    <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff] opacity-20" style="font-size:28px">record_voice_over</span>
                </div>
                <div style="position:relative; height:230px;">
                    <canvas id="chartParticipaciones"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff] text-base leading-tight">Ranking de participación</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Top 15 estudiantes más activos en el período</p>
                    </div>
                    <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff] opacity-20" style="font-size:28px">social_leaderboard</span>
                </div>
                <div id="chartRankingWrap" style="position:relative; height:230px; overflow-y:auto;">
                    <canvas id="chartRanking"></canvas>
                </div>
            </div>

        </div>
    </div>

    {{-- Sesiones recientes --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-[#1a3040]">
            <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff]">Sesiones recientes</h3>
            <a href="{{ route('sesiones.index') }}"
               class="text-xs text-[#000b60] dark:text-[#bcc2ff] font-semibold hover:underline flex items-center gap-0.5">
                Ver todas
                <span class="material-symbols-outlined" style="font-size:15px">chevron_right</span>
            </a>
        </div>

        @if($sesionesRecientes->isEmpty())
            <div class="py-12 text-center text-gray-400">
                <span class="material-symbols-outlined" style="font-size:40px">event_note</span>
                <p class="mt-2 text-sm">No hay sesiones en este rango de fechas</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#f3faff] dark:bg-[#162830] text-[#000b60] dark:text-[#bcc2ff]">
                    <tr>
                        <th class="text-left px-6 py-3 font-bold">Fecha</th>
                        <th class="text-center px-4 py-3 font-bold">Asistentes</th>
                        <th class="text-center px-4 py-3 font-bold">% Asist.</th>
                        <th class="text-center px-4 py-3 font-bold">Participaciones</th>
                        <th class="text-center px-4 py-3 font-bold">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1a3040]">
                    @foreach($sesionesRecientes as $sesion)
                    @php
                        $pct      = $totalEstClase > 0 ? round(($sesion->asistencias_count / $totalEstClase) * 100) : null;
                        $pctColor = $pct !== null ? ($pct >= 75 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500')) : 'text-gray-400';
                    @endphp
                    <tr class="hover:bg-[#f3faff] dark:hover:bg-[#162830] transition">
                        <td class="px-6 py-3 font-semibold text-[#000b60] dark:text-[#bcc2ff]">
                            {{ $sesion->fecha->format('D d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold">{{ $sesion->asistencias_count }}</span>
                            @if($totalEstClase !== null)
                                <span class="text-gray-400 text-xs"> / {{ $totalEstClase }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-bold {{ $pctColor }}">
                            {{ $pct !== null ? $pct . '%' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">
                            {{ $sesion->participaciones_count }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($sesion->finalizada)
                                <span class="bg-gray-100 dark:bg-[#2a3d4a] text-gray-500 dark:text-gray-400 text-xs font-semibold px-2.5 py-1 rounded-full">Finalizada</span>
                            @else
                                <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">Activa</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
    @endif

</div>

@push('pre-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    var charts = {};

    function destroyAll() {
        Object.values(charts).forEach(function (c) { if (c) c.destroy(); });
        charts = {};
    }

    function isDarkMode() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function renderAll(data) {
        if (typeof Chart === 'undefined') {
            setTimeout(function () { renderAll(data); }, 80);
            return;
        }
        destroyAll();
        var dark = isDarkMode();
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size   = 11;
        Chart.defaults.color       = dark ? '#94a3b8' : '#6b7280';
        renderAsistenciaSesion(data.asistenciaSesion);
        renderAsistenciaEstudiante(data.asistenciaEstudiante);
        renderParticipaciones(data.participaciones);
        renderRanking(data.rankingParticipacion);
    }

    function gridColor() { return isDarkMode() ? '#1e2d38' : '#f3f4f6'; }

    function renderAsistenciaSesion(d) {
        var ctx = document.getElementById('chartAsistenciaSesion');
        if (!ctx) return;
        charts.as = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    { label: 'Presentes', data: d.presentes, backgroundColor: '#4ade80', borderRadius: 4, borderSkipped: false },
                    { label: 'Ausentes',  data: d.ausentes,  backgroundColor: '#fca5a5', borderRadius: 4, borderSkipped: false }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } },
                    tooltip: { callbacks: { afterBody: function() { return d.totalEst > 0 ? ['Total clase: ' + d.totalEst] : []; } } }
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: gridColor() } }
                }
            }
        });
    }

    function renderAsistenciaEstudiante(d) {
        var ctx  = document.getElementById('chartAsistenciaEstudiante');
        var wrap = document.getElementById('chartAEWrap');
        if (!ctx || !wrap) return;
        var dynH = Math.max(200, d.labels.length * 26);
        ctx.style.height  = dynH + 'px';
        wrap.style.height = Math.min(dynH + 10, 230) + 'px';
        charts.ae = new Chart(ctx, {
            type: 'bar',
            data: { labels: d.labels, datasets: [{ label: '% Asistencia', data: d.data, backgroundColor: d.colores, borderRadius: 4, borderSkipped: false }] },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c) { return ' ' + c.raw + '%'; } } } },
                scales: {
                    x: { beginAtZero: true, max: 100, ticks: { callback: function(v) { return v + '%'; } }, grid: { color: gridColor() } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    function renderParticipaciones(d) {
        var ctx = document.getElementById('chartParticipaciones');
        if (!ctx) return;
        var dark = isDarkMode();
        charts.part = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    { type: 'bar',  label: 'Participaciones',    data: d.data,     backgroundColor: dark ? 'rgba(99,179,237,0.75)' : 'rgba(59,130,246,0.65)', borderRadius: 4, yAxisID: 'y' },
                    { type: 'line', label: 'Prom. calificación', data: d.promedio, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.08)', pointBackgroundColor: '#f97316', pointRadius: 4, tension: 0.3, fill: false, yAxisID: 'y2' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } },
                    tooltip: {
                        callbacks: {
                            label: function(c) {
                                if (c.datasetIndex === 0) return ' ' + c.raw + ' participación(es)';
                                return c.raw > 0 ? ' Promedio: ' + c.raw + ' / 100' : ' Sin calificación';
                            }
                        }
                    }
                },
                scales: {
                    y:  { beginAtZero: true, position: 'left',  suggestedMax: 5, ticks: { stepSize: 1, precision: 0 }, grid: { color: gridColor() } },
                    y2: { beginAtZero: true, position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, ticks: { stepSize: 20, callback: function(v) { return v + ' pts'; } } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }

    function renderRanking(d) {
        var ctx  = document.getElementById('chartRanking');
        var wrap = document.getElementById('chartRankingWrap');
        if (!ctx || !wrap) return;
        var dynH = Math.max(200, d.labels.length * 28);
        ctx.style.height  = dynH + 'px';
        wrap.style.height = Math.min(dynH + 10, 230) + 'px';
        // Gradiente de color según posición
        var palette = d.labels.map(function(_, i) {
            var ratio = d.labels.length > 1 ? i / (d.labels.length - 1) : 0;
            // oro → azul oscuro
            if (i === 0) return '#f59e0b';
            if (i === 1) return '#94a3b8';
            if (i === 2) return '#b45309';
            return 'rgba(0,11,96,' + (0.75 - ratio * 0.4) + ')';
        });
        charts.rank = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [{
                    label: 'Participaciones',
                    data: d.data,
                    backgroundColor: palette,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c) { return ' ' + c.raw + ' participaciones'; },
                            afterLabel: function(c) {
                                return d.promedios[c.dataIndex] > 0
                                    ? 'Prom. calificación: ' + d.promedios[c.dataIndex]
                                    : '';
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: gridColor() } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // Exponer para que el script inline lo llame en cada re-render
    window.__dashboardRender = renderAll;

    // Renderizar al cargar la página por primera vez
    function tryInitRender() {
        if (window.__dashboardData) {
            renderAll(window.__dashboardData);
        }
    }

    document.addEventListener('livewire:initialized', tryInitRender);
    document.addEventListener('livewire:navigated',   tryInitRender);

    // Actualizar al cambiar filtros (evento Livewire dispatch)
    window.addEventListener('dashboard-charts', function (e) {
        renderAll(e.detail.data);
    });
})();
</script>
@endpush
