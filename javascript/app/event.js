
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


// =====================================
// EVENT COUNTDOWN
// =====================================

const $countdown = $('#eventCountdown');

if ($countdown.length) {

    const eventStart = new Date($countdown.attr('data-start')).getTime();

    function updateCountdown() {

        const now = new Date().getTime();
        const difference = eventStart - now;

        if (difference <= 0) {
            $('#countdownDays').text('00');
            $('#countdownHours').text('00');
            $('#countdownMinutes').text('00');
            $('#countdownSeconds').text('00');

            return;
        }

        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((difference / (1000 * 60)) % 60);
        const seconds = Math.floor((difference / 1000) % 60);

        $('#countdownDays').text(String(days).padStart(2, '0'));
        $('#countdownHours').text(String(hours).padStart(2, '0'));
        $('#countdownMinutes').text(String(minutes).padStart(2, '0'));
        $('#countdownSeconds').text(String(seconds).padStart(2, '0'));
    }

    updateCountdown();

    setInterval(updateCountdown, 1000);
}

// =====================================
// EVENT MAP
// =====================================

const $mapElement = $('#eventMap');

if ($mapElement.length) {

    const latitude = parseFloat($mapElement.attr('data-lat'));
    const longitude = parseFloat($mapElement.attr('data-lng'));
    const venueName = $mapElement.attr('data-venue');

    if (
        !isNaN(latitude) &&
        !isNaN(longitude) &&
        typeof L !== 'undefined'
    ) {

        // Initialize map using venue coordinates.
        const map = L.map('eventMap').setView(
            [latitude, longitude],
            16
        );

        // Load OpenStreetMap tiles.
        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);

        // Add venue marker.
        L.marker([latitude, longitude])
            .addTo(map)
            .bindPopup(venueName)
            .openPopup();

    }
}