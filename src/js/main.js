import { confirmation, delete_cookie, showToast } from "./utils.js"

$(document).ready(function () {
	const toast_message = $(".toast-container").data("toast-message")
	if (toast_message) {
		showToast(toast_message)
	}

	$("form").prop("noValidate", true)
	$("form").prop("autocomplete", "off")

	$("input[type='text']").each(function () {
		$(this).data("previous-value", $(this).val())
	})

	$("input[type='number']").on("keypress", function (event) {
		if (event.code === "KeyE") {
			event.preventDefault()
		}
	})

	$(".password-show-button").on("change", function () {
		$(this)
			.prev()
			.prop("type", $(this).prop("checked") ? "text" : "password")
	})

	$("#logout-button").on("click", async function () {
		if (await confirmation("Are you sure you want to logout?", "Yes")) {
			await $.ajax({
				url: "/backend/shared/do.logout.php",
				type: "POST"
			})

			delete_cookie("session_id")
			delete_cookie("session_token")
			window.location.href = "/"
		}
	})

	$(".custom-select input[type='radio']").on("change", function () {
		if ($(this).prop("checked")) {
			$(this).closest(".custom-select").find(".selected-text").text($(this).next().text())
		}
	})
	const custom_select_radio_checked = $(".custom-select input[type='radio'][checked]").toArray()
	for (const custom_select_radio of custom_select_radio_checked) {
		$(custom_select_radio).closest(".custom-select").find(".selected-text").text($(custom_select_radio).next().text())
	}

	function ensureTwoDigitsAfterDecimal(value) {
		const decimalString = value.toString()
		const decimalPointLoc = decimalString.indexOf(".")

		if (decimalPointLoc !== -1) {
			const numberOfDecimalDigits = decimalString.length - (decimalPointLoc + 1)
			if (numberOfDecimalDigits === 1) {
				return decimalString + "0"
			}
			return decimalString
		} else {
			return decimalString + ".00"
		}
	}
	function formatCents(cents) {
		if (cents !== "") {
			return ensureTwoDigitsAfterDecimal(cents / 100)
		} else {
			return ""
		}
	}

	$("input[data-rm-format='true']").on("input", function () {
		const value = $(this)
			.val()
			.replace(/[^0-9]/g, "")
		if (value !== "") {
			$(this).val(formatCents(value))
		}
	})
})
