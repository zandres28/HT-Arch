@extends('layouts.app')

@section('title', 'Historial de actividades')

@section('content')
    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5"
         x-data="{ mode: '{{ !empty($filters['week']) ? 'week' : (!empty($filters['month']) ? 'month' : 'range') }}' }">
        <form method="GET" action="{{ route('work-logs.index') }}" class="flex flex-wrap items-end gap-3 text-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Filtrar por</label>
                <div class="flex rounded-lg border border-slate-300 overflow-hidden">
                    <button type="button" @click="mode = 'range'"
                            :class="mode === 'range' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                            class="px-3 py-2 text-xs font-medium transition-colors">Rango</button>
                    <button type="button" @click="mode = 'week'"
                            :class="mode === 'week' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                            class="px-3 py-2 text-xs font-medium border-x border-slate-300 transition-colors">Semana</button>
                    <button type="button" @click="mode = 'month'"
                            :class="mode === 'month' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                            class="px-3 py-2 text-xs font-medium transition-colors">Mes</button>
                </div>
            </div>

            <div x-show="mode === 'range'" class="flex items-end gap-2">
                <div>
                    <label for="date_from" class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div x-show="mode === 'week'" x-cloak>
                <label for="week" class="block text-xs font-medium text-slate-500 mb-1">Semana</label>
                <input type="week" name="week" id="week" value="{{ $filters['week'] ?? '' }}"
                       class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div x-show="mode === 'month'" x-cloak>
                <label for="month" class="block text-xs font-medium text-slate-500 mb-1">Mes</label>
                <input type="month" name="month" id="month" value="{{ $filters['month'] ?? '' }}"
                       class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="project_id" class="block text-xs font-medium text-slate-500 mb-1">Proyecto</label>
                <select name="project_id" id="project_id"
                        class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 max-w-[200px]">
                    <option value="">Todos</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) ($filters['project_id'] ?? '') === (string) $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="project_status" class="block text-xs font-medium text-slate-500 mb-1">Estado del proyecto</label>
                <select name="project_status" id="project_status"
                        class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['project_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('work-logs.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Limpiar</a>
            </div>

            <div class="ml-auto text-sm text-slate-600">
                Total filtrado: <span class="font-semibold text-slate-800">{{ number_format($totalHours, 2) }} h</span>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        @if ($logs->isEmpty())
            <div class="p-10 text-center text-slate-500">
                <p>No se encontraron registros con los filtros aplicados.</p>
                <a href="{{ route('work-logs.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">+ Registrar horas</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-left">
                        <tr>
                            <th class="px-5 py-3 font-medium">Fecha</th>
                            <th class="px-5 py-3 font-medium">Proyecto</th>
                            <th class="px-5 py-3 font-medium">Actividad</th>
                            <th class="px-5 py-3 font-medium text-right">Horas</th>
                            <th class="px-5 py-3 font-medium text-center" title="Evidencias">
                                <span class="sr-only">Evidencias</span>📎
                            </th>
                            <th class="px-5 py-3 font-medium text-center" title="Entregables">
                                <span class="sr-only">Entregables</span>📦
                            </th>
                            <th class="px-5 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600">{{ $log->date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $log->project->color }}"></span>
                                        <span class="text-slate-700">{{ $log->project->name }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-3 max-w-xs">
                                    <a href="{{ route('work-logs.show', $log) }}" class="text-slate-800 hover:text-indigo-600 line-clamp-1">{{ $log->activity }}</a>
                                </td>
                                <td class="px-5 py-3 text-right font-medium whitespace-nowrap">{{ number_format($log->hours, 2) }}</td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ $log->attachments_count ?: '—' }}</td>
                                <td class="px-5 py-3 text-center text-slate-500">{{ $log->deliverables_count ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('work-logs.edit', $log) }}" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-500 hover:text-indigo-600" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('work-logs.duplicate', $log) }}">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-500 hover:text-emerald-600" title="Duplicar (con fecha de hoy)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('work-logs.destroy', $log) }}"
                                              onsubmit="return confirmDelete('¿Eliminar este registro de {{ number_format($log->hours, 2) }} h del {{ $log->date->format('d/m/Y') }}? Sus evidencias también se eliminarán.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-500 hover:text-red-600" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-200">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
