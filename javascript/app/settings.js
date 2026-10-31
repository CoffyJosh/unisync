
$(document).ready(function () {

    // Clear all previous errors.
    function clearErrors($form) {
        $form.find('.field-error').empty();

        $form.find('input').removeClass('border-red-500');

        $('#generalMessage')
            .addClass('hidden')
            .removeClass('bg-green-50 border border-green-200 text-green-700 bg-red-50 border-red-200 text-red-700')
            .empty();
    }

    // Display validation errors.
    function displayErrors(errors, $form) {

        $.each(errors, function (field, messages) {

            if (field === 'general') {
                showMessage(messages.join(' '), false);
                return;
            }

            const $error = $form.find('[data-error="' + field + '"]');

            if ($error.length) {

                $.each(messages, function (index, message) {
                    $('<p></p>').text(message).appendTo($error);
                });

                $form.find('[name="' + field + '"]')
                    .addClass('border-red-500');
            }
        });
    }

    // Display success or general error messages.
    function showMessage(message, success) {

        const $message = $('#generalMessage');

        $message
            .removeClass('hidden bg-green-50 border-green-200 text-green-700 bg-red-50 border-red-200 text-red-700')
            .addClass(
                success
                    ? 'bg-green-50 border border-green-200 text-green-700'
                    : 'bg-red-50 border border-red-200 text-red-700'
            )
            .text(message);

        $('html, body').animate({
            scrollTop: 0
        }, 300);
    }

    // Remove an error when the user edits a field.
    $('input').on('input', function () {

        const field = $(this).attr('name');

        $('[data-error="' + field + '"]').empty();

        $(this).removeClass('border-red-500');
    });

    // ACCOUNT INFORMATION FORM
    $('#accountForm').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $button = $('#accountButton');

        clearErrors($form);

        $button
            .prop('disabled', true)
            .text('Saving...');

        $.ajax({
            url: 'settings.php',
            type: 'POST',
            data: $form.serialize() + '&ajax=1&action=update_account',
            dataType: 'json',

            success: function (response) {

                if (response.success) {
                    showMessage(response.message, true);
                } else {
                    displayErrors(response.errors || {}, $form);
                }
            },

            error: function (xhr) {

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    displayErrors(xhr.responseJSON.errors, $form);
                } else {
                    showMessage('Unable to update your account. Please try again.', false);
                }
            },

            complete: function () {
                $button
                    .prop('disabled', false)
                    .text('Save Changes');
            }
        });

    });

    // CHANGE PASSWORD FORM
    $('#passwordForm').on('submit', function (e) {

        e.preventDefault();

        const $form = $(this);
        const $button = $('#passwordButton');

        clearErrors($form);

        $button
            .prop('disabled', true)
            .text('Updating...');

        $.ajax({
            url: 'settings.php',
            type: 'POST',
            data: $form.serialize() + '&ajax=1&action=change_password',
            dataType: 'json',

            success: function (response) {

                if (response.success) {

                    showMessage(response.message, true);

                    // Clear password fields after success.
                    $form[0].reset();

                } else {
                    displayErrors(response.errors || {}, $form);
                }
            },

            error: function (xhr) {

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    displayErrors(xhr.responseJSON.errors, $form);
                } else {
                    showMessage('Unable to change your password. Please try again.', false);
                }
            },

            complete: function () {
                $button
                    .prop('disabled', false)
                    .text('Update Password');
            }
        });

    });

});
