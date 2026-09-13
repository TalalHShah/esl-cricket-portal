@extends('layouts.admin')

@section('title', 'Edit ' . $competition->name)

@section('content')
    @include('partials.page-header', ['title' => 'Edit ' . $competition->name, 'eyebrow' => 'Competitions'])

    <div class="card-section p-8 max-w-3xl">
        <form action="{{ route('admin.competitions.update', $competition) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.competitions._form', ['competition' => $competition])

            <div class="flex gap-3 pt-8">
                <button type="submit" class="btn-accent flex-1 py-3">Save Changes</button>
                <a href="{{ route('admin.competitions.show', $competition) }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
