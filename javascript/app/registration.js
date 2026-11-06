
$(document).ready(function () {

    const $form = $('#registrationForm');
    const $card = $('#registrationCard');
    const $loading = $('#registrationLoading');
    const $success = $('#registrationSuccess');
    const $button = $('#registerButton');

    // Clear all previous validation errors.
    function clearErrors() {
        $('.field-error').empty();

        $('#generalError')
            .addClass('hidden')
            .empty();

        $('input').removeClass('border-red-500');
    }

    // Display validation errors returned by PHP.
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

    // Clear an individual field's errors when edited.
    $('input').on('input', function () {
        const field = $(this).attr('name');

        $('[data-error="' + field + '"]').empty();

        $(this).removeClass('border-red-500');

        $('#generalError')
            .addClass('hidden')
            .empty();
    });

    // Handle registration form submission.
    $form.on('submit', function (e) {
        e.preventDefault();

        clearErrors();

        // Show loading screen.
        $card.addClass('hidden');
        $loading.removeClass('hidden');

        $button.prop('disabled', true);

        // Send registration request through AJAX.
        $.ajax({
            // Post back to the current page, including its mounted app path.
            url: window.location.pathname + window.location.search,
            type: 'POST',
            data: $form.serialize() + '&ajax=1',
            dataType: 'json',

            success: function (response) {

                if (response.success) {

                    // Show account-created screen.
                    $loading.addClass('hidden');
                    $success.removeClass('hidden');

                    // Redirect after 2 seconds.
                    setTimeout(function () {
                        window.location.href = 'home.php';
                    }, 2000);

                } else {

                    // Return to the registration form.
                    $loading.addClass('hidden');
                    $card.removeClass('hidden');

                    $button.prop('disabled', false);

                    // Display all validation errors.
                    displayErrors(response.errors || {
                        general: ['Registration failed. Please try again.']
                    });
                }
            },

            error: function (xhr) {

                $loading.addClass('hidden');
                $card.removeClass('hidden');

                $button.prop('disabled', false);

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    displayErrors(xhr.responseJSON.errors);
                } else {
                    const message = xhr.status === 0
                        ? 'Could not reach the server. Check that the local web server is running and try again.'
                        : 'The server could not complete registration (HTTP ' + xhr.status + '). Please try again.';

                    $('#generalError')
                        .removeClass('hidden')
                        .text(message);
                }
            }
        });
    });

});
