
$(document).ready(function () {

    const $form = $('#loginForm');
    const $card = $('#loginCard');
    const $loading = $('#loginLoading');
    const $success = $('#loginSuccess');
    const $button = $('#loginButton');

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

    // Handle login submission.
    $form.on('submit', function (e) {
        e.preventDefault();

        clearErrors();

        // Show loading screen.
        $card.addClass('hidden');
        $loading.removeClass('hidden');

        $button.prop('disabled', true);

        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: $form.serialize() + '&ajax=1',
            dataType: 'json',

            success: function (response) {

                if (response.success) {
                    window.location.href = 'index.php';

                } else {

                    // Return to login form.
                    $loading.addClass('hidden');
                    $card.removeClass('hidden');

                    $button.prop('disabled', false);

                    displayErrors(response.errors || {
                        general: ['Login failed. Please try again.']
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
                    $('#generalError')
                        .removeClass('hidden')
                        .text('Unable to connect to the server. Please try again.');
                }
            }
        });
    });

});
