import { detect_form_changes, show_form_error, showToast, confirmation } from "../utils.js"

$(document).ready(function () {
	const formChanges = detect_form_changes(
		"form",
		"input",
		function () {
			$("form button[type='submit']").prop("disabled", false)
		},
		function () {
			$("form button[type='submit']").prop("disabled", true)
		}
	)

	$("form").on("submit", async function (e) {
		e.preventDefault()
		$("form button[type='submit']").text("Sending...")
		$("form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/admin/do.edit-admin.php",
			type: "POST",
			data: $(this).serialize() + "&id=" + $("form").data("id")
		})

		if (response.login) {
			window.location.reload()
			return
		}

		if (response.errors) {
			show_form_error(response.errors)
			if (response.errors?.id) {
				window.location.href = "/manage-admin"
				return
			}
		}

		$("form button[type='submit']").text("Save Changes")
		$("form button[type='submit']").prop("disabled", false)

		if (response.success) {
			formChanges()
			showToast("Saved changes")
		}
	})

	$("#reset-password-button").on("click", async function () {
		$("#reset-password-button").prop("disabled", true)
		$("#reset-password-button").text("Sending...")

		const response = await $.ajax({
			url: "/backend/admin/do.admin-reset-password.php",
			type: "POST",
			data: `id=${$("form").data("id")}`
		})

		if (response.login) {
			window.location.reload()
			return
		}

		if (response.errors?.id) {
			window.location.href = "/manage-admin"
			return
		}

		if (response.success) {
			showToast("Password reset link sent")
		}

		$("#reset-password-button").text("Reset Password")
		$("#reset-password-button").prop("disabled", false)
    })
    
    $("#logout-from-all-devices-button").on("click", async function () {
		if (await confirmation("Are you sure you want to log out from all devices for this admin?", "Yes")) {
			$("#logout-from-all-devices-button").prop("disabled", true)
			$("#logout-from-all-devices-button").text("Sending...")

			const response = await $.ajax({
				url: "/backend/admin/do.admin-device-logout.php",
				type: "POST",
				data: `id=${$("form").data("id")}`
			})

			if (response.login) {
				window.location.reload()
				return
			}

			if (response.errors?.id) {
				window.location.href = "/manage-admin"
				return
			}

			$("#logout-from-all-devices-button").text("Log Out from All Devices")
			$("#logout-from-all-devices-button").prop("disabled", false)

			if (response.success) {
				showToast("Logged out from all devices")
			}
		}
	})
})
