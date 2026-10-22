// Login page: toggle password visibility

(function () {
    const input = document.getElementById('password');
    const button = document.getElementById('togglePassword');
    const icon = button.querySelector('i');

    button.addEventListener('click', function () {
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        icon.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
        button.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
        button.setAttribute('aria-pressed', tampil ? 'true' : 'false');
    });
})();
