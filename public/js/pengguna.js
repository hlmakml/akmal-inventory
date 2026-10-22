// User management page: add/edit and delete modals
(function () {

    // Modal add/edit user
    var modalPengguna = document.getElementById('modalPengguna');
    var formPengguna = document.getElementById('formPengguna');
    var inputPassword = document.getElementById('inputPassword');
    var inputKonfirmasi = document.getElementById('inputKonfirmasi');
    var inputRole = document.getElementById('inputRole');

    modalPengguna.addEventListener('show.bs.modal', function (event) {
        var tombol = event.relatedTarget;
        var mode = tombol ? tombol.getAttribute('data-mode') : 'tambah';
        var judul = document.getElementById('judulModalPengguna');
        var simpan = document.getElementById('tombolSimpan');
        var infoPassword = document.getElementById('infoPassword');
        var infoRole = document.getElementById('infoRole');

        formPengguna.reset();
        document.getElementById('inputId').value = '';
        inputKonfirmasi.setCustomValidity('');
        inputRole.disabled = false;
        infoRole.textContent = '';

        if (mode === 'ubah') {
            judul.textContent = 'Ubah pengguna';
            simpan.textContent = 'Simpan perubahan';
            formPengguna.action = formPengguna.getAttribute('data-url-ubah');

            document.getElementById('inputId').value = tombol.getAttribute('data-id');
            document.getElementById('inputNama').value = tombol.getAttribute('data-nama');
            document.getElementById('inputUsername').value = tombol.getAttribute('data-username');
            inputRole.value = tombol.getAttribute('data-role');

            // Password optional when editing
            inputPassword.required = false;
            inputKonfirmasi.required = false;
            infoPassword.textContent = 'Kosongkan jika tidak ingin mengubah password.';

            // Prevent changing own role
            if (tombol.getAttribute('data-saya') === '1') {
                inputRole.disabled = true;
                infoRole.textContent = 'Role akun yang sedang Anda pakai tidak dapat diubah.';
            }
        } else {
            judul.textContent = 'Tambah pengguna';
            simpan.textContent = 'Simpan pengguna';
            formPengguna.action = formPengguna.getAttribute('data-url-tambah');

            inputPassword.required = true;
            inputKonfirmasi.required = true;
            infoPassword.textContent = 'Minimal 6 karakter.';
        }
    });

    // Validate password confirmation
    var cekKonfirmasi = function () {
        inputKonfirmasi.setCustomValidity(
            inputPassword.value !== inputKonfirmasi.value ? 'Konfirmasi password tidak sama.' : ''
        );
    };
    inputPassword.addEventListener('input', cekKonfirmasi);
    inputKonfirmasi.addEventListener('input', cekKonfirmasi);

    // Modal delete confirmation
    var modalHapus = document.getElementById('modalHapus');
    var formHapus = document.getElementById('formHapus');

    modalHapus.addEventListener('show.bs.modal', function (event) {
        var tombol = event.relatedTarget;
        document.getElementById('namaHapus').textContent = tombol.getAttribute('data-nama');
        formHapus.action = formHapus.getAttribute('data-url') + encodeURIComponent(tombol.getAttribute('data-id'));
    });
})();
