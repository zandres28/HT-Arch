@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required maxlength="150"
               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
        <textarea name="description" id="description" rows="2" maxlength="2000"
                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $project->description) }}</textarea>
    </div>

    <div>
        <label for="client" class="block text-sm font-medium text-slate-700 mb-1">Cliente / Responsable</label>
        <input type="text" name="client" id="client" value="{{ old('client', $project->client) }}" maxlength="150"
               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Estado <span class="text-red-500">*</span></label>
        <select name="status" id="status" required
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">Fecha de inicio</label>
        <input type="date" name="start_date" id="start_date"
               value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
    </div>

    <div>
        <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Fecha de finalización</label>
        <input type="date" name="end_date" id="end_date"
               value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
    </div>

    <div>
        <label for="color" class="block text-sm font-medium text-slate-700 mb-1">Color identificador <span class="text-red-500">*</span></label>
        <div class="flex items-center gap-3">
            <input type="color" name="color" id="color" value="{{ old('color', $project->color ?? '#6366f1') }}"
                   class="h-10 w-16 rounded-lg border border-slate-300 cursor-pointer p-1">
            <div class="flex gap-1.5">
                @foreach (['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#64748b'] as $preset)
                    <button type="button" onclick="document.getElementById('color').value = '{{ $preset }}'"
                            class="w-7 h-7 rounded-full border-2 border-white shadow hover:scale-110 transition-transform"
                            style="background-color: {{ $preset }}" title="{{ $preset }}"></button>
                @endforeach
            </div>
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Observaciones</label>
        <textarea name="notes" id="notes" rows="2" maxlength="2000"
                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes', $project->notes) }}</textarea>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors">
        Guardar proyecto
    </button>
    <a href="{{ $project->exists ? route('projects.show', $project) : route('projects.index') }}"
       class="text-sm text-slate-600 hover:text-slate-800">Cancelar</a>
</div>
