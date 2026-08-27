@extends('layouts.app')

@section('title', 'Detalle de actividad')

@section('content')
    <div class="flex items-center gap-2 text-sm text-slate-500 mb-4">
        <a href="{{ route('work-logs.index') }}" class="hover:text-indigo-600">Historial</a>
        <span>/</span>
        <span class="text-slate-700">{{ $log->activity }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Datos del registro --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 h-fit">
            <h2 class="text-lg font-semibold text-slate-800">{{ $log->activity }}</h2>
            <a href="{{ route('projects.show', $log->project) }}" class="mt-1 inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-indigo-600">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $log->project->color }}"></span>
                {{ $log->project->name }}
            </a>

            <dl class="mt-4 space-y-2.5 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Fecha</dt>
                    <dd class="text-slate-800">{{ $log->date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Horas</dt>
                    <dd class="font-semibold text-slate-800">{{ number_format($log->hours, 2) }} h</dd>
                </div>
                @if ($log->start_time)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Hora de inicio</dt>
                        <dd class="text-slate-800">{{ substr($log->start_time, 0, 5) }}</dd>
                    </div>
                @endif
                @if ($log->end_time)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Hora de finalización</dt>
                        <dd class="text-slate-800">{{ substr($log->end_time, 0, 5) }}</dd>
                    </div>
                @endif
            </dl>

            @if ($log->description)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-slate-500">Descripción</h3>
                    <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $log->description }}</p>
                </div>
            @endif
            @if ($log->notes)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-slate-500">Observaciones</h3>
                    <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $log->notes }}</p>
                </div>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('work-logs.edit', $log) }}"
                   class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700">Editar</a>
                <form method="POST" action="{{ route('work-logs.duplicate', $log) }}">
                    @csrf
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700">Duplicar</button>
                </form>
                <form method="POST" action="{{ route('work-logs.destroy', $log) }}"
                      onsubmit="return confirmDelete('¿Eliminar este registro? Sus evidencias también se eliminarán.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">Eliminar</button>
                </form>
            </div>
        </div>

        {{-- Evidencias + entregables --}}
        <div class="lg:col-span-2 space-y-5">
            @include('work-logs._attachments', ['log' => $log])
            @include('work-logs._deliverables', ['log' => $log])
        </div>
    </div>
@endsection
