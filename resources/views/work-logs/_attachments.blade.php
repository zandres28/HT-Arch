<div class="bg-white rounded-xl border border-slate-200 p-6" x-data="{ viewer: null }">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-800">Evidencias <span class="text-slate-400 font-normal">({{ $log->attachments->count() }})</span></h3>
    </div>

    <form method="POST" action="{{ route('attachments.store', $log) }}" enctype="multipart/form-data"
          class="mb-4 flex flex-wrap items-center gap-3">
        @csrf
        <input type="file" name="files[]" multiple required
               accept=".png,.jpg,.jpeg,.gif,.webp,.bmp,.pdf,.txt,.md,.log,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.zip,.rar,.7z"
               class="text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <button type="submit" class="text-sm bg-slate-800 hover:bg-slate-900 text-white px-3 py-1.5 rounded-lg transition-colors">Subir</button>
    </form>

    @if ($log->attachments->isEmpty())
        <p class="text-sm text-slate-500">Sin evidencias adjuntas.</p>
    @else
        {{-- Galería de imágenes --}}
        @if ($log->imageAttachments()->isNotEmpty())
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
                @foreach ($log->imageAttachments() as $attachment)
                    <div class="relative group">
                        <button type="button" @click="viewer = '{{ route('attachments.view', $attachment) }}'"
                                class="block w-full aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-indigo-400 transition-colors">
                            <img src="{{ route('attachments.view', $attachment) }}" alt="{{ $attachment->original_name }}"
                                 class="w-full h-full object-cover" loading="lazy">
                        </button>
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}"
                              onsubmit="return confirmDelete('¿Eliminar «{{ $attachment->original_name }}»?')"
                              class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600/90 hover:bg-red-700 text-white rounded-md p-1" title="Eliminar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Otros archivos --}}
        @if ($log->otherAttachments()->isNotEmpty())
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($log->otherAttachments() as $attachment)
                    <li class="py-2.5 flex items-center justify-between gap-3">
                        <span class="truncate text-slate-700">
                            📄 {{ $attachment->original_name }}
                            <span class="text-xs text-slate-400">({{ $attachment->humanSize() }})</span>
                        </span>
                        <span class="flex items-center gap-3 shrink-0">
                            @if ($attachment->isPdf())
                                <a href="{{ route('attachments.view', $attachment) }}" target="_blank" class="text-slate-600 hover:text-indigo-600">Ver</a>
                            @endif
                            <a href="{{ route('attachments.download', $attachment) }}" class="text-indigo-600 hover:text-indigo-800">Descargar</a>
                            <form method="POST" action="{{ route('attachments.destroy', $attachment) }}"
                                  onsubmit="return confirmDelete('¿Eliminar «{{ $attachment->original_name }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                            </form>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    {{-- Visor de imágenes --}}
    <div x-show="viewer" x-cloak @click="viewer = null" @keydown.escape.window="viewer = null"
         class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-6 cursor-zoom-out">
        <img :src="viewer" class="max-w-full max-h-full rounded-lg shadow-2xl" alt="Vista previa">
    </div>
</div>
