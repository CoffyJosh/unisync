<?php
session_start();

require_once __DIR__ . '/../php/app/event/get_event.php';

function eventEscape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$eventStatus = strtolower((string) ($event['status'] ?? ''));
$eventName = $event['event_name'] ?? 'Event';
$eventDescription = $event['description'] ?? '';
$organizationName = $event['organization_name'] ?? 'Organization';

$organizationSlug = strtolower(trim(
    preg_replace('/[^a-z0-9]+/i', '-', $organizationName),
    '-'
));

$organizationPageUrl = 'organization.php?id='
    . urlencode((string) ($event['organization_id'] ?? ''))
    . '&name='
    . urlencode($organizationSlug);

$statusClasses = [
    'pending' => 'bg-yellow-400/10 text-yellow-200 border border-yellow-300/20',
    'confirmed' => 'bg-green-400/10 text-green-200 border border-green-300/20',
    'completed' => 'bg-blue-400/10 text-blue-200 border border-blue-300/20',
    'deleted' => 'bg-red-400/10 text-red-200 border border-red-300/20',
    'cancelled' => 'bg-red-400/10 text-red-200 border border-red-300/20'
];

$statusClass = $statusClasses[$eventStatus]
    ?? 'bg-white/10 text-white border border-white/20';

$publicSchedules = array_values(array_filter(
    $schedules,
    static function ($schedule) {
        return in_array(
            strtolower((string) ($schedule['schedule_status'] ?? '')),
            ['pending', 'confirmed', 'completed'],
            true
        );
    }
));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo eventEscape($eventName); ?> | UniSync</title>

    <script src="./assets/javascript/jquery.js"></script>
    <script src="./assets/javascript/tailwind.js"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        #eventMap {
            width: 100%;
            height: 320px;
            z-index: 1;
            border-radius: 0.75rem;
        }

        .schedule-map-option {
            background: #fff;
            border: 1px solid #e2e8f0;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .schedule-map-option:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .schedule-map-option.is-selected {
            background: #f8fafc;
            border-color: #1e3a5f;
        }

        .schedule-map-option:focus-visible {
            outline: 2px solid #64748b;
            outline-offset: 2px;
        }

        .schedule-selection-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            color: #cbd5e1;
            transition: color 0.15s ease;
        }

        .schedule-map-option.is-selected .schedule-selection-icon {
            color: #1e3a5f;
        }

        .event-banner {
            background: linear-gradient(135deg, #1e3a5f, #294d78);
        }

        @media (max-width: 640px) {
            #eventMap {
                height: 260px;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <?php include __DIR__ . '/../php/components/navbar.php'; ?>

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-40">

            <a href="javascript:history.back()"
               class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 mb-6">
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>

            <!-- Event banner -->
            <section class="event-banner relative rounded-2xl overflow-hidden mb-8">
                <div class="grid grid-cols-1 md:grid-cols-5">

                    <div class="md:col-span-3 p-6 sm:p-8 lg:p-10 flex flex-col justify-center">
                        <p class="text-sm text-blue-100 mb-3">
                            <?php echo eventEscape($event['university_name'] ?? ''); ?>
                        </p>

                        <h1 class="text-3xl sm:text-4xl font-bold text-white leading-tight">
                            <?php echo eventEscape($eventName); ?>
                        </h1>

                        <div class="flex flex-wrap items-center gap-3 mt-5">
                            <a href="<?php echo eventEscape($organizationPageUrl); ?>" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm text-white hover:bg-white/20 transition-colors">
                                <i class="fa-solid fa-building-columns"></i>
                                <?php echo eventEscape($organizationName); ?>
                            </a>

                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm <?php echo $statusClass; ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                <?php echo eventEscape(ucfirst($eventStatus)); ?>
                            </span>
                        </div>
                    </div>

                    <div class="md:col-span-2 relative min-h-[175px] md:min-h-[230px] overflow-hidden bg-slate-200">
                        <?php if (!empty($eventImage)): ?>
                            <img
                                src="<?php echo eventEscape($eventImage); ?>"
                                alt="<?php echo eventEscape($eventName); ?>"
                                class="absolute inset-0 w-full h-full object-cover"
                            >
                        <?php else: ?>
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-100">
                                <div class="text-center text-slate-400">
                                    <i class="fa-regular fa-calendar text-5xl mb-3"></i>
                                    <p class="text-sm">Event image unavailable</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

                <?php if ($canManageEvent): ?>

                    <!-- Edit Button -->
                    <a href="edit_event.php?id=<?php echo (int) $eventId; ?>"
                    class="absolute bottom-4 right-4 z-10 w-11 h-11 rounded-full bg-white text-[#1E3A5F] shadow-lg flex items-center justify-center hover:bg-blue-50 transition-colors"
                    title="Edit event"
                    aria-label="Edit event">
                        <i class="fa-solid fa-pencil"></i>
                    </a>

                    <!-- Delete Button -->
                    <button type="button"
                            id="deleteEventButton"
                            class="absolute bottom-4 right-[4.25rem] z-10 w-11 h-11 rounded-full bg-white text-red-600 shadow-lg flex items-center justify-center hover:bg-red-50 transition-colors"
                            title="Delete event"
                            aria-label="Delete event">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                <?php endif; ?>
            </section>

            <?php if ($canManageEvent): ?>

                <!-- Admin / Manager tabs -->
                <div class="flex items-center gap-2 border-b border-slate-200 mb-8 overflow-x-auto" role="tablist">

                    <button type="button"
                            class="event-tab shrink-0 px-5 py-4 text-sm font-bold border-b-2 border-[#1E3A5F] text-[#1E3A5F]"
                            data-tab="details"
                            role="tab"
                            aria-selected="true">
                        Details
                    </button>

                    <button type="button"
                            class="event-tab shrink-0 px-5 py-4 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-[#1E3A5F]"
                            data-tab="attendees"
                            role="tab"
                            aria-selected="false">
                        Attendees
                        <span id="attendeeTabCount" class="ml-1 text-xs text-slate-400">(<?php echo (int) $registeredAttendeeCount; ?>)</span>
                    </button>

                </div>

                <div id="eventTabDetails">

            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <!-- Main content -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- About -->
                    <section class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">
                            About This Event
                        </h2>

                        <?php if (trim((string) $eventDescription) !== ''): ?>
                            <div class="text-slate-600 leading-7 whitespace-pre-line"><?php
                                echo eventEscape($eventDescription);
                            ?></div>
                        <?php else: ?>
                            <p class="text-slate-500">
                                No event description has been provided.
                            </p>
                        <?php endif; ?>
                    </section>

                    <!-- Event schedules and shared map -->
                    <section class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8">

                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-slate-900">
                                Event Schedules
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Select a schedule to view its venue on the map.
                            </p>
                        </div>

                        <?php if (empty($publicSchedules)): ?>

                            <div class="rounded-xl border border-slate-200 p-6 text-center">
                                <i class="fa-regular fa-calendar-xmark text-3xl text-slate-300 mb-3"></i>

                                <p class="font-medium text-slate-700">
                                    No confirmed schedules yet
                                </p>

                                <p class="text-sm text-slate-500 mt-1">
                                    Schedule details will appear here when available.
                                </p>
                            </div>

                        <?php else: ?>

                            <!-- Clickable schedule cards -->
                            <div id="mapScheduleList" class="space-y-3">

                                <?php foreach ($publicSchedules as $index => $schedule): ?>

                                    <?php
                                    $scheduleDate = !empty($schedule['event_date'])
                                        ? date('F j, Y', strtotime($schedule['event_date']))
                                        : 'Date not specified';

                                    $startTime = !empty($schedule['start_time'])
                                        ? date('g:i A', strtotime($schedule['start_time']))
                                        : '';

                                    $endTime = !empty($schedule['end_time'])
                                        ? date('g:i A', strtotime($schedule['end_time']))
                                        : '';

                                    $scheduleStatus = strtolower(
                                        (string) ($schedule['schedule_status'] ?? '')
                                    );
                                    ?>

                                    <button
                                        type="button"
                                        class="schedule-map-option w-full text-left rounded-xl p-4"
                                        data-schedule-id="<?php echo (int) $schedule['schedule_id']; ?>"
                                        aria-pressed="false"
                                    >

                                        <div class="flex items-center gap-4">

                                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                                                <i class="fa-regular fa-calendar text-lg"></i>
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="font-semibold text-slate-900">
                                                        Schedule <?php echo $index + 1; ?>
                                                    </h3>

                                                    <span class="text-xs text-slate-400">
                                                        <?php echo eventEscape(ucfirst($scheduleStatus)); ?>
                                                    </span>
                                                </div>

                                                <p class="text-sm text-slate-600 mt-2">
                                                    <i class="fa-regular fa-calendar w-5 text-slate-400"></i>
                                                    <?php echo eventEscape($scheduleDate); ?>
                                                </p>

                                                <?php if ($startTime !== '' || $endTime !== ''): ?>
                                                    <p class="text-sm text-slate-600 mt-1">
                                                        <i class="fa-regular fa-clock w-5 text-slate-400"></i>

                                                        <?php
                                                        echo eventEscape($startTime);

                                                        if ($startTime !== '' && $endTime !== '') {
                                                            echo ' - ';
                                                        }

                                                        echo eventEscape($endTime);
                                                        ?>
                                                    </p>
                                                <?php endif; ?>

                                                <p class="text-sm text-slate-600 mt-1">
                                                    <i class="fa-solid fa-location-dot w-5 text-slate-400"></i>
                                                    <?php echo eventEscape($schedule['venue_name'] ?? 'Venue not specified'); ?>
                                                </p>

                                                <?php if (!empty($schedule['location'])): ?>
                                                    <p class="text-sm text-slate-500 mt-1 ml-6">
                                                        <?php echo eventEscape($schedule['location']); ?>
                                                    </p>
                                                <?php endif; ?>

                                            </div>

                                            <!-- Selection circle on the right -->
                                            <div class="flex-shrink-0 flex items-center justify-center">
                                                <i class="fa-regular fa-circle schedule-selection-icon"
                                                   aria-hidden="true"></i>
                                            </div>

                                        </div>

                                    </button>

                                <?php endforeach; ?>

                            </div>


                            <!-- Shared map -->
                            <div class="mt-8">

                                <div class="mb-4">
                                    <h3 class="text-lg font-bold text-slate-900">
                                        Venue Map
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Showing the location of the selected schedule.
                                    </p>
                                </div>

                                <div id="eventMapContainer">
                                    <div
                                        id="eventMap"
                                        aria-label="Map showing the selected event venue"
                                    ></div>
                                </div>

                                <p
                                    id="mapUnavailable"
                                    class="hidden text-sm text-slate-500 mt-3"
                                >
                                    Coordinates are not available for this venue.
                                </p>

                                <div class="mt-4">
                                    <a
                                        id="openMapLink"
                                        href="#"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="hidden inline-flex items-center gap-2 text-sm font-semibold text-[#1E3A5F] hover:underline"
                                    >
                                        Open location in OpenStreetMap
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>

                            </div>


                            <!-- Schedule data for JavaScript -->
                            <script type="application/json" id="mapScheduleData"><?php
                                echo json_encode(
                                    $mapSchedules,
                                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                                );
                            ?></script>

                        <?php endif; ?>

                    </section>

                </div>


                <!-- Sidebar -->
                <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">

                    <!-- Countdown -->
                    <section class="bg-white border border-slate-200 rounded-2xl p-6">

                        <h2 class="text-lg font-bold text-slate-900 mb-5">
                            Event Countdown
                        </h2>

                        <div
                            id="eventTiming"
                            data-status="<?php echo eventEscape($eventStatus); ?>"
                            data-schedules="<?php
                                echo htmlspecialchars(
                                    json_encode($timingSchedules),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
                        >

                            <div id="eventCountdown">

                                <p class="text-sm text-slate-500 mb-4">
                                    Time until the next schedule
                                </p>

                                <div class="grid grid-cols-4 gap-2 text-center">

                                    <?php foreach ([
                                        'countdownDays' => 'Days',
                                        'countdownHours' => 'Hours',
                                        'countdownMinutes' => 'Minutes',
                                        'countdownSeconds' => 'Seconds'
                                    ] as $countdownId => $countdownLabel): ?>

                                        <div class="rounded-lg bg-slate-50 border border-slate-200 p-2">
                                            <p
                                                id="<?php echo $countdownId; ?>"
                                                class="text-xl font-bold text-[#1E3A5F]"
                                            >00</p>

                                            <p class="text-xs text-slate-500">
                                                <?php echo $countdownLabel; ?>
                                            </p>
                                        </div>

                                    <?php endforeach; ?>

                                </div>
                            </div>

                            <div id="eventOngoing" class="hidden rounded-lg bg-green-50 text-green-700 p-4 text-sm">
                                <i class="fa-solid fa-circle-check mr-2"></i>
                                A schedule is currently ongoing.
                            </div>

                            <div id="eventEnded" class="hidden rounded-lg bg-slate-50 text-slate-600 p-4 text-sm">
                                <i class="fa-regular fa-calendar-check mr-2"></i>
                                All schedules have ended.
                            </div>

                            <div id="eventNoSchedule" class="hidden rounded-lg bg-slate-50 text-slate-600 p-4 text-sm">
                                <i class="fa-regular fa-calendar mr-2"></i>
                                Waiting for schedule confirmation.
                            </div>

                            <div id="eventCancelled" class="hidden rounded-lg bg-red-50 text-red-700 p-4 text-sm">
                                <i class="fa-solid fa-circle-xmark mr-2"></i>
                                This event has been cancelled.
                            </div>

                            <div id="eventCompleted" class="hidden rounded-lg bg-slate-50 text-slate-600 p-4 text-sm">
                                <i class="fa-solid fa-circle-check mr-2"></i>
                                This event has been completed.
                            </div>

                        </div>

                    </section>


                    <!-- Registration -->
                    <section class="bg-white border border-slate-200 rounded-2xl p-6">

                        <h2 class="text-lg font-bold text-slate-900 mb-2">
                            Event Registration
                        </h2>

                        <p class="text-sm text-slate-500 mb-5">
                            Register to reserve your place at this event.
                        </p>

                        <div
                            id="registrationMessage"
                            class="hidden rounded-lg p-3 text-sm mb-4"
                        ></div>

                        <?php if (empty($accountId)): ?>

                            <a
                                href="account_login.php"
                                class="block w-full text-center bg-[#1E3A5F] text-white font-semibold py-3 rounded-lg hover:bg-[#294D78] transition-colors"
                            >
                                Log In to Register
                            </a>

                        <?php elseif (!empty($registered)): ?>

                            <div id="registrationStatus">

                                <div class="bg-green-50 text-green-700 rounded-lg p-4 text-center">
                                    <i class="fa-solid fa-circle-check text-xl mb-2"></i>

                                    <p class="font-semibold">You're registered!</p>

                                    <p class="text-sm mt-1">
                                        You're already registered for this event.
                                    </p>
                                </div>

                                <p class="text-sm text-slate-500 text-center mt-3">
                                    Changed your mind?

                                    <a
                                        href="#"
                                        id="unregisterLink"
                                        class="font-semibold text-red-600 hover:underline"
                                    >
                                        Cancel registration
                                    </a>
                                </p>

                            </div>

                        <?php elseif (!empty($canRegister)): ?>

                            <div id="registrationStatus"></div>

                            <button
                                id="registerButton"
                                type="button"
                                class="w-full bg-[#1E3A5F] text-white font-semibold py-3 rounded-lg hover:bg-[#294D78] transition-colors"
                            >
                                Register for Event
                            </button>

                        <?php else: ?>

                            <div
                                id="registrationStatus"
                                class="rounded-lg bg-slate-50 border border-slate-200 p-4 text-center"
                            >
                                <i class="fa-solid fa-circle-info text-slate-400 text-xl mb-2"></i>

                                <p class="text-sm text-slate-600">
                                    Registration is currently unavailable.
                                </p>
                            </div>

                        <?php endif; ?>

                    </section>

                </aside>

            </div>

            <?php if ($canManageEvent): ?>

                </div><!-- /#eventTabDetails -->

                <!-- Attendees tab (Admin / Manager only) -->
                <div id="eventTabAttendees" class="min-h-[480px] mb-12 hidden">

                    <section class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8">

                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">

                            <div>
                                <h2 class="text-xl font-bold text-slate-900">
                                    Attendees
                                </h2>

                                <p class="text-sm text-slate-500 mt-1">
                                    <span id="attendeeRegisteredCount"><?php echo (int) $registeredAttendeeCount; ?></span>
                                    registered for this event.
                                </p>
                            </div>

                            <div class="inline-flex rounded-lg border border-slate-200 overflow-hidden self-start" role="group" aria-label="Attendee filter">

                                <button type="button"
                                        class="attendee-filter px-4 py-2 text-sm font-semibold bg-[#1E3A5F] text-white"
                                        data-filter="all"
                                        aria-pressed="true">
                                    All
                                </button>

                                <button type="button"
                                        class="attendee-filter px-4 py-2 text-sm font-semibold bg-white text-slate-600 hover:bg-slate-50 border-l border-slate-200"
                                        data-filter="members"
                                        aria-pressed="false">
                                    Members
                                </button>

                            </div>

                        </div>

                        <div id="attendeeMessage" class="hidden rounded-lg p-3 text-sm mb-4"></div>

                        <div id="attendeeList" class="divide-y divide-slate-100"></div>

                        <div id="attendeeEmpty" class="hidden py-10 text-center">
                            <i class="fa-regular fa-user text-3xl text-slate-300 mb-3"></i>
                            <p id="attendeeEmptyText" class="text-sm text-slate-500"></p>
                        </div>

                    </section>

                </div><!-- /#eventTabAttendees -->

                <!-- Attendee data for JavaScript -->
                <script type="application/json" id="attendeeData"><?php
                    echo json_encode(
                        [
                            'currentAccountId' => (int) $accountId,
                            'attendees' => $attendees
                        ],
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                    );
                ?></script>

            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . '/../php/components/footer.php'; ?>

    <script src="./assets/javascript/leaflet.js"></script>
    <script src="./assets/javascript/app/event.js"></script>

</body>
</html>