import { show_form_error, showToast, UploadOverlay, confirmation } from "../utils.js"

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

			$("#remove-picture-button").prop("disabled", false)
		}
	})

	$("#remove-picture-button").on("click", async function () {
        if (await confirmation("Are you sure you want to remove notice picture?", "Yes")) {
			$("#remove-picture-button").prop("disabled", true)

			$("#picture-preview").css("transform", "")
			scale = 1
			imgX = 0
			imgY = 0
			$("#picture-preview").addClass("vertical-fit")
			$("#picture-preview").removeClass("horizontal-fit")
			$("#picture-preview").attr("src", "")
		}
    })

    $("#add-option-button").on("click", function () { 
        if ($("#add-option-text").val() === "") {
            showToast("Please enter text for the option")
            return
        }

        const optionText = $("#add-option-text").val()
        $("#add-option-text").val("")

        $(".poll-container").append(`
            <div class="poll-option" data-vote-count="0">
                <input type="hidden" name="add_option[]" value="${optionText}">
                <div class="poll-input-box">
                    <div class="poll-text-container">
                        <div class="poll-text">${optionText}</div>
                    </div>
                </div>
                <button type="button" class="button-red remove-option-button" data-id="">Remove</button>
            </div>
        `)
    })

    $("form").on("click", ".remove-option-button", async function () {
        if (await confirmation("Are you sure you want to remove this option?", "Yes")) { 
            $(this).closest(".poll-option").remove()
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
			url: "/backend/admin/do.add-notice.php",
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
				window.location.href = "/manage-notice"
				return
			}
        }
        
        if (response.success) {
			window.location.href = "/manage-notice"
		}

		$("form button[type='submit']").text("Add Category")
		$("form button[type='submit']").prop("disabled", false)
	})
})
