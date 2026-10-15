import './bootstrap';
import Alpine from 'alpinejs';
import ArtAlert from './art-alert';

window.Alpine = Alpine;
window.ArtAlert = ArtAlert;

// Maintain window.Swal as alias to ArtAlert for 100% backward compatibility
// so any existing scripts/views automatically use our custom styled alert!
window.Swal = ArtAlert;

// Global Toast / Alert Helpers with ARTDEVATA Clay theme
window.toastSuccess = function (message, title = 'Berhasil!') {
    return ArtAlert.toast(message, 'success', title);
};

window.toastError = function (message, title = 'Perhatian') {
    return ArtAlert.toast(message, 'error', title);
};

window.confirmAction = function (message, onConfirm, title = 'Konfirmasi Tindakan', icon = 'warning', confirmBtn = 'Ya, Lanjutkan!') {
    const isDanger = icon === 'danger' || confirmBtn.toLowerCase().includes('hapus') || confirmBtn.toLowerCase().includes('tutup');
    return ArtAlert.fire({
        title: title,
        text: message,
        icon: isDanger ? 'danger' : icon,
        showCancelButton: true,
        confirmButtonText: confirmBtn,
        cancelButtonText: 'Batal',
        isDanger: isDanger,
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === 'function') {
            onConfirm();
        }
    });
};

// Global Listener for Forms with data-confirm & Global Double-Submit Prevention
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form || form.tagName !== 'FORM') return;

    if (form.hasAttribute('data-confirm')) {
        if (!form.dataset.confirmed) {
            e.preventDefault();
            const message = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const icon = form.getAttribute('data-confirm-icon') || 'warning';
            const confirmBtn = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan!';
            const isDanger = form.getAttribute('data-confirm-danger') === 'true';

            ArtAlert.fire({
                title: title,
                text: message,
                icon: isDanger ? 'danger' : icon,
                showCancelButton: true,
                confirmButtonText: confirmBtn,
                cancelButtonText: 'Batal',
                isDanger: isDanger,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
            return;
        }
    }

    // Double-submit prevention
    if (form.dataset.submitting === 'true') {
        e.preventDefault();
        return;
    }

    form.dataset.submitting = 'true';

    // Gracefully disable submit buttons after a minimal tick to allow form values to be captured
    setTimeout(() => {
        const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitBtns.forEach((btn) => {
            btn.setAttribute('data-original-disabled', btn.disabled);
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
        });
    }, 10);
});

// Reset submitting state if user navigates back (bfcache)
window.addEventListener('pageshow', function () {
    document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
        form.dataset.submitting = 'false';
        delete form.dataset.confirmed;
        const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitBtns.forEach((btn) => {
            btn.disabled = false;
            btn.classList.remove('opacity-70', 'cursor-not-allowed');
        });
    });
});

Alpine.start();
