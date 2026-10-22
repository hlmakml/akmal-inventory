// Incoming transaction page: stock info, prevent double submit, date filter
(function () {

    // Show stock info when item selected
    var pilihBarang = document.getElementById('pilihBarang');
    var infoStok = document.getElementById('infoStok');
    var satuan = document.getElementById('satuanBarang');

    if (pilihBarang) {
        pilihBarang.addEventListener('change', function () {
            var opsi = pilihBarang.options[pilihBarang.selectedIndex];
            if (!opsi.value) {
                infoStok.textContent = 'Pilih barang untuk melihat stok saat ini.';
                satuan.textContent = 'satuan';
                return;
            }
            var stok = Number(opsi.getAttribute('data-stok')).toLocaleString('id-ID');
            var unit = opsi.getAttribute('data-satuan');
            infoStok.textContent = 'Stok saat ini: ' + stok + ' ' + unit;
            satuan.textContent = unit;
        });
    }

    // Prevent double submit
    var form = document.getElementById('formTransaksi');
    var tombol = document.getElementById('tombolSimpan');
    if (form && tombol) {
        var labelAsli = tombol.innerHTML;

        form.addEventListener('submit', function () {
            tombol.disabled = true;
            tombol.textContent = 'Menyimpan...';
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                tombol.disabled = false;
                tombol.innerHTML = labelAsli;
            }
        });
    }

    // Date filter for history
    var dari = document.getElementById('filterDari');
    var sampai = document.getElementById('filterSampai');
    var reset = document.getElementById('resetFilter');
    var kosong = document.getElementById('barisKosong');
    var info = document.getElementById('infoRiwayat');

    if (dari && sampai && reset && kosong) {
        var baris = document.querySelectorAll('#tabelRiwayat tbody tr[data-tanggal]');

        var terapkan = function () {
            var d = dari.value;
            var s = sampai.value;
            var tampil = 0;

            baris.forEach(function (tr) {
                var t = tr.getAttribute('data-tanggal');
                var terlihat = (!d || t >= d) && (!s || t <= s);
                tr.classList.toggle('d-none', !terlihat);
                if (terlihat) { tampil++; }
            });

            kosong.classList.toggle('d-none', tampil !== 0);
            info.textContent = (d || s)
                ? tampil + ' dari ' + baris.length + ' transaksi'
                : baris.length + ' transaksi';
        };

        dari.addEventListener('change', terapkan);
        sampai.addEventListener('change', terapkan);
        reset.addEventListener('click', function () {
            dari.value = '';
            sampai.value = '';
            terapkan();
        });
    }
})();
