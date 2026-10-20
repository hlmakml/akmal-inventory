// Laporan page: date range validation and auto-submit filter
(function () {
    var form = document.getElementById('formFilter');
    var dari = document.getElementById('inputDari');
    var sampai = document.getElementById('inputSampai');
    var kategori = document.getElementById('inputKategori');

    // Validate date range
    var cekRentang = function () {
        sampai.setCustomValidity(
            (dari.value && sampai.value && dari.value > sampai.value)
                ? 'Tanggal "Sampai tanggal" tidak boleh lebih awal dari "Dari tanggal".'
                : ''
        );
    };
    dari.addEventListener('change', cekRentang);
    sampai.addEventListener('change', cekRentang);

    // Auto-submit when date or category changes
    [dari, sampai, kategori].forEach(function (input) {
        input.addEventListener('change', function () {
            if (form.reportValidity()) {
                form.submit();
            }
        });
    });

})();
