$(document).ready(function () {

    const BROWSE_URL = 'browse.php';

    // If the server says this event (or its organization) is gone,
    // tell the user and leave the page.
    function handleEventGone(response) {

        if (response && response.event_gone) {
            alert(response.message || 'This event no longer exists.');
            window.location.href = res.redirect || 'dashboard.php';
            return true;
        }

        return false;

    }


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

                if (handleEventGone(response)) {
                    return;
                }

                if (response.success) {

                    // Keep the Attendees tab (Admins/Managers) in sync.
                    if (typeof window.syncOwnAttendeeState === 'function') {
                        window.syncOwnAttendeeState(action);
                    }

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

                if (handleEventGone(xhr.responseJSON)) {
                    return;
                }

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

    function initCountdown() {

        const $eventTiming = $('#eventTiming');

        if (!$eventTiming.length) {
            return;
        }

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

    function initScheduleMap() {

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

    }


    // =====================================
    // ATTENDEE MANAGEMENT (ADMIN / MANAGER)
    // =====================================

    function initAttendees() {

        const $attendeeData = $('#attendeeData');

        // Only rendered for Admins and Managers of the event's organization.
        if (!$attendeeData.length) {
            return;
        }


        let payload = {};

        try {
            payload = JSON.parse($attendeeData.text() || '{}');
        } catch (error) {
            console.error('Unable to read attendee data:', error);
            return;
        }

        let attendees = Array.isArray(payload.attendees)
            ? payload.attendees
            : [];

        const currentAccountId = Number(payload.currentAccountId);

        let activeFilter = 'all';

        const $list = $('#attendeeList');
        const $empty = $('#attendeeEmpty');
        const $emptyText = $('#attendeeEmptyText');
        const $message = $('#attendeeMessage');


        // ---------------------------------
        // HELPERS
        // ---------------------------------

        function getRegisteredCount() {
            return attendees.filter(a => a.is_registered).length;
        }


        function updateCounts(count) {

            const registeredCount = Number.isFinite(count)
                ? count
                : getRegisteredCount();

            $('#attendeeRegisteredCount').text(registeredCount);
            $('#attendeeTabCount').text('(' + registeredCount + ')');

        }


        function showMessage(type, text) {

            $message
                .removeClass('hidden bg-green-50 text-green-700 bg-red-50 text-red-700')
                .addClass(type === 'success'
                    ? 'bg-green-50 text-green-700'
                    : 'bg-red-50 text-red-700')
                .text(text);

        }


        function clearMessage() {

            $message
                .addClass('hidden')
                .removeClass('bg-green-50 text-green-700 bg-red-50 text-red-700')
                .empty();

        }


        // ---------------------------------
        // RENDER LIST
        // ---------------------------------

        function renderAttendees() {

            const visible = attendees.filter(a => {
                return activeFilter === 'members'
                    ? a.is_member
                    : a.is_registered;
            });

            $list.empty();

            if (!visible.length) {

                $emptyText.text(
                    activeFilter === 'members'
                        ? 'This organization has no active members.'
                        : 'No one has registered for this event yet.'
                );

                $empty.removeClass('hidden');
                return;
            }

            $empty.addClass('hidden');

            visible.forEach(function (attendee) {

                const $row = $('<div>', {
                    class: 'flex items-center gap-4 py-4',
                    'data-account-id': attendee.account_id
                });

                const initial = String(attendee.account_name || '?')
                    .trim()
                    .charAt(0)
                    .toUpperCase();

                $row.append(
                    $('<div>', {
                        class: 'w-11 h-11 shrink-0 rounded-full bg-blue-50 text-[#1E3A5F] flex items-center justify-center font-bold',
                        text: initial
                    })
                );

                const $info = $('<div>', { class: 'min-w-0 flex-1' });

                const $nameLine = $('<div>', {
                    class: 'flex flex-wrap items-center gap-2'
                });

                $nameLine.append(
                    $('<p>', {
                        class: 'font-semibold text-slate-900 truncate',
                        text: attendee.account_name
                    })
                );

                if (attendee.is_registered) {
                    $nameLine.append(
                        $('<span>', {
                            class: 'px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold',
                            text: 'Attendee'
                        })
                    );
                }

                $info.append($nameLine);

                $info.append(
                    $('<p>', {
                        class: 'text-sm text-slate-500 truncate',
                        text: attendee.email
                    })
                );

                $row.append($info);

                if (attendee.is_registered) {

                    $row.append(
                        $('<button>', {
                            type: 'button',
                            class: 'attendee-remove shrink-0 px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold transition-colors',
                            'data-account-id': attendee.account_id,
                            text: 'Remove'
                        })
                    );
                }

                $list.append($row);
            });

        }


        // ---------------------------------
        // TABS (DETAILS / ATTENDEES)
        // ---------------------------------

        $(document).on('click', '.event-tab', function () {

            const tab = $(this).attr('data-tab');

            $('.event-tab')
                .removeClass('border-[#1E3A5F] text-[#1E3A5F] font-bold')
                .addClass('border-transparent text-slate-500 font-semibold')
                .attr('aria-selected', 'false');

            $(this)
                .removeClass('border-transparent text-slate-500 font-semibold')
                .addClass('border-[#1E3A5F] text-[#1E3A5F] font-bold')
                .attr('aria-selected', 'true');

            if (tab === 'attendees') {

                $('#eventTabDetails').addClass('hidden');
                $('#eventTabAttendees').removeClass('hidden');

            } else {

                $('#eventTabAttendees').addClass('hidden');
                $('#eventTabDetails').removeClass('hidden');

                // Leaflet resizes itself on window resize,
                // which fixes the map after it was hidden.
                $(window).trigger('resize');
            }

        });


        // ---------------------------------
        // FILTERS (ALL / MEMBERS)
        // ---------------------------------

        $(document).on('click', '.attendee-filter', function () {

            activeFilter = $(this).attr('data-filter') === 'members'
                ? 'members'
                : 'all';

            $('.attendee-filter')
                .removeClass('bg-[#1E3A5F] text-white')
                .addClass('bg-white text-slate-600 hover:bg-slate-50')
                .attr('aria-pressed', 'false');

            $(this)
                .removeClass('bg-white text-slate-600 hover:bg-slate-50')
                .addClass('bg-[#1E3A5F] text-white')
                .attr('aria-pressed', 'true');

            clearMessage();
            renderAttendees();

        });


        // ---------------------------------
        // REMOVE ATTENDEE
        // ---------------------------------

        $(document).on('click', '.attendee-remove', function () {

            const $button = $(this);
            const targetAccountId = Number($button.attr('data-account-id'));

            const attendee = attendees.find(a => a.account_id === targetAccountId);

            if (!attendee) {
                return;
            }

            if (!confirm('Remove ' + attendee.account_name + ' from this event?')) {
                return;
            }

            clearMessage();

            $button.prop('disabled', true).text('Removing...');

            $.ajax({

                url: window.location.pathname + window.location.search,
                type: 'POST',

                data: {
                    ajax: 1,
                    action: 'remove_attendee',
                    target_account_id: targetAccountId
                },

                dataType: 'json',

                success: function (response) {

                    if (handleEventGone(response)) {
                        return;
                    }

                    if (response.success) {

                        attendee.is_registered = false;

                        updateCounts(Number(response.registered_count));
                        renderAttendees();
                        showMessage('success', response.message);

                        // If the current account removed itself, mirror
                        // the sidebar's "cancel registration" state.
                        if (targetAccountId === currentAccountId) {

                            $('#registrationStatus')
                                .addClass('hidden')
                                .empty();

                            if (!$('#registerButton').length) {

                                $('#registrationStatus').after(
                                    $('<button>', {
                                        id: 'registerButton',
                                        type: 'button',
                                        class: 'w-full bg-[#1E3A5F] text-white font-semibold py-3 rounded-lg hover:bg-[#294D78] transition-colors',
                                        text: 'Register for Event'
                                    })
                                );
                            }
                        }

                    } else {

                        showMessage('error', response.message);

                        $button.prop('disabled', false).text('Remove');
                    }

                },

                error: function (xhr) {

                    if (handleEventGone(xhr.responseJSON)) {
                        return;
                    }

                    showMessage(
                        'error',
                        xhr.responseJSON?.message ||
                        'Unable to remove this attendee. Please try again.'
                    );

                    $button.prop('disabled', false).text('Remove');
                }

            });

        });


        // ---------------------------------
        // SIDEBAR REGISTRATION SYNC
        // ---------------------------------

        window.syncOwnAttendeeState = function (action) {

            const own = attendees.find(a => a.account_id === currentAccountId);

            if (!own) {
                return;
            }

            own.is_registered = (action === 'register');

            updateCounts();
            renderAttendees();

        };


        // ---------------------------------
        // INITIAL RENDER
        // ---------------------------------

        updateCounts();
        renderAttendees();

    }

    $('#deleteEventButton').on('click', function () {
        if (!confirm('Delete this event? This cannot be undone.')) return;

        const $btn = $(this).prop('disabled', true);

        $.post(window.location.pathname + window.location.search, {
            ajax: 1,
            action: 'delete_event'
        }, null, 'json')
        .done(function (res) {
            if (res.success) {
                window.location.href = res.redirect || 'dashboard.php';
            } else {
                alert(res.message);
                $btn.prop('disabled', false);
            }
        })
        .fail(function (xhr) {
            const res = xhr.responseJSON || {};
            // Event already gone → just leave the page
            if (res.event_gone) {
                window.location.href = 'browse.php';
                return;
            }
            alert(res.message || 'Something went wrong. Please try again.');
            $btn.prop('disabled', false);
        });
    });


    // =====================================
    // START
    // =====================================

    initCountdown();
    initScheduleMap();
    initAttendees();

});