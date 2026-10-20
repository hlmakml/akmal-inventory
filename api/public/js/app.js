// Shared script: sidebar toggle and table sorting

// Sidebar toggle for mobile
(function () {
    var body = document.body;
    var tombol = document.getElementById('sidebarToggle');
    var latar = document.getElementById('sidebarBackdrop');
    if (!tombol || !latar) { return; }

    function atur(buka) {
        body.classList.toggle('sidebar-open', buka);
        tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
    }

    tombol.addEventListener('click', function () {
        atur(!body.classList.contains('sidebar-open'));
    });
    latar.addEventListener('click', function () { atur(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { atur(false); }
    });
})();

// Table sorting (used by multiple pages)
(function () {
    var pilihan = document.querySelectorAll('select[data-urut-tabel]');

    pilihan.forEach(function (sel) {
        var tabel = document.getElementById(sel.getAttribute('data-urut-tabel'));
        if (!tabel || !tabel.tBodies.length) { return; }
        var tbody = tabel.tBodies[0];

        var urutkan = function () {
            var mode = sel.value;
            var baris = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-nama]'));

            baris.sort(function (a, b) {
                if (mode === 'nama-asc' || mode === 'nama-desc') {
                    var hasil = a.getAttribute('data-nama').localeCompare(
                        b.getAttribute('data-nama'), 'id', { sensitivity: 'base' }
                    );
                    return (mode === 'nama-asc') ? hasil : -hasil;
                }
                var selisih = Number(a.getAttribute('data-waktu')) - Number(b.getAttribute('data-waktu'));
                return (mode === 'terbaru') ? -selisih : selisih;
            });

            var penutup = tbody.querySelector('tr:not([data-nama])');
            baris.forEach(function (tr) { tbody.insertBefore(tr, penutup); });
        };

        sel.addEventListener('change', urutkan);
        urutkan();
    });
})();
