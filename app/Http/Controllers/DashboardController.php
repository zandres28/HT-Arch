<?php

namespace App\Http\Controllers;

use App\Services\StatsService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private StatsService $stats) {}

    public function index()
    {
        $today = $this->stats->today();
        $week = $this->stats->week();
        $month = $this->stats->month();
        $general = $this->stats->general();

        $monthStart = today()->startOfMonth();
        $monthEnd = today()->endOfMonth();
        $distribution = $this->stats->distributionByProject($monthStart, $monthEnd);

        $daily = $this->stats->dailySeries($monthStart, $monthEnd);
        $monthly = $this->stats->monthlySeries(6);

        $recent = \App\Models\WorkLog::with('project')
            ->withCount(['attachments', 'deliverables'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'today', 'week', 'month', 'general',
            'distribution', 'daily', 'monthly', 'recent'
        ));
    }
}
