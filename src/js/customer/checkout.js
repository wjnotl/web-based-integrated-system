import { show_form_error, toDateFormat, toRMFormat } from "../utils.js"

$(document).ready(function () {
	$("#select-voucher-button").on("click", async function () {
		$("#voucher-overlay-list-container").empty()

		const response = await $.ajax({
			url: "/backend/customer/do.voucher.php",
			type: "POST",
			data: {
				total_price: $(".container form").data("total-price")
			}
		})

		if (response.vouchers.length > 0) {
			for (const voucher of response.vouchers) {
				$("#voucher-overlay-list-container").append(`
					<div class="voucher" data-voucher-id="${voucher.id}">
						<div class="left">
							<div class="tear-off"><span>Enjoy your voucher</span></div>
							<div class="content">
								<div data-voucher-value="${voucher.value}" class="value"></div>
								<div class="voucher-text"></div>
								<div class="expiry-date">${toDateFormat(voucher.expiry_date)}</div>
							</div>
						</div>
						<div class="right">
							<span>${voucher.id}</span>
						</div>
					</div>
				`)
			}
		}

		$("#voucher-overlay").addClass("show")
	})

	$("#voucher-overlay-list-container").on("click", ".voucher", function () {
		$("#voucher-select-container").empty()
		$("#voucher-select-container").append($(this).clone())
		recalculatePrice()
		$("#voucher-overlay").removeClass("show")
	})

	$("#remove-voucher-button").on("click", function () {
		$("#voucher-select-container").empty()
		recalculatePrice()
	})

	$("#voucher-overlay .overlay-close").on("click", function () {
		$("#voucher-overlay").removeClass("show")
	})

	$("#contact-number").on("input", function () {
		let value = $(this).val()
		value = value.replace(/\D/g, "")
		const rawValue = value

		if (value.length > 3) {
			value = value.slice(0, 3) + "-" + value.slice(3)
		}

		let cursorPosition = this.selectionStart
		if (rawValue.length > $(this).data("previous-value").length) {
			const diff = rawValue.length - $(this).data("previous-value").length
			if (cursorPosition - diff === 3) {
				cursorPosition++
			}
		}

		$(this).val(value)
		$(this).data("previous-value", rawValue)
		this.setSelectionRange(cursorPosition, cursorPosition)
	})

	$("#postal-code").on("input", function () {
		let value = $(this).val()
		value = value.replace(/\D/g, "")

		$(this).val(value)
	})

	$("input[name='shipping_type']").on("change", recalculatePrice)

	function recalculatePrice() {
		$("#summary-subtotal-container").empty()
		$("#summary-total-container").empty()

		const items_total = parseFloat($(".container form").data("total-price"))

		const shipping_type = $("input[name='shipping_type']:checked").val()
		const shipping_price = shipping_type === "Express" ? 15 : 8

		const voucher_value = parseFloat($("#voucher-select-container .value").data("voucher-value") || "0")
		const voucher_valid = items_total > voucher_value

		$("#summary-subtotal-container").append(`<div>Item(s) Total</div>`)
		$("#summary-subtotal-container").append(`<div>${toRMFormat(items_total)}</div>`)

		if (voucher_value) {
			$("#summary-subtotal-container").append(`<div>Voucher</div>`)
			$("#summary-subtotal-container").append(`<div class="negative">${toRMFormat(voucher_value)}</div>`)
		}

		$("#summary-subtotal-container").append(`<div>Shipping Fee</div>`)
		$("#summary-subtotal-container").append(`<div>${toRMFormat(shipping_price)}</div>`)

		$("#summary-total-container").append(`<div>Order Total</div>`)
		$("#summary-total-container").append(`<div>${voucher_valid ? toRMFormat(items_total - voucher_value + shipping_price) : "-"}</div>`)
	}

	$(".container form").on("submit", async function (event) {
		event.preventDefault()

		$(".container form button[type='submit']").text("Sending...")
		$(".container form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/customer/do.checkout.php",
			type: "POST",
			data:
				$(this).serialize() +
				`&checkout_items=${$(this).data("checkout-items")}&voucher_id=${$("#voucher-select-container .voucher").data("voucher-id") ?? ""}`
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.errors?.order_payment_pending || response.errors?.checkout_items) {
			window.location.href = "/cart"
			return
		}

		show_form_error(response.errors)

		if (response.success) {
			window.location.href = "/payment?id=" + response.order_id
			return
		}

		$(".container form button[type='submit']").prop("disabled", false)
		$(".container form button[type='submit']").text("Place Order")
	})
})
