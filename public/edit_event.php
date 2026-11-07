<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId || $eventId < 1) {
    http_response_code(400);
    exit('Invalid event.');
}

require_once __DIR__ . '/../config/db.php';

$pdo = $pdo ?? $conn ?? $db ?? null;
if (!$pdo instanceof PDO) {
    http_response_code(500);
    exit('Database connection is unavailable.');
}

$accountId = $_SESSION['account_id'] ?? null;
if (!$accountId) {
    header('Location: account_login.php');
    exit;
}

// Load the event and its organization.
$eventQuery = $pdo->prepare("
    SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.event_image,
        e.status,
        e.organization_id,
        o.organization_name
    FROM EVENT e
    INNER JOIN ORGANIZATION o
        ON e.organization_id = o.organization_id
    WHERE e.event_id = :event_id
      AND e.status <> 'deleted'
      AND o.status = 'active'
    LIMIT 1
");
$eventQuery->execute([':event_id' => $eventId]);
$event = $eventQuery->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

// Only Admins and Managers of the event's organization may edit it.
$permission = $pdo->prepare("
    SELECT 1
    FROM MEMBERSHIP
    WHERE account_id = :account_id
      AND organization_id = :organization_id
      AND status = 'active'
      AND role IN ('Admin', 'Manager')
    LIMIT 1
");
$permission->execute([
    ':account_id' => $accountId,
    ':organization_id' => $event['organization_id']
]);

if (!$permission->fetchColumn()) {
    http_response_code(403);
    exit('You are not authorized to edit this event.');
}

// Load the current schedules with their venues.
$scheduleQuery = $pdo->prepare("
    SELECT
        es.schedule_id,
        es.event_date,
        es.start_time,
        es.end_time,
        v.venue_name,
        v.location,
        v.capacity,
        v.latitude,
        v.longitude
    FROM EVENT_SCHEDULE es
    INNER JOIN VENUE v
        ON es.venue_id = v.venue_id
    WHERE es.event_id = :event_id
      AND es.status IN ('pending', 'confirmed', 'completed')
    ORDER BY es.event_date ASC, es.start_time ASC
");
$scheduleQuery->execute([':event_id' => $eventId]);

$initialSchedules = array_map(
    static function ($row) {
        return [
            'schedule_id' => (int) $row['schedule_id'],
            'venue_name' => $row['venue_name'],
            'location' => $row['location'],
            'event_date' => $row['event_date'],
            'start_time' => substr($row['start_time'], 0, 5),
            'end_time' => substr($row['end_time'], 0, 5),
            'capacity' => $row['capacity'] !== null ? (int) $row['capacity'] : '',
            'latitude' => $row['latitude'] !== null ? (float) $row['latitude'] : null,
            'longitude' => $row['longitude'] !== null ? (float) $row['longitude'] : null
        ];
    },
    $scheduleQuery->fetchAll(PDO::FETCH_ASSOC)
);

$eventPageUrl = 'event.php?id=' . (int) $eventId;
$existingImage = $event['event_image'] ?? '';

function editEscape($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event | UniSync</title>
    <script src="./assets/javascript/tailwind.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #scheduleMap { height: 340px; width: 100%; z-index: 1; }
        .schedule-item.is-active { border-color: #1e3a5f; background: #eff6ff; }
        .schedule-item { transition: .15s ease; }
        .schedule-item:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
        .form-control { width:100%; border:1px solid #cbd5e1; border-radius:.75rem; padding:.75rem .9rem; color:#1e293b; background:white; outline:none; }
        .form-control:focus { border-color:#1e3a5f; box-shadow:0 0 0 3px #dbeafe; }
        .field-label { display:block; font-size:.875rem; font-weight:600; color:#334155; margin-bottom:.45rem; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex flex-col">
<?php require __DIR__ . '/../php/components/navbar.php'; ?>

<main class="w-full max-w-7xl mx-auto px-6 lg:px-8 pt-8 pb-16 flex-1"
      id="editEventPage"
      data-event-id="<?= (int) $eventId ?>">
    <div class="mb-7">
        <a href="<?= editEscape($eventPageUrl) ?>"
           class="text-sm font-semibold text-slate-500 hover:text-[#1E3A5F]">
            <i class="fa-solid fa-arrow-left mr-2"></i>Back to event
        </a>
        <h1 class="text-3xl font-bold text-[#1E3A5F] mt-3">Edit Event</h1>
        <p class="text-slate-500 mt-1">Editing for <?= editEscape($event['organization_name']) ?></p>
    </div>

    <form id="eventForm" enctype="multipart/form-data" novalidate autocomplete="off">
        <input type="hidden" name="event_id" value="<?= (int) $eventId ?>">

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 mb-7">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E3A5F] flex items-center justify-center">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Event Information</h2>
                    <p class="text-sm text-slate-500">Update the basic details of your event.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="lg:col-span-2">
                    <label class="field-label" for="eventName">Event Name <span class="text-red-500">*</span></label>
                    <input class="form-control" id="eventName" name="event_name" maxlength="200" required
                           value="<?= editEscape($event['event_name']) ?>"
                           placeholder="Enter event name">
                </div>
                <div class="lg:col-span-2">
                    <label class="field-label" for="eventDescription">Description</label>
                    <textarea class="form-control" id="eventDescription" name="description" rows="5"
                              placeholder="Tell students what this event is about."><?= editEscape($event['description']) ?></textarea>
                </div>
                <div class="lg:col-span-2">
                    <label class="field-label" for="eventImage">Event Image</label>
                    <input class="form-control" type="file" id="eventImage" name="event_image"
                           accept="image/png,image/jpeg,image/webp">
                    <p class="text-xs text-slate-400 mt-2">PNG, JPG, or WEBP. Maximum 5 MB. Choose a file to replace the current image.</p>
                    <div id="imagePreviewWrap" class="<?= $existingImage !== '' ? '' : 'hidden' ?> mt-4">
                        <img id="imagePreview"
                             src="<?= editEscape($existingImage) ?>"
                             class="max-h-64 rounded-xl border border-slate-200 object-cover" alt="Event image preview">
                        <button type="button" id="removeImage"
                                class="mt-3 text-sm font-semibold text-red-600 hover:underline">
                            Remove image
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section id="eventSchedulesSection" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Event Schedules</h2>
                    <p class="text-sm text-slate-500">Changes are only applied when you save the event.</p>
                </div>
                <span id="scheduleCount" class="text-xs font-semibold rounded-full bg-slate-100 text-slate-600 px-3 py-2">0 schedules</span>
            </div>

            <div id="scheduleNotice" class="hidden mb-5 rounded-xl p-3 text-sm" role="status"></div>

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(260px,0.8fr)_minmax(0,2fr)] gap-6">
                <div class="border border-slate-200 rounded-xl p-4">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <h3 class="font-bold text-slate-700">Schedule List</h3>
                        <button type="button" id="addSchedule"
                                class="text-sm font-semibold text-[#1E3A5F] hover:text-blue-700">
                            <i class="fa-solid fa-plus mr-1"></i>Add Schedule
                        </button>
                    </div>
                    <div id="scheduleList" class="space-y-3">
                        <div id="emptySchedules" class="text-center py-10 text-slate-400">
                            <i class="fa-regular fa-calendar-xmark text-3xl mb-3"></i>
                            <p class="text-sm">No schedules added yet.</p>
                            <p class="text-xs mt-1">You can save without schedules.</p>
                        </div>
                    </div>
                </div>

                <!-- Shown while no schedule form is open -->
                <div id="scheduleFormPlaceholder"
                     class="border border-dashed border-slate-300 rounded-xl p-5 min-w-0 flex flex-col items-center justify-center text-center text-slate-400 min-h-[240px]">
                    <i class="fa-regular fa-calendar-plus text-3xl mb-3"></i>
                    <p class="text-sm">Click <span class="font-semibold text-slate-600">Add Schedule</span> or select a schedule to edit it.</p>
                </div>

                <!-- Schedule form (hidden until Add Schedule / a schedule is clicked) -->
                <div id="scheduleFormPanel" class="hidden border border-slate-200 rounded-xl p-5 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
                        <div>
                            <h3 id="scheduleFormTitle" class="font-bold text-slate-800 text-lg">Add a Schedule</h3>
                            <p class="text-sm text-slate-500">Set the venue, date, and event duration.</p>
                        </div>
                        <button type="button" id="clearScheduleForm" class="hidden text-sm text-slate-500 hover:text-slate-800">
                            Clear form
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="field-label" for="venueName">Venue Name <span class="text-red-500">*</span></label>
                            <input class="form-control" id="venueName" maxlength="150" placeholder="e.g. University Auditorium" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label" for="venueLocation">Venue Address / Location <span class="text-red-500">*</span></label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input class="form-control" id="venueLocation" placeholder="Search or enter an address" required>
                                <button type="button" id="searchLocation" class="shrink-0 rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                                    <i class="fa-solid fa-magnifying-glass mr-1"></i>Find
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Search for a place or click the map to pin the venue.</p>
                        </div>
                        <div>
                            <label class="field-label" for="eventDate">Date <span class="text-red-500">*</span></label>
                            <input class="form-control" type="date" id="eventDate" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label" for="startTime">Start Time <span class="text-red-500">*</span></label>
                                <input class="form-control" type="time" id="startTime" required>
                            </div>
                            <div>
                                <label class="field-label" for="endTime">End Time <span class="text-red-500">*</span></label>
                                <input class="form-control" type="time" id="endTime" required>
                            </div>
                        </div>
                        <div>
                            <label class="field-label" for="venueCapacity">Venue Capacity</label>
                            <input class="form-control" type="number" id="venueCapacity" min="1" step="1" placeholder="Optional">
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <label class="field-label mb-0">Pin Venue Location <span class="text-red-500">*</span></label>
                                <span id="coordinatesText" class="text-xs text-slate-400">No pin selected</span>
                            </div>
                            <div id="scheduleMap" class="rounded-xl border border-slate-200 overflow-hidden"></div>
                            <p class="text-xs text-slate-400 mt-2">Click the map or drag the marker to set the exact coordinates.</p>
                        </div>
                    </div>

                    <div id="scheduleFormError" class="hidden mt-4 rounded-xl bg-red-50 text-red-700 p-3 text-sm" role="alert"></div>

                    <div class="flex flex-wrap justify-end gap-3 mt-6">
                        <button type="button" id="cancelScheduleForm"
                                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="button" id="removeCurrentSchedule"
                                class="hidden rounded-xl border border-red-200 px-5 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">
                            Remove Schedule
                        </button>
                        <button type="button" id="saveSchedule"
                                class="rounded-xl bg-[#1E3A5F] px-6 py-3 text-sm font-semibold text-white hover:bg-[#294D78]">
                            <i class="fa-solid fa-check mr-2"></i>Save Schedule
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-7">
            <p class="text-sm text-slate-500">The event status updates automatically from its schedules.</p>
            <div class="flex gap-3">
                <a href="<?= editEscape($eventPageUrl) ?>"
                   class="rounded-xl border border-slate-300 px-6 py-3 font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
                <button type="submit" id="saveEvent" disabled
                        class="rounded-xl bg-[#1E3A5F] px-7 py-3 font-semibold text-white hover:bg-[#294D78] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-[#1E3A5F]">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Save Changes
                </button>
            </div>
        </div>
        <div id="formMessage" class="hidden mt-5 rounded-xl p-4 text-sm" role="alert"></div>
    </form>

    <!-- Current schedules for JavaScript -->
    <script type="application/json" id="initialSchedules"><?php
        echo json_encode(
            $initialSchedules,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        );
    ?></script>
</main>

<?php require __DIR__ . '/../php/components/footer.php'; ?>
<script src="./assets/javascript/jquery.js"></script>
<script src="./assets/javascript/leaflet.js"></script>
<script src="./assets/javascript/app/edit_event.js"></script>
</body>
</html>