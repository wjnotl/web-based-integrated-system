import { show_form_error } from "../utils.js"

$(document).ready(function () {
	$(".container form").on("submit", async function (e) {
		e.preventDefault()

		$(".container form button[type='submit']").text("Sending...")
		$(".container form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/customer/do.signup.php",
			type: "POST",
			data: $(this).serialize()
		})

		show_form_error(response.errors)

		if (response.success) {
			window.location.href = `/session-verification?id=${response.session_id}`
		}

		$(".container form button[type='submit']").text("Sign Up")
		$(".container form button[type='submit']").prop("disabled", false)
	})
})
