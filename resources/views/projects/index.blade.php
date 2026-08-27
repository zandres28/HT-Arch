@extends('layouts.app')

@section('title', 'Proyectos')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <form method="GET" action="{{ route('projects.index') }}" class="flex items-center gap-2 text-sm">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($showArchived)
                <input type="hidden" name="archived" value="1">
            @endif
        </form>

        <div class="flex items-center gap-2">
            @if ($showArchived)
                <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:underline">← Ver activos</a>
            @else
                <a href="{{ route('projects.index', ['archived' => 1]) }}" class="text-sm text-slate-500 hover:underline">Ver archivados</a>
            @endif
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo proyecto
            </a>
        </div>
    </div>

    @if ($projects->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-500">
            <p>{{ $showArchived ? 'No hay proyectos archivados.' : 'Aún no tienes proyectos. Crea el primero para empezar a registrar horas.' }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($projects as $project)
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2.5 min-w-0">
                            <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $project->color }}"></span>
                            <span class="font-semibold text-slate-800 truncate hover:text-indigo-600">{{ $project->name }}</span>
                        </a>
                        <span class="text-xs px-2 py-0.5 rounded-full shrink-0
                            {{ $project->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $project->status === 'finished' ? 'bg-slate-200 text-slate-600' : '' }}
                            {{ $project->status === 'paused' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $project->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $statuses[$project->status] }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-slate-500 line-clamp-2 flex-1">{{ $project->description ?: 'Sin descripción' }}</p>

                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-slate-600">
                            <span class="font-semibold text-slate-800">{{ number_format($project->work_logs_sum_hours ?? 0, 1) }} h</span>
                            · {{ $project->work_logs_count }} {{ $project->work_logs_count === 1 ? 'actividad' : 'actividades' }}
                        </span>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('projects.edit', $project) }}" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            @if ($showArchived)
                                <form method="POST" action="{{ route('projects.unarchive', $project) }}">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-emerald-600" title="Restaurar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('projects.archive', $project) }}"
                                      onsubmit="return confirmDelete('¿Archivar el proyecto «{{ $project->name }}»? No aparecerá para nuevos registros.')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-amber-600" title="Archivar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $projects->links() }}</div>
    @endif
@endsection
