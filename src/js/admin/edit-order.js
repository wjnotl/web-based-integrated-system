import { detect_form_changes, show_form_error, showToast } from "../utils.js"

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
			url: "/backend/admin/do.edit-order.php",
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
				window.location.href = "/manage-order"
				return
			}
		}

		$("form button[type='submit']").text("Save Changes")
		$("form button[type='submit']").prop("disabled", false)

		if (response.success) {
			formChanges()

			if ($(this).find("input[name='status']:checked").val() === "Canceled") {
				$(this).find(".button-group:has(button[type='submit'])").remove()
				$(this).find(".form-group:has(input[name='status'])").after(`
					<div>Canceled</div>	
				`)
				$(this).find(".form-group:has(input[name='status'])").remove()
			}

			showToast("Saved changes")
		}
	})
})
