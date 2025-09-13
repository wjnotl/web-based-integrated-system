$(document).ready(function () {
	$(".container form").on("submit", async function (e) {
		e.preventDefault()

		$(".container form button[type='submit']").text("Sending...")
		$(".container form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/shared/do.confirm-delete-account.php",
			type: "POST",
			data: `token=${$(this).data("token")}`
		})

		if (response.errors.token) {
			window.location.href = "/account"
			return
		}

		if (response.success) {
			window.location.href = "/"
			return
		}

		$(".container form button[type='submit']").text("Confirm Delete Account")
		$(".container form button[type='submit']").prop("disabled", false)
	})
})