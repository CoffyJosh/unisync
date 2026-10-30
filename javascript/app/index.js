 console.log("UniSync index.js loaded");
 
 $(document).ready(function () {
    console.log("Search form:", $("#eventSearchForm").length);
    console.log("Search input:", $("#eventSearch").length);
    console.log("Event cards:", $(".event-item").length);

    $("#eventSearchForm").on("submit", function (event) {
        event.preventDefault();

        const searchTerm = $.trim(
            $("#eventSearch").val()
        ).toLowerCase();

        let visibleEvents = 0;

        $(".event-item").each(function () {
            const eventName = String(
                $(this).data("event-name") || ""
            ).toLowerCase();

            const organization = String(
                $(this).data("organization") || ""
            ).toLowerCase();

            const description = String(
                $(this).data("description") || ""
            ).toLowerCase();

            const matches =
                eventName.includes(searchTerm) ||
                organization.includes(searchTerm) ||
                description.includes(searchTerm);

            $(this).toggle(matches);

            if (matches) {
                visibleEvents++;
            }
        });

        $("#noSearchResults").toggle(
            visibleEvents === 0
        );

        if ($("#events").length) {
            $("html, body").animate({
                scrollTop: $("#events").offset().top - 80
            }, 300);
        }
    });

    $("#eventSearch").on("input", function () {
        if ($.trim($(this).val()) === "") {
            $(".event-item").show();
            $("#noSearchResults").hide();
        }
    });
});