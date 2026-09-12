@extends('layouts.admin')

@section('title', 'New Article')

@section('content')
    @include('partials.page-header', ['title' => 'New Article', 'eyebrow' => 'Content'])

    <div class="card-section p-8 max-w-3xl">
        <form action="{{ route('admin.news.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.news._form')

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Create Article</button>
                <a href="{{ route('admin.news.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
