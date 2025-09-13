import { detect_form_changes, show_form_error, showToast, UploadOverlay, confirmation, open_overlay } from "../utils.js"

$(document).ready(function () {
	let originalKeywords = $("input[name='keyword']").val()

	const uploadOverlay = new UploadOverlay("#picture-file")
	let uploads = []
	$(".picture-preview-container").each(function () {
		uploads.push({
			src: $(this).find("img").attr("src"),
			scale: 1,
			imgX: 0,
			imgY: 0,
			horizontalFit: false
		})
	})
	let originalUploads = uploads.map(upload => ({
		src: upload.src,
		scale: upload.scale,
		imgX: upload.imgX,
		imgY: upload.imgY,
		horizontalFit: upload.horizontalFit,
	}))

	function areUploadsSame() {
		if (uploads.length !== originalUploads.length) return false;

		return uploads.every((obj, i) => {
			const other = originalUploads[i]
			return obj.src === other.src &&
				obj.scale === other.scale &&
				obj.imgX === other.imgX &&
				obj.imgY === other.imgY &&
				obj.horizontalFit === other.horizontalFit
		})
	}

	$("#upload-picture-button").on("click", async function () {
		const uploadResult = await uploadOverlay.open()
		if (uploadResult) {
			uploads.push({
				src: uploadResult.src,
				scale: uploadResult.scale,
				imgX: uploadResult.imgX,
				imgY: uploadResult.imgY,
				horizontalFit: uploadResult.horizontalFit
			})
			generatePicturePreview(uploadResult.src, uploadResult.scale, uploadResult.imgX, uploadResult.imgY, uploadResult.horizontalFit, 1)
			$("#reset-picture-button").prop("disabled", false)
			$("#change-picture-checkbox").prop("checked", true).trigger("change")
		}
	})

	$("form.three-column-content").on("click", ".picture-wrapper", async function () { 
		if (await confirmation("Are you sure you want to remove this picture?", "Yes")) { 
			if ($(this).data("upload") !== "") {
				const index = $(this).index(".picture-wrapper[data-upload='1']")
				uploadOverlay.removeFile(index)
			} else {
				$("form.three-column-content").append(`<input type="hidden" name="remove_photos[]" value="${($(this).find("img").attr("src")).replace("\/uploads\/product\/", "").replace("/uploads/product/", "")}">`)
			}

			const index = $(this).index(".picture-wrapper")
			uploads.splice(index, 1)
			$(this).remove()

			$("#change-picture-checkbox").prop("checked", !areUploadsSame).trigger("change")
			$("#reset-picture-button").prop("disabled", areUploadsSame)
		}
	})

	$("#reset-picture-button").on("click", async function () {
		uploadOverlay.removeAllFiles()
		uploads = originalUploads.map(upload => ({
			src: upload.src,
			scale: upload.scale,
			imgX: upload.imgX,
			imgY: upload.imgY,
			horizontalFit: upload.horizontalFit
		}))
		$("#picture-galary").empty()
		uploads.forEach(upload => {
			generatePicturePreview(upload.src, upload.scale, upload.imgX, upload.imgY, upload.horizontalFit, "")	
		})
		$("input[name='remove_photos[]']").remove()

		$("#change-picture-checkbox").prop("checked", false).trigger("change")
		$("#reset-picture-button").prop("disabled", true)
	})

	function generatePicturePreview(src, scale, imgX, imgY, horizontalFit, upload) {
		$("#picture-galary").append(`
            <div class="picture-wrapper" data-upload="${upload}" data-scale="${scale}" data-imgx="${imgX}" data-imgy="${imgY}">
                <div class="picture-preview-container">
                    <img 
                        class="picture-preview ${horizontalFit ? "horizontal-fit" : "vertical-fit"}"
                        src="${src}" 
                        alt="Product Picture"
                        style="transform: translate(calc(-50% + ${imgX}px), calc(-50% + ${imgY}px)) scale(${scale});"
                    >
                </div>
				<div class="delete-preview">Delete</div>
            </div>
        `)
	}

	const formChanges = detect_form_changes(
		"form.three-column-content",
		"input, textarea",
		function () {
			$("form.three-column-content button[type='submit']").prop("disabled", false)
		},
		function () {
			$("form.three-column-content button[type='submit']").prop("disabled", true)
		}
	)

	$("form.three-column-content").on("submit", async function (e) {
		e.preventDefault()
		$("form.three-column-content button[type='submit']").text("Sending...")
		$("form.three-column-content button[type='submit']").prop("disabled", true)

		const formData = new FormData(this)
		formData.append("id", $("form.three-column-content").data("id"))
		formData.append("keyword_change", originalKeywords !== $("input[name='keyword']").val())
		$(".picture-wrapper[data-upload='1']").each(function () { 
			formData.append("scale[]", $(this).data("scale"))
			formData.append("imgX[]", $(this).data("imgx"))
			formData.append("imgY[]", $(this).data("imgy"))
		})
		

		const response = await $.ajax({
			url: "/backend/admin/do.edit-product.php",
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
				window.location.href = "/manage-product"
				return
			}
		}

		$("form.three-column-content button[type='submit']").text("Save Changes")
		$("form.three-column-content button[type='submit']").prop("disabled", false)

		if (response.success) {
			uploadOverlay.removeAllFiles()
			originalUploads = uploads.map(upload => ({
				src: upload.src,
				scale: upload.scale,
				imgX: upload.imgX,
				imgY: upload.imgY,
				horizontalFit: upload.horizontalFit,
			}))
			originalKeywords = $("input[name='keyword']").val()
			$("#picture-galary").empty()
			uploads.forEach(upload => {
				generatePicturePreview(upload.src, upload.scale, upload.imgX, upload.imgY, upload.horizontalFit, "")	
			})
			$("input[name='remove_photos[]']").remove()

			$("#change-picture-checkbox").prop("checked", false)
			$("#reset-picture-button").prop("disabled", true)
			formChanges()
			showToast("Saved changes")
		}
	})

	$("#add-variant-button").on("click", function () {
		$("#variant-overlay form input").val("")
		open_overlay("variant-overlay")
	})

	$("#variant-overlay form").on("submit", async function (e) { 
		e.preventDefault()
		$("#variant-overlay form button[type='submit']").text("Sending...")
		$("#variant-overlay form button[type='submit']").prop("disabled", true)

		const response = await $.ajax({
			url: "/backend/admin/do.add-variant.php",
			type: "POST",
			data: $(this).serialize() + "&id=" + $("form.three-column-content").data("id")
		})

		if (response.login) {
			window.location.reload()
			return
		}

		if (response.errors) {
			show_form_error(response.errors)
			if (response.errors?.id) {
				window.location.href = "/manage-product"
				return
			}
		}

		if (response.success && response.variants) { 
			$("tbody").empty()
			for (const variant of response.variants) { 
				$("tbody").append(`
					<tr data-id="${variant.id}">
						<td>${variant.colour}</td>
						<td>${variant.size}</td>
						<td>
							<div class="form-group">
								<div class="form-input">
									<input type="number" name="variant_stock" value="${variant.stock}">
								</div>
								<div class="form-error" data-error="variant_stock_${variant.id}"></div>
							</div>
						</td>
						<td>
							<div class="button-group-inline">
								<button class="button-red delete-variant-button">Delete</button>
							</div>
						</td>
					</tr>
				`)
			}
			$("#variant-overlay").removeClass("show")
			showToast("Variant added successfully")
		}

		$("#variant-overlay form button[type='submit']").text("Add Variant")
		$("#variant-overlay form button[type='submit']").prop("disabled", false)
	})

	$("tbody").on("click", ".delete-variant-button", async function () { 
		if (await confirmation("Are you sure you want to delete this variant?", "Yes")) { 
			$(this).text("Deleting...")
			$(this).prop("disabled", true)

			const response = await $.ajax({
				url: "/backend/admin/do.delete-variant.php",
				type: "POST",
				data: {
					id: $(this).closest("tr").data("id")
				}
			})

			if (response.errors?.id) {
				showToast(response.errors.id)
			}

			$(this).text("Delete")
			$(this).prop("disabled", false)

			if (response.success) {
				$(this).closest("tr").remove()
				showToast("Variant deleted successfully")
			}
		}
	})

	$("tbody").on("input", "input[name='variant_stock']", async function () { 
		$(this).val(
			$(this)
				.val()
				.replace(/[^0-9]/g, "")
		)

		const response = await $.ajax({
			url: "/backend/admin/do.edit-variant.php",
			type: "POST",
			data: {
				id: $(this).closest("tr").data("id"),
				stock: $(this).val()
			}
		})
		if (response.errors) {
			show_form_error(response.errors)
			if (response.errors?.id) {
				showToast(response.errors.id)
			}
		}
	})
})
