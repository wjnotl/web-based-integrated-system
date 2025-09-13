import { show_form_error, showToast, UploadOverlay, confirmation, open_overlay } from "../utils.js"

$(document).ready(function () {
	const uploadOverlay = new UploadOverlay("#picture-file")

	$("#upload-picture-button").on("click", async function () {
		const uploadResult = await uploadOverlay.open()
		if (uploadResult) {
			generatePicturePreview(uploadResult.src, uploadResult.scale, uploadResult.imgX, uploadResult.imgY, uploadResult.horizontalFit)
		}
	})

	$("form.container").on("click", ".picture-wrapper", async function () {
		if (await confirmation("Are you sure you want to remove this picture?", "Yes")) {
			const index = $(this).index(".picture-wrapper")
			uploadOverlay.removeFile(index)

			$(this).remove()
		}
	})

	function generatePicturePreview(src, scale, imgX, imgY, horizontalFit) {
		$("#picture-galary").append(`
            <div class="picture-wrapper" data-scale="${scale}" data-imgx="${imgX}" data-imgy="${imgY}">
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

	$("form.container").on("submit", async function (e) {
		e.preventDefault()
		$("form.container button[type='submit']").text("Sending...")
		$("form.container button[type='submit']").prop("disabled", true)

		const formData = new FormData(this)

		$(".picture-wrapper").each(function () {
			formData.append("scale[]", $(this).data("scale"))
			formData.append("imgX[]", $(this).data("imgx"))
			formData.append("imgY[]", $(this).data("imgy"))
		})

		const response = await $.ajax({
			url: "/backend/admin/do.add-product.php",
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

		$("form.container button[type='submit']").text("Save Changes")
		$("form.container button[type='submit']").prop("disabled", false)

		if (response.success) {
			window.location.href = "/manage-product"
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
			url: "/backend/admin/do.check-add-variant.php",
			type: "POST",
			data: $(this).serialize()
		})

		if (response.login) {
			window.location.reload()
			return
		}

		if (response.errors) {
			show_form_error(response.errors)
		}

		if (response.success) {
			const variantColour = $("#variant-overlay form input[name='new_variant_colour']").val()
			const variantSize = $("#variant-overlay form input[name='new_variant_size']").val()
			const variantStock = $("#variant-overlay form input[name='new_variant_stock']").val()

			if ($(`tbody tr[data-variant-colour='${variantColour}'][data-variant-size='${variantSize}']`).length) {
				show_form_error({
					new_variant_colour: "Variant already exists",
					new_variant_size: "Variant already exists",
					new_variant_stock: null
				})
				response.success = false
			} else if ($(`tbody tr[data-variant-colour='${variantColour}']`).length) {
				$(`tbody tr[data-variant-colour='${variantColour}']`).last().after(`
					<tr data-variant-colour="${variantColour}" data-variant-size="${variantSize}" data-variant-stock="${variantStock}">
						<td>${variantColour}</td>
						<td>${variantSize}</td>
						<td>${variantStock}</td>
						<td>
							<div class="button-group-inline">
								<button class="button-red delete-variant-button">Delete</button>
							</div>
						</td>
						<input type="hidden" name="variants_colour[]" value="${variantColour}">
						<input type="hidden" name="variants_size[]" value="${variantSize}">
						<input type="hidden" name="variants_stock[]" value="${variantStock}">
					</tr>
				`)
			} else {
				$("tbody").append(`
					<tr data-variant-colour="${variantColour}" data-variant-size="${variantSize}" data-variant-stock="${variantStock}">
						<td>${variantColour}</td>
						<td>${variantSize}</td>
						<td>${variantStock}</td>
						<td>
							<div class="button-group-inline">
								<button class="button-red delete-variant-button">Delete</button>
							</div>
						</td>
						<input type="hidden" name="variants_colour[]" value="${variantColour}">
						<input type="hidden" name="variants_size[]" value="${variantSize}">
						<input type="hidden" name="variants_stock[]" value="${variantStock}">
					</tr>
				`)
			}

			if (response.success) {
				$("#variant-overlay").removeClass("show")
				showToast("Variant added successfully")
			}
		}

		$("#variant-overlay form button[type='submit']").text("Add Variant")
		$("#variant-overlay form button[type='submit']").prop("disabled", false)
	})

	$("tbody").on("click", ".delete-variant-button", async function () {
		if (await confirmation("Are you sure you want to delete this variant?", "Yes")) {
			$(this).closest("tr").remove()
		}
	})
})
