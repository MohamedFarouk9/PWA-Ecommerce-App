<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $(document).off('submit', '.delete-file-form').on('submit', '.delete-file-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var row = form.closest('tr');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This item will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6f42c1',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method') || 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                    },
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Item deleted successfully.',
                            confirmButtonColor: '#6f42c1',
                            confirmButtonText: 'OK'
                        });
                        if (row.length) {
                            row.fadeOut(300, function() { $(this).remove(); });
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg,
                            confirmButtonColor: '#6f42c1',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});
</script>
