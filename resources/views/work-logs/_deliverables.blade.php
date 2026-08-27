<div class="bg-white rounded-xl border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-800">Entregables <span class="text-slate-400 font-normal">({{ $log->deliverables->count() }})</span></h3>
        <a href="{{ route('deliverables.create', ['work_log_id' => $log->id, 'project_id' => $log->project_id]) }}"
           class="text-sm text-indigo-600 hover:underline">+ Añadir entregable</a>
    </div>

    @if ($log->deliverables->isEmpty())
        <p class="text-sm text-slate-500">Sin entregables asociados a esta actividad.</p>
    @else
        <ul class="divide-y divide-slate-100 text-sm">
            @foreach ($log->deliverables as $deliverable)
                <li class="py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-slate-800 font-medium">{{ $deliverable->name }}</span>
                        @if ($deliverable->version)
                            <span class="text-xs text-slate-500 ml-1">v{{ $deliverable->version }}</span>
                        @endif
                        <div class="text-xs text-slate-500">
                            {{ $deliverable->type ? \App\Models\Deliverable::TYPES[$deliverable->type] : 'Otro' }}
                            · {{ $deliverable->date->format('d/m/Y') }}
                            · {{ $deliverable->original_name }} ({{ $deliverable->humanSize() }})
                        </div>
                    </div>
                    <span class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('deliverables.download', $deliverable) }}" class="text-indigo-600 hover:text-indigo-800">Descargar</a>
                        <a href="{{ route('deliverables.edit', $deliverable) }}" class="text-slate-600 hover:text-indigo-600">Editar</a>
                        <form method="POST" action="{{ route('deliverables.destroy', $deliverable) }}"
                              onsubmit="return confirmDelete('¿Eliminar el entregable «{{ $deliverable->name }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </form>
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
