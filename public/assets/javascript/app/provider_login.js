
$(document).ready(function () {

    const $form = $('#providerLoginForm');
    const $button = $('#providerLoginButton');

    // Clear previous errors.
    function clearErrors() {
        $('.field-error').empty();

        $('#generalError')
            .addClass('hidden')
            .empty();

        $('input').removeClass('border-red-500');
    }

    // Display errors returned by PHP.
    function displayErrors(errors) {
        $.each(errors, function (field, messages) {

            if (field === 'general') {
                $('#generalError')
                    .removeClass('hidden')
                    .text(messages.join(' '));

                return;
            }

            const $errorContainer = $('[data-error="' + field + '"]');

            if ($errorContainer.length) {

                $.each(messages, function (index, message) {
                    $('<p></p>')
                        .text(message)
                        .appendTo($errorContainer);
                });

                $('#' + field).addClass('border-red-500');
            }
        });
    }

    // Clear field errors when the user edits an input.
    $('input').on('input', function () {
        const field = $(this).attr('name');

        $('[data-error="' + field + '"]').empty();

        $(this).removeClass('border-red-500');

        $('#generalError')
            .addClass('hidden')
            .empty();
    });

    // Handle provider login submission.
    $form.on('submit', function (e) {
        e.preventDefault();

        clearErrors();

        $button.prop('disabled', true).text('Signing In...');

        $.ajax({
            url: window.location.pathname,
            type: 'POST',
            data: $form.serialize() + '&ajax=1',
            dataType: 'json',

            success: function (response) {

                if (response.success) {
                    window.location.href = 'provider_dashboard.php';

                } else {

                    $button.prop('disabled', false).text('Sign In');

                    displayErrors(response.errors || {
                        general: ['Login failed. Please try again.']
                    });
                }
            },

            error: function (xhr) {

                $button.prop('disabled', false).text('Sign In');

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    displayErrors(xhr.responseJSON.errors);
                } else {
                    $('#generalError')
                        .removeClass('hidden')
                        .text('Unable to connect to the server. Please try again.');
                }
            }
        });
    });
});
