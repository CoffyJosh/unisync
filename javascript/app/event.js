
    // =====================================
    // REGISTER / CANCEL REGISTRATION
    // =====================================

    function updateRegistration(action) {

        const $button = $('#registerButton');
        const $link = $('#unregisterLink');
        const $message = $('#registrationMessage');
        const $status = $('#registrationStatus');

        if (action === 'register') {
            $button.prop('disabled', true).text('Registering...');
        } else {
            $link.css('pointer-events', 'none').text('Cancelling...');
        }

        $message
            .addClass('hidden')
            .removeClass('bg-green-50 text-green-700 bg-red-50 text-red-700')
            .empty();

        $.ajax({

            url: window.location.pathname + window.location.search,
            type: 'POST',

            data: {
                ajax: 1,
                action: action
            },

            dataType: 'json',

            success: function (response) {

                if (response.success) {

                  if (action === 'register') {

                      // Remove Register button and show confirmation.
                      $button.remove();

                      $status
                          .removeClass('hidden')
                          .html(`
                              <div class="bg-green-50 text-green-700 rounded-lg p-4 text-center">

                                  <i class="fa-solid fa-circle-check text-xl mb-2"></i>

                                  <p class="font-semibold">You're registered!</p>

                                  <p class="text-sm mt-1">
                                      You're already registered for this event.
                                  </p>

                              </div>

                              <p class="text-sm text-slate-500 text-center mt-3">
                                  Changed your mind?

                                  <a href="#" id="unregisterLink"
                                    class="font-semibold text-red-600 hover:underline">
                                      Cancel registration
                                  </a>
                              </p>
                          `);

                  } else {

                        // Remove confirmation and restore Register button.
                        $status
                            .removeClass('bg-green-50 text-green-700 rounded-lg p-4 text-center')
                            .addClass('hidden')
                            .empty();

                        $message.addClass('hidden').empty();

                        const newButton = $('<button>', {
                            id: 'registerButton',
                            type: 'button',
                            class: 'w-full bg-[#1E3A5F] text-white font-semibold py-3 rounded-lg hover:bg-[#294D78] transition-colors',
                            text: 'Register for Event'
                        });

                        $status.after(newButton);
                    }

                } else {

                    $message
                        .removeClass('hidden')
                        .addClass('bg-red-50 text-red-700')
                        .text(response.message);

                    $button.prop('disabled', false).text('Register for Event');

                    $link
                        .css('pointer-events', 'auto')
                        .text('Cancel registration');
                }

            },

            error: function (xhr) {

                $message
                    .removeClass('hidden')
                    .addClass('bg-red-50 text-red-700')
                    .text(
                        xhr.responseJSON?.message ||
                        'Unable to process your request. Please try again.'
                    );

                $button.prop('disabled', false).text('Register for Event');

                $link
                    .css('pointer-events', 'auto')
                    .text('Cancel registration');
            }

        });
    }

    $(document).on('click', '#registerButton', function () {
        updateRegistration('register');
    });

    $(document).on('click', '#unregisterLink', function (e) {

        e.preventDefault();

        if (confirm('Are you sure you want to cancel your registration?')) {
            updateRegistration('unregister');
        }

    });
