@extends('layouts.app')

@section('title', 'Editar registro')

@section('content')
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form method="POST" action="{{ route('work-logs.update', $log) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('work-logs._form')
            </form>
        </div>

        @if ($log->attachments->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6 mt-5">
                <h3 class="font-semibold text-slate-800 mb-3">Evidencias actuales</h3>
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($log->attachments as $attachment)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <span class="truncate text-slate-700">{{ $attachment->original_name }}
                                <span class="text-xs text-slate-400">({{ $attachment->humanSize() }})</span>
                            </span>
                            <form method="POST" action="{{ route('attachments.destroy', $attachment) }}"
                                  onsubmit="return confirmDelete('¿Eliminar el archivo «{{ $attachment->original_name }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Eliminar</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
