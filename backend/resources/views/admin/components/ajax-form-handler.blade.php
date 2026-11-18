<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $(document).off('submit', '.ajax-form').on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnHtml = submitBtn.html();
        var isMultipart = form.attr('enctype') === 'multipart/form-data';

        // Clear previous field errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback.ajax-error').remove();

        // Disable button during request
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...');

        var ajaxOptions = {
            url: form.attr('action'),
            type: form.attr('method') || 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            },
            success: function(response) {
                var message = response.message || 'Operation completed successfully!';
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonColor: '#6f42c1',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                }).then(function() {
                    var redirectUrl = response.redirect || form.data('redirect');
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        location.reload();
                    }
                });
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);

                var title = 'Error';
                var msg = 'An error occurred.';

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errorList = [];
                    $.each(xhr.responseJSON.errors, function(field, messages) {
                        errorList.push(messages.join(' '));
                        var input = form.find('[name="' + field + '"], [name="' + field + '[]"]');
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback ajax-error d-block">' + messages[0] + '</div>');
                        }
                    });
                    msg = errorList.join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: msg,
                    confirmButtonColor: '#6f42c1',
                    confirmButtonText: 'OK'
                });
            }
        };

        if (isMultipart) {
            ajaxOptions.data = new FormData(form[0]);
            ajaxOptions.processData = false;
            ajaxOptions.contentType = false;
        } else {
            ajaxOptions.data = form.serialize();
        }

        $.ajax(ajaxOptions);
    });
});
</script>
