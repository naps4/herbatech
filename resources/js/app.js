// resources/js/app.js
require("./bootstrap");
require("admin-lte");

$(document).ready(function () {
    // =============================
    // TOOLTIP & POPOVER
    // =============================
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    // =============================
    // SIDEBAR SEARCH
    // =============================
    $('[data-widget="sidebar-search"]').SidebarSearch({
        arrowSign: '<i class="fas fa-chevron-right"></i>',
    });

    // =============================
    // ALERT AUTO-DISMISS
    // =============================
    setTimeout(function () {
        $(".alert").alert("close");
    }, 5000);

    // =============================
    // NOTIFICATION CHECK
    // =============================
    function checkNotifications() {
        $.get("/api/notifications/unread-count", function (data) {
            const count = data.count || 0;
            $("#notification-count").text(count);

            if (count > 0) {
                $("#notification-count")
                    .removeClass("badge-warning")
                    .addClass("badge-danger");
            } else {
                $("#notification-count")
                    .removeClass("badge-danger")
                    .addClass("badge-warning");
            }
        });
    }
    checkNotifications(); // initial check
    setInterval(checkNotifications, 60000); // tiap menit

    // =============================
    // CONFIRM BEFORE DELETE
    // =============================
    $("form[data-confirm]").submit(function (e) {
        if (!confirm($(this).data("confirm"))) {
            e.preventDefault();
        }
    });

    // =============================
    // AUTO-SUBMIT ON SELECT CHANGE
    // =============================
    $(".auto-submit").change(function () {
        $(this).closest("form").submit();
    });

    // =============================
    // DATE FORMATTING
    // =============================
    $(".format-date").each(function () {
        const date = new Date($(this).text());
        $(this).text(
            date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            })
        );
    });

    // =============================
    // AUTO-REFRESH PAGE
    // =============================
    const refreshInterval = $('meta[name="refresh-interval"]').attr("content");
    if (refreshInterval) {
        setInterval(function () {
            location.reload();
        }, parseInt(refreshInterval) * 1000);
    }

    // =============================
    // SIDEBAR TOGGLE
    // =============================
    $("#sidebar-toggle").on("click", function (e) {
        e.preventDefault();
        $("body").toggleClass("sidebar-hidden");

        // Simpan preferensi user di localStorage
        const isHidden = $("body").hasClass("sidebar-hidden");
        localStorage.setItem("sidebar-pref", isHidden ? "hidden" : "visible");
    });

    // Cek preferensi saat halaman dimuat
    if (localStorage.getItem("sidebar-pref") === "hidden") {
        $("body").addClass("sidebar-hidden");
    }
});
