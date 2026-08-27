@extends('layouts.app')

@section('title', 'Editar entregable')

@section('content')
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form method="POST" action="{{ route('deliverables.update', $deliverable) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="project_id" class="block text-sm font-medium text-slate-700 mb-1">Proyecto <span class="text-red-500">*</span></label>
                        <select name="project_id" id="project_id" required
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) old('project_id', $deliverable->project_id) === (string) $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select name="type" id="type" required
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $deliverable->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required maxlength="200" value="{{ old('name', $deliverable->name) }}"
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="version" class="block text-sm font-medium text-slate-700 mb-1">Versión</label>
                        <input type="text" name="version" id="version" maxlength="30" value="{{ old('version', $deliverable->version) }}"
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" required max="{{ today()->format('Y-m-d') }}"
                               value="{{ old('date', $deliverable->date?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label for="file" class="block text-sm font-medium text-slate-700 mb-1">Reemplazar archivo</label>
                        @if ($deliverable->original_name)
                            <p class="text-xs text-slate-500 mb-1">Archivo actual: {{ $deliverable->original_name }} ({{ $deliverable->humanSize() }})</p>
                        @endif
                        <input type="file" name="file"
                               accept="{{ '.' . implode(',.', \App\Models\Deliverable::ALLOWED_EXTENSIONS) }}"
                               class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-slate-400">Deja vacío para conservar el actual. Máx. {{ (int) env('MAX_UPLOAD_MB', 15) }} MB.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="description" id="description" rows="2" maxlength="2000"
                                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $deliverable->description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Observaciones</label>
                        <textarea name="notes" id="notes" rows="2" maxlength="2000"
                                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes', $deliverable->notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors">
                        Actualizar entregable
                    </button>
                    <a href="{{ $deliverable->work_log_id ? route('work-logs.show', $deliverable->work_log_id) : route('projects.show', $deliverable->project_id) }}"
                       class="text-sm text-slate-600 hover:text-slate-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
