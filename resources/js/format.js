// Helper format angka. Dipakai lewat window.fmt.
(function () {
    const nf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

    // PARSE: sama persis dengan angka() lama di sisa-mak ("12" -> 12, "" -> 0)
    function toInt(v) { return parseInt(v || '0', 10) || 0; }

    // 1234567 -> "1.234.567" (null/NaN -> "0")
    function num(n) { return nf.format(Math.round(Number(n) || 0)); }

    // 1234567 -> "Rp 1.234.567", -5000 -> "-Rp 5.000"
    function rp(n) {
        n = Math.round(Number(n) || 0);
        return (n < 0 ? '-Rp ' : 'Rp ') + nf.format(Math.abs(n));
    }

    // Buat nilai input: "" tetap "", selain itu ambil digit lalu format
    function input(v) {
        const d = String(v ?? '').replace(/\D/g, '');
        return d === '' ? '' : nf.format(Number(d));
    }

    // Ambil digit murni dari string/angka apa pun
    function digits(v) { return String(v ?? '').replace(/\D/g, ''); }

    window.fmt = { toInt, num, rp, input, digits };
})();