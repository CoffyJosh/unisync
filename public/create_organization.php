
<?php

require_once __DIR__ . '/../php/app/organization/save_organization.php';

function e($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Organization | UniSync</title>

    <script src="../javascript/tailwind.js"></script>
</head>

<body class="bg-slate-50 min-h-screen">

    <?php require_once __DIR__ . '/../php/components/navbar.php'; ?>

    <main class="max-w-3xl mx-auto px-6 py-12">

        <div class="mb-8">
            <a
                href="organizations.php"
                class="text-sm text-slate-500 hover:text-blue-900"
            >
                &larr; Back to Organizations
            </a>

            <h1 class="text-3xl font-extrabold text-slate-900 mt-4">
                Create an Organization
            </h1>

            <p class="text-slate-500 mt-2">
                Register your organization and start bringing students together.
            </p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form
            action="create_organization.php"
            method="POST"
            class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 space-y-6"
        >

            <div>
                <label
                    for="organization_name"
                    class="block text-sm font-semibold text-slate-700 mb-2"
                >
                    Organization Name
                </label>

                <input
                    type="text"
                    id="organization_name"
                    name="organization_name"
                    maxlength="150"
                    required
                    value="<?= e($organizationName) ?>"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-100"
                    placeholder="e.g. Computer Society"
                >
            </div>

            <div>
                <label
                    for="university_name"
                    class="block text-sm font-semibold text-slate-700 mb-2"
                >
                    University Name
                </label>

                <input
                    type="text"
                    id="university_name"
                    name="university_name"
                    maxlength="150"
                    required
                    value="<?= e($universityName) ?>"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-100"
                    placeholder="e.g. Polytechnic University of the Philippines"
                >
            </div>

            <div>
                <label
                    for="description"
                    class="block text-sm font-semibold text-slate-700 mb-2"
                >
                    Description <span class="font-normal text-slate-400">(Optional)</span>
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-100 resize-y"
                    placeholder="Tell students what your organization is about..."
                ><?= e($description) ?></textarea>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                <a
                    href="organizations.php"
                    class="text-center rounded-xl border border-slate-300 px-6 py-3 font-semibold text-slate-600 hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-[#1E3A5F] px-6 py-3 font-semibold text-white hover:bg-blue-950"
                >
                    Create Organization
                </button>
            </div>

        </form>

    </main>

</body>
</html>
