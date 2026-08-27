@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Periodo</label>
                <select name="period"
                        class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Mensual</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Semanal</option>
                    <option value="range" {{ $period === 'range' ? 'selected' : '' }}>Por rango</option>
                    <option value="project" {{ $period === 'project' ? 'selected' : '' }}>Anual por proyecto</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Proyecto</label>
                <select name="project_id" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Todos los proyectos</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" {{ (string) $projectId === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Mes</label>
                <input type="month" name="month" value="{{ $range['from']->format('Y-m') }}"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Ver reporte</button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-4">
            <a href="{{ route('reports.pdf', request()->query()) }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium px-4 py-2 rounded-lg">
                ⬇ Descargar PDF
            </a>
            <a href="{{ route('reports.csv', request()->query()) }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                ⬇ Exportar CSV
            </a>
        </div>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total de horas</p>
            <p class="text-xl font-bold text-slate-800">{{ number_format($totalHours, 2) }} h</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Actividades</p>
            <p class="text-xl font-bold text-slate-800">{{ $logs->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Días trabajados</p>
            <p class="text-xl font-bold text-slate-800">{{ $daysWorked }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Promedio / día</p>
            <p class="text-xl font-bold text-slate-800">{{ number_format($avg, 2) }} h</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
        <h3 class="font-semibold text-slate-800 mb-3">Resumen por proyecto</h3>
        @if ($byProject->isEmpty())
            <p class="text-sm text-slate-500">Sin actividades en el periodo.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-slate-100">
                        <th class="py-2 font-medium">Proyecto</th>
                        <th class="py-2 font-medium text-right">Horas</th>
                        <th class="py-2 font-medium text-right">Actividades</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byProject as $row)
                        <tr class="border-b border-slate-50">
                            <td class="py-2">{{ $row['name'] }}</td>
                            <td class="py-2 text-right font-medium">{{ number_format($row['hours'], 2) }} h</td>
                            <td class="py-2 text-right text-slate-500">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Detalle de actividades</h3>
        @if ($logs->isEmpty())
            <p class="text-sm text-slate-500">No hay actividades que mostrar.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-slate-100">
                            <th class="py-2 font-medium">Fecha</th>
                            <th class="py-2 font-medium">Proyecto</th>
                            <th class="py-2 font-medium">Actividad</th>
                            <th class="py-2 font-medium text-right">Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-b border-slate-50">
                                <td class="py-2 whitespace-nowrap text-slate-500">{{ $log->date->format('d/m/Y') }}</td>
                                <td class="py-2">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $log->project->color ?? '#94a3b8' }}"></span>
                                        {{ $log->project?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="py-2">{{ $log->activity }}</td>
                                <td class="py-2 text-right font-medium">{{ number_format($log->hours, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-slate-800">
                            <td class="py-2" colspan="3">Total</td>
                            <td class="py-2 text-right">{{ number_format($totalHours, 2) }} h</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
@endsection
