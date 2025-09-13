import { show_form_error, UploadOverlay } from "../utils.js"

$(document).ready(function () {
	const uploadOverlay = new UploadOverlay("#picture-file")
	let scale = 1
	let imgX = 0
	let imgY = 0

	$("#upload-picture-button").on("click", async function () {
		const uploadResult = await uploadOverlay.open()
		if (uploadResult) {
			if (uploadResult.horizontalFit) {
				$("#picture-preview").addClass("horizontal-fit")
				$("#picture-preview").removeClass("vertical-fit")
			} else {
				$("#picture-preview").addClass("vertical-fit")
				$("#picture-preview").removeClass("horizontal-fit")
			}
			$("#picture-preview").attr("src", uploadResult.src)
			$("#picture-preview").css(
				"transform",
				`translate(calc(-50% + ${uploadResult.imgX}px), calc(-50% + ${uploadResult.imgY}px)) scale(${uploadResult.scale})`
			)
			scale = uploadResult.scale
			imgX = uploadResult.imgX
			imgY = uploadResult.imgY
		}
	})

	$("form").on("submit", async function (e) {
		e.preventDefault()
		$("form button[type='submit']").text("Sending...")
		$("form button[type='submit']").prop("disabled", true)

		const formData = new FormData(this)
		formData.append("scale", scale.toString())
		formData.append("imgX", imgX.toString())
		formData.append("imgY", imgY.toString())

		const response = await $.ajax({
			url: "/backend/admin/do.add-category.php",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false
		})

		if (response.login) {
			window.location.reload()
			return
		}

		if (response.errors) {
			show_form_error(response.errors)
		}

		if (response.success) {
			window.location.href = `/manage-category`
		}

		$("form button[type='submit']").text("Add Category")
		$("form button[type='submit']").prop("disabled", false)
	})
})
