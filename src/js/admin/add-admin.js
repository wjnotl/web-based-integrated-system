import { show_form_error } from "../utils.js"

$(document).ready(function () {
	$(".container form").on("submit", async function (e) {
		e.preventDefault()

		$(".container form button[type='submit']").text("Sending...")
		$(".container form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/admin/do.add-admin.php",
			type: "POST",
			data: $(this).serialize()
		})

		show_form_error(response.errors)

		if (response.success) {
			window.location.href = `/manage-admin`
		}

		$(".container form button[type='submit']").text("Add Admin")
		$(".container form button[type='submit']").prop("disabled", false)
	})
})
