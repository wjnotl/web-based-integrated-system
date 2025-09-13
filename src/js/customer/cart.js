import { confirmation, show_form_error, showToast } from "../utils.js"

$(document).ready(function () {
	$("#select-all-items").on("change", function () {
		$(".cart-items-container input[type='checkbox']").prop("checked", this.checked).trigger("change")
	})

	$(".cart-items-container input[type='checkbox']").on("change", orderSummaryChange)

	function orderSummaryChange() {
		$("#cart-summary-items-container").empty()

		$("#select-all-items").prop("checked", $(".cart-items-container:has(input[type='checkbox']:not(:checked))").length === 0)

		let totalPrice = 0
		let haveError = false
		let haveChecked = false
		$(".cart-items-container:has(input[type='checkbox']:checked)").each(function () {
			const getQuantity = parseInt($(this).find("input[type='number']").val()) || 0
			let subTotal = (parseFloat($(this).find(".product-price").text()) * getQuantity).toFixed(2)
			let error = ""

			if (getQuantity === 0 || $(this).find(".form-error").is(".show")) {
				error = "error"
				subTotal = "-"
				haveError = true
			} else {
				totalPrice += parseFloat(subTotal)
			}

			haveChecked = true

			$("#cart-summary-items-container").append(`<div class="${error}">${$(this).find(".product-name").text()}</div>`)
			$("#cart-summary-items-container").append(`<div class="${error}">${$(this).find(".product-variation > div:nth-child(1)").text()}</div>`)
			$("#cart-summary-items-container").append(`<div class="${error}">${$(this).find(".product-variation > div:nth-child(2)").text()}</div>`)
			$("#cart-summary-items-container").append(`<div class="${error}">${getQuantity}</div>`)
			$("#cart-summary-items-container").append(`<div class="${error}">${subTotal}</div>`)
		})

		$("#cart-form button[type='submit']").prop("disabled", haveError || !haveChecked)
		$("#cart-total-container > div:nth-child(2)").text(totalPrice.toFixed(2))
	}

	$(".cart-items-container input[type='number']").on("input", async function () {
		$(this).val(
			$(this)
				.val()
				.replace(/[^0-9]/g, "")
		)

		const response = await $.ajax({
			url: "/backend/customer/do.cart-stock.php",
			type: "POST",
			data: {
				product_variant_id: $(this).closest(".cart-items-container").data("product-variant-id"),
				quantity: $(this).val()
			}
		})

		show_form_error(response.errors)

		orderSummaryChange()
	})

	$(".cart-items-container .cart-favourite-button").on("click", async function (event) {
		event.preventDefault()

		const product_id = $(this).closest(".cart-items-container").data("product-id")
		const response = await $.ajax({
			url: "/backend/customer/do.favourite.php",
			type: "POST",
			data: {
				product_id: product_id,
				action: $(this).hasClass("favourite") ? "remove" : "add"
			}
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.errors?.product_id) {
			showToast(response.errors.product_id)
			return
		}

		if (response.success) {
			if (response.favourite === "added") {
				$(`.cart-items-container[data-product-id="${product_id}"] .cart-favourite-button`).addClass("favourite")
				showToast("Added to favourite")
			} else if (response.favourite === "removed") {
				$(`.cart-items-container[data-product-id="${product_id}"] .cart-favourite-button`).removeClass("favourite")
				showToast("Removed from favourite")
			}
		}
	})

	$(".cart-items-container .cart-remove-button").on("click", async function (event) {
		event.preventDefault()

		if (await confirmation("Are you sure you want to remove this item from cart?", "Yes", "No")) {
			const response = await $.ajax({
				url: "/backend/customer/do.cart-remove.php",
				type: "POST",
				data: {
					product_variant_id: $(this).closest(".cart-items-container").data("product-variant-id")
				}
			})

			if (response.login) {
				window.location.href = "/login"
				return
			}

			if (response.success) {
				$(this).closest(".cart-items-container").remove()
				orderSummaryChange()
				showToast("Item removed from cart")
			}
		}
	})

	$("#bulk-remove-button").on("click", async function () {
		const items = $(".cart-items-container:has(input[type='checkbox']:checked)")
		if (items.length) {
			if (await confirmation("Are you sure you want to remove selected items from cart?", "Yes", "No")) {
				let variants = []
				items.each(function () {
					variants.push($(this).data("product-variant-id"))
				})

				const response = await $.ajax({
					url: "/backend/customer/do.cart-remove.php",
					type: "POST",
					data: {
						product_variant_id: variants.join(",")
					}
				})

				if (response.login) {
					window.location.href = "/login"
					return
				}

				if (response.success) {
					$(".cart-items-container:has(input[type='checkbox']:checked)").remove()
					orderSummaryChange()
					showToast("Items removed from cart")
				}
			}
		} else {
			showToast("No items selected. Please select item first")
		}
	})

	orderSummaryChange()
})
