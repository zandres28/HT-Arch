@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div class="flex items-center gap-2 text-sm text-slate-500 mb-4">
        <a href="{{ route('projects.index') }}" class="hover:text-indigo-600">Proyectos</a>
        <span>/</span>
        <span class="text-slate-700">{{ $project->name }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Info del proyecto --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 h-fit">
            <div class="flex items-center gap-2.5">
                <span class="w-4 h-4 rounded-full" style="background-color: {{ $project->color }}"></span>
                <h2 class="text-lg font-semibold text-slate-800">{{ $project->name }}</h2>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $project->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                    {{ $project->status === 'finished' ? 'bg-slate-200 text-slate-600' : '' }}
                    {{ $project->status === 'paused' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $project->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ \App\Models\Project::STATUSES[$project->status] }}
                </span>
                @if ($project->isArchived())
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">Archivado</span>
                @endif
            </div>

            <dl class="mt-4 space-y-2.5 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Horas acumuladas</dt>
                    <dd class="font-semibold text-slate-800">{{ number_format($project->work_logs_sum_hours ?? 0, 2) }} h</dd>
                </div>
                @if ($project->client)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Cliente</dt>
                        <dd class="text-slate-800">{{ $project->client }}</dd>
                    </div>
                @endif
                @if ($project->start_date)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Inicio</dt>
                        <dd class="text-slate-800">{{ $project->start_date->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if ($project->end_date)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Finalización</dt>
                        <dd class="text-slate-800">{{ $project->end_date->format('d/m/Y') }}</dd>
                    </div>
                @endif
            </dl>

            @if ($project->description)
                <p class="mt-4 text-sm text-slate-600">{{ $project->description }}</p>
            @endif
            @if ($project->notes)
                <div class="mt-3 text-sm">
                    <span class="text-slate-500 font-medium">Observaciones:</span>
                    <p class="text-slate-600">{{ $project->notes }}</p>
                </div>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('projects.edit', $project) }}"
                   class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700">Editar</a>
                @if ($project->isArchived())
                    <form method="POST" action="{{ route('projects.unarchive', $project) }}">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50">Restaurar</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('projects.archive', $project) }}"
                          onsubmit="return confirmDelete('¿Archivar el proyecto «{{ $project->name }}»?')">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50">Archivar</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                      onsubmit="return confirmDelete('¿Eliminar el proyecto «{{ $project->name }}»? Solo es posible si no tiene registros de horas.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">Eliminar</button>
                </form>
            </div>
        </div>

        {{-- Actividades recientes + entregables --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Actividades recientes</h3>
                    <a href="{{ route('work-logs.create', ['project_id' => $project->id]) }}"
                       class="text-sm text-indigo-600 hover:underline">+ Registrar horas</a>
                </div>
                @if ($recentLogs->isEmpty())
                    <p class="p-6 text-sm text-slate-500">Sin actividades registradas en este proyecto.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-left">
                            <tr>
                                <th class="px-6 py-3 font-medium">Fecha</th>
                                <th class="px-6 py-3 font-medium">Actividad</th>
                                <th class="px-6 py-3 font-medium text-right">Horas</th>
                                <th class="px-6 py-3 font-medium text-center" title="Evidencias">📎</th>
                                <th class="px-6 py-3 font-medium text-center" title="Entregables">📦</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentLogs as $log)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 whitespace-nowrap text-slate-600">{{ $log->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3">
                                        <a href="{{ route('work-logs.show', $log) }}" class="text-slate-800 hover:text-indigo-600">{{ $log->activity }}</a>
                                    </td>
                                    <td class="px-6 py-3 text-right font-medium">{{ number_format($log->hours, 2) }}</td>
                                    <td class="px-6 py-3 text-center text-slate-500">{{ $log->attachments_count ?: '—' }}</td>
                                    <td class="px-6 py-3 text-center text-slate-500">{{ $log->deliverables_count ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-3 border-t border-slate-200">{{ $recentLogs->links() }}</div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Entregables</h3>
                </div>
                @if ($deliverables->isEmpty())
                    <p class="p-6 text-sm text-slate-500">Sin entregables en este proyecto.</p>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($deliverables as $deliverable)
                            <li class="px-6 py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="text-slate-800 font-medium">{{ $deliverable->name }}</span>
                                    @if ($deliverable->version)
                                        <span class="text-xs text-slate-500 ml-1">v{{ $deliverable->version }}</span>
                                    @endif
                                    <div class="text-xs text-slate-500">{{ $deliverable->date->format('d/m/Y') }} · {{ $deliverable->original_name }} ({{ $deliverable->humanSize() }})</div>
                                </div>
                                <a href="{{ route('deliverables.download', $deliverable) }}"
                                   class="shrink-0 text-indigo-600 hover:text-indigo-800 text-sm">Descargar</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection
