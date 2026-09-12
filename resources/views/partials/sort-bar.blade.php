@php
    $label = $label ?? null;
    $paramName = $paramName ?? 'sort';
@endphp
<form method="GET" class="flex items-center gap-2 flex-wrap">
    @foreach (request()->except([$paramName, 'page']) as $key => $value)
        @if(is_array($value))
            @foreach ($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    @if($label)
        <p class="eyebrow gold">{{ $label }}</p>
    @endif
    <label class="eyebrow" for="sort-{{ $sortId ?? 'default' }}">Sort</label>
    <select name="{{ $paramName }}" id="sort-{{ $sortId ?? 'default' }}" class="field px-3 py-2 text-sm" onchange="this.form.submit()">
        @foreach ($options as $value => $optLabel)
            <option value="{{ $value }}" @selected($current === $value)>{{ $optLabel }}</option>
        @endforeach
    </select>
</form>
