{{-- City Skyline Illustration (Pure SVG, no external image) --}}
<svg {{ $attributes->merge(['class' => 'city-skyline-svg']) }} viewBox="0 0 1440 680" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        {{-- Sky Gradient --}}
        <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="60%" stop-color="#f5f8ff" />
            <stop offset="100%" stop-color="#e9f0fc" />
        </linearGradient>

        {{-- Center Sky Dome Gradient --}}
        <radialGradient id="skyDome" cx="50%" cy="85%" r="70%" fx="50%" fy="85%">
            <stop offset="0%" stop-color="#d6e5fb" stop-opacity="0.9" />
            <stop offset="45%" stop-color="#e6f0fe" stop-opacity="0.6" />
            <stop offset="75%" stop-color="#f4f8ff" stop-opacity="0.25" />
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
        </radialGradient>

        {{-- Distant Building Silhouettes Fill --}}
        <linearGradient id="distBldg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#dbe6fa" />
            <stop offset="100%" stop-color="#cbdaf7" />
        </linearGradient>

        {{-- Glass Gradient for Slanted Pavilion --}}
        <linearGradient id="glassSlope" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#a4c2f8" />
            <stop offset="50%" stop-color="#c2d7fb" />
            <stop offset="100%" stop-color="#8bb0f5" />
        </linearGradient>

        {{-- Subtle Drop Shadow --}}
        <filter id="softShadow" x="-5%" y="-5%" width="110%" height="110%">
            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#1e293b" flood-opacity="0.04" />
        </filter>
    </defs>

    {{-- 1. BASE BACKGROUND --}}
    <rect width="1440" height="680" fill="url(#skyGrad)" />

    {{-- 2. CENTRAL SKY DOME / ARCH --}}
    <ellipse cx="720" cy="560" rx="640" ry="510" fill="url(#skyDome)" />

    {{-- 3. OUTLINE CLOUDS --}}
    <g stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="#ffffff">
        {{-- Cloud Top Left --}}
        <path d="M 105 138 Q 95 138 95 128 Q 95 118 108 116 Q 115 104 128 104 Q 139 104 145 112 Q 153 108 162 114 Q 170 115 171 123 Q 178 126 177 133 Q 175 138 166 138 Z" />
        <line x1="86" y1="138" x2="94" y2="138" />

        {{-- Cloud Mid Left --}}
        <path d="M 252 322 Q 244 322 244 314 Q 244 306 254 305 Q 260 297 270 297 Q 279 297 284 303 Q 290 301 296 306 Q 302 308 302 315 Q 302 322 294 322 Z" />
        <line x1="236" y1="322" x2="244" y2="322" />

        {{-- Cloud Top Right --}}
        <path d="M 1255 142 Q 1245 142 1245 132 Q 1245 122 1259 120 Q 1266 108 1280 108 Q 1292 108 1298 116 Q 1307 112 1316 118 Q 1324 119 1325 127 Q 1331 130 1330 137 Q 1328 142 1319 142 Z" />
        <line x1="1332" y1="142" x2="1342" y2="142" />

        {{-- Cloud Mid Right --}}
        <path d="M 1085 304 Q 1077 304 1077 296 Q 1077 288 1087 287 Q 1093 279 1103 279 Q 1112 279 1117 285 Q 1123 283 1129 288 Q 1135 290 1135 297 Q 1135 304 1127 304 Z" />
        <line x1="1137" y1="304" x2="1146" y2="304" />
    </g>

    {{-- 4. DISTANT CITY SILHOUETTES (Soft Layer Behind) --}}
    <g fill="url(#distBldg)">
        {{-- Distant Tower 1 (Left) --}}
        <rect x="112" y="270" width="50" height="365" rx="2" />
        {{-- Window slits --}}
        <g fill="#ffffff" opacity="0.65">
            <rect x="118" y="295" width="38" height="6" rx="1" />
            <rect x="118" y="320" width="38" height="6" rx="1" />
            <rect x="118" y="345" width="38" height="6" rx="1" />
            <rect x="118" y="370" width="38" height="6" rx="1" />
            <rect x="118" y="395" width="38" height="6" rx="1" />
            <rect x="118" y="420" width="38" height="6" rx="1" />
            <rect x="118" y="445" width="38" height="6" rx="1" />
        </g>

        {{-- Distant Tower 2 (Mid-Left) --}}
        <rect x="290" y="350" width="65" height="285" rx="2" />
        <g fill="#ffffff" opacity="0.65">
            <rect x="296" y="375" width="53" height="6" rx="1" />
            <rect x="296" y="400" width="53" height="6" rx="1" />
            <rect x="296" y="425" width="53" height="6" rx="1" />
            <rect x="296" y="450" width="53" height="6" rx="1" />
            <rect x="296" y="475" width="53" height="6" rx="1" />
            <rect x="296" y="500" width="53" height="6" rx="1" />
        </g>

        {{-- Distant Tower 3 (Center-Left) --}}
        <rect x="410" y="390" width="50" height="245" rx="2" />
        <g fill="#ffffff" opacity="0.65">
            <rect x="415" y="415" width="40" height="5" rx="1" />
            <rect x="415" y="435" width="40" height="5" rx="1" />
            <rect x="415" y="455" width="40" height="5" rx="1" />
            <rect x="415" y="475" width="40" height="5" rx="1" />
            <rect x="415" y="495" width="40" height="5" rx="1" />
        </g>

        {{-- Distant Tower 4 (Center-Right behind Card) --}}
        <rect x="690" y="340" width="68" height="295" rx="2" />
        <g fill="#ffffff" opacity="0.65">
            <rect x="696" y="365" width="56" height="6" rx="1" />
            <rect x="696" y="390" width="56" height="6" rx="1" />
            <rect x="696" y="415" width="56" height="6" rx="1" />
            <rect x="696" y="440" width="56" height="6" rx="1" />
            <rect x="696" y="465" width="56" height="6" rx="1" />
            <rect x="696" y="490" width="56" height="6" rx="1" />
            <rect x="696" y="515" width="56" height="6" rx="1" />
        </g>

        {{-- Distant Tower 5 (Right) --}}
        <rect x="990" y="330" width="55" height="305" rx="2" />
        <g fill="#ffffff" opacity="0.65">
            <rect x="996" y="355" width="43" height="6" rx="1" />
            <rect x="996" y="380" width="43" height="6" rx="1" />
            <rect x="996" y="405" width="43" height="6" rx="1" />
            <rect x="996" y="430" width="43" height="6" rx="1" />
            <rect x="996" y="455" width="43" height="6" rx="1" />
        </g>

        {{-- Distant Tower 6 (Far Right) --}}
        <rect x="1160" y="360" width="50" height="275" rx="2" />
        <g fill="#ffffff" opacity="0.65">
            <rect x="1165" y="385" width="40" height="6" rx="1" />
            <rect x="1165" y="410" width="40" height="6" rx="1" />
            <rect x="1165" y="435" width="40" height="6" rx="1" />
            <rect x="1165" y="460" width="40" height="6" rx="1" />
        </g>
    </g>

    {{-- ========================================== --}}
    {{-- 5. FOREGROUND DETAILED BUILDINGS (LINE ART) --}}
    {{-- ========================================== --}}

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 1: Far Left Modern Skyscraper     --}}
    {{-- ------------------------------------------ --}}
    <g id="building-1">
        {{-- Rooftop Penthouse & Antenna --}}
        <line x1="120" y1="175" x2="120" y2="210" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <line x1="108" y1="195" x2="108" y2="210" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <rect x="34" y="192" width="52" height="18" fill="#edf3fe" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Main Building Frame --}}
        <rect x="8" y="210" width="134" height="425" rx="2" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Vertical Window Columns (5 columns) --}}
        {{-- Col 1 --}}
        <rect x="22" y="225" width="17" height="48" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="22" y="279" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="22" y="333" width="17" height="48" fill="#a4c2f8" stroke="#1c2438" stroke-width="1.8" />
        <rect x="22" y="387" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="22" y="480" width="17" height="42" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="22" y="528" width="17" height="42" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />

        {{-- Col 2 --}}
        <rect x="44" y="225" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="44" y="279" width="17" height="48" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="44" y="333" width="17" height="48" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />
        <rect x="44" y="387" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="44" y="480" width="17" height="42" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="44" y="528" width="17" height="42" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />

        {{-- Col 3 --}}
        <rect x="66" y="225" width="17" height="48" fill="#a4c2f8" stroke="#1c2438" stroke-width="1.8" />
        <rect x="66" y="279" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="66" y="333" width="17" height="48" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="66" y="387" width="17" height="48" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="66" y="480" width="17" height="42" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="66" y="528" width="17" height="42" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />

        {{-- Col 4 --}}
        <rect x="88" y="225" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="88" y="279" width="17" height="48" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="88" y="333" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="88" y="387" width="17" height="48" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="88" y="480" width="17" height="42" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="88" y="528" width="17" height="42" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />

        {{-- Col 5 --}}
        <rect x="110" y="225" width="17" height="48" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="110" y="279" width="17" height="48" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />
        <rect x="110" y="333" width="17" height="48" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="110" y="387" width="17" height="48" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="110" y="480" width="17" height="42" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="110" y="528" width="17" height="42" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />

        {{-- Horizontal Belt Divider --}}
        <line x1="8" y1="455" x2="142" y2="455" stroke="#1c2438" stroke-width="2.2" />

        {{-- Entrance Portal --}}
        <rect x="22" y="585" width="105" height="50" fill="#eef4fd" stroke="#1c2438" stroke-width="2" />
        {{-- Entrance Doors --}}
        <rect x="58" y="595" width="33" height="40" fill="#ffffff" stroke="#1c2438" stroke-width="2" />
        <line x1="74.5" y1="595" x2="74.5" y2="635" stroke="#1c2438" stroke-width="1.6" />
        <line x1="71" y1="612" x2="71" y2="622" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <line x1="78" y1="612" x2="78" y2="622" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- TREE & BUSH 1 (Between Building 1 & 2)     --}}
    {{-- ------------------------------------------ --}}
    <g id="tree-1">
        <line x1="160" y1="615" x2="160" y2="635" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <polygon points="160,570 151,592 169,592" fill="#ffffff" stroke="#1c2438" stroke-width="2" stroke-linejoin="round" />
        <polygon points="160,587 147,610 173,610" fill="#eaf1fd" stroke="#1c2438" stroke-width="2" stroke-linejoin="round" />
        <polygon points="160,604 143,626 177,626" fill="#ffffff" stroke="#1c2438" stroke-width="2" stroke-linejoin="round" />
        <path d="M 172 635 C 172 626 182 624 184 635 Z" fill="#9dbbfc" stroke="#1c2438" stroke-width="2" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 2: Slanted Modern Glass Pavilion  --}}
    {{-- ------------------------------------------ --}}
    <g id="building-2">
        {{-- Elevator Tower Core (Left) --}}
        <rect x="178" y="360" width="40" height="275" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />
        {{-- Round Porthole Window --}}
        <circle cx="198" cy="378" r="7.5" fill="#9dbbfc" stroke="#1c2438" stroke-width="2" />
        <circle cx="198" cy="378" r="3.5" fill="#ffffff" stroke="#1c2438" stroke-width="1.5" />

        {{-- Slanted Glass Canopy Outline --}}
        <polygon points="218,390 415,575 415,635 218,635" fill="#f8faff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Slanted Structural Trusses & Glass Panels --}}
        {{-- Diagonal Roof Beam Accent --}}
        <line x1="218" y1="390" x2="415" y2="575" stroke="#1c2438" stroke-width="3" stroke-linecap="round" />

        {{-- Glass Facet Panel Grid --}}
        {{-- Vertical Grid Lines --}}
        <line x1="262" y1="431" x2="262" y2="575" stroke="#1c2438" stroke-width="1.8" />
        <line x1="310" y1="476" x2="310" y2="575" stroke="#1c2438" stroke-width="1.8" />
        <line x1="360" y1="523" x2="360" y2="575" stroke="#1c2438" stroke-width="1.8" />

        {{-- Diagonal Mullions inside glass facade --}}
        <line x1="218" y1="510" x2="262" y2="431" stroke="#1c2438" stroke-width="1.8" />
        <line x1="262" y1="575" x2="310" y2="476" stroke="#1c2438" stroke-width="1.8" />
        <line x1="310" y1="575" x2="360" y2="523" stroke="#1c2438" stroke-width="1.8" />
        <line x1="218" y1="575" x2="262" y2="510" stroke="#1c2438" stroke-width="1.8" />

        {{-- Colored Glass Triangles & Polygons --}}
        <polygon points="218,392 262,431 218,510" fill="#7fa4f5" stroke="#1c2438" stroke-width="1.8" />
        <polygon points="218,510 262,431 262,575" fill="#a8c5fa" stroke="#1c2438" stroke-width="1.8" />
        <polygon points="218,510 262,575 218,575" fill="#cde0fd" stroke="#1c2438" stroke-width="1.8" />
        
        <polygon points="262,431 310,476 262,575" fill="#6088ee" stroke="#1c2438" stroke-width="1.8" />
        <polygon points="310,476 310,575 262,575" fill="#bdd2fc" stroke="#1c2438" stroke-width="1.8" />
        
        <polygon points="310,476 360,523 310,575" fill="#8eb0f6" stroke="#1c2438" stroke-width="1.8" />
        <polygon points="360,523 360,575 310,575" fill="#dae7fe" stroke="#1c2438" stroke-width="1.8" />

        <polygon points="360,523 415,575 360,575" fill="#7ba0f5" stroke="#1c2438" stroke-width="1.8" />

        {{-- Entrance Lintel Beam --}}
        <line x1="178" y1="575" x2="415" y2="575" stroke="#1c2438" stroke-width="2.5" />

        {{-- Lower Entrance Glass Doors (4 bays) --}}
        <rect x="226" y="583" width="38" height="52" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" />
        <rect x="272" y="583" width="38" height="52" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" />
        <rect x="318" y="583" width="38" height="52" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" />
        <rect x="364" y="583" width="38" height="52" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" />
        {{-- Vertical dividing bars in entrance --}}
        <line x1="245" y1="583" x2="245" y2="635" stroke="#1c2438" stroke-width="1.2" />
        <line x1="291" y1="583" x2="291" y2="635" stroke="#1c2438" stroke-width="1.2" />
        <line x1="337" y1="583" x2="337" y2="635" stroke="#1c2438" stroke-width="1.2" />
        <line x1="383" y1="583" x2="383" y2="635" stroke="#1c2438" stroke-width="1.2" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- STREET DETAILS BETWEEN BLDG 2 & 3          --}}
    {{-- ------------------------------------------ --}}
    <g id="street-trees-left">
        {{-- Tree 2 --}}
        <line x1="432" y1="615" x2="432" y2="635" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <polygon points="432,580 424,598 440,598" fill="#ffffff" stroke="#1c2438" stroke-width="1.8" stroke-linejoin="round" />
        <polygon points="432,595 421,614 443,614" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" stroke-linejoin="round" />
        <polygon points="432,610 418,628 446,628" fill="#ffffff" stroke="#1c2438" stroke-width="1.8" stroke-linejoin="round" />
        {{-- Utility Box --}}
        <rect x="449" y="618" width="14" height="17" fill="#dce8fd" stroke="#1c2438" stroke-width="1.8" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 3: Mid-Left Tower                 --}}
    {{-- ------------------------------------------ --}}
    <g id="building-3">
        {{-- Rooftop Antenna & Penthouse --}}
        <line x1="518" y1="410" x2="518" y2="435" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <line x1="513" y1="420" x2="523" y2="420" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <rect x="490" y="423" width="56" height="12" fill="#ffffff" stroke="#1c2438" stroke-width="2" />

        {{-- Building Outer Frame --}}
        <rect x="475" y="435" width="88" height="200" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Left Column: Louvers / Ventilation Grille --}}
        <g stroke="#1c2438" stroke-width="2" stroke-linecap="round">
            <line x1="485" y1="458" x2="510" y2="458" />
            <line x1="485" y1="472" x2="510" y2="472" />
            <line x1="485" y1="486" x2="510" y2="486" />
            <line x1="485" y1="500" x2="510" y2="500" />
            <line x1="485" y1="514" x2="510" y2="514" />
            <line x1="485" y1="528" x2="510" y2="528" />
            <line x1="485" y1="542" x2="510" y2="542" />
            <line x1="485" y1="556" x2="510" y2="556" />
            <line x1="485" y1="570" x2="510" y2="570" />
            <line x1="485" y1="584" x2="510" y2="584" />
            <line x1="485" y1="598" x2="510" y2="598" />
        </g>

        {{-- Right Column: 4 Windows --}}
        <rect x="522" y="455" width="30" height="26" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />
        <rect x="522" y="492" width="30" height="26" fill="#9dbbfc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="522" y="529" width="30" height="26" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="522" y="566" width="30" height="26" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />

        {{-- Ground Door --}}
        <rect x="496" y="605" width="32" height="30" fill="#5077e6" stroke="#1c2438" stroke-width="2" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- STREET DETAILS IN CENTER (Under card)      --}}
    {{-- ------------------------------------------ --}}
    <g id="street-center">
        {{-- Lamppost Left (x=605) --}}
        <line x1="605" y1="595" x2="605" y2="635" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <path d="M 598 597 C 601 593 605 593 605 595 C 605 593 609 593 612 597" fill="none" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <circle cx="598" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
        <circle cx="612" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />

        {{-- Curb Divider Block --}}
        <rect x="670" y="625" width="35" height="10" fill="#edf3fe" stroke="#1c2438" stroke-width="1.8" />

        {{-- Lamppost Right (x=770) --}}
        <line x1="770" y1="595" x2="770" y2="635" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <path d="M 763 597 C 766 593 770 593 770 595 C 770 593 774 593 777 597" fill="none" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <circle cx="763" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
        <circle cx="777" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />

        {{-- Cute Parked Bicycle (x=810 to 835) --}}
        <g id="bicycle">
            {{-- Wheels --}}
            <circle cx="813" cy="627" r="6.5" fill="none" stroke="#1c2438" stroke-width="1.8" />
            <circle cx="832" cy="627" r="6.5" fill="none" stroke="#1c2438" stroke-width="1.8" />
            {{-- Frame --}}
            <polyline points="813,627 822,627 827,620 818,620 813,627" fill="none" stroke="#1c2438" stroke-width="1.6" stroke-linejoin="round" />
            <line x1="822" y1="627" x2="820" y2="617" stroke="#1c2438" stroke-width="1.6" />
            {{-- Seat --}}
            <line x1="817" y1="617" x2="823" y2="617" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
            {{-- Front Fork & Handlebars --}}
            <line x1="832" y1="627" x2="829" y2="615" stroke="#1c2438" stroke-width="1.6" />
            <line x1="826" y1="615" x2="832" y2="615" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        </g>
    </g>

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 4: Storefront with Dome Marquee   --}}
    {{-- ------------------------------------------ --}}
    <g id="building-4">
        {{-- Circular Sign on Top --}}
        <line x1="918" y1="534" x2="918" y2="544" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <circle cx="918" cy="525" r="9" fill="#eaf1fd" stroke="#1c2438" stroke-width="2" />
        <circle cx="918" cy="525" r="4" fill="none" stroke="#1c2438" stroke-width="1.5" />

        {{-- Dome Marquee / Barrel Awning --}}
        <path d="M 865 575 C 865 540 970 540 970 575 Z" fill="#b9d1fc" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Awning Scallop Ribs --}}
        <path d="M 878 575 C 878 548 894 544 894 575" fill="none" stroke="#1c2438" stroke-width="1.8" />
        <path d="M 910 575 C 910 543 926 543 926 575" fill="none" stroke="#1c2438" stroke-width="1.8" />
        <path d="M 942 575 C 942 546 958 550 958 575" fill="none" stroke="#1c2438" stroke-width="1.8" />

        {{-- Awning Alternating Color Fills --}}
        <path d="M 865 575 C 865 550 878 548 878 575 Z" fill="#dae7fe" />
        <path d="M 894 575 C 894 544 910 543 910 575 Z" fill="#dae7fe" />
        <path d="M 926 575 C 926 543 942 546 942 575 Z" fill="#dae7fe" />
        <path d="M 958 575 C 958 550 970 550 970 575 Z" fill="#dae7fe" />

        {{-- Awning Bottom Bar --}}
        <line x1="865" y1="575" x2="970" y2="575" stroke="#1c2438" stroke-width="2.5" />

        {{-- Storefront Main Body --}}
        <rect x="872" y="575" width="94" height="60" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Store Display Window (Left) --}}
        <rect x="880" y="586" width="54" height="38" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" />
        <line x1="880" y1="602" x2="934" y2="602" stroke="#1c2438" stroke-width="1.6" />

        {{-- Store Entrance Door (Right) --}}
        <rect x="940" y="586" width="20" height="49" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.8" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 5: Townhouse with Gabled Roof     --}}
    {{-- ------------------------------------------ --}}
    <g id="building-5">
        {{-- Pitched Gable Roof --}}
        <polygon points="1030,470 980,530 1080,530" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Round Attic Window --}}
        <circle cx="1030" cy="505" r="8" fill="#ffffff" stroke="#1c2438" stroke-width="2" />
        <line x1="1022" y1="505" x2="1038" y2="505" stroke="#1c2438" stroke-width="1.4" />
        <line x1="1030" y1="497" x2="1030" y2="513" stroke="#1c2438" stroke-width="1.4" />

        {{-- House Body --}}
        <rect x="984" y="530" width="92" height="105" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- 6 Windows (2 columns x 3 rows) --}}
        {{-- Row 1 --}}
        <rect x="996" y="542" width="16" height="18" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6" />
        <line x1="1004" y1="542" x2="1004" y2="560" stroke="#1c2438" stroke-width="1.2" />
        <line x1="996" y1="551" x2="1012" y2="551" stroke="#1c2438" stroke-width="1.2" />

        <rect x="1048" y="542" width="16" height="18" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6" />
        <line x1="1056" y1="542" x2="1056" y2="560" stroke="#1c2438" stroke-width="1.2" />
        <line x1="1048" y1="551" x2="1064" y2="551" stroke="#1c2438" stroke-width="1.2" />

        {{-- Row 2 --}}
        <rect x="996" y="570" width="16" height="18" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6" />
        <line x1="1004" y1="570" x2="1004" y2="588" stroke="#1c2438" stroke-width="1.2" />
        <line x1="996" y1="579" x2="1012" y2="579" stroke="#1c2438" stroke-width="1.2" />

        <rect x="1048" y="570" width="16" height="18" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6" />
        <line x1="1056" y1="570" x2="1056" y2="588" stroke="#1c2438" stroke-width="1.2" />
        <line x1="1048" y1="579" x2="1064" y2="579" stroke="#1c2438" stroke-width="1.2" />

        {{-- Row 3 --}}
        <rect x="996" y="598" width="16" height="18" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6" />
        <line x1="1004" y1="598" x2="1004" y2="616" stroke="#1c2438" stroke-width="1.2" />
        <line x1="996" y1="607" x2="1012" y2="607" stroke="#1c2438" stroke-width="1.2" />

        <rect x="1048" y="598" width="16" height="18" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6" />
        <line x1="1056" y1="598" x2="1056" y2="616" stroke="#1c2438" stroke-width="1.2" />
        <line x1="1048" y1="607" x2="1064" y2="607" stroke="#1c2438" stroke-width="1.2" />

        {{-- Entrance Door --}}
        <rect x="1020" y="605" width="20" height="30" fill="#5077e6" stroke="#1c2438" stroke-width="2" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 6: Modern Blue Office Building    --}}
    {{-- ------------------------------------------ --}}
    <g id="building-6">
        {{-- Antenna Mast --}}
        <line x1="1145" y1="400" x2="1145" y2="430" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <line x1="1138" y1="412" x2="1152" y2="412" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />

        {{-- Outer Structure Frame --}}
        <rect x="1076" y="430" width="126" height="205" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- 6 Large Blue Window Panels (2 columns x 3 rows) --}}
        {{-- Row 1 --}}
        <rect x="1088" y="445" width="48" height="52" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1144" y="445" width="48" height="52" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />

        {{-- Row 2 --}}
        <rect x="1088" y="507" width="48" height="52" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1144" y="507" width="48" height="52" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />

        {{-- Row 3 --}}
        <rect x="1088" y="569" width="48" height="52" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1144" y="569" width="48" height="52" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />

        {{-- Street Tree 3 in front of Building 6 --}}
        <line x1="1152" y1="615" x2="1152" y2="635" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <polygon points="1152,570 1143,592 1161,592" fill="#ffffff" stroke="#1c2438" stroke-width="2" stroke-linejoin="round" />
        <polygon points="1152,587 1139,610 1165,610" fill="#eaf1fd" stroke="#1c2438" stroke-width="2" stroke-linejoin="round" />
        <polygon points="1152,604 1135,626 1169,626" fill="#ffffff" stroke="#1c2438" stroke-width="2" stroke-linejoin="round" />
    </g>

    {{-- ------------------------------------------ --}}
    {{-- BUILDING 7: Far Right High-Rise Skyscraper --}}
    {{-- ------------------------------------------ --}}
    <g id="building-7">
        {{-- Rooftop Structure & Mast --}}
        <line x1="1300" y1="180" x2="1300" y2="215" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <line x1="1292" y1="195" x2="1308" y2="195" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <rect x="1268" y="200" width="64" height="18" fill="#edf3fe" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Main Skyscraper Frame --}}
        <rect x="1215" y="218" width="145" height="417" fill="#ffffff" stroke="#1c2438" stroke-width="2.2" stroke-linejoin="round" />

        {{-- Attic Horizontal Windows --}}
        <rect x="1230" y="235" width="32" height="15" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1271" y="235" width="32" height="15" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1312" y="235" width="32" height="15" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />

        <line x1="1215" y1="262" x2="1360" y2="262" stroke="#1c2438" stroke-width="2.2" />

        {{-- 3 Window Columns x 4 Vertical Section Rows --}}
        {{-- Col 1 --}}
        <rect x="1230" y="278" width="32" height="70" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1230" y="358" width="32" height="70" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1230" y="438" width="32" height="70" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1230" y="518" width="32" height="52" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />

        {{-- Col 2 --}}
        <rect x="1271" y="278" width="32" height="70" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1271" y="358" width="32" height="70" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1271" y="438" width="32" height="70" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1271" y="518" width="32" height="52" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />

        {{-- Col 3 --}}
        <rect x="1312" y="278" width="32" height="70" fill="#c4d8fc" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1312" y="358" width="32" height="70" fill="#698ef0" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1312" y="438" width="32" height="70" fill="#5077e6" stroke="#1c2438" stroke-width="1.8" />
        <rect x="1312" y="518" width="32" height="52" fill="#8caef7" stroke="#1c2438" stroke-width="1.8" />

        {{-- Entrance Area --}}
        <rect x="1235" y="585" width="105" height="50" fill="#eaf1fd" stroke="#1c2438" stroke-width="2" />
        <rect x="1260" y="593" width="55" height="42" fill="#ffffff" stroke="#1c2438" stroke-width="2" />
        <line x1="1287.5" y1="593" x2="1287.5" y2="635" stroke="#1c2438" stroke-width="1.6" />
    </g>

    {{-- ========================================== --}}
    {{-- 6. GROUND, SIDEWALK & FOUNDATION BASELINE  --}}
    {{-- ========================================== --}}
    <g id="ground-and-sidewalk">
        {{-- Continuous Ground Baseline --}}
        <line x1="0" y1="635" x2="1440" y2="635" stroke="#1c2438" stroke-width="2.5" />

        {{-- Sidewalk Curb Strip --}}
        <rect x="0" y="635" width="1440" height="45" fill="#f4f8fe" stroke="#1c2438" stroke-width="2" />

        {{-- Curb Divider Ticks --}}
        <g stroke="#1c2438" stroke-width="1.5">
            <line x1="40" y1="635" x2="40" y2="650" />
            <line x1="120" y1="635" x2="120" y2="650" />
            <line x1="200" y1="635" x2="200" y2="650" />
            <line x1="280" y1="635" x2="280" y2="650" />
            <line x1="360" y1="635" x2="360" y2="650" />
            <line x1="440" y1="635" x2="440" y2="650" />
            <line x1="520" y1="635" x2="520" y2="650" />
            <line x1="600" y1="635" x2="600" y2="650" />
            <line x1="680" y1="635" x2="680" y2="650" />
            <line x1="760" y1="635" x2="760" y2="650" />
            <line x1="840" y1="635" x2="840" y2="650" />
            <line x1="920" y1="635" x2="920" y2="650" />
            <line x1="1000" y1="635" x2="1000" y2="650" />
            <line x1="1080" y1="635" x2="1080" y2="650" />
            <line x1="1160" y1="635" x2="1160" y2="650" />
            <line x1="1240" y1="635" x2="1240" y2="650" />
            <line x1="1320" y1="635" x2="1320" y2="650" />
            <line x1="1400" y1="635" x2="1400" y2="650" />
        </g>
    </g>
</svg>
