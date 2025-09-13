import { show_form_error } from "../utils.js"

$(document).ready(function () {
	$(".container form").on("submit", async function (e) {
		e.preventDefault()

		$(".container form button[type='submit']").text("Sending...")
		$(".container form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/shared/do.reset-password.php",
			type: "POST",
			data: $(this).serialize() + `&token=${$(this).data("token")}`
		})

		if (response.errors.token) {
			window.location.href = "/forgot-password"
			return
		}

		show_form_error(response.errors)

		if (response.success) {
			window.location.href = "/login"
			return
		}

		$(".container form button[type='submit']").text("Reset Password")
		$(".container form button[type='submit']").prop("disabled", false)
	})
})
