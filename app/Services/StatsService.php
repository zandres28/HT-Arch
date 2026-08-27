<?php

namespace App\Services;

use App\Models\Project;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Centraliza todos los cálculos de horas (día, semana, mes, total, promedios,
 * desglose por proyecto y series temporales) para el dashboard, reportes y calendario.
 */
class StatsService
{
    public function today(Carbon $date = null): array
    {
        $date = $date ?? today();

        $logs = WorkLog::whereDate('date', $date)
            ->with('project')
            ->get();

        return [
            'hours' => (float) $logs->sum('hours'),
            'count' => $logs->count(),
            'projects' => $logs->pluck('project')->filter()->unique('id')->count(),
        ];
    }

    public function week(Carbon $date = null): array
    {
        $date = $date ?? today();
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        $logs = WorkLog::whereBetween('date', [$start, $end])
            ->with('project')
            ->get();

        $daysWithActivity = $logs->pluck('date')->unique()->count();

        return [
            'hours' => (float) $logs->sum('hours'),
            'count' => $logs->count(),
            'avg_per_day' => $daysWithActivity ? round($logs->sum('hours') / $daysWithActivity, 2) : 0,
            'days_worked' => $daysWithActivity,
            'by_project' => $this->groupByProject($logs),
        ];
    }

    public function month(Carbon $date = null): array
    {
        $date = $date ?? today();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $logs = WorkLog::whereBetween('date', [$start, $end])
            ->with('project')
            ->get();

        $daysWithActivity = $logs->pluck('date')->unique()->count();

        return [
            'hours' => (float) $logs->sum('hours'),
            'count' => $logs->count(),
            'avg_per_day' => $daysWithActivity ? round($logs->sum('hours') / $daysWithActivity, 2) : 0,
            'days_worked' => $daysWithActivity,
            'by_project' => $this->groupByProject($logs),
        ];
    }

    public function general(): array
    {
        $logs = WorkLog::with('project')->get();
        $daysWithActivity = $logs->pluck('date')->unique()->count();

        return [
            'hours' => (float) $logs->sum('hours'),
            'count' => $logs->count(),
            'projects' => Project::notArchived()->count(),
            'evidence' => \App\Models\Attachment::count(),
            'deliverables' => \App\Models\Deliverable::count(),
            'avg_per_day' => $daysWithActivity ? round($logs->sum('hours') / $daysWithActivity, 2) : 0,
        ];
    }

    /** Distribución de horas por proyecto (para gráfico de dona). */
    public function distributionByProject(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = WorkLog::query()->with('project');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        }

        $logs = $query->get();

        return $logs->groupBy('project_id')->map(function (Collection $items) {
            $project = $items->first()->project;

            return [
                'id' => $project?->id,
                'name' => $project?->name ?? 'Sin proyecto',
                'color' => $project?->color ?? '#94a3b8',
                'hours' => round((float) $items->sum('hours'), 2),
            ];
        })->sortByDesc('hours')->values();
    }

    /** Horas por día en un rango (para gráfico de barras). */
    public function dailySeries(Carbon $from, Carbon $to): Collection
    {
        $logs = WorkLog::whereBetween('date', [$from, $to])
            ->selectRaw('date, SUM(hours) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $series = collect();
        for ($day = $from->copy(); $day <= $to; $day->addDay()) {
            $key = $day->format('Y-m-d');
            $series->push([
                'date' => $key,
                'label' => $day->format('d/m'),
                'hours' => round((float) ($logs[$key] ?? 0), 2),
            ]);
        }

        return $series;
    }

    /** Evolución mensual (últimos N meses). */
    public function monthlySeries(int $months = 6): Collection
    {
        $end = today()->startOfMonth();
        $start = $end->copy()->subMonths($months - 1);

        $logs = WorkLog::whereBetween('date', [$start, $end->copy()->endOfMonth()])
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as ym, SUM(hours) as total')
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $series = collect();
        for ($m = $start->copy(); $m <= $end; $m->addMonth()) {
            $key = $m->format('Y-m');
            $series->push([
                'month' => $key,
                'label' => ucfirst($m->translatedFormat('M')),
                'hours' => round((float) ($logs[$key] ?? 0), 2),
            ]);
        }

        return $series;
    }

    private function groupByProject(Collection $logs): Collection
    {
        return $logs->groupBy('project_id')->map(function (Collection $items) {
            $project = $items->first()->project;

            return [
                'name' => $project?->name ?? 'Sin proyecto',
                'color' => $project?->color ?? '#94a3b8',
                'hours' => round((float) $items->sum('hours'), 2),
            ];
        })->sortByDesc('hours')->values();
    }
}
