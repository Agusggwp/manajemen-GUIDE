<style>
@media print {
    #art-alert-modal,
    #art-toast-container,
    .art-alert-card,
    .art-toast-item,
    .swal2-container,
    .swal2-popup,
    .swal2-backdrop {
        display: none !important;
        visibility: hidden !important;
    }
}
</style>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertHandler = window.ArtAlert || window.Swal;
        if (alertHandler) {
            alertHandler.toast({!! json_encode(session('success')) !!}, 'success', 'Berhasil!');
        }
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertHandler = window.ArtAlert || window.Swal;
        if (alertHandler) {
            alertHandler.fire({
                icon: 'error',
                title: 'Perhatian / Terjadi Kesalahan',
                text: {!! json_encode(session('error')) !!},
                confirmButtonText: 'Tutup'
            });
        }
    });
</script>
@endif

@if(isset($errors) && $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertHandler = window.ArtAlert || window.Swal;
        if (alertHandler) {
            alertHandler.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: {!! json_encode(implode('<br>', $errors->all())) !!},
                confirmButtonText: 'Periksa Kembali'
            });
        }
    });
</script>
@endif
