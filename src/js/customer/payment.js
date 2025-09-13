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
    
    $(".container form").on("submit", async function (event) {
		event.preventDefault()

		$(".container form button[type='submit']").text("Sending...")
		$(".container form button[type='submit']").prop("disabled", true)
		
        const response = await $.ajax({
			url: "/backend/customer/do.payment.php",
			type: "POST",
			data: $(this).serialize() + "&id=" + $(this).data("id")
		})

		if (response.errors) {
			show_form_error(response.errors)
		}
		
		if (response.errors.order) {
			window.location.href = "/order-history"
			return
		}
        
		if (response.success) {
			window.location.href = "/order-history"
			return
		}
		
		$(".container form button[type='submit']").prop("disabled", false)
		$(".container form button[type='submit']").text("Pay Now")
	})
})
