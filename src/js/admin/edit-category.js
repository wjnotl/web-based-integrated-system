import { detect_form_changes, show_form_error, showToast, UploadOverlay } from "../utils.js"

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
			$("#change-picture-checkbox").prop("checked", true).trigger("change")
			$("#reset-picture-button").prop("disabled", false)
		}
	})

	$("#picture-preview").data("original-scale", scale)
	$("#picture-preview").data("original-imgX", imgX)
	$("#picture-preview").data("original-imgY", imgY)
	$("#picture-preview").data("original-src", $("#picture-preview").attr("src"))

	$("#reset-picture-button").on("click", async function () {
		$("#picture-file").val("")
		scale = parseFloat($("#picture-preview").data("original-scale"))
		imgX = parseFloat($("#picture-preview").data("original-imgX"))
		imgY = parseFloat($("#picture-preview").data("original-imgY"))
		$("#picture-preview").css("transform", `translate(calc(-50% + ${imgX}px), calc(-50% + ${imgY}px)) scale(${scale})`)
		$("#picture-preview").attr("src", $("#picture-preview").data("original-src"))
		$("#picture-preview").addClass("vertical-fit")
		$("#picture-preview").removeClass("horizontal-fit")

		$("#change-picture-checkbox").prop("checked", false).trigger("change")
		$("#reset-picture-button").prop("disabled", true)
    })
    
    const formChanges = detect_form_changes(
		"form",
		"input",
		function () {
			$("form button[type='submit']").prop("disabled", false)
		},
		function () {
			$("form button[type='submit']").prop("disabled", true)
		}
	)

	$("form").on("submit", async function (e) {
		e.preventDefault()
		$("form button[type='submit']").text("Sending...")
        $("form button[type='submit']").prop("disabled", true)
        
        const formData = new FormData(this)
        formData.append("id", $("form").data("id"))
		formData.append("scale", scale.toString())
		formData.append("imgX", imgX.toString())
		formData.append("imgY", imgY.toString())

		const response = await $.ajax({
			url: "/backend/admin/do.edit-category.php",
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
			if (response.errors?.id) {
				window.location.href = "/manage-category"
				return
			}
		}

		$("form button[type='submit']").text("Save Changes")
		$("form button[type='submit']").prop("disabled", false)

        if (response.success) {
            $("#picture-preview").data("original-scale", scale)
			$("#picture-preview").data("original-imgX", imgX)
			$("#picture-preview").data("original-imgY", imgY)
			$("#picture-preview").data("original-src", $("#picture-preview").attr("src"))
			$("#change-picture-checkbox").prop("checked", false)
			$("#reset-picture-button").prop("disabled", true)
			formChanges()
			showToast("Saved changes")
		}
	})
})
