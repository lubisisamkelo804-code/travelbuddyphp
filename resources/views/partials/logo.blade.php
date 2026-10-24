<svg viewBox="0 0 100 130" xmlns="http://www.w3.org/2000/svg" class="{{ $class ?? 'logo-md' }}" aria-label="TravelBuddy logo">
    <defs><clipPath id="tb-pin-{{ $id ?? 'x' }}"><path d="M50 0 C77.61 0 100 22.39 100 50 C100 85 50 130 50 130 C50 130 0 85 0 50 C0 22.39 22.39 0 50 0 Z"/></clipPath></defs>
    <g clip-path="url(#tb-pin-{{ $id ?? 'x' }})">
        <rect width="100" height="130" fill="#3b2f2d"/>
        <path d="M-10 85 Q 25 60 50 85 T 110 85 L 110 -10 L -10 -10 Z" fill="#63524d"/>
        <path d="M-10 60 Q 25 35 50 60 T 110 60 L 110 -10 L -10 -10 Z" fill="#968476"/>
        <path d="M-10 35 Q 25 10 50 35 T 110 35 L 110 -10 L -10 -10 Z" fill="#c4b6a0"/>
    </g>
</svg>