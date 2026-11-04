$(document).ready(function () {

    $('.org-tab').on('click', function () {

        const selectedTab = $(this).data('tab');

        // Hide all tab panels.
        $('.org-panel').addClass('hidden');

        // Show selected panel.
        $('#tab-' + selectedTab).removeClass('hidden');

        // Reset tab styles.
        $('.org-tab')
            .removeClass('border-[#1E3A5F] text-[#1E3A5F]')
            .addClass('border-transparent text-slate-500');

        // Highlight active tab.
        $(this)
            .removeClass('border-transparent text-slate-500')
            .addClass('border-[#1E3A5F] text-[#1E3A5F]');

    });

    const $modal = $('#editOrganizationModal');

    $('#openEditOrganization').on('click', function () {
        $modal.removeClass('hidden').addClass('flex');
    });

    $('#closeEditOrganization, #cancelEditOrganization').on('click', function () {
        $modal.addClass('hidden').removeClass('flex');
    });

    // Close when clicking the dark background
    $modal.on('click', function (e) {
        if (e.target === this) {
            $modal.addClass('hidden').removeClass('flex');
        }
    });

    // Close with Escape
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $modal.addClass('hidden').removeClass('flex');
        }
    });

});