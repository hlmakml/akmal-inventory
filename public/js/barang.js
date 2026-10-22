// Item page: add/edit modal, delete confirmation, search and critical stock filter
(function () {

    // Modal add/edit item
    var modalBarang = document.getElementById('modalBarang');
    var formBarang = document.getElementById('formBarang');

    modalBarang.addEventListener('show.bs.modal', function (event) {
        var tombol = event.relatedTarget;
        var mode = tombol ? tombol.getAttribute('data-mode') : 'tambah';
        var judul = document.getElementById('judulModalBarang');
        var simpan = document.getElementById('tombolSimpan');

        formBarang.reset();
        document.getElementById('inputId').value = '';

        if (mode === 'ubah') {
            judul.textContent = 'Ubah barang';
            simpan.textContent = 'Simpan perubahan';
            formBarang.action = formBarang.getAttribute('data-url-ubah');

            document.getElementById('inputId').value = tombol.getAttribute('data-id');
            document.getElementById('inputKode').value = tombol.getAttribute('data-kode');
            document.getElementById('inputNama').value = tombol.getAttribute('data-nama');
            document.getElementById('inputKategori').value = tombol.getAttribute('data-kategori');
            document.getElementById('inputSatuan').value = tombol.getAttribute('data-satuan');
            document.getElementById('inputStok').value = tombol.getAttribute('data-stok');
            document.getElementById('inputStokMin').value = tombol.getAttribute('data-stok-min');
            document.getElementById('inputHargaBeli').value = tombol.getAttribute('data-harga-beli');
            document.getElementById('inputHargaJual').value = tombol.getAttribute('data-harga-jual');
            document.getElementById('inputRak').value = tombol.getAttribute('data-rak');
        } else {
            judul.textContent = 'Tambah barang';
            simpan.textContent = 'Simpan barang';
            formBarang.action = formBarang.getAttribute('data-url-tambah');
        }
    });

    // Auto uppercase for rack location
    var inputRak = document.getElementById('inputRak');
    inputRak.addEventListener('input', function () {
        inputRak.value = inputRak.value.toUpperCase();
    });

    // Modal delete confirmation
    var modalHapus = document.getElementById('modalHapus');
    var formHapus = document.getElementById('formHapus');

    modalHapus.addEventListener('show.bs.modal', function (event) {
        var tombol = event.relatedTarget;
        document.getElementById('namaHapus').textContent = tombol.getAttribute('data-nama');
        formHapus.action = formHapus.getAttribute('data-url') + encodeURIComponent(tombol.getAttribute('data-id'));
    });

    // Search and critical stock filter
    var inputCari = document.getElementById('cariBarang');
    var saklarKritis = document.getElementById('filterKritis');
    var kosong = document.getElementById('barisKosong');

    if (inputCari && saklarKritis && kosong) {
        var baris = document.querySelectorAll('#tabelBarang tbody tr[data-cari]');

        var terapkanFilter = function () {
            var kata = inputCari.value.trim().toLowerCase();
            var tampil = 0;

            baris.forEach(function (tr) {
                var cocok = tr.getAttribute('data-cari').toLowerCase().indexOf(kata) !== -1;
                var lolos = !saklarKritis.checked || tr.getAttribute('data-kritis') === '1';
                var terlihat = cocok && lolos;

                tr.classList.toggle('d-none', !terlihat);
                if (terlihat) { tampil++; }
            });

            kosong.classList.toggle('d-none', tampil !== 0);
        };

        inputCari.addEventListener('input', terapkanFilter);
        saklarKritis.addEventListener('change', terapkanFilter);

        // Enable filter from dashboard link
        if (window.location.hash === '#kritis') {
            saklarKritis.checked = true;
        }
        terapkanFilter();
    }
})();
