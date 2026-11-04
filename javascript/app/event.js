
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

                    $('#registerButton').remove();

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

                                <a href="#"
                                   id="unregisterLink"
                                   class="font-semibold text-red-600 hover:underline">
                                    Cancel registration
                                </a>
                            </p>
                        `);

                } else {

                    $status
                        .addClass('hidden')
                        .empty();

                    $message
                        .addClass('hidden')
                        .empty();

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

                $('#registerButton')
                    .prop('disabled', false)
                    .text('Register for Event');

                $('#unregisterLink')
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

            $('#registerButton')
                .prop('disabled', false)
                .text('Register for Event');

            $('#unregisterLink')
                .css('pointer-events', 'auto')
                .text('Cancel registration');
        }

    });
}


// Register button.
$(document).on('click', '#registerButton', function () {
    updateRegistration('register');
});


// Cancel registration.
$(document).on('click', '#unregisterLink', function (e) {

    e.preventDefault();

    if (confirm('Are you sure you want to cancel your registration?')) {
        updateRegistration('unregister');
    }

});


// =====================================
// EVENT COUNTDOWN
// =====================================

const $eventTiming = $('#eventTiming');

if ($eventTiming.length) {

    const eventStatus = $eventTiming.attr('data-status');

    let schedules = [];

    try {
        schedules = JSON.parse(
            $eventTiming.attr('data-schedules') || '[]'
        );
    } catch (error) {
        console.error('Unable to read event schedules:', error);
    }

    schedules = schedules.map(schedule => ({
        start: new Date(schedule.start).getTime(),
        end: new Date(schedule.end).getTime()
    })).filter(schedule =>
        Number.isFinite(schedule.start) &&
        Number.isFinite(schedule.end)
    );

    schedules.sort((a, b) => a.start - b.start);

    const $countdown = $('#eventCountdown');
    const $ongoing = $('#eventOngoing');
    const $ended = $('#eventEnded');
    const $noSchedule = $('#eventNoSchedule');
    const $cancelled = $('#eventCancelled');
    const $completed = $('#eventCompleted');


    function hideTimingMessages() {

        $countdown.addClass('hidden');
        $ongoing.addClass('hidden');
        $ended.addClass('hidden');
        $noSchedule.addClass('hidden');
        $cancelled.addClass('hidden');
        $completed.addClass('hidden');

    }


    function displayZeroCountdown() {

        $('#countdownDays').text('00');
        $('#countdownHours').text('00');
        $('#countdownMinutes').text('00');
        $('#countdownSeconds').text('00');

        $countdown.removeClass('hidden');

    }


    function updateCountdown() {

        hideTimingMessages();

        if (eventStatus === 'cancelled' || eventStatus === 'deleted') {
            $cancelled.removeClass('hidden');
            return;
        }

        if (eventStatus === 'completed') {
            $completed.removeClass('hidden');
            return;
        }

        const now = Date.now();

        let nextSchedule = null;
        let currentlyOngoing = false;

        for (const schedule of schedules) {

            if (now >= schedule.start && now < schedule.end) {
                currentlyOngoing = true;
            }

            if (now < schedule.start) {

                if (
                    nextSchedule === null ||
                    schedule.start < nextSchedule.start
                ) {
                    nextSchedule = schedule;
                }
            }
        }


        // Count down to the next upcoming confirmed schedule.
        if (nextSchedule !== null) {

            const difference = nextSchedule.start - now;

            const days = Math.floor(
                difference / (1000 * 60 * 60 * 24)
            );

            const hours = Math.floor(
                (difference / (1000 * 60 * 60)) % 24
            );

            const minutes = Math.floor(
                (difference / (1000 * 60)) % 60
            );

            const seconds = Math.floor(
                (difference / 1000) % 60
            );

            $('#countdownDays').text(
                String(days).padStart(2, '0')
            );

            $('#countdownHours').text(
                String(hours).padStart(2, '0')
            );

            $('#countdownMinutes').text(
                String(minutes).padStart(2, '0')
            );

            $('#countdownSeconds').text(
                String(seconds).padStart(2, '0')
            );

            $countdown.removeClass('hidden');
            return;
        }


        // An approved schedule is currently ongoing.
        if (currentlyOngoing) {
            $ongoing.removeClass('hidden');
            return;
        }


        // No confirmed schedules, including when all are pending.
        if (schedules.length === 0) {
            displayZeroCountdown();
            return;
        }


        // Confirmed schedules exist, but none are upcoming or ongoing.
        $ended.removeClass('hidden');

    }


    updateCountdown();

    setInterval(updateCountdown, 1000);

}


// =====================================
// EVENT SCHEDULE SELECTION AND MAP
// =====================================

$(document).ready(function () {

    const $scheduleList = $('#mapScheduleList');

    if (!$scheduleList.length) {
        return;
    }


    // ---------------------------------
    // LOAD SCHEDULE DATA
    // ---------------------------------

    let mapSchedules = [];

    try {

        mapSchedules = JSON.parse(
            $('#mapScheduleData').text() || '[]'
        );

    } catch (error) {

        console.error('Unable to read map schedule data:', error);
        return;

    }


    if (!mapSchedules.length) {
        return;
    }


    mapSchedules = mapSchedules.map(schedule => ({

        ...schedule,

        id: Number(schedule.id),

        start: new Date(schedule.start).getTime(),

        end: new Date(schedule.end).getTime(),

        latitude: schedule.latitude !== null
            ? Number(schedule.latitude)
            : null,

        longitude: schedule.longitude !== null
            ? Number(schedule.longitude)
            : null,

        status: String(schedule.status || '').toLowerCase()

    })).filter(schedule =>
        Number.isFinite(schedule.id) &&
        Number.isFinite(schedule.start) &&
        Number.isFinite(schedule.end)
    );


    mapSchedules.sort((a, b) => {

        if (a.start !== b.start) {
            return a.start - b.start;
        }

        return a.end - b.end;

    });


    if (!mapSchedules.length) {
        return;
    }


    // ---------------------------------
    // MAP ELEMENTS
    // ---------------------------------

    const $map = $('#eventMap');
    const $mapUnavailable = $('#mapUnavailable');
    const $openMapLink = $('#openMapLink');


    // ---------------------------------
    // MAP STATE
    // ---------------------------------

    let map = null;
    let marker = null;

    let selectedScheduleId = null;

    let manualSelection = false;

    let lastAutomaticScheduleId = null;


    // ---------------------------------
    // COORDINATE VALIDATION
    // ---------------------------------

    function hasValidCoordinates(schedule) {

        return (
            Number.isFinite(schedule.latitude) &&
            Number.isFinite(schedule.longitude) &&
            schedule.latitude >= -90 &&
            schedule.latitude <= 90 &&
            schedule.longitude >= -180 &&
            schedule.longitude <= 180
        );

    }


    // ---------------------------------
    // DETERMINE AUTOMATIC SELECTION
    // CURRENT -> NEXT -> LAST
    // ---------------------------------

    function getAutomaticSchedule() {

        const now = Date.now();


        // 1. Current confirmed schedule.
        const currentSchedules = mapSchedules.filter(schedule => {

            return (
                schedule.status === 'confirmed' &&
                now >= schedule.start &&
                now < schedule.end
            );

        });

        if (currentSchedules.length > 0) {

            currentSchedules.sort((a, b) => {

                if (a.end !== b.end) {
                    return a.end - b.end;
                }

                return a.start - b.start;

            });

            return {
                schedule: currentSchedules[0],
                reason: 'Current'
            };

        }


        // 2. Next upcoming confirmed schedule.
        const upcomingSchedules = mapSchedules.filter(schedule => {

            return (
                schedule.status === 'confirmed' &&
                schedule.start > now
            );

        });

        if (upcomingSchedules.length > 0) {

            upcomingSchedules.sort((a, b) => a.start - b.start);

            return {
                schedule: upcomingSchedules[0],
                reason: 'Next'
            };

        }


        // 3. Most recently ended schedule.
        const endedSchedules = mapSchedules.filter(schedule => {
            return schedule.end <= now;
        });

        if (endedSchedules.length > 0) {

            endedSchedules.sort((a, b) => {

                if (a.end !== b.end) {
                    return b.end - a.end;
                }

                return b.start - a.start;

            });

            return {
                schedule: endedSchedules[0],
                reason: 'Last'
            };

        }

        return null;

    }


    // ---------------------------------
    // UPDATE SELECTED SCHEDULE CARD
    // ---------------------------------

    function updateSelectedIndicator(scheduleId) {

        $('.schedule-map-option').each(function () {

            const $option = $(this);

            const optionId = Number(
                $option.attr('data-schedule-id')
            );

            const isSelected = optionId === scheduleId;

            const $icon = $option.find('.schedule-selection-icon');

            $option.toggleClass('is-selected', isSelected);

            $option.attr(
                'aria-pressed',
                isSelected ? 'true' : 'false'
            );

            if (isSelected) {

                $icon
                    .removeClass('fa-circle')
                    .addClass('fa-circle-dot');

            } else {

                $icon
                    .removeClass('fa-circle-dot')
                    .addClass('fa-circle');

            }

        });

    }


    // ---------------------------------
    // INITIALIZE OR UPDATE MAP
    // ---------------------------------

    function initializeMap(schedule) {

        if (typeof L === 'undefined') {

            console.error(
                'Leaflet is unavailable. Check the Leaflet script URL.'
            );

            return false;

        }

        if (!hasValidCoordinates(schedule)) {
            return false;
        }

        const coordinates = [
            schedule.latitude,
            schedule.longitude
        ];

        $map.removeClass('hidden');


        // Initialize Leaflet only once.
        if (!map) {

            map = L.map('eventMap').setView(coordinates, 16);

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }
            ).addTo(map);

            marker = L.marker(coordinates)
                .addTo(map)
                .bindPopup(schedule.venue_name || 'Event venue');

        } else {

            // Reuse the existing map and marker.
            marker.setLatLng(coordinates);

            marker.setPopupContent(
                schedule.venue_name || 'Event venue'
            );

            map.setView(coordinates, 16);

        }


        // Recalculate map dimensions after displaying it.
        setTimeout(function () {

            if (map) {
                map.invalidateSize();
            }

        }, 100);

        return true;

    }


    // ---------------------------------
    // UPDATE OPENSTREETMAP LINK
    // ---------------------------------

    function updateOpenMapLink(schedule) {

        if (!hasValidCoordinates(schedule)) {

            $openMapLink
                .attr('href', '#')
                .addClass('hidden');

            return;

        }

        const latitude = schedule.latitude;
        const longitude = schedule.longitude;

        const openStreetMapURL =
            'https://www.openstreetmap.org/?mlat=' +
            encodeURIComponent(latitude) +
            '&mlon=' +
            encodeURIComponent(longitude) +
            '#map=18/' +
            encodeURIComponent(latitude) +
            '/' +
            encodeURIComponent(longitude);

        $openMapLink
            .attr('href', openStreetMapURL)
            .removeClass('hidden');

    }


    // ---------------------------------
    // DISPLAY SELECTED SCHEDULE
    // ---------------------------------

    function selectSchedule(schedule) {

        if (!schedule) {
            return;
        }

        selectedScheduleId = schedule.id;

        updateSelectedIndicator(schedule.id);


        // Update the shared map.
        if (hasValidCoordinates(schedule)) {

            $mapUnavailable.addClass('hidden');

            updateOpenMapLink(schedule);

            initializeMap(schedule);

        } else {

            // Prevent the previous schedule's map from being displayed.
            $map.addClass('hidden');

            $mapUnavailable.removeClass('hidden');

            updateOpenMapLink(schedule);

        }

    }


    // ---------------------------------
    // AUTOMATIC SCHEDULE TRACKING
    // ---------------------------------

    function updateAutomaticSelection() {

        const automaticResult = getAutomaticSchedule();

        const automaticSchedule = automaticResult
            ? automaticResult.schedule
            : null;

        const newAutomaticId = automaticSchedule
            ? automaticSchedule.id
            : null;

        const automaticScheduleChanged =
            newAutomaticId !== lastAutomaticScheduleId;


        if (automaticScheduleChanged) {

            lastAutomaticScheduleId = newAutomaticId;

            // Automatic selection takes control again
            // when the preferred schedule changes.
            manualSelection = false;

        }


        if (!automaticSchedule) {
            return;
        }


        // Keep manual selection until the next automatic transition.
        if (manualSelection && !automaticScheduleChanged) {
            return;
        }


        // Avoid repeatedly updating the same schedule.
        if (
            selectedScheduleId === automaticSchedule.id &&
            !automaticScheduleChanged
        ) {
            return;
        }


        selectSchedule(automaticSchedule);

    }


    // ---------------------------------
    // MANUAL SCHEDULE SELECTION
    // ---------------------------------

    $(document).on('click', '.schedule-map-option', function () {

        const scheduleId = Number(
            $(this).attr('data-schedule-id')
        );

        const selectedSchedule = mapSchedules.find(schedule => {
            return schedule.id === scheduleId;
        });

        if (!selectedSchedule) {
            return;
        }

        manualSelection = true;

        selectSchedule(selectedSchedule);

    });


    // ---------------------------------
    // INITIAL AUTOMATIC SELECTION
    // ---------------------------------

    updateAutomaticSelection();


    // Recheck every second for schedule transitions.
    setInterval(updateAutomaticSelection, 1000);

});
