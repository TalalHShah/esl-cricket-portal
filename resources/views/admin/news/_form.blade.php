@php $article = $article ?? null; @endphp

<div>
    <label class="eyebrow block mb-2">Title</label>
    <input type="text" name="title" class="field w-full px-4 py-3" value="{{ old('title', $article->title ?? '') }}" required>
    @error('title') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
</div>

<div>
    <label class="eyebrow block mb-2">Excerpt</label>
    <textarea name="excerpt" rows="2" class="field w-full px-4 py-3">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
    @error('excerpt') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
</div>

<div>
    <label class="eyebrow block mb-2">Body</label>
    <textarea name="body" rows="10" class="field w-full px-4 py-3" required>{{ old('body', $article->body ?? '') }}</textarea>
    @error('body') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
</div>

<div>
    <label class="eyebrow block mb-2">Cover Image URL</label>
    <input type="url" name="cover_image" class="field w-full px-4 py-3" value="{{ old('cover_image', $article->cover_image ?? '') }}" placeholder="https://...">
    @error('cover_image') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div>
        <label class="eyebrow block mb-2">Category</label>
        <select name="category" class="field w-full px-4 py-3" required>
            @foreach (['match_report' => 'Match Report', 'transfer' => 'Transfer', 'announcement' => 'Announcement', 'preview' => 'Preview', 'opinion' => 'Opinion'] as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $article->category ?? 'announcement') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="eyebrow block mb-2">Status</label>
        <select name="status" class="field w-full px-4 py-3" required>
            @foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $article->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end pb-3">
        <label class="flex items-center gap-2 text-sm" style="color: var(--paper);">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured ?? false))>
            Featured on homepage
        </label>
    </div>
</div>

<div>
    <label class="eyebrow block mb-2">Publish Date (optional — defaults to now when published)</label>
    <input type="datetime-local" name="published_at" class="field w-full px-4 py-3" value="{{ old('published_at', optional($article->published_at ?? null)->format('Y-m-d\TH:i')) }}">
</div>
