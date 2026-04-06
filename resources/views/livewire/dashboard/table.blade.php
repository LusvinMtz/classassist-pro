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

    {{-- Sin clases --}}
    @if($clases->isEmpty())
        <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined" style="font-size:56px">school</span>
            <p class="mt-3 font-semibold text-gray-500">No tienes clases registradas aún</p>
            <p class="text-sm mt-1">Crea una clase para ver estadísticas aquí</p>
            <a href="{{ route('clases.index') }}"
               class="mt-5 bg-[#000b60] text-white px-5 py-2 rounded-lg font-semibold text-sm hover:opacity-90 transition">
                Ir a Clases
            </a>
        </div>
    @else

    {{-- Filtros --}}
    <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">

        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1.5 uppercase tracking-wide">Clase</label>
            <select wire:model.live="claseId"
                    class="w-full border border-gray-200 dark:border-[#1a3040] dark:bg-[#162830] dark:text-[#dff4ff] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#000b60] dark:focus:ring-[#bcc2ff]">
                @foreach($clases as $clase)
                    <option value="{{ $clase->id }}">{{ $clase->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-[#000b60] dark:text-[#bcc2ff] mb-1.5 uppercase tracking-wide">Período (sesiones)</label>
            <div class="flex gap-1.5">
                @foreach(['5' => 'Últ. 5', '10' => 'Últ. 10', '20' => 'Últ. 20', 'todo' => 'Todo'] as $val => $lbl)
                    <button wire:click="$set('periodo', '{{ $val }}')"
                            class="px-3 py-2 rounded-lg text-xs font-bold border transition
                                {{ $periodo === $val
                                    ? 'bg-[#000b60] dark:bg-[#303c9a] text-white border-[#000b60] dark:border-[#303c9a]'
                                    : 'bg-white dark:bg-[#162830] text-[#000b60] dark:text-[#bcc2ff] border-gray-200 dark:border-[#1a3040] hover:bg-blue-50 dark:hover:bg-[#1a3040]' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        @if($claseNombre)
        <div class="ml-auto text-right hidden md:block">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Clase seleccionada</p>
            <p class="text-sm font-black text-[#000b60] dark:text-[#bcc2ff]">{{ $claseNombre }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $totalEstClase }} estudiante(s)</p>
        </div>
        @endif

    </div>

    {{-- Datos iniciales para las gráficas (leído por JS, no Alpine) --}}
    <script>window.__dashboardData = {!! json_encode($chartData) !!};</script>

    {{-- Gráficas (wire:ignore protege los canvas del morphing de Livewire) --}}
    <div wire:ignore>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <div class="bg-white dark:bg-[#1e333c] rounded-xl shadow p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff] text-base leading-tight">Asistencia por sesión</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Presentes vs. ausentes</p>
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
                        <h3 class="font-black text-[#000b60] dark:text-[#bcc2ff] text-base leading-tight">Calificaciones por tipo</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Promedio por categoría de evaluación</p>
                    </div>
                    <span class="material-symbols-outlined text-[#000b60] dark:text-[#bcc2ff] opacity-20" style="font-size:28px">grading</span>
                </div>
                <div style="position:relative; height:230px;">
                    <canvas id="chartCalificaciones"></canvas>
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
                <p class="mt-2 text-sm">No hay sesiones registradas para esta clase</p>
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
                        $pct      = $totalEstClase > 0 ? round(($sesion->asistencias_count / $totalEstClase) * 100) : 0;
                        $pctColor = $pct >= 75 ? 'text-green-600' : ($pct >= 50 ? 'text-orange-500' : 'text-red-500');
                    @endphp
                    <tr class="hover:bg-[#f3faff] dark:hover:bg-[#162830] transition">
                        <td class="px-6 py-3 font-semibold text-[#000b60] dark:text-[#bcc2ff]">
                            {{ $sesion->fecha->format('D d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold">{{ $sesion->asistencias_count }}</span>
                            <span class="text-gray-400 text-xs"> / {{ $totalEstClase }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold {{ $pctColor }}">
                            {{ $pct }}%
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700">
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

    var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    function renderAll(data) {
        destroyAll();
        isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size   = 11;
        Chart.defaults.color       = isDark ? '#94a3b8' : '#6b7280';
        renderAsistenciaSesion(data.asistenciaSesion);
        renderAsistenciaEstudiante(data.asistenciaEstudiante);
        renderParticipaciones(data.participaciones);
        renderCalificaciones(data.calificaciones);
    }

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
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: isDark ? '#1e2d38' : '#f3f4f6' } }
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
                    x: { beginAtZero: true, max: 100, ticks: { callback: function(v) { return v + '%'; } }, grid: { color: isDark ? '#1e2d38' : '#f3f4f6' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    function renderParticipaciones(d) {
        var ctx = document.getElementById('chartParticipaciones');
        if (!ctx) return;
        charts.part = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    { type: 'bar',  label: 'Participaciones',   data: d.data,     backgroundColor: 'rgba(0,11,96,0.75)', borderRadius: 4, yAxisID: 'y' },
                    { type: 'line', label: 'Prom. calificación', data: d.promedio, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.08)', pointBackgroundColor: '#f97316', pointRadius: 4, tension: 0.3, fill: true, yAxisID: 'y2' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } } },
                scales: {
                    y:  { beginAtZero: true, position: 'left',  ticks: { stepSize: 1 }, grid: { color: isDark ? '#1e2d38' : '#f3f4f6' } },
                    y2: { beginAtZero: true, position: 'right', max: 10, grid: { drawOnChartArea: false }, ticks: { stepSize: 2 } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }

    function renderCalificaciones(d) {
        var ctx = document.getElementById('chartCalificaciones');
        if (!ctx) return;
        var palette = ['#000b60','#1d4ed8','#3b82f6','#60a5fa','#93c5fd','#bfdbfe'];
        charts.cal = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [{ label: 'Promedio', data: d.data, backgroundColor: d.labels.map(function(_, i) { return palette[i % palette.length]; }), borderRadius: 6, borderSkipped: false }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { afterLabel: function(c) { return d.totales[c.dataIndex] ? 'Registros: ' + d.totales[c.dataIndex] : ''; } } }
                },
                scales: {
                    y: { beginAtZero: true, max: 10, ticks: { stepSize: 1 }, grid: { color: isDark ? '#1e2d38' : '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Inicializar al cargar la página
    document.addEventListener('livewire:initialized', function () {
        if (window.__dashboardData) renderAll(window.__dashboardData);
    });

    // Actualizar al cambiar filtros
    window.addEventListener('dashboard-charts', function (e) {
        renderAll(e.detail.data);
    });
})();
</script>
@endpush
