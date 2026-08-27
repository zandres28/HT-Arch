@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Resumen principal --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card label="Hoy" :value="number_format($today['hours'], 2) . ' h'" :sub="$today['count'] . ' actividad' . ($today['count'] === 1 ? '' : 'es') . ' · ' . $today['projects'] . ' proyecto' . ($today['projects'] === 1 ? '' : 's')" icon="sun" color="indigo" />
        <x-stat-card label="Esta semana" :value="number_format($week['hours'], 2) . ' h'" :sub="$week['days_worked'] . ' días · ' . number_format($week['avg_per_day'], 2) . ' h/día'" icon="calendar" color="emerald" />
        <x-stat-card label="Este mes" :value="number_format($month['hours'], 2) . ' h'" :sub="$month['days_worked'] . ' días trabajados · ' . $month['count'] . ' actividades'" icon="chart" color="amber" />
        <x-stat-card label="Total acumulado" :value="number_format($general['hours'], 2) . ' h'" :sub="$general['projects'] . ' proyectos · ' . $general['count'] . ' registros'" icon="clock" color="slate" />
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
        <div class="bg-white rounded-xl border border-slate-200 p-5 lg:col-span-2">
            <h3 class="font-semibold text-slate-800 mb-4">Horas por día ({{ today()->translatedFormat('F Y') }})</h3>
            <canvas id="chartDaily" height="120"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-4">Distribución por proyecto</h3>
            <canvas id="chartProject" height="120"></canvas>
            @if ($distribution->isEmpty())
                <p class="text-sm text-slate-400 text-center mt-4">Sin datos este mes.</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5 mt-5">
        <h3 class="font-semibold text-slate-800 mb-4">Evolución mensual (últimos 6 meses)</h3>
        <canvas id="chartMonthly" height="90"></canvas>
    </div>

    {{-- Actividad reciente + contadores --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
        <div class="bg-white rounded-xl border border-slate-200 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-800">Actividad reciente</h3>
                <a href="{{ route('work-logs.index') }}" class="text-sm text-indigo-600 hover:underline">Ver historial</a>
            </div>
            @if ($recent->isEmpty())
                <p class="text-sm text-slate-500">Aún no has registrado actividades.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($recent as $log)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <a href="{{ route('work-logs.show', $log) }}" class="flex items-center gap-2.5 min-w-0 hover:text-indigo-600">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $log->project->color ?? '#94a3b8' }}"></span>
                                <span class="truncate">{{ $log->activity }}</span>
                            </a>
                            <span class="flex items-center gap-3 text-slate-500 shrink-0">
                                <span class="text-slate-400">{{ $log->date->format('d/m') }}</span>
                                <span class="font-medium text-slate-700">{{ number_format($log->hours, 2) }} h</span>
                                @if ($log->attachments_count || $log->deliverables_count)
                                    <span class="text-xs">{{ $log->attachments_count ? '📎' . $log->attachments_count : '' }} {{ $log->deliverables_count ? '📦' . $log->deliverables_count : '' }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-3">Resumen general</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Total de horas</dt><dd class="font-semibold">{{ number_format($general['hours'], 2) }} h</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Proyectos</dt><dd class="font-semibold">{{ $general['projects'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Actividades</dt><dd class="font-semibold">{{ $general['count'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Evidencias</dt><dd class="font-semibold">{{ $general['evidence'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Entregables</dt><dd class="font-semibold">{{ $general['deliverables'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Promedio / día c/trabajo</dt><dd class="font-semibold">{{ number_format($general['avg_per_day'], 2) }} h</dd></div>
            </dl>
        </div>
    </div>

    @push('scripts')
        <script>
            const palette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#64748b'];

            // Horas por día
            new Chart(document.getElementById('chartDaily'), {
                type: 'bar',
                data: {
                    labels: @json($daily->pluck('label')),
                    datasets: [{
                        label: 'Horas',
                        data: @json($daily->pluck('hours')),
                        backgroundColor: '#6366f1',
                        borderRadius: 4,
                        maxBarThickness: 22,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { grid: { display: false } },
                    },
                },
            });

            // Distribución por proyecto (dona)
            new Chart(document.getElementById('chartProject'), {
                type: 'doughnut',
                data: {
                    labels: @json($distribution->pluck('name')),
                    datasets: [{
                        data: @json($distribution->pluck('hours')),
                        backgroundColor: @json($distribution->pluck('color')->count() ? $distribution->pluck('color') : []),
                        borderWidth: 2,
                        borderColor: '#fff',
                    }],
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    },
                    cutout: '62%',
                },
            });

            // Evolución mensual
            new Chart(document.getElementById('chartMonthly'), {
                type: 'line',
                data: {
                    labels: @json($monthly->pluck('label')),
                    datasets: [{
                        label: 'Horas',
                        data: @json($monthly->pluck('hours')),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        </script>
    @endpush
@endsection
