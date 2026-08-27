@extends('layouts.app')

@section('title', 'Calendario')

@section('content')
    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <div class="flex items-center gap-3">
            <div class="flex items-center rounded-lg border border-slate-200 overflow-hidden">
                <a href="{{ route('calendar.index', ['month' => $prev]) }}" class="w-10 h-10 grid place-items-center text-slate-500 hover:bg-slate-50" aria-label="Mes anterior">‹</a>
                <a href="{{ route('calendar.index') }}" class="h-10 px-3 grid place-items-center text-sm font-medium text-indigo-600 hover:bg-indigo-50 border-x border-slate-200">Hoy</a>
                <a href="{{ route('calendar.index', ['month' => $next]) }}" class="w-10 h-10 grid place-items-center text-slate-500 hover:bg-slate-50" aria-label="Mes siguiente">›</a>
            </div>
            <h2 class="text-xl font-bold text-slate-800 capitalize">{{ $month->translatedFormat('F Y') }}</h2>
        </div>

        <div class="flex items-center gap-5 text-sm">
            <div class="text-right">
                <p class="text-xs text-slate-400">Horas del mes</p>
                <p class="font-semibold text-slate-800">{{ number_format($monthStats['hours'], 2) }} h</p>
            </div>
            <div class="h-8 w-px bg-slate-200"></div>
            <div class="text-right">
                <p class="text-xs text-slate-400">Días trabajados</p>
                <p class="font-semibold text-slate-800">{{ $monthStats['days_worked'] }}</p>
            </div>
        </div>
    </div>

    {{-- Calendario --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- Días de la semana --}}
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50/70">
            @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $i => $d)
                <div class="py-2.5 text-center text-xs font-semibold uppercase tracking-wide {{ $i >= 5 ? 'text-rose-400' : 'text-slate-400' }}">{{ $d }}</div>
            @endforeach
        </div>

        {{-- Cuadrícula --}}
        <div class="grid grid-cols-7 grid-rows-{{ count($weeks) }} gap-px bg-slate-200 min-h-[660px]">
            @foreach ($weeks as $week)
                @foreach ($week as $cell)
                    @php
                        $groups = $cell['logs']->groupBy('project_id');
                        $top = $groups->take(4);
                    @endphp
                    <a href="{{ route('work-logs.index', ['date' => $cell['date']->format('Y-m-d')]) }}"
                       class="group relative flex flex-col p-2.5 transition
                              {{ !$cell['in_month'] ? 'bg-slate-100/60' : ($cell['date']->isWeekend() ? 'bg-slate-50' : 'bg-white hover:bg-indigo-50/50') }}
                              {{ $cell['is_today'] ? 'ring-2 ring-inset ring-indigo-500 z-10' : '' }}">
                        <div class="flex items-start justify-between gap-1">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-semibold
                                         {{ $cell['is_today'] ? 'bg-indigo-600 text-white' : ($cell['in_month'] ? 'text-slate-700' : 'text-slate-400') }}">
                                {{ $cell['date']->format('j') }}
                            </span>
                            @if ($cell['total'] > 0)
                                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700">
                                    {{ number_format($cell['total'], 1) }}h
                                </span>
                            @endif
                        </div>

                        @if ($top->isNotEmpty())
                            <div class="mt-2 space-y-1 overflow-hidden">
                                @foreach ($top as $group)
                                    @php
                                        $project = $group->first()->project;
                                        $color = $project->color ?? '#94a3b8';
                                        $name = $project->name ?? 'Sin proyecto';
                                    @endphp
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-600" title="{{ $name }}">
                                        <span class="w-2 h-2 shrink-0 rounded-full" style="background-color: {{ $color }}"></span>
                                        <span class="truncate">{{ number_format((float) $group->sum('hours'), 1) }}h</span>
                                    </div>
                                @endforeach
                                @if ($groups->count() > 4)
                                    <div class="text-[11px] text-slate-400 pl-3.5">+{{ $groups->count() - 4 }} más</div>
                                @endif
                            </div>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Leyenda --}}
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4">
        <span class="text-xs font-medium text-slate-400">Proyectos:</span>
        @foreach ($projects as $p)
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $p->color }}"></span>
                {{ $p->name }}
            </span>
        @endforeach
    </div>

    <p class="text-xs text-slate-400 mt-3">Haz clic en un día para ver y filtrar sus actividades en el historial.</p>
@endsection
