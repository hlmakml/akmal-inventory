// Supplier page: add/edit modal, delete confirmation, search
(function () {

    // Modal add/edit supplier
    var modalSupplier = document.getElementById('modalSupplier');
    var formSupplier = document.getElementById('formSupplier');

    modalSupplier.addEventListener('show.bs.modal', function (event) {
        var tombol = event.relatedTarget;
        var mode = tombol ? tombol.getAttribute('data-mode') : 'tambah';
        var judul = document.getElementById('judulModalSupplier');
        var simpan = document.getElementById('tombolSimpan');

        formSupplier.reset();
        document.getElementById('inputId').value = '';

        if (mode === 'ubah') {
            judul.textContent = 'Ubah supplier';
            simpan.textContent = 'Simpan perubahan';
            formSupplier.action = formSupplier.getAttribute('data-url-ubah');

            document.getElementById('inputId').value = tombol.getAttribute('data-id');
            document.getElementById('inputKode').value = tombol.getAttribute('data-kode');
            document.getElementById('inputNama').value = tombol.getAttribute('data-nama');
            document.getElementById('inputKontak').value = tombol.getAttribute('data-kontak');
            document.getElementById('inputEmail').value = tombol.getAttribute('data-email');
            document.getElementById('inputAlamat').value = tombol.getAttribute('data-alamat');
        } else {
            judul.textContent = 'Tambah supplier';
            simpan.textContent = 'Simpan supplier';
            formSupplier.action = formSupplier.getAttribute('data-url-tambah');
        }
    });

    // Modal delete confirmation
    var modalHapus = document.getElementById('modalHapus');
    var formHapus = document.getElementById('formHapus');

    modalHapus.addEventListener('show.bs.modal', function (event) {
        var tombol = event.relatedTarget;
        document.getElementById('namaHapus').textContent = tombol.getAttribute('data-nama');
        formHapus.action = formHapus.getAttribute('data-url') + encodeURIComponent(tombol.getAttribute('data-id'));
    });

    // Search functionality
    var inputCari = document.getElementById('cariSupplier');
    var kosong = document.getElementById('barisKosong');

    if (inputCari && kosong) {
        var baris = document.querySelectorAll('#tabelSupplier tbody tr[data-cari]');

        inputCari.addEventListener('input', function () {
            var kata = inputCari.value.trim().toLowerCase();
            var tampil = 0;

            baris.forEach(function (tr) {
                var cocok = tr.getAttribute('data-cari').toLowerCase().indexOf(kata) !== -1;
                tr.classList.toggle('d-none', !cocok);
                if (cocok) { tampil++; }
            });

            kosong.classList.toggle('d-none', tampil !== 0);
        });
    }
})();
