@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('success')) !!},
                confirmButtonColor: '#14433b',
                confirmButtonText: 'OK',
                timer: 4000,
                timerProgressBar: true
            });
        }
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Perhatian / Terjadi Kesalahan',
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#14433b',
                confirmButtonText: 'Tutup'
            });
        }
    });
</script>
@endif

@if(isset($errors) && $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: {!! json_encode(implode('<br>', $errors->all())) !!},
                confirmButtonColor: '#14433b',
                confirmButtonText: 'Periksa Kembali'
            });
        }
    });
</script>
@endif
