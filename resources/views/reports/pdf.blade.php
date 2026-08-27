<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 18px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1e293b; font-size: 11px; margin: 0; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #4338ca; }
        .sub { color: #64748b; margin: 0 0 14px; }
        .cards { display: flex; gap: 10px; margin-bottom: 16px; }
        .card { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; background: #f8fafc; }
        .card .label { color: #64748b; font-size: 10px; }
        .card .value { font-size: 16px; font-weight: bold; }
        h2 { font-size: 13px; margin: 16px 0 6px; border-bottom: 2px solid #6366f1; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 5px 7px; border-bottom: 1px solid #e2e8f0; }
        th { background: #eef2ff; color: #4338ca; font-size: 10px; text-transform: uppercase; }
        td.num { text-align: right; }
        .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }
        tfoot td { font-weight: bold; border-top: 2px solid #cbd5e1; }
        .foot { margin-top: 18px; color: #94a3b8; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <h1>HT-Arch · Bitácora profesional</h1>
    <p class="sub">{{ $title }} &nbsp;|&nbsp; Generado el {{ $now->format('d/m/Y H:i') }}</p>

    <div class="cards">
        <div class="card"><div class="label">Total de horas</div><div class="value">{{ number_format($totalHours, 2) }} h</div></div>
        <div class="card"><div class="label">Actividades</div><div class="value">{{ $logs->count() }}</div></div>
        <div class="card"><div class="label">Días trabajados</div><div class="value">{{ $daysWorked }}</div></div>
        <div class="card"><div class="label">Promedio / día</div><div class="value">{{ number_format($avg, 2) }} h</div></div>
    </div>

    <h2>Resumen por proyecto</h2>
    @if ($byProject->isNotEmpty())
        <table>
            <thead>
                <tr><th>Proyecto</th><th class="num">Horas</th><th class="num">Actividades</th></tr>
            </thead>
            <tbody>
                @foreach ($byProject as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="num">{{ number_format($row['hours'], 2) }} h</td>
                        <td class="num">{{ $row['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Sin actividades en el periodo.</p>
    @endif

    <h2>Detalle de actividades</h2>
    @if ($logs->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Fecha</th><th>Proyecto</th><th>Actividad</th>
                    <th>Descripción</th><th class="num">Horas</th>
                    <th class="num">Ev.</th><th class="num">Ent.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->date->format('d/m/Y') }}</td>
                        <td><span class="dot" style="background-color: {{ $log->project->color ?? '#94a3b8' }}"></span>{{ $log->project?->name ?? '—' }}</td>
                        <td>{{ $log->activity }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($log->description, 80) }}</td>
                        <td class="num">{{ number_format($log->hours, 2) }}</td>
                        <td class="num">{{ $log->attachments_count }}</td>
                        <td class="num">{{ $log->deliverables_count }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="4">Total</td><td class="num">{{ number_format($totalHours, 2) }} h</td><td></td><td></td></tr>
            </tfoot>
        </table>
    @else
        <p>No hay actividades que mostrar.</p>
    @endif

    <p class="foot">HT-Arch · Reporte generado automáticamente</p>
</body>
</html>
