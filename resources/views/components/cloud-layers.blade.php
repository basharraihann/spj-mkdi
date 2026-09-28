{{-- resources/views/components/cloud-layers.blade.php
     Koordinat 200 x 1000. Sisi kanan (x=200) menempel ke panel putih, lekukan menonjol ke kiri. --}}

{{-- Lapis paling belakang (paling muda) --}}
<path fill="white" fill-opacity=".22" d="M200 100 Q100 100 100 140
    A70 70 0 0 0 100 280
    A95 95 0 0 0 100 460
    A70 70 0 0 0 100 600
    A100 100 0 0 0 100 790
    A45 45 0 0 0 100 880
    Q100 920 200 920 Z" />

{{-- Lapis tengah --}}
<path fill="white" fill-opacity=".38" d="M200 40 Q125 40 125 80
    A50 50 0 0 0 125 170
    A95 95 0 0 0 125 350
    A80 80 0 0 0 125 500
    A105 105 0 0 0 125 700
    A80 80 0 0 0 125 850
    A50 50 0 0 0 125 940
    Q125 980 200 980 Z" />

{{-- Lapis depan (putih, menyatu dengan panel form) --}}
<path fill="white" d="M200 0 H165
    A60 60 0 0 0 165 110
    A100 100 0 0 0 165 300
    A80 80 0 0 0 165 450
    A100 100 0 0 0 165 640
    A70 70 0 0 0 165 770
    A100 100 0 0 0 165 960
    A30 30 0 0 0 165 1000
    H200 Z" />
