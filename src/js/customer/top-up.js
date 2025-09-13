import { show_form_error } from "../utils.js"

$(document).ready(function () {
	$("#card-number").data("previous-nondigit-value", "")
	$("#card-number").on("input", function () {
		let value = $(this).val()
		value = value.replace(/\D/g, "")
		const rawValue = value

		value = value.replace(/(.{4})(?=.)/g, "$1 ")
		const nonDigitValue = value.replace(/\d/g, "")

		let cursorPosition = this.selectionStart
		if (rawValue.length > $(this).data("previous-value").length) {
			cursorPosition += nonDigitValue.length - $(this).data("previous-nondigit-value").length
		}

		$(this).val(value)
		$(this).data("previous-value", rawValue)
		$(this).data("previous-nondigit-value", nonDigitValue)
		this.setSelectionRange(cursorPosition, cursorPosition)
	})

	$("#card-expiry").on("input", function () {
		let value = $(this).val()
		value = value.replace(/\D/g, "")
		const rawValue = value

		if (value.length > 2) {
			value = value.slice(0, 2) + "/" + value.slice(2)
		}

		let cursorPosition = this.selectionStart
		if (rawValue.length > $(this).data("previous-value").length) {
			const diff = rawValue.length - $(this).data("previous-value").length
			if (cursorPosition - diff === 2) {
				cursorPosition++
			}
		}

		$(this).val(value)
		$(this).data("previous-value", rawValue)
		this.setSelectionRange(cursorPosition, cursorPosition)
	})
    
    $("form.container").on("submit", async function (e) {
        e.preventDefault()

        $("form.container button[type='submit']").text("Sending...")
        $("form.container button[type='submit']").prop("disabled", true)

        const response = await $.ajax({
            url: "/backend/customer/do.top-up.php",
            type: "POST",
            data: $(this).serialize()
        })

        if (response.login) {
            window.location.href = "/login"
            return
        }
        
        show_form_error(response.errors)

        if (response.success) {
            window.location.href = "/wallet"
            return
        }

        $("form.container button[type='submit']").text("Top Up")
        $("form.container button[type='submit']").prop("disabled", false)
    })
})
