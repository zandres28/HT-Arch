<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkLog;
use App\Services\StatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(private StatsService $stats) {}

    public function index(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
            : today()->startOfMonth();

        $start = $month->copy()->startOfMonth()->startOfWeek();
        $end = $month->copy()->endOfMonth()->endOfWeek();

        $logs = WorkLog::with('project')
            ->whereBetween('date', [$start, $end])
            ->get();

        $byDay = $logs->groupBy(fn ($log) => $log->date->format('Y-m-d'));

        $weeks = [];
        for ($day = $start->copy(); $day <= $end; $day->addDay()) {
            $key = $day->format('Y-m-d');
            $weeks[$day->weekOfMonth][] = [
                'date' => $day->copy(),
                'in_month' => $day->month === $month->month,
                'is_today' => $day->isToday(),
                'logs' => $byDay->get($key, collect()),
                'total' => round((float) ($byDay->get($key, collect())->sum('hours')), 2),
            ];
        }

        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');

        $monthStats = $this->stats->month($month);

        return view('calendar.index', compact('month', 'weeks', 'prev', 'next', 'monthStats'))
            ->with('projects', Project::orderBy('name')->get(['id', 'name', 'color']));
    }
}
