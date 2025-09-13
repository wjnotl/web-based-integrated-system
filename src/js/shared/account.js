import { confirmation, open_overlay, UploadOverlay, show_form_error, detect_form_changes, showToast } from "../utils.js"

$(document).ready(function () {
	$("#change-email-button").on("click", async function () {
		$("#change-email-button").addClass("pending")
		if (await confirmation("Are you sure you want to change your email address?", "Yes")) {
			const response = await $.ajax({
				url: "/backend/shared/do.request-change-email.php",
				type: "POST"
			})

			if (response.login) {
				window.location.reload()
				return
			}

			if (response.success) {
				showToast("Please check your email to proceed with the email change")
			}
		}
		$("#change-email-button").removeClass("pending")
	})

	$("#change-password-button").on("click", function () {
		open_overlay("password-overlay")
	})

	$("#delete-account-button").on("click", async function () {
		if (await confirmation("Are you sure you want to delete your account?", "Yes")) {
			$("#delete-account-button").prop("disabled", true)
			$("#delete-account-button").text("Sending...")
			const response = await $.ajax({
				url: "/backend/shared/do.request-delete-account.php",
				type: "POST"
			})

			if (response.login) {
				window.location.reload()
				return
			}

			if (response.success) {
				showToast("Please check your email to proceed with the account deletion")
			}

			$("#delete-account-button").text("Delete Account")
			$("#delete-account-button").prop("disabled", false)
		}
	})

	const uploadOverlay = new UploadOverlay("#profile-picture-file")
	let scale = 1
	let imgX = 0
	let imgY = 0

	$("#upload-profile-picture-button").on("click", async function () {
		const uploadResult = await uploadOverlay.open()
		if (uploadResult) {
			if (uploadResult.horizontalFit) {
				$("#profile-preview").addClass("horizontal-fit")
				$("#profile-preview").removeClass("vertical-fit")
			} else {
				$("#profile-preview").addClass("vertical-fit")
				$("#profile-preview").removeClass("horizontal-fit")
			}
			$("#profile-preview").attr("src", uploadResult.src)
			$("#profile-preview").css(
				"transform",
				`translate(calc(-50% + ${uploadResult.imgX}px), calc(-50% + ${uploadResult.imgY}px)) scale(${uploadResult.scale})`
			)
			scale = uploadResult.scale
			imgX = uploadResult.imgX
			imgY = uploadResult.imgY
			$("#remove-picture-checkbox").prop("checked", false).trigger("change")
			$("#remove-profile-picture-button").prop("disabled", false)
			$("#change-picture-checkbox").prop("checked", true).trigger("change")
			$("#reset-profile-picture-button").prop("disabled", false)
		}
	})

	$("#profile-preview").data("original-scale", scale)
	$("#profile-preview").data("original-imgX", imgX)
	$("#profile-preview").data("original-imgY", imgY)
	$("#profile-preview").data("original-src", $("#profile-preview").attr("src"))

	$("#reset-profile-picture-button").on("click", async function () {
		$("#profile-picture-file").val("")
		scale = parseFloat($("#profile-preview").data("original-scale"))
		imgX = parseFloat($("#profile-preview").data("original-imgX"))
		imgY = parseFloat($("#profile-preview").data("original-imgY"))
		$("#profile-preview").css("transform", `translate(calc(-50% + ${imgX}px), calc(-50% + ${imgY}px)) scale(${scale})`)
		$("#profile-preview").attr("src", $("#profile-preview").data("original-src"))
		$("#profile-preview").addClass("vertical-fit")
		$("#profile-preview").removeClass("horizontal-fit")

		$("#remove-picture-checkbox").prop("checked", false).trigger("change")
		$("#remove-profile-picture-button").prop("disabled", $("#profile-preview").data("original-src") === "/src/img/icon/pfp.png")
		$("#change-picture-checkbox").prop("checked", false).trigger("change")
		$("#reset-profile-picture-button").prop("disabled", true)
	})

	$("#remove-profile-picture-button").prop("disabled", $("#profile-preview").data("original-src") === "/src/img/icon/pfp.png")
	$("#remove-profile-picture-button").on("click", async function () {
		if (await confirmation("Are you sure you want to remove your profile picture?", "Yes")) {
			if ($("#profile-preview").data("original-src") === "/src/img/icon/pfp.png") {
				$("#remove-picture-checkbox").prop("checked", false).trigger("change")
			} else {
				$("#remove-picture-checkbox").prop("checked", true).trigger("change")
			}
			$("#remove-profile-picture-button").prop("disabled", true)
			$("#change-picture-checkbox").prop("checked", false).trigger("change")
			$("#reset-profile-picture-button").prop("disabled", $("#profile-preview").data("original-src") === "/src/img/icon/pfp.png")

			$("#profile-preview").css("transform", "")
			scale = 1
			imgX = 0
			imgY = 0
			$("#profile-preview").addClass("vertical-fit")
			$("#profile-preview").removeClass("horizontal-fit")
			$("#profile-preview").attr("src", "/src/img/icon/pfp.png")
		}
	})

	$("#account-submit-button").prop("disabled", true)
	const formChanges = detect_form_changes(
		"#account-form",
		"input",
		function () {
			$("#account-submit-button").prop("disabled", false)
		},
		function () {
			$("#account-submit-button").prop("disabled", true)
		}
	)

	$("#account-container form").on("submit", async function (e) {
		e.preventDefault()

		$("#account-container form button[type='submit']").text("Sending...")
		$("#account-container form button[type='submit']").prop("disabled", true)

		const formData = new FormData(this)
		formData.append("scale", scale.toString())
		formData.append("imgX", imgX.toString())
		formData.append("imgY", imgY.toString())

		const response = await $.ajax({
			url: "/backend/shared/do.account-update.php",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false
		})

		if (response.login) {
			window.location.reload()
			return
		}

		show_form_error(response.errors)

		$("#account-container form button[type='submit']").text("Save Changes")
		$("#account-container form button[type='submit']").prop("disabled", false)

		if (response.success) {
			$("#profile-preview").data("original-scale", scale)
			$("#profile-preview").data("original-imgX", imgX)
			$("#profile-preview").data("original-imgY", imgY)
			$("#profile-preview").data("original-src", $("#profile-preview").attr("src"))
			$("#remove-picture-checkbox").prop("checked", false)
			$("#remove-profile-picture-button").prop("disabled", $("#profile-preview").data("original-src") === "/src/img/icon/pfp.png")
			$("#change-picture-checkbox").prop("checked", false)
			$("#reset-profile-picture-button").prop("disabled", true)
			formChanges()
			if (response.remove_photo) {
				$("#header-account-image").attr("src", "/src/img/icon/pfp.png")
			} else if (response.photo) {
				$("#header-account-image").attr("src", "/uploads/account/" + response.photo)
			}

			showToast("Saved changes")
		}
	})

	$("#password-overlay form").on("submit", async function (e) {
		e.preventDefault()

		$("#password-overlay form button[type='submit']").text("Sending...")
		$("#password-overlay form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/shared/do.account-update-password.php",
			type: "POST",
			data: $(this).serialize()
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		show_form_error(response.errors)

		if (response.success) {
			window.location.href = "/login"
			return
		}

		$("#password-overlay form button[type='submit']").text("Update Password")
		$("#password-overlay form button[type='submit']").prop("disabled", false)
	})
})
