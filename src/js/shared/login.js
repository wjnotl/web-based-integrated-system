import { show_form_error } from "../utils.js"

$(".container form").on("submit", async function (e) {
	e.preventDefault()

	$(".container form button[type='submit']").text("Sending...")
	$(".container form button[type='submit']").prop("disabled", true)

	const response = await $.ajax({
		url: "/backend/shared/do.login.php",
		type: "POST",
		data: $(this).serialize()
	})

	show_form_error(response.errors)

	if (response.success) {
		if (response.verify) {
			window.location.href = `/session-verification?id=${response.session_id}`
        } else {
            window.location.reload()
        }
	}

	$(".container form button[type='submit']").text("Login")
	$(".container form button[type='submit']").prop("disabled", false)
})
