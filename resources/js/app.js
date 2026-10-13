import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

// Global Toast / Alert Helpers with ARTDEVATA Clay theme
window.toastSuccess = function (message, title = 'Berhasil!') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonColor: '#6C63FF',
        confirmButtonText: 'OK',
        timer: 3500,
        timerProgressBar: true,
    });
};

window.toastError = function (message, title = 'Perhatian') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#6C63FF',
        confirmButtonText: 'Tutup',
    });
};

window.confirmAction = function (message, onConfirm, title = 'Konfirmasi Tindakan', icon = 'warning', confirmBtn = 'Ya, Lanjutkan!') {
    return Swal.fire({
        title: title,
        text: message,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: '#6C63FF',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: confirmBtn,
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === 'function') {
            onConfirm();
        }
    });
};

// Global Listener for Forms with data-confirm
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form && form.hasAttribute('data-confirm')) {
        if (!form.dataset.confirmed) {
            e.preventDefault();
            const message = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const icon = form.getAttribute('data-confirm-icon') || 'warning';
            const confirmBtn = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan!';
            const isDanger = form.getAttribute('data-confirm-danger') === 'true';

            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: isDanger ? '#e11d48' : '#6C63FF',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: confirmBtn,
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    }
});

Alpine.start();
