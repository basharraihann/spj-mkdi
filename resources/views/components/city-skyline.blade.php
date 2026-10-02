{{-- City Skyline Illustration (Pure SVG, no external image) --}}
<svg {{ $attributes->merge(['class' => 'city-skyline-svg']) }} viewBox="0 0 1440 680"
    preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="100%" stop-color="#f1f6ff" />
        </linearGradient>
        <linearGradient id="domeGrad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#eef4ff" />
            <stop offset="25%" stop-color="#e3ecfd" />
            <stop offset="50%" stop-color="#cbdbf7" />
            <stop offset="100%" stop-color="#cbdbf7" />
        </linearGradient>
        <linearGradient id="distBldg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#dbe6fa" />
            <stop offset="100%" stop-color="#c9d9f7" />
        </linearGradient>
        <linearGradient id="bldgFill" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="100%" stop-color="#e2ecfc" />
        </linearGradient>
        <linearGradient id="winDark" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#6a8bef" />
            <stop offset="100%" stop-color="#4568dc" />
        </linearGradient>
        <linearGradient id="winMid" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#93b0f6" />
            <stop offset="100%" stop-color="#7396f0" />
        </linearGradient>
        <linearGradient id="winLight" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#d2e0fb" />
            <stop offset="100%" stop-color="#adc5f7" />
        </linearGradient>
        <linearGradient id="pavGlass" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#a9c2f8" />
            <stop offset="100%" stop-color="#7b9cf0" />
        </linearGradient>
        <clipPath id="pavClip">
            <polygon points="190,395 240,395 418,558 418,598 190,598" />
        </clipPath>
    </defs>
    {{-- 1. LANGIT & DOME --}}
    <rect class="sky-bg" width="1440" height="680" fill="url(#skyGrad)" />
    <ellipse cx="720" cy="640" rx="680" ry="640" fill="url(#domeGrad)" />
    {{-- 2. AWAN --}}
    <g transform="translate(123 165) scale(1.08)" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round"
        stroke-linejoin="round" fill="#ffffff">
        <path
            d="M 0 0 Q -8 0 -8 -9 Q -8 -18 4 -19 Q 10 -30 24 -30 Q 35 -30 41 -22 Q 50 -26 58 -20 Q 68 -19 68 -10 Q 68 0 58 0 Z" />
        <line x1="-26" y1="0" x2="-14" y2="0" />
        <line x1="72" y1="0" x2="82" y2="0" />
    </g>
    <g transform="translate(287 328) scale(0.9)" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round"
        stroke-linejoin="round" fill="#ffffff">
        <path
            d="M 0 0 Q -8 0 -8 -9 Q -8 -18 4 -19 Q 10 -30 24 -30 Q 35 -30 41 -22 Q 50 -26 58 -20 Q 68 -19 68 -10 Q 68 0 58 0 Z" />
        <line x1="-26" y1="0" x2="-14" y2="0" />
    </g>
    <g transform="translate(717 428) scale(0.74)" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round"
        stroke-linejoin="round" fill="#ffffff">
        <path
            d="M 0 0 Q -8 0 -8 -9 Q -8 -18 4 -19 Q 10 -30 24 -30 Q 35 -30 41 -22 Q 50 -26 58 -20 Q 68 -19 68 -10 Q 68 0 58 0 Z" />
        <line x1="-26" y1="0" x2="-14" y2="0" />
        <line x1="72" y1="0" x2="82" y2="0" />
    </g>
    <g transform="translate(1090 310) scale(0.7)" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round"
        stroke-linejoin="round" fill="#ffffff">
        <path
            d="M 0 0 Q -8 0 -8 -9 Q -8 -18 4 -19 Q 10 -30 24 -30 Q 35 -30 41 -22 Q 50 -26 58 -20 Q 68 -19 68 -10 Q 68 0 58 0 Z" />
        <line x1="-26" y1="0" x2="-14" y2="0" />
        <line x1="72" y1="0" x2="82" y2="0" />
    </g>
    <g transform="translate(1245 176) scale(1.0)" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round"
        stroke-linejoin="round" fill="#ffffff">
        <path
            d="M 0 0 Q -8 0 -8 -9 Q -8 -18 4 -19 Q 10 -30 24 -30 Q 35 -30 41 -22 Q 50 -26 58 -20 Q 68 -19 68 -10 Q 68 0 58 0 Z" />
        <line x1="-26" y1="0" x2="-14" y2="0" />
    </g>
    {{-- 3. GEDUNG BAYANGAN (LATAR) --}}
    <g fill="url(#distBldg)" opacity="0.9">
        <polygon points="150,322 181,291 244,291 244,640 150,640" />
        <rect x="319" y="370" width="86" height="270" rx="2" />
        <rect x="998" y="412" width="96" height="228" rx="2" />
        <polygon points="1190,332 1231,291 1295,291 1295,640 1190,640" />
    </g>
    <g fill="#ffffff" opacity="0.6">
        <rect x="160" y="330" width="70" height="6" rx="1" />
        <rect x="160" y="356" width="70" height="6" rx="1" />
        <rect x="160" y="382" width="70" height="6" rx="1" />
        <rect x="160" y="408" width="70" height="6" rx="1" />
        <rect x="160" y="434" width="70" height="6" rx="1" />
        <rect x="160" y="460" width="70" height="6" rx="1" />
        <rect x="160" y="486" width="70" height="6" rx="1" />
        <rect x="160" y="512" width="70" height="6" rx="1" />
        <rect x="160" y="538" width="70" height="6" rx="1" />
        <rect x="160" y="564" width="70" height="6" rx="1" />
        <rect x="160" y="590" width="70" height="6" rx="1" />
    </g>
    <g fill="#ffffff" opacity="0.6">
        <rect x="327" y="396" width="70" height="6" rx="1" />
        <rect x="327" y="422" width="70" height="6" rx="1" />
        <rect x="327" y="448" width="70" height="6" rx="1" />
        <rect x="327" y="474" width="70" height="6" rx="1" />
        <rect x="327" y="500" width="70" height="6" rx="1" />
        <rect x="327" y="526" width="70" height="6" rx="1" />
        <rect x="327" y="552" width="70" height="6" rx="1" />
        <rect x="327" y="578" width="70" height="6" rx="1" />
        <rect x="327" y="604" width="70" height="6" rx="1" />
        <rect x="327" y="630" width="70" height="6" rx="1" />
    </g>
    <g fill="#ffffff" opacity="0.6">
        <rect x="1006" y="438" width="80" height="6" rx="1" />
        <rect x="1006" y="464" width="80" height="6" rx="1" />
        <rect x="1006" y="490" width="80" height="6" rx="1" />
        <rect x="1006" y="516" width="80" height="6" rx="1" />
        <rect x="1006" y="542" width="80" height="6" rx="1" />
        <rect x="1006" y="568" width="80" height="6" rx="1" />
        <rect x="1006" y="594" width="80" height="6" rx="1" />
        <rect x="1006" y="620" width="80" height="6" rx="1" />
    </g>
    <g fill="#ffffff" opacity="0.6">
        <rect x="1200" y="330" width="85" height="6" rx="1" />
        <rect x="1200" y="356" width="85" height="6" rx="1" />
        <rect x="1200" y="382" width="85" height="6" rx="1" />
        <rect x="1200" y="408" width="85" height="6" rx="1" />
        <rect x="1200" y="434" width="85" height="6" rx="1" />
        <rect x="1200" y="460" width="85" height="6" rx="1" />
        <rect x="1200" y="486" width="85" height="6" rx="1" />
        <rect x="1200" y="512" width="85" height="6" rx="1" />
        <rect x="1200" y="538" width="85" height="6" rx="1" />
        <rect x="1200" y="564" width="85" height="6" rx="1" />
        <rect x="1200" y="590" width="85" height="6" rx="1" />
    </g>
    {{-- GEDUNG A: Menara kiri --}}
    <g id="tower-left">
        <line x1="122" y1="210" x2="122" y2="236" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <line x1="131" y1="220" x2="131" y2="236" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <rect x="38" y="224" width="48" height="12" fill="#cfe0fb" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
        <rect x="12" y="236" width="138" height="404" fill="url(#bldgFill)" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <rect x="12" y="236" width="138" height="22" fill="#f4f8ff" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <rect x="12" y="258" width="138" height="6" fill="#3f5fc9" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <rect x="29" y="282" width="21" height="18" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="29" y="300" width="21" height="50" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="29" y="350" width="21" height="48" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="29" y="398" width="21" height="52" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="29" y="450" width="21" height="70" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="29" y="520" width="21" height="66" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="57" y="282" width="21" height="48" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="57" y="330" width="21" height="42" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="57" y="372" width="21" height="58" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="57" y="430" width="21" height="68" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="57" y="498" width="21" height="88" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="85" y="282" width="21" height="28" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="85" y="310" width="21" height="70" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="85" y="380" width="21" height="60" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="85" y="440" width="21" height="80" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="85" y="520" width="21" height="66" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="113" y="282" width="21" height="58" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="113" y="340" width="21" height="80" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="113" y="420" width="21" height="60" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="113" y="480" width="21" height="60" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="113" y="540" width="21" height="46" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="12" y="596" width="138" height="44" fill="#eef4fd" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <rect x="24" y="606" width="28" height="34" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="38" y1="606" x2="38" y2="640" stroke="#1c2438" stroke-width="1.2" stroke-linecap="round" />
        <rect x="62" y="606" width="28" height="34" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="76" y1="606" x2="76" y2="640" stroke="#1c2438" stroke-width="1.2" stroke-linecap="round" />
        <rect x="100" y="606" width="28" height="34" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="114" y1="606" x2="114" y2="640" stroke="#1c2438" stroke-width="1.2" stroke-linecap="round" />
    </g>
    {{-- GEDUNG C: Gedung jendela biru (di belakang paviliun) --}}
    <g id="mid-left-blue">
        <line x1="385" y1="386" x2="385" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="395" y1="386" x2="395" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="405" y1="386" x2="405" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="415" y1="386" x2="415" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="425" y1="386" x2="425" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="435" y1="386" x2="435" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="445" y1="386" x2="445" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="455" y1="386" x2="455" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <line x1="465" y1="386" x2="465" y2="394" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <rect x="367" y="394" width="133" height="8" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <rect x="375" y="402" width="123" height="238" fill="url(#bldgFill)" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
        <rect x="389" y="420" width="26" height="34" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="421" y="420" width="26" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="453" y="420" width="26" height="34" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="389" y="462" width="26" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="421" y="462" width="26" height="34" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="453" y="462" width="26" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="389" y="504" width="26" height="34" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="421" y="504" width="26" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="453" y="504" width="26" height="34" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="389" y="546" width="26" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="421" y="546" width="26" height="34" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="453" y="546" width="26" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
    </g>
    {{-- GEDUNG B: Paviliun kaca miring --}}
    <g id="glass-pavilion">
        <polygon points="172,377 240,377 434,555 434,640 172,640" fill="#ffffff" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <rect x="188" y="356" width="34" height="21" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <circle cx="205" cy="366" r="5.5" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.8" />
        <circle cx="205" cy="366" r="2" fill="#ffffff" stroke="#1c2438" stroke-width="1.2" />
        <polygon points="190,395 240,395 418,558 418,598 190,598" fill="url(#pavGlass)" stroke="#1c2438"
            stroke-width="1.8" stroke-linejoin="round" />
        <g clip-path="url(#pavClip)">
            <line x1="228" y1="380" x2="228" y2="598" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="266" y1="380" x2="266" y2="598" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="304" y1="380" x2="304" y2="598" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="342" y1="380" x2="342" y2="598" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="380" y1="380" x2="380" y2="598" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="418" y1="380" x2="418" y2="598" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="185" y1="435" x2="425" y2="435" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="185" y1="475" x2="425" y2="475" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="185" y1="515" x2="425" y2="515" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <line x1="185" y1="555" x2="425" y2="555" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
            <polygon points="190,560 300,430 332,430 190,598" fill="#ffffff" opacity="0.22" />
            <polygon points="300,598 420,470 420,520 346,598" fill="#ffffff" opacity="0.22" />
        </g>
        <line x1="240" y1="377" x2="434" y2="555" stroke="#1c2438" stroke-width="2.6" stroke-linecap="round" />
        <rect x="172" y="598" width="262" height="42" fill="#ffffff" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
        <rect x="186" y="606" width="60" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="216" y1="606" x2="216" y2="640" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
        <rect x="270" y="606" width="60" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="300" y1="606" x2="300" y2="640" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
        <rect x="354" y="606" width="60" height="34" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="384" y1="606" x2="384" y2="640" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
    </g>
    {{-- GEDUNG D: Gedung kecil dengan papan bulat --}}
    <g id="small-sign-building">
        <rect x="486" y="429" width="73" height="22" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <circle cx="522" cy="440" r="7.5" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.8" />
        <circle cx="522" cy="440" r="3" fill="#ffffff" stroke="#1c2438" stroke-width="1.2" />
        <rect x="480" y="451" width="85" height="12" fill="#e3ecfc" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <rect x="475" y="463" width="97" height="177" fill="url(#bldgFill)" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
        <rect x="485" y="478" width="77" height="19" fill="#f4f8ff" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="485" y="491" width="77" height="6" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.2"
            stroke-linejoin="round" />
        <rect x="485" y="506" width="77" height="19" fill="#f4f8ff" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="485" y="519" width="77" height="6" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.2"
            stroke-linejoin="round" />
        <rect x="485" y="534" width="77" height="19" fill="#f4f8ff" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="485" y="547" width="77" height="6" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.2"
            stroke-linejoin="round" />
        <rect x="485" y="562" width="77" height="19" fill="#f4f8ff" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="485" y="575" width="77" height="6" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.2"
            stroke-linejoin="round" />
        <rect x="508" y="603" width="36" height="37" fill="#5f82ec" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
    </g>
    {{-- GEDUNG G: Kantor biru --}}
    <g id="blue-office">
        <rect x="1168" y="384" width="52" height="10" fill="#e3ecfc" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <line x1="1244" y1="378" x2="1244" y2="394" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <line x1="1238" y1="384" x2="1250" y2="384" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <rect x="1146" y="394" width="129" height="246" fill="url(#bldgFill)" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
        <rect x="1158" y="405" width="50" height="56" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1213" y="405" width="50" height="56" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1158" y="467" width="50" height="56" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1213" y="467" width="50" height="56" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1158" y="529" width="50" height="56" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1213" y="529" width="50" height="56" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1146" y="598" width="129" height="42" fill="#eef4fd" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
    </g>
    {{-- GEDUNG E: Toko dengan kanopi kubah --}}
    <g id="dome-store">
        <rect x="905" y="586" width="131" height="54" fill="#ffffff" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <path d="M 898 586 A 72.5 44 0 0 1 1043 586 Z" fill="#c0d4fb" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <line x1="922" y1="586" x2="922" y2="553.3" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="946" y1="586" x2="946" y2="544.6" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="970.5" y1="586" x2="970.5" y2="542.0" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="995" y1="586" x2="995" y2="544.6" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="1019" y1="586" x2="1019" y2="553.3" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="898" y1="586" x2="1043" y2="586" stroke="#1c2438" stroke-width="2.5" stroke-linecap="round" />
        <rect x="934" y="509" width="75" height="26" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <circle cx="971.5" cy="522" r="8.5" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.8" />
        <circle cx="971.5" cy="522" r="3.5" fill="#ffffff" stroke="#1c2438" stroke-width="1.2" />
        <line x1="950" y1="535" x2="950" y2="544" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <line x1="993" y1="535" x2="993" y2="544" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <rect x="912" y="594" width="83" height="30" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="912" y1="609" x2="995" y2="609" stroke="#1c2438" stroke-width="1.4" stroke-linecap="round" />
        <rect x="1002" y="594" width="28" height="46" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
    </g>
    {{-- GEDUNG F: Rumah dengan atap pelana --}}
    <g id="gable-house">
        <rect x="1070" y="485" width="94" height="155" fill="url(#bldgFill)" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <polygon points="1117,423 1055,485 1178,485" fill="#ffffff" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <polygon points="1117,434 1068,485 1166,485" fill="none" stroke="#1c2438" stroke-width="1.4"
            stroke-linejoin="round" />
        <circle cx="1117" cy="470" r="9" fill="#ffffff" stroke="#1c2438" stroke-width="1.8" />
        <line x1="1108" y1="470" x2="1126" y2="470" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
        <line x1="1117" y1="461" x2="1117" y2="479" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
        <rect x="1091" y="497" width="19" height="22" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <line x1="1100.5" y1="497" x2="1100.5" y2="519" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <line x1="1091" y1="508" x2="1110" y2="508" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <rect x="1124" y="497" width="19" height="22" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <line x1="1133.5" y1="497" x2="1133.5" y2="519" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <line x1="1124" y1="508" x2="1143" y2="508" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <rect x="1091" y="527" width="19" height="22" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <line x1="1100.5" y1="527" x2="1100.5" y2="549" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <line x1="1091" y1="538" x2="1110" y2="538" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <rect x="1124" y="527" width="19" height="22" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <line x1="1133.5" y1="527" x2="1133.5" y2="549" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <line x1="1124" y1="538" x2="1143" y2="538" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <rect x="1091" y="557" width="19" height="22" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <line x1="1100.5" y1="557" x2="1100.5" y2="579" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <line x1="1091" y1="568" x2="1110" y2="568" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <rect x="1124" y="557" width="19" height="22" fill="#eaf1fd" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <line x1="1133.5" y1="557" x2="1133.5" y2="579" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <line x1="1124" y1="568" x2="1143" y2="568" stroke="#1c2438" stroke-width="1.1" stroke-linecap="round" />
        <rect x="1105" y="599" width="24" height="41" fill="#5f82ec" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
    </g>
    {{-- GEDUNG H: Menara kanan (rapat ke tepi layar) --}}
    <g id="tower-right">
        <line x1="1402" y1="192" x2="1402" y2="223" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <line x1="1396" y1="202" x2="1408" y2="202" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <line x1="1320" y1="250" x2="1320" y2="260" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <line x1="1314" y1="250" x2="1326" y2="250" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
        <rect x="1359" y="223" width="41" height="37" fill="#e3ecfc" stroke="#1c2438" stroke-width="2"
            stroke-linejoin="round" />
        <rect x="1372" y="233" width="16" height="22" fill="#ffffff" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <rect x="1295" y="260" width="150" height="380" fill="url(#bldgFill)" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <rect x="1295" y="260" width="150" height="18" fill="#f4f8ff" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <rect x="1295" y="276" width="150" height="6" fill="#3f5fc9" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <rect x="1308" y="292" width="32" height="34" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <rect x="1308" y="316" width="32" height="10" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.4"
            stroke-linejoin="round" />
        <rect x="1350" y="292" width="32" height="34" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <rect x="1350" y="316" width="32" height="10" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.4"
            stroke-linejoin="round" />
        <rect x="1392" y="292" width="32" height="34" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <rect x="1392" y="316" width="32" height="10" fill="#7f9ff0" stroke="#1c2438" stroke-width="1.4"
            stroke-linejoin="round" />
        <rect x="1295" y="336" width="150" height="5" fill="#3f5fc9" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <rect x="1308" y="375" width="32" height="70" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1308" y="445" width="32" height="65" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1308" y="510" width="32" height="75" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1350" y="375" width="32" height="70" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1350" y="445" width="32" height="65" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1350" y="510" width="32" height="75" fill="url(#winMid)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1392" y="375" width="32" height="70" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1392" y="445" width="32" height="65" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1392" y="510" width="32" height="75" fill="url(#winDark)" stroke="#1c2438" stroke-width="1.5"
            stroke-linejoin="round" />
        <rect x="1295" y="598" width="150" height="42" fill="#eef4fd" stroke="#1c2438" stroke-width="2.2"
            stroke-linejoin="round" />
        <rect x="1330" y="608" width="40" height="32" fill="url(#winLight)" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="1350" y1="608" x2="1350" y2="640" stroke="#1c2438" stroke-width="1.3" stroke-linecap="round" />
    </g>
    {{-- 4. POHON, LAMPU, SEPEDA --}}
    <g>
        <line x1="182" y1="632" x2="182" y2="640" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <polygon points="182,575 173,598 191,598" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="182,590 169,617 195,617" fill="#eef4fe" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="182,606 165,632 199,632" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
    </g>
    <g>
        <line x1="578" y1="632" x2="578" y2="640" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <polygon points="578,575 569,598 587,598" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="578,590 565,617 591,617" fill="#eef4fe" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="578,606 561,632 595,632" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
    </g>
    <g>
        <line x1="874" y1="632" x2="874" y2="640" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <polygon points="874,575 865,598 883,598" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="874,590 861,617 887,617" fill="#eef4fe" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="874,606 857,632 891,632" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
    </g>
    <g>
        <line x1="1234" y1="632" x2="1234" y2="640" stroke="#1c2438" stroke-width="2.2" stroke-linecap="round" />
        <polygon points="1234,575 1225,598 1243,598" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="1234,590 1221,617 1247,617" fill="#eef4fe" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
        <polygon points="1234,606 1217,632 1251,632" fill="#ffffff" stroke="#1c2438" stroke-width="1.8"
            stroke-linejoin="round" />
    </g>
    <g>
        <line x1="451" y1="595" x2="451" y2="640" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <path d="M 444 597 C 447 593 451 593 451 595 C 451 593 455 593 458 597" fill="none" stroke="#1c2438"
            stroke-width="1.8" stroke-linecap="round" />
        <circle cx="444" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
        <circle cx="458" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
    </g>
    <g>
        <line x1="640" y1="595" x2="640" y2="640" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <path d="M 633 597 C 636 593 640 593 640 595 C 640 593 644 593 647 597" fill="none" stroke="#1c2438"
            stroke-width="1.8" stroke-linecap="round" />
        <circle cx="633" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
        <circle cx="647" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
    </g>
    <g>
        <line x1="798" y1="595" x2="798" y2="640" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <path d="M 791 597 C 794 593 798 593 798 595 C 798 593 802 593 805 597" fill="none" stroke="#1c2438"
            stroke-width="1.8" stroke-linecap="round" />
        <circle cx="791" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
        <circle cx="805" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
    </g>
    <g>
        <line x1="1064" y1="595" x2="1064" y2="640" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <path d="M 1057 597 C 1060 593 1064 593 1064 595 C 1064 593 1068 593 1071 597" fill="none" stroke="#1c2438"
            stroke-width="1.8" stroke-linecap="round" />
        <circle cx="1057" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
        <circle cx="1071" cy="600" r="2.5" fill="#fdf2bb" stroke="#1c2438" stroke-width="1.5" />
    </g>
    <line x1="158" y1="626" x2="158" y2="640" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
    <circle cx="158" cy="623" r="3" fill="#ffffff" stroke="#1c2438" stroke-width="1.5" />
    <line x1="1049" y1="626" x2="1049" y2="640" stroke="#1c2438" stroke-width="1.8" stroke-linecap="round" />
    <circle cx="1049" cy="623" r="3" fill="#ffffff" stroke="#1c2438" stroke-width="1.5" />
    <g transform="translate(20 5)">
        <circle cx="813" cy="627" r="6.5" fill="none" stroke="#1c2438" stroke-width="1.8" />
        <circle cx="832" cy="627" r="6.5" fill="none" stroke="#1c2438" stroke-width="1.8" />
        <polyline points="813,627 822,627 827,620 818,620 813,627" fill="none" stroke="#1c2438" stroke-width="1.6"
            stroke-linejoin="round" />
        <line x1="822" y1="627" x2="820" y2="617" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="817" y1="617" x2="823" y2="617" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
        <line x1="832" y1="627" x2="829" y2="615" stroke="#1c2438" stroke-width="1.6" stroke-linecap="round" />
        <line x1="826" y1="615" x2="832" y2="615" stroke="#1c2438" stroke-width="2" stroke-linecap="round" />
    </g>
    <rect x="114" y="629" width="30" height="11" fill="#5f82ec" stroke="#1c2438" stroke-width="1.6"
        stroke-linejoin="round" />
    <rect x="413" y="629" width="30" height="11" fill="#5f82ec" stroke="#1c2438" stroke-width="1.6"
        stroke-linejoin="round" />
    <rect x="705" y="629" width="30" height="11" fill="#5f82ec" stroke="#1c2438" stroke-width="1.6"
        stroke-linejoin="round" />
    <rect x="1287" y="629" width="30" height="11" fill="#5f82ec" stroke="#1c2438" stroke-width="1.6"
        stroke-linejoin="round" />
    {{-- 5. TANAH & TROTOAR --}}
    <line x1="0" y1="640" x2="1440" y2="640" stroke="#1c2438" stroke-width="2.4" stroke-linecap="round" />
    <rect class="ground-strip" x="0" y="640" width="1440" height="40" fill="#fdfdff" stroke="#1c2438"
        stroke-width="1.6" />
</svg>