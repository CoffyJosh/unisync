$(document).ready(function () {

    let searchTimer;
    let currentRequest = null;

    // Search comes from the navbar only.
    const $navSearch = $('form[action="browse.php"] input[name="search"]');

    const TAB_ACTIVE = 'bg-[#1E3A5F] text-white border border-[#1E3A5F]';
    const TAB_INACTIVE = 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100';

    // Fallback if the navbar field is missing or not pre-filled.
    const initialSearch = new URLSearchParams(window.location.search).get('search') || '';

    if ($navSearch.length && $navSearch.val() === '' && initialSearch) {
        $navSearch.val(initialSearch);
    }

    function getSearch() {
        return $navSearch.length ? $navSearch.val().trim() : initialSearch;
    }

    function getFilters(page = 1) {
        return {
            search: getSearch(),
            view: $('#view').val(),
            organization_id: $('#organization_id').val(),
            status: $('#status').val(),
            sort: $('#sort').val(),
            university: $('#university').val(),
            org_sort: $('#org_sort').val(),
            page: page
        };
    }

    function setActiveTab(view) {
        $('.view-tab').each(function () {
            const isActive = $(this).data('view') === view;

            $(this)
                .removeClass(TAB_ACTIVE + ' ' + TAB_INACTIVE)
                .addClass(isActive ? TAB_ACTIVE : TAB_INACTIVE);
        });
    }

    function showError(message) {
        $('#eventResults').html(
            '<div class="bg-white border border-red-200 rounded-xl p-10 text-center">' +
            '<h3 class="text-lg font-semibold text-red-600">Could not load results</h3>' +
            '<p class="text-sm text-slate-500 mt-2"></p>' +
            '</div>'
        ).find('p').text(message);
    }

    function updateResults(page = 1) {

        // Cancel any in-flight request so stale responses can't overwrite newer ones.
        if (currentRequest) {
            currentRequest.abort();
        }

        $('#loadingIndicator').removeClass('hidden');

        const filters = getFilters(page);
        const request = $.ajax({
            url: 'browse.php',
            type: 'GET',
            data: { ...filters, ajax: 1 },
            dataType: 'json',
            timeout: 15000
        });

        currentRequest = request;

        request
            .done(function (response) {
                try {
                    $('#eventResults').html(response.html);
                    $('#orgChip').html(response.chip);
                    $('#eventCount').text(response.label);

                    const params = new URLSearchParams();

                    Object.keys(filters).forEach(function (key) {
                        const value = filters[key];

                        if (value !== '' && value !== null && value !== undefined) {
                            params.set(key, value);
                        }
                    });

                    history.replaceState(null, '', 'browse.php?' + params.toString());
                } catch (err) {
                    console.error('Failed to render results:', err);
                    showError('Render error: ' + err.message);
                }
            })
            .fail(function (xhr, status) {
                if (status === 'abort') {
                    return;
                }

                console.error('Failed to update results:', status, xhr.status, xhr.responseText);

                showError(
                    status === 'timeout'
                        ? 'The request timed out.'
                        : 'Server returned ' + xhr.status + ' (' + status + '). Check the Network tab response.'
                );
            })
            .always(function (a, status) {
                // Only hide if this is still the latest request.
                if (currentRequest === request) {
                    $('#loadingIndicator').addClass('hidden');
                }
            });
    }

    // Tabs
    $('.view-tab').on('click', function () {
        const view = $(this).data('view');

        if (view === $('#view').val()) {
            return;
        }

        $('#view').val(view);

        // Every tab switch starts from the defaults.
        $('#status').val('all');
        $('#sort').val('nearest');
        $('#organization_id').val('');
        $('#university').val('');
        $('#org_sort').val('name_asc');

        // Show only the filters that belong to this tab.
        $('#eventFilters').toggleClass('hidden', view !== 'events');
        $('#orgFilters').toggleClass('hidden', view !== 'organizations');

        setActiveTab(view);
        updateResults(1);
    });

    // Navbar search: update while typing and on submit
    $navSearch.on('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            updateResults(1);
        }, 300);
    });

    $navSearch.closest('form').on('submit', function (e) {
        e.preventDefault();
        clearTimeout(searchTimer);
        updateResults(1);
    });

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
    });

    // Event filters and organization filters
    $('#status, #sort, #university, #org_sort').on('change', function () {
        updateResults(1);
    });

    // "View N Events" on an organization card
    $(document).on('click', '.org-filter-link', function (e) {
        e.preventDefault();

        $('#organization_id').val($(this).data('org-id'));
        $('#view').val('events');

        $('#eventFilters').removeClass('hidden');
        $('#orgFilters').addClass('hidden');

        setActiveTab('events');
        updateResults(1);
    });

    // Clear the organization chip
    $(document).on('click', '#clearOrgFilter', function () {
        $('#organization_id').val('');
        updateResults(1);
    });

    // AJAX pagination
    $(document).on('click', '.pagination-link', function (e) {
        e.preventDefault();

        const url = new URL($(this).attr('href'), window.location.href);
        const page = url.searchParams.get('page') || 1;

        updateResults(page);
    });

});