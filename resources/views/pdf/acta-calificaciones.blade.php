<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        color: #1a1a2e;
        background: #fff;
    }

    /* ── Encabezado ── */
    .header {
        background: #000b60;
        color: #fff;
        padding: 14px 18px 10px;
        margin-bottom: 10px;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }
    .header-title {
        font-size: 17px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .header-sub {
        font-size: 9px;
        opacity: 0.75;
        margin-top: 2px;
    }
    .header-badge {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 4px;
        padding: 4px 9px;
        font-size: 8px;
        text-align: center;
    }
    .header-badge-label { opacity: 0.7; font-size: 7px; display: block; }

    /* ── Ficha de clase ── */
    .info-grid {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        background: #f5f7ff;
        border: 1px solid #d0d5f5;
        border-radius: 4px;
    }
    .info-grid td {
        padding: 5px 10px;
        font-size: 8.5px;
        border-right: 1px solid #d0d5f5;
    }
    .info-grid td:last-child { border-right: none; }
    .info-label {
        font-size: 7px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: block;
        margin-bottom: 1px;
    }
    .info-value {
        font-weight: bold;
        color: #000b60;
    }

    /* ── Tabla principal ── */
    .table-wrap { width: 100%; }

    table.grades {
        width: 100%;
        border-collapse: collapse;
        font-size: 8px;
    }
    table.grades thead tr.head-main th {
        background: #000b60;
        color: #fff;
        padding: 5px 4px;
        text-align: center;
        font-size: 7.5px;
        font-weight: bold;
        border: 1px solid #000b60;
    }
    table.grades thead tr.head-main th.col-left {
        text-align: left;
        padding-left: 7px;
    }
    table.grades thead tr.head-sub th {
        background: #e8ecff;
        color: #000b60;
        padding: 3px 4px;
        text-align: center;
        font-size: 7px;
        border: 1px solid #c5cdf5;
    }
    table.grades tbody tr {
        border-bottom: 1px solid #edf0ff;
    }
    table.grades tbody tr:nth-child(even) {
        background: #f8f9ff;
    }
    table.grades tbody td {
        padding: 4px 4px;
        border: 1px solid #e5e8f5;
        text-align: center;
        font-size: 8px;
    }
    table.grades tbody td.col-carnet {
        font-family: DejaVu Sans Mono, monospace;
        font-size: 7.5px;
        color: #000b60;
        text-align: left;
        padding-left: 7px;
    }
    table.grades tbody td.col-nombre {
        text-align: left;
        font-weight: bold;
        font-size: 8px;
        padding-left: 5px;
    }
    .pts-value { font-weight: bold; }
    .pts-pct   { color: #6b7280; font-size: 6.5px; margin-left: 1px; }
    .empty     { color: #c0c4d6; }

    .total-cell {
        font-size: 9.5px;
        font-weight: bold;
    }
    .total-green { color: #15803d; }
    .total-red   { color: #dc2626; }
    .total-gray  { color: #6b7280; }

    .estado-aprobado {
        background: #dcfce7;
        color: #15803d;
        border-radius: 3px;
        padding: 1px 5px;
        font-size: 7px;
        font-weight: bold;
    }
    .estado-reprobado {
        background: #fee2e2;
        color: #dc2626;
        border-radius: 3px;
        padding: 1px 5px;
        font-size: 7px;
        font-weight: bold;
    }
    .estado-pendiente {
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 3px;
        padding: 1px 5px;
        font-size: 7px;
    }

    /* ── Resumen estadístico ── */
    .stats-row {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .stats-row td {
        width: 25%;
        padding: 7px 10px;
        text-align: center;
        border: 1px solid #e5e8f5;
        background: #f8f9ff;
    }
    .stat-num { font-size: 15px; font-weight: bold; color: #000b60; display: block; }
    .stat-lbl { font-size: 7px; color: #6b7280; display: block; margin-top: 1px; }
    .stat-green .stat-num { color: #15803d; }
    .stat-red   .stat-num { color: #dc2626; }
    .stat-amber .stat-num { color: #d97706; }

    /* ── Firma ── */
    .firma-section {
        margin-top: 18px;
        width: 100%;
        border-collapse: collapse;
    }
    .firma-section td {
        width: 50%;
        padding: 0 20px;
        text-align: center;
        vertical-align: bottom;
    }
    .firma-line {
        border-top: 1px solid #6b7280;
        padding-top: 4px;
        font-size: 8px;
        color: #374151;
    }
    .firma-label {
        font-size: 7px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Footer ── */
    .footer {
        margin-top: 12px;
        padding-top: 6px;
        border-top: 1px solid #e5e8f5;
        display: flex;
        justify-content: space-between;
        font-size: 7px;
        color: #9ca3af;
    }
    .footer-legend span { margin-right: 10px; }
    .dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 2px; vertical-align: middle; }
    .dot-green { background: #15803d; }
    .dot-red   { background: #dc2626; }
    .dot-gray  { background: #9ca3af; }
</style>
</head>
<body>

{{-- ── ENCABEZADO ── --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-title">ClassAssist Pro</div>
            <div class="header-sub">Acta Oficial de Calificaciones</div>
        </div>
        <div class="header-badge">
            <span class="header-badge-label">Generado</span>
            {{ now()->translatedFormat('d/m/Y H:i') }}
        </div>
    </div>
</div>

{{-- ── FICHA DE CLASE ── --}}
<table class="info-grid">
    <tr>
        <td style="width:35%">
            <span class="info-label">Clase</span>
            <span class="info-value">{{ $clase->nombre }}</span>
        </td>
        <td style="width:25%">
            <span class="info-label">Carrera</span>
            <span class="info-value">{{ $clase->carrera?->nombre ?? '—' }}</span>
        </td>
        <td style="width:20%">
            <span class="info-label">Ciclo / Semestre</span>
            <span class="info-value">{{ $clase->ciclo ?? '—' }} · {{ $clase->semestre ?? '—' }}</span>
        </td>
        <td style="width:20%">
            <span class="info-label">Catedrático</span>
            <span class="info-value">{{ $clase->catedratico?->nombre ?? auth()->user()->nombre }}</span>
        </td>
    </tr>
</table>

{{-- ── TABLA DE CALIFICACIONES ── --}}
<div class="table-wrap">
<table class="grades">
    <thead>
        <tr class="head-main">
            <th class="col-left" style="width:80px">#</th>
            <th class="col-left" style="width:90px">Carné</th>
            <th class="col-left" style="min-width:130px">Nombre</th>
            @foreach($tipos as $tipo)
            <th style="width:70px">{{ $tipo->nombre }}<br><span style="font-weight:normal;font-size:6.5px;opacity:0.8">/ {{ $tipo->punteo_max }} pts</span></th>
            @endforeach
            <th style="width:55px">TOTAL<br><span style="font-weight:normal;font-size:6.5px;opacity:0.8">/ 100</span></th>
            <th style="width:60px">Estado</th>
        </tr>
        <tr class="head-sub">
            <th colspan="3" style="text-align:left;padding-left:7px">Estudiante</th>
            @foreach($tipos as $tipo)
            <th>Aprobación ≥ {{ round($tipo->punteo_max * 0.61, 0) }}</th>
            @endforeach
            <th>Aprobación ≥ 61</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($resumen as $i => $fila)
        @php
            $total    = $fila['total'];
            $aprobado = $fila['aprobado'];
            $totalClass = $aprobado === true ? 'total-green' : ($aprobado === false ? 'total-red' : 'total-gray');
        @endphp
        <tr>
            <td class="col-carnet" style="color:#9ca3af;font-weight:normal">{{ $i + 1 }}</td>
            <td class="col-carnet">{{ $fila['carnet'] }}</td>
            <td class="col-nombre">{{ $fila['nombre'] }}</td>
            @foreach($tipos as $tipo)
            @php
                $d   = $fila['tipos'][$tipo->id] ?? ['pts' => null, 'max' => 0];
                $pts = $d['pts'];
                $max = $d['max'];
                $pct = $pts !== null && $max > 0 ? round($pts / $max * 100) : null;
            @endphp
            <td>
                @if($pts !== null)
                    <span class="pts-value">{{ number_format($pts, 2) }}</span>
                    <span class="pts-pct">({{ $pct }}%)</span>
                @else
                    <span class="empty">—</span>
                @endif
            </td>
            @endforeach
            <td class="total-cell {{ $totalClass }}">{{ number_format($total, 2) }}</td>
            <td>
                @if($aprobado === true)
                    <span class="estado-aprobado">Aprobado</span>
                @elseif($aprobado === false)
                    <span class="estado-reprobado">Reprobado</span>
                @else
                    <span class="estado-pendiente">Pendiente</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

{{-- ── ESTADÍSTICAS ── --}}
@php
    $total_est  = $resumen->count();
    $aprobados  = $resumen->where('aprobado', true)->count();
    $reprobados = $resumen->where('aprobado', false)->count();
    $pendientes = $resumen->whereStrict('aprobado', null)->count();
    $promedio   = $total_est > 0 ? round($resumen->avg('total'), 2) : 0;
@endphp
<table class="stats-row">
    <tr>
        <td>
            <span class="stat-num">{{ $total_est }}</span>
            <span class="stat-lbl">Estudiantes</span>
        </td>
        <td class="stat-green">
            <span class="stat-num">{{ $aprobados }}</span>
            <span class="stat-lbl">Aprobados</span>
        </td>
        <td class="stat-red">
            <span class="stat-num">{{ $reprobados }}</span>
            <span class="stat-lbl">Reprobados</span>
        </td>
        <td class="stat-amber">
            <span class="stat-num">{{ number_format($promedio, 2) }}</span>
            <span class="stat-lbl">Promedio del grupo</span>
        </td>
    </tr>
</table>

{{-- ── FIRMAS ── --}}
<table class="firma-section">
    <tr>
        <td>
            <div style="height:28px"></div>
            <div class="firma-line">{{ $clase->catedratico?->nombre ?? auth()->user()->nombre }}</div>
            <div class="firma-label">Firma del Catedrático</div>
        </td>
        <td>
            <div style="height:28px"></div>
            <div class="firma-line">&nbsp;</div>
            <div class="firma-label">Sello / Vo.Bo.</div>
        </td>
    </tr>
</table>

{{-- ── FOOTER ── --}}
<div class="footer">
    <div class="footer-legend">
        <span><span class="dot dot-green"></span>Aprobado ≥ 61 pts</span>
        <span><span class="dot dot-red"></span>Reprobado &lt; 61 pts</span>
        <span><span class="dot dot-gray"></span>Pendiente — notas incompletas</span>
    </div>
    <div>ClassAssist Pro · {{ now()->format('Y') }}</div>
</div>

</body>
</html>
