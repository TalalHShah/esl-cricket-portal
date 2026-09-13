@props(['class' => 'w-full h-full'])

<svg viewBox="0 0 200 240" xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" style="color: var(--paper);">
  <g id="batsman">
    <circle cx="100" cy="35" r="12" fill="currentColor" opacity="0.9"/>
    <rect x="95" y="50" width="10" height="35" fill="currentColor" opacity="0.85" rx="2"/>
    <line x1="95" y1="55" x2="70" y2="45" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity="0.9"/>
    <line x1="105" y1="58" x2="130" y2="50" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity="0.9"/>
    <g id="bat">
      <line x1="70" y1="45" x2="55" y2="15" stroke="currentColor" stroke-width="6" stroke-linecap="round" opacity="0.95"/>
      <polygon points="55,15 50,12 52,22" fill="currentColor" opacity="0.95"/>
    </g>
    <line x1="97" y1="85" x2="85" y2="135" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity="0.85"/>
    <line x1="103" y1="85" x2="125" y2="140" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity="0.85"/>
    <circle cx="85" cy="137" r="3" fill="currentColor" opacity="0.9"/>
    <circle cx="125" cy="142" r="3" fill="currentColor" opacity="0.9"/>
  </g>
  <g id="stumps">
    <line x1="95" y1="155" x2="95" y2="190" stroke="currentColor" stroke-width="3" opacity="0.8"/>
    <line x1="105" y1="155" x2="105" y2="190" stroke="currentColor" stroke-width="3" opacity="0.8"/>
    <line x1="100" y1="155" x2="100" y2="195" stroke="currentColor" stroke-width="4" opacity="0.85"/>
    <line x1="92" y1="155" x2="108" y2="155" stroke="currentColor" stroke-width="2" opacity="0.7"/>
  </g>
  <path d="M 60 200 Q 100 220 140 200" fill="none" stroke="currentColor" stroke-width="2" opacity="0.6"/>
</svg>
