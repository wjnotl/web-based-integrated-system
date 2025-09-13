import { detect_form_changes, show_form_error, showToast, UploadOverlay, confirmation, calculatePollPercentages } from "../utils.js"

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
			$("#remove-picture-checkbox").prop("checked", false).trigger("change")
			$("#remove-picture-button").prop("disabled", false)
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

        $("#remove-picture-checkbox").prop("checked", false).trigger("change")
		$("#remove-picture-button").prop("disabled", $("#picture-preview").data("original-src") === "")
		$("#change-picture-checkbox").prop("checked", false).trigger("change")
		$("#reset-picture-button").prop("disabled", true)
    })
    
    $("#remove-picture-button").prop("disabled", $("#picture-preview").data("original-src") === "")
	$("#remove-picture-button").on("click", async function () {
        if (await confirmation("Are you sure you want to remove notice picture?", "Yes")) {
            if ($("#picture-preview").data("original-src") === "") { 
                $("#remove-picture-checkbox").prop("checked", false).trigger("change")
            } else {
                $("#remove-picture-checkbox").prop("checked", true).trigger("change")
            }
			$("#remove-picture-button").prop("disabled", true)
			$("#change-picture-checkbox").prop("checked", false).trigger("change")
			$("#reset-picture-button").prop("disabled", $("#picture-preview").data("original-src") === "")

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
                        <div class="poll-result-value"></div>
                    </div>
                    <div class="poll-result-percentage" style="display: none;"></div>
                </div>
                <button type="button" class="button-red remove-option-button" data-id="">Remove</button>
            </div>
        `)

        $("input").trigger("change")
        recalculatePercentage()
    })

    $("form").on("click", ".remove-option-button", async function () {
        if (await confirmation("Are you sure you want to remove this option?", "Yes")) { 
            if ($(this).data("id") !== "") { 
                $("form").append(`<input type="hidden" name="remove_option[]" value="${$(this).data("id")}">`)
            }
            $(this).closest(".poll-option").remove()
			$("input").trigger("change")
			recalculatePercentage()
        }
    })
    
    const formChanges = detect_form_changes(
		"form",
		"input, textarea",
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
			url: "/backend/admin/do.edit-notice.php",
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

		$("form button[type='submit']").text("Save Changes")
		$("form button[type='submit']").prop("disabled", false)

        if (response.success) {
            $("#picture-preview").data("original-scale", scale)
			$("#picture-preview").data("original-imgX", imgX)
			$("#picture-preview").data("original-imgY", imgY)
            $("#picture-preview").data("original-src", $("#picture-preview").attr("src"))
            $("#remove-picture-checkbox").prop("checked", false)
			$("#remove-picture-button").prop("disabled", $("#picture-preview").data("original-src") === "")
			$("#change-picture-checkbox").prop("checked", false)
			$("#reset-picture-button").prop("disabled", true)
			recalculatePercentage()
			formChanges()
			showToast("Saved changes")
		}
	})

	function recalculatePercentage() {
		const counts = []
		$(".poll-option").each(function () {
			counts.push(parseInt($(this).data("vote-count")))
		})

		const percentages = calculatePollPercentages(counts)
		$(".poll-option").each(function (index) {
			const percentage = `${percentages[index]}%`
			const option = $(this)
			option.find(".poll-result-value").text(percentage)
			option.find(".poll-result-percentage").css("width", percentage)
		})
	}
})
