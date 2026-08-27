@extends('layouts.app')

@section('title', 'Registrar horas')

@section('content')
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form method="POST" action="{{ route('work-logs.store') }}" enctype="multipart/form-data">
                @include('work-logs._form')
            </form>
        </div>
    </div>
@endsection
