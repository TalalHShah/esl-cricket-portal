@extends('layouts.admin')

@section('title', 'New Competition')

@section('content')
    @include('partials.page-header', ['title' => 'New Competition', 'eyebrow' => 'Competitions'])

    <div class="card-section p-8 max-w-3xl">
        <form action="{{ route('admin.competitions.store') }}" method="POST">
            @csrf
            @include('admin.competitions._form')

            <div class="flex gap-3 pt-8">
                <button type="submit" class="btn-accent flex-1 py-3">Create Competition</button>
                <a href="{{ route('admin.competitions.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
