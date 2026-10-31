
$(document).ready(function () {

    let searchTimer;

    function getFilters(page = 1) {
        return {
            search: $('form[action="browse.php"] input[name="search"]').val() || '',
            organization: $('#organization').val(),
            status: $('#status').val(),
            sort: $('#sort').val(),
            page: page
        };
    }

    function updateEvents(page = 1) {

        $('#loadingIndicator').removeClass('hidden');

        $.ajax({
            url: 'browse.php',
            type: 'GET',
            data: {
                ...getFilters(page),
                ajax: 1
            },
            dataType: 'json',

            success: function (response) {
                $('#eventResults').html(response.html);
                $('#eventCount').text(
                    Number(response.count).toLocaleString() + ' events found'
                );

                const params = new URLSearchParams(getFilters(page));
                history.replaceState(null, '', 'browse.php?' + params.toString());
            },

            error: function (xhr) {
                console.error('Failed to update events:', xhr.responseText);
            },

            complete: function () {
                $('#loadingIndicator').addClass('hidden');
            }
        });
    }

    // Organization search with debounce
    $('#organization').on('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            updateEvents(1);
        }, 300);
    });

    // Status and sorting filters
    $('#status, #sort').on('change', function () {
        updateEvents(1);
    });

    // Navbar event search
    $('form[action="browse.php"]').on('submit', function (e) {
        e.preventDefault();
        updateEvents(1);
    });

    // Update while typing in navbar search
    $('form[action="browse.php"] input[name="search"]').on('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            updateEvents(1);
        }, 300);
    });

    // AJAX pagination
    $(document).on('click', '.pagination-link', function (e) {
        e.preventDefault();

        const url = new URL($(this).attr('href'), window.location.origin);
        const page = url.searchParams.get('page') || 1;

        updateEvents(page);
    });

});