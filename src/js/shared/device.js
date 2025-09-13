import { confirmation } from "../utils.js"

$(document).ready(function () { 
    $(".device-remove").on("click", async function () {
        if (await confirmation("Are you sure you want to remove this device?", "Yes")) {
            const response = await $.ajax({
                url: "/backend/shared/do.device-logout.php",
                type: "POST",
                data: "session_id=" + $(this).data("session-id")
            })

            if (response.login) {
                window.location.href = "/login"
                return
            }

            if (response.success) {
                $(this).parent().remove()
            }
        }
    })

    $("#logout-all-devices").on("click", async function () {
        if (await confirmation("Are you sure you want to logout all known devices?", "Yes")) {
            const response = await $.ajax({
                url: "/backend/shared/do.device-logout.php",
                type: "POST",
                data: "all=true"
            })

            if (response.login) {
                window.location.href = "/login"
                return
            }

            if (response.success) {
                $("#other-devices .device-group").remove()
                window.location.href = "/"
            }
        }
    })
})