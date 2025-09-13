$(document).ready(function () {
	$("#otp-container input").on("input", function (ev) {
		const value = $(this)
			.val()
			.replace(/[^0-9]/g, "")
		if (value === "") {
			$(this).val("")
		} else if ($(this).next("input")[0]) {
			$(this).next("input").focus()
		}

		if (
			$("#otp-container input").filter(function () {
				return $(this).val() !== ""
			}).length === 6
		) {
			$("#otp-container input").off("keydown")
			$("#otp-container input").off("input")
			$("#otp-container input").prop("readonly", true)
			submitOTP()
		}
	})

	$("#otp-container input").on("keydown", function (ev) {
		if (ev.code === "Backspace" && $(this).val() === "" && $(this).prev("input")[0]) {
			$(this).prev("input").focus()
		}
	})

	$("#otp-container input").on("paste", function (ev) {
		ev.preventDefault()
		const paste = (ev.originalEvent || ev).clipboardData.getData("text")
		const digits = paste.replace(/[^0-9]/g, "").split("")
		$("#otp-container input").each(function (index) {
			if (digits[index]) {
				$(this).val(digits[index])
			}
		})
		if (digits.length === 6) {
			$("#otp-container input").off("keydown")
			$("#otp-container input").off("input")
			$("#otp-container input").prop("readonly", true)
			submitOTP()
		}
	})

	$("#try-again-button").on("click", function () {
		const urlParams = new URLSearchParams(window.location.search)
		urlParams.delete("otp")
		window.location.href = window.location.pathname + "?" + urlParams.toString()
	})

	function submitOTP() {
		window.location.href +=
			"&otp=" +
			$("#otp-container input")
				.map(function () {
					return $(this).val()
				})
				.get()
				.join("")
	}
})
