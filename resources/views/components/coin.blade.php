@props(['size' => 14])

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"
     style="display:inline-block; vertical-align:-2px; flex-shrink:0;" {{ $attributes }}>
    <circle cx="16" cy="16" r="15" fill="var(--gold)" stroke="var(--gold-dim)" stroke-width="1.5"/>
    <circle cx="16" cy="16" r="10.5" fill="none" stroke="var(--gold-dim)" stroke-width="1.25"/>
    <text x="16" y="21" font-family="Georgia, serif" font-size="14" font-weight="700" text-anchor="middle" fill="var(--gold-dim)">E</text>
</svg>
