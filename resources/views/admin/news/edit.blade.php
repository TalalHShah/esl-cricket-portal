@extends('layouts.admin')

@section('title', 'Edit Article')

@section('content')
    @include('partials.page-header', ['title' => 'Edit Article', 'eyebrow' => 'Content'])

    <div class="card-section p-8 max-w-3xl">
        <form action="{{ route('admin.news.update', $article) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.news._form')

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Save Changes</button>
                <a href="{{ route('admin.news.index') }}" class="btn-ghost flex-1 py-3 text-center">Back</a>
            </div>
        </form>
    </div>
@endsection
