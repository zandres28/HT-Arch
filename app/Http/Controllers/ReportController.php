<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $projectId = $request->input('project_id');

        $range = $this->resolveRange($request, $period);

        $query = WorkLog::with('project')->withCount(['attachments', 'deliverables'])
            ->whereBetween('date', [$range['from'], $range['to']])
            ->orderBy('date')->orderBy('id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $logs = $query->get();
        $totalHours = (float) $logs->sum('hours');

        $byProject = $logs->groupBy('project_id')->map(fn ($items) => [
            'name' => $items->first()->project?->name ?? 'Sin proyecto',
            'hours' => round((float) $items->sum('hours'), 2),
            'count' => $items->count(),
        ])->sortByDesc('hours')->values();

        $daysWorked = $logs->pluck('date')->unique()->count();
        $avg = $daysWorked ? round($totalHours / $daysWorked, 2) : 0;

        return view('reports.index', [
            'logs' => $logs,
            'totalHours' => $totalHours,
            'byProject' => $byProject,
            'daysWorked' => $daysWorked,
            'avg' => $avg,
            'range' => $range,
            'period' => $period,
            'projectId' => $projectId,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'color']),
            'now' => Carbon::now(),
        ]);
    }

    public function pdf(Request $request)
    {
        $period = $request->input('period', 'month');
        $projectId = $request->input('project_id');
        $range = $this->resolveRange($request, $period);

        $query = WorkLog::with('project')->withCount(['attachments', 'deliverables'])
            ->whereBetween('date', [$range['from'], $range['to']])
            ->orderBy('date')->orderBy('id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $logs = $query->get();
        $totalHours = (float) $logs->sum('hours');

        $byProject = $logs->groupBy('project_id')->map(fn ($items) => [
            'name' => $items->first()->project?->name ?? 'Sin proyecto',
            'hours' => round((float) $items->sum('hours'), 2),
            'count' => $items->count(),
        ])->sortByDesc('hours')->values();

        $daysWorked = $logs->pluck('date')->unique()->count();
        $avg = $daysWorked ? round($totalHours / $daysWorked, 2) : 0;

        $title = $this->titleFor($period, $range);
        $now = Carbon::now();

        $pdf = Pdf::loadView('reports.pdf', compact(
            'logs', 'totalHours', 'byProject', 'daysWorked', 'avg', 'range', 'title', 'now'
        ));
        $pdf->setPaper('A4', 'landscape');

        $filename = 'reporte-' . $range['from']->format('Y-m-d') . '_' . $range['to']->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function csv(Request $request)
    {
        $period = $request->input('period', 'month');
        $projectId = $request->input('project_id');
        $range = $this->resolveRange($request, $period);

        $query = WorkLog::with('project')
            ->whereBetween('date', [$range['from'], $range['to']])
            ->orderBy('date')->orderBy('id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $logs = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reporte-' . $range['from']->format('Y-m-d') . '_' . $range['to']->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Fecha', 'Proyecto', 'Actividad', 'Descripción', 'Horas', 'Inicio', 'Fin', 'Evidencias', 'Entregables']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->date->format('Y-m-d'),
                    $log->project?->name ?? '',
                    $log->activity,
                    $log->description,
                    number_format($log->hours, 2),
                    $log->start_time?->format('H:i') ?? '',
                    $log->end_time?->format('H:i') ?? '',
                    $log->attachments()->count(),
                    $log->deliverables()->count(),
                ]);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function resolveRange(Request $request, string $period): array
    {
        $today = today();

        return match ($period) {
            'week' => $this->rangeForWeek($request->input('week')),
            'range' => [
                'from' => $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : $today->copy()->startOfWeek(),
                'to' => $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : $today->copy()->endOfWeek(),
            ],
            'project' => [
                'from' => $today->copy()->startOfYear(),
                'to' => $today->copy()->endOfDay(),
            ],
            default => [
                'from' => $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth() : $today->copy()->startOfMonth(),
                'to' => $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->input('month'))->endOfMonth() : $today->copy()->endOfMonth(),
            ],
        };
    }

    private function rangeForWeek(?string $week): array
    {
        if ($week && preg_match('/^(\d{4})-W(\d{2})$/', $week, $m)) {
            $start = Carbon::now()->setISODate((int) $m[1], (int) $m[2])->startOfWeek();
        } else {
            $start = today()->startOfWeek();
        }

        return ['from' => $start, 'to' => $start->copy()->endOfWeek()];
    }

    private function titleFor(string $period, array $range): string
    {
        return match ($period) {
            'week' => 'Reporte semanal (' . $range['from']->format('d/m/Y') . ' – ' . $range['to']->format('d/m/Y') . ')',
            'range' => 'Reporte por rango (' . $range['from']->format('d/m/Y') . ' – ' . $range['to']->format('d/m/Y') . ')',
            'project' => 'Reporte anual por proyecto (' . $range['from']->format('Y') . ')',
            default => 'Reporte mensual (' . $range['from']->translatedFormat('F Y') . ')',
        };
    }
}
