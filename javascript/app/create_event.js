$(document).ready(function () {
    const $page = $("#createEventPage");
    if (!$page.length) return;

    const organizationId = Number($page.data("organization-id"));
    const $scheduleList = $("#scheduleList");
    const $emptySchedules = $("#emptySchedules");
    const $formMessage = $("#formMessage");

    // Schedules exist only in this browser-side array until Publish Event.
    let schedules = [];
    let editingId = null;
    let nextTemporaryId = 1;
    let map = null;
    let marker = null;
    let selectedCoordinates = null;

    function escapeHtml(value) {
        return String(value ?? "").replace(/[&<>"']/g, function (character) {
            return ({
                "&": "&amp;", "<": "&lt;", ">": "&gt;",
                '"': "&quot;", "'": "&#039;"
            })[character];
        });
    }

    function showMessage(message, type) {
        $formMessage
            .removeClass("hidden bg-red-50 text-red-700 bg-green-50 text-green-700 bg-blue-50 text-blue-700")
            .addClass(type === "success" ? "bg-green-50 text-green-700" :
                type === "info" ? "bg-blue-50 text-blue-700" : "bg-red-50 text-red-700")
            .text(message);
        $("html, body").animate({ scrollTop: $formMessage.offset().top - 120 }, 200);
    }

    function formatSchedule(schedule) {
        const date = new Date(schedule.event_date + "T00:00:00");
        const dateText = Number.isNaN(date.getTime())
            ? schedule.event_date
            : date.toLocaleDateString(undefined, { month: "short", day: "numeric", year: "numeric" });
        return dateText + " · " + schedule.start_time + " - " + schedule.end_time;
    }

    function renderSchedules() {
        $scheduleList.find(".schedule-item").remove();
        $emptySchedules.toggle(schedules.length === 0);
        $("#scheduleCount").text(schedules.length + (schedules.length === 1 ? " schedule" : " schedules"));

        schedules.forEach(function (schedule) {
            const active = schedule.temp_id === editingId;
            const $item = $(`
                <div class="schedule-item ${active ? "is-active" : ""} border border-slate-200 rounded-xl p-3"
                     data-id="${schedule.temp_id}">
                    <button type="button" class="select-schedule text-left w-full">
                        <span class="block font-semibold text-sm text-slate-800">${escapeHtml(schedule.venue_name)}</span>
                        <span class="block text-xs text-slate-500 mt-1">${escapeHtml(formatSchedule(schedule))}</span>
                        <span class="block text-xs text-slate-400 mt-1">${escapeHtml(schedule.location)}</span>
                    </button>
                    <div class="flex justify-end gap-3 mt-3 border-t border-slate-100 pt-2">
                        <button type="button" class="edit-schedule text-xs font-semibold text-[#1E3A5F]">Edit</button>
                        <button type="button" class="delete-schedule text-xs font-semibold text-red-600">Remove</button>
                    </div>
                </div>
            `);
            $scheduleList.append($item);
        });
    }

    function resetScheduleForm() {
        editingId = null;
        $("#scheduleFormTitle").text("Add a Schedule");
        $("#venueName, #venueLocation, #eventDate, #startTime, #endTime, #venueCapacity").val("");
        $("#removeCurrentSchedule, #clearScheduleForm").addClass("hidden");
        selectedCoordinates = null;
        updateCoordinateText();
        if (marker && map) map.removeLayer(marker);
        renderSchedules();
    }

    function updateCoordinateText() {
        $("#coordinatesText").text(selectedCoordinates
            ? selectedCoordinates.lat.toFixed(6) + ", " + selectedCoordinates.lng.toFixed(6)
            : "No pin selected");
    }

    function setMarker(lat, lng, zoom) {
        selectedCoordinates = { lat: Number(lat), lng: Number(lng) };
        if (!map) return;
        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on("dragend", function () {
                const position = marker.getLatLng();
                selectedCoordinates = { lat: position.lat, lng: position.lng };
                updateCoordinateText();
                reverseGeocode(position.lat, position.lng);
            });
        } else {
            marker.setLatLng([lat, lng]);
            if (!map.hasLayer(marker)) marker.addTo(map);
        }
        if (zoom !== false) map.setView([lat, lng], 16);
        updateCoordinateText();
    }

    function reverseGeocode(lat, lng) {
        $.getJSON("https://nominatim.openstreetmap.org/reverse", {
            format: "jsonv2", lat: lat, lon: lng
        }).done(function (result) {
            if (result && result.display_name && !$("#venueLocation").val().trim()) {
                $("#venueLocation").val(result.display_name);
            }
        });
    }

    function initializeMap() {
        if (typeof L === "undefined") {
            showMessage("The map library could not be loaded. Check your internet connection.", "error");
            return;
        }
        map = L.map("scheduleMap").setView([14.5995, 120.9842], 11);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        map.on("click", function (event) {
            setMarker(event.latlng.lat, event.latlng.lng, false);
            reverseGeocode(event.latlng.lat, event.latlng.lng);
        });
        setTimeout(function () { map.invalidateSize(); }, 150);
    }

    function loadSchedule(schedule) {
        editingId = schedule.temp_id;
        $("#scheduleFormTitle").text("Edit Schedule");
        $("#venueName").val(schedule.venue_name);
        $("#venueLocation").val(schedule.location);
        $("#eventDate").val(schedule.event_date);
        $("#startTime").val(schedule.start_time);
        $("#endTime").val(schedule.end_time);
        $("#venueCapacity").val(schedule.capacity ?? "");
        $("#removeCurrentSchedule, #clearScheduleForm").removeClass("hidden");
        setMarker(schedule.latitude, schedule.longitude);
        renderSchedules();
        setTimeout(function () { if (map) map.invalidateSize(); }, 100);
    }

    function validateSchedule() {
        const venueName = $("#venueName").val().trim();
        const location = $("#venueLocation").val().trim();
        const date = $("#eventDate").val();
        const start = $("#startTime").val();
        const end = $("#endTime").val();
        const capacity = $("#venueCapacity").val();

        if (!venueName || !location || !date || !start || !end) {
            showMessage("Complete the venue, location, date, start time, and end time.", "error");
            return null;
        }
        if (end <= start) {
            showMessage("The end time must be later than the start time.", "error");
            return null;
        }
        if (!selectedCoordinates) {
            showMessage("Pin the venue location on the map before saving this schedule.", "error");
            return null;
        }
        if (capacity !== "" && (!Number.isInteger(Number(capacity)) || Number(capacity) < 1)) {
            showMessage("Venue capacity must be a positive whole number.", "error");
            return null;
        }

        return {
            temp_id: editingId || nextTemporaryId++,
            venue_name: venueName,
            location: location,
            event_date: date,
            start_time: start,
            end_time: end,
            capacity: capacity,
            latitude: selectedCoordinates.lat,
            longitude: selectedCoordinates.lng
        };
    }

    $("#addSchedule").on("click", function () {
        resetScheduleForm();
        $("#venueName").trigger("focus");
    });

    $("#clearScheduleForm").on("click", resetScheduleForm);

    $("#saveSchedule").on("click", function () {
        const schedule = validateSchedule();
        if (!schedule) return;

        const index = schedules.findIndex(item => item.temp_id === schedule.temp_id);
        if (index >= 0) schedules[index] = schedule;
        else schedules.push(schedule);

        editingId = schedule.temp_id;
        renderSchedules();
        $("#scheduleFormTitle").text("Schedule Saved Temporarily");
        $("#removeCurrentSchedule, #clearScheduleForm").removeClass("hidden");
        showMessage("Schedule saved to the temporary list. It has not been added to the database.", "success");
    });

    $scheduleList.on("click", ".select-schedule, .edit-schedule", function () {
        const id = Number($(this).closest(".schedule-item").data("id"));
        const schedule = schedules.find(item => item.temp_id === id);
        if (schedule) loadSchedule(schedule);
    });

    $scheduleList.on("click", ".delete-schedule", function () {
        const id = Number($(this).closest(".schedule-item").data("id"));
        schedules = schedules.filter(item => item.temp_id !== id);
        if (editingId === id) resetScheduleForm();
        else renderSchedules();
        showMessage("Schedule removed from the temporary list. No database record was changed.", "info");
    });

    $("#removeCurrentSchedule").on("click", function () {
        if (editingId === null) return;
        schedules = schedules.filter(item => item.temp_id !== editingId);
        resetScheduleForm();
        showMessage("Schedule removed from the temporary list.", "info");
    });

    $("#searchLocation").on("click", function () {
        const query = $("#venueLocation").val().trim();
        if (!query) {
            showMessage("Enter a location to search for.", "error");
            return;
        }
        $.getJSON("https://nominatim.openstreetmap.org/search", {
            q: query, format: "jsonv2", limit: 1
        }).done(function (results) {
            if (!results || !results.length) {
                showMessage("No matching location was found. You can pin it manually on the map.", "error");
                return;
            }
            setMarker(Number(results[0].lat), Number(results[0].lon));
            $("#venueLocation").val(results[0].display_name);
        }).fail(function () {
            showMessage("Location search failed. You can still pin the venue manually.", "error");
        });
    });

    $("#eventImage").on("change", function () {
        const file = this.files[0];
        if (!file) {
            $("#imagePreviewWrap").addClass("hidden");
            return;
        }
        if (!["image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024) {
            this.value = "";
            $("#imagePreviewWrap").addClass("hidden");
            showMessage("Choose a JPG, PNG, or WEBP image no larger than 5 MB.", "error");
            return;
        }
        const reader = new FileReader();
        reader.onload = event => {
            $("#imagePreview").attr("src", event.target.result);
            $("#imagePreviewWrap").removeClass("hidden");
        };
        reader.readAsDataURL(file);
    });

    $("#eventForm").on("submit", function (event) {
        event.preventDefault();
        const eventName = $("#eventName").val().trim();
        if (!eventName) {
            showMessage("Enter an event name before publishing.", "error");
            $("#eventName").trigger("focus");
            return;
        }

        const formData = new FormData(this);
        formData.set("organization_id", organizationId);
        formData.set("schedules", JSON.stringify(schedules.map(function (schedule) {
            return {
                venue_name: schedule.venue_name,
                location: schedule.location,
                event_date: schedule.event_date,
                start_time: schedule.start_time,
                end_time: schedule.end_time,
                capacity: schedule.capacity,
                latitude: schedule.latitude,
                longitude: schedule.longitude
            };
        })));

        const $button = $("#publishEvent");
        $button.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i>Publishing...');

        $.ajax({
            url: "../php/app/event/create_event.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json"
        }).done(function (response) {
            if (!response.success) {
                showMessage(response.message || "Unable to publish the event.", "error");
                return;
            }
            showMessage(response.message, "success");
            window.location.href = "event.php?event_id=" + encodeURIComponent(response.event_id);
        }).fail(function (xhr) {
            const response = xhr.responseJSON;
            showMessage(response?.message || "A server error occurred while publishing.", "error");
        }).always(function () {
            $button.prop("disabled", false).html('<i class="fa-solid fa-paper-plane mr-2"></i>Publish Event');
        });
    });

    initializeMap();
});
