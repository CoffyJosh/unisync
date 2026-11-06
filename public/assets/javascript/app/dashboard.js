$(document).ready(function () {

    const getDashboardURL = '../php/app/dashboard/get_dashboard.php';
    const cancelURL = '../php/app/dashboard/cancel_registration.php';

    let activeTab = 'events';
    let eventPage = 1;
    let organizationPage = 1;

    let eventSearchTimer;
    let organizationSearchTimer;

    function showMessage(message, isError = false) {
        $('#dashboardMessage')
            .removeClass('hidden text-red-600 text-green-600')
            .addClass(isError ? 'text-red-600' : 'text-green-600')
            .text(message);

        setTimeout(function () {
            $('#dashboardMessage').addClass('hidden');
        }, 3000);
    }

    function renderPagination(container, currentPage, totalPages, type) {

        if (totalPages <= 1) {
            $(container).empty();
            return;
        }

        let html = '<div class="flex justify-center items-center flex-wrap gap-2">';

        function pageButton(page, label = page) {
            return `
                <button
                    class="dashboard-page px-3 py-2 rounded-lg border text-sm
                    ${page === currentPage
                        ? 'bg-[#1E3A5F] text-white border-[#1E3A5F]'
                        : 'hover:bg-slate-100'}"
                    data-type="${type}"
                    data-page="${page}">
                    ${label}
                </button>
            `;
        }

        html += `
            <button
                class="dashboard-page px-3 py-2 rounded-lg border text-sm disabled:opacity-40"
                data-type="${type}"
                data-page="${currentPage - 1}"
                ${currentPage <= 1 ? 'disabled' : ''}>
                Previous
            </button>
        `;

        const start = Math.max(1, currentPage - 2);
        const end = Math.min(totalPages, currentPage + 2);

        if (start > 1) {
            html += pageButton(1);
            if (start > 2) html += '<span>...</span>';
        }

        for (let i = start; i <= end; i++) {
            html += pageButton(i);
        }

        if (end < totalPages) {
            if (end < totalPages - 1) html += '<span>...</span>';
            html += pageButton(totalPages);
        }

        html += `
            <button
                class="dashboard-page px-3 py-2 rounded-lg border text-sm disabled:opacity-40"
                data-type="${type}"
                data-page="${currentPage + 1}"
                ${currentPage >= totalPages ? 'disabled' : ''}>
                Next
            </button>
        `;

        html += '</div>';

        $(container).html(html);
    }

    function loadDashboard(type, page = 1) {

        const isEvents = type === 'events';

        const search = isEvents
            ? $('#eventOrganizationSearch').val()
            : $('#organizationSearch').val();

        const resultsContainer = isEvents
            ? '#eventResults'
            : '#organizationResults';

        const paginationContainer = isEvents
            ? '#eventPagination'
            : '#organizationPagination';

        $(resultsContainer).html(
            '<p class="text-center text-slate-500 py-10">Loading...</p>'
        );

        $.ajax({
            url: getDashboardURL,
            type: 'GET',
            dataType: 'json',
            data: {
                type: type,
                page: page,
                search: search
            },

            success: function (response) {

                if (!response.success) {
                    $(resultsContainer).html(
                        '<p class="text-center text-red-600 py-10">' +
                        response.message +
                        '</p>'
                    );
                    return;
                }

                $(resultsContainer).html(response.html);

                renderPagination(
                    paginationContainer,
                    response.page,
                    response.totalPages,
                    type
                );

                if (isEvents) {
                    eventPage = page;
                    $('#registeredCount').text(response.total);
                } else {
                    organizationPage = page;
                    $('#organizationCount').text(response.total);
                }
            },

            error: function (xhr) {

                const message = xhr.responseJSON?.message ||
                    'Something went wrong while loading the dashboard.';

                $(resultsContainer).html(
                    '<p class="text-center text-red-600 py-10">' +
                    message +
                    '</p>'
                );
            }
        });
    }

    // Switch tabs.
    $('.dashboard-tab').on('click', function () {

        activeTab = $(this).data('tab');

        $('.dashboard-tab')
            .removeClass('text-[#1E3A5F] border-[#1E3A5F]')
            .addClass('text-slate-500 border-transparent');

        $(this)
            .removeClass('text-slate-500 border-transparent')
            .addClass('text-[#1E3A5F] border-[#1E3A5F]');

        $('#eventsTab').toggleClass('hidden', activeTab !== 'events');
        $('#organizationsTab').toggleClass('hidden', activeTab !== 'organizations');

        if (activeTab === 'events') {
            loadDashboard('events', eventPage);
        } else {
            loadDashboard('organizations', organizationPage);
        }
    });

    // Search registered events by organization name.
    $('#eventOrganizationSearch').on('input', function () {

        clearTimeout(eventSearchTimer);

        eventSearchTimer = setTimeout(function () {
            loadDashboard('events', 1);
        }, 350);
    });

    // Search joined organizations.
    $('#organizationSearch').on('input', function () {

        clearTimeout(organizationSearchTimer);

        organizationSearchTimer = setTimeout(function () {
            loadDashboard('organizations', 1);
        }, 350);
    });

    // Pagination.
    $(document).on('click', '.dashboard-page', function () {

        const type = $(this).data('type');
        const page = Number($(this).data('page'));

        if (page < 1) {
            return;
        }

        loadDashboard(type, page);
    });

    // Cancel registration.
    $(document).on('click', '.cancel-registration', function () {

        const button = $(this);
        const eventId = button.data('event-id');

        if (!confirm('Are you sure you want to cancel this registration?')) {
            return;
        }

        button.prop('disabled', true).text('Cancelling...');

        $.ajax({
            url: cancelURL,
            type: 'POST',
            dataType: 'json',
            data: {
                event_id: eventId
            },

            success: function (response) {

                if (response.success) {
                    showMessage(response.message);
                    loadDashboard('events', eventPage);
                } else {
                    showMessage(response.message, true);
                    button.prop('disabled', false).text('Cancel');
                }
            },

            error: function () {
                showMessage('Unable to cancel registration.', true);
                button.prop('disabled', false).text('Cancel');
            }
        });
    });

    // Initial dashboard load.
    loadDashboard('events');
    loadDashboard('organizations');

});