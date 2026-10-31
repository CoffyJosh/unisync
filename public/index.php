
<?php
require_once '../php/db.php';

// Retrieve up to six upcoming confirmed events.
$sql = "
    SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.status,
        o.organization_name,
        v.venue_name,
        v.location AS venue_location,
        es.event_date,
        es.start_time,
        es.end_time
    FROM event e
    INNER JOIN organization o
        ON e.organization_id = o.organization_id
    INNER JOIN event_schedule es
        ON e.event_id = es.event_id
    INNER JOIN venue v
        ON es.venue_id = v.venue_id
    WHERE e.status = 'confirmed'
      AND es.event_date >= CURRENT_DATE
    ORDER BY es.event_date ASC, es.start_time ASC
    LIMIT 6
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format event date and time.
function formatEventDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatEventTime($start, $end) {
    return date('g:i A', strtotime($start))
        . ' - ' .
        date('g:i A', strtotime($end));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UniSync | Campus Events Made Simple</title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

    <?php include '../php/components/navbar.php'; ?>

    <main class="flex-1">

        <!-- Hero Section -->
        <section class="bg-white">
            <div class="max-w-7xl mx-auto px-6 py-20 md:py-28">

                <div class="max-w-3xl">

                    <span class="inline-block bg-blue-50 text-blue-900 text-sm font-semibold px-4 py-2 rounded-full mb-6">
                        Your Campus, Connected
                    </span>

                    <h1 class="text-4xl md:text-6xl font-bold text-slate-900 leading-tight mb-6">
                        Discover. Connect.
                        <span class="text-blue-900">Participate.</span>
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-2xl mb-8">
                        Stay connected with your campus community.
                        Discover university events, explore student organizations,
                        and find opportunities to get involved.
                    </p>

                    <div class="flex flex-wrap gap-4">

                        <a href="browse.php"
                           class="inline-flex items-center justify-center bg-blue-900 hover:bg-blue-800 text-white font-semibold px-7 py-3 rounded-lg transition">
                            Explore Events
                            <span class="ml-2">&rarr;</span>
                        </a>

                        <a href="#about"
                           class="inline-flex items-center justify-center border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold px-7 py-3 rounded-lg transition">
                            Learn More
                        </a>

                    </div>

                </div>

            </div>
        </section>


        <!-- Featured Events -->
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">

                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">

                    <div>
                        <span class="text-sm font-semibold uppercase tracking-wider text-amber-600">
                            What's happening
                        </span>

                        <h2 class="text-3xl font-bold text-slate-900 mt-2">
                            Featured Campus Events
                        </h2>

                        <p class="text-slate-600 mt-2">
                            Discover activities and opportunities happening around campus.
                        </p>
                    </div>

                    <a href="browse.php"
                       class="text-blue-900 hover:text-blue-700 font-semibold">
                        View All Events &rarr;
                    </a>

                </div>


                <?php if (count($events) > 0): ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <?php foreach ($events as $event): ?>

                            <article class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">

                                <div class="h-2 bg-blue-900"></div>

                                <div class="p-6 flex flex-col flex-1">

                                    <span class="inline-block text-xs font-semibold uppercase tracking-wide text-blue-900 bg-blue-50 px-3 py-1 rounded-full mb-4">
                                        Confirmed
                                    </span>

                                    <h3 class="text-xl font-bold text-slate-900 mb-2">
                                        <?= htmlspecialchars($event['event_name']) ?>
                                    </h3>

                                    <p class="text-sm text-slate-500 mb-4">
                                        Organized by
                                        <?= htmlspecialchars($event['organization_name']) ?>
                                    </p>

                                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                        <?= htmlspecialchars($event['description']) ?>
                                    </p>

                                    <div class="space-y-3 text-sm text-slate-600">

                                        <div class="flex items-start gap-3">
                                            <span class="text-blue-900">&#128197;</span>
                                            <span>
                                                <?= htmlspecialchars(formatEventDate($event['event_date'])) ?>
                                            </span>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <span class="text-blue-900">&#128337;</span>
                                            <span>
                                                <?= htmlspecialchars(formatEventTime(
                                                    $event['start_time'],
                                                    $event['end_time']
                                                )) ?>
                                            </span>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <span class="text-blue-900">&#128205;</span>
                                            <span>
                                                <?= htmlspecialchars($event['venue_name']) ?>,
                                                <?= htmlspecialchars($event['venue_location']) ?>
                                            </span>
                                        </div>

                                    </div>

                                    <div class="border-t border-slate-100 mt-auto pt-4">

                                        <a href="event_details.php?id=<?= (int) $event['event_id'] ?>"
                                           class="text-blue-900 hover:text-blue-700 font-semibold text-sm">
                                            View Event Details &rarr;
                                        </a>

                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="bg-white border border-slate-200 rounded-xl p-10 text-center">

                        <h3 class="text-xl font-semibold text-slate-800 mb-2">
                            No Upcoming Events
                        </h3>

                        <p class="text-slate-500">
                            There are currently no confirmed upcoming events.
                            Check back later for new campus activities.
                        </p>

                    </div>

                <?php endif; ?>

            </div>
        </section>


        <!-- About Section -->
        <section id="about" class="bg-white py-20 px-6">

            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">

                <div>
                    <span class="text-sm font-semibold uppercase tracking-wider text-amber-600">
                        About UniSync
                    </span>

                    <h2 class="text-3xl font-bold text-slate-900 mt-2 mb-5">
                        Bringing the Campus Community Together
                    </h2>

                    <p class="text-slate-600 leading-relaxed mb-4">
                        UniSync is a university event and organization scheduling
                        platform designed to help students stay informed and
                        connected with campus activities.
                    </p>

                    <p class="text-slate-600 leading-relaxed">
                        Through one platform, students can discover upcoming
                        events, explore student organizations, and register
                        for activities that match their interests.
                    </p>
                </div>

                <div class="bg-blue-50 rounded-2xl p-8 md:p-10">

                    <h3 class="text-2xl font-bold text-blue-950 mb-6">
                        Everything in One Place
                    </h3>

                    <div class="space-y-6">

                        <div class="flex gap-4">
                            <div class="bg-white rounded-lg p-3 text-blue-900">
                                <span class="text-xl">&#128269;</span>
                            </div>

                            <div>
                                <h4 class="font-semibold text-slate-900">
                                    Discover Events
                                </h4>
                                <p class="text-sm text-slate-600 mt-1">
                                    Find campus activities and upcoming events.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-white rounded-lg p-3 text-blue-900">
                                <span class="text-xl">&#128101;</span>
                            </div>

                            <div>
                                <h4 class="font-semibold text-slate-900">
                                    Connect with Organizations
                                </h4>
                                <p class="text-sm text-slate-600 mt-1">
                                    Explore the organizations behind campus events.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-white rounded-lg p-3 text-blue-900">
                                <span class="text-xl">&#128221;</span>
                            </div>

                            <div>
                                <h4 class="font-semibold text-slate-900">
                                    Register for Activities
                                </h4>
                                <p class="text-sm text-slate-600 mt-1">
                                    Keep track of the events you have registered for.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- Call to Action -->
        <section class="bg-blue-900 py-16 px-6">

            <div class="max-w-4xl mx-auto text-center">

                <h2 class="text-3xl font-bold text-white mb-4">
                    Be Part of Your Campus Community
                </h2>

                <p class="text-blue-100 mb-8">
                    Explore upcoming events and find your next campus experience.
                </p>

                <a href="browse.php"
                   class="inline-flex items-center justify-center bg-white hover:bg-slate-100 text-blue-900 font-semibold px-7 py-3 rounded-lg transition">
                    Browse Events
                    <span class="ml-2">&rarr;</span>
                </a>

            </div>

        </section>

    </main>

    <?php include '../php/components/footer.php'; ?>

</body>
</html>
