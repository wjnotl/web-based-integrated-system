function confirmation(question = "", okay = "Okay", cancel = "Cancel") {
	return new Promise((resolve) => {
		$("#confirmation-popup .title").text(question)
		$("#confirmation-popup-confirm").text(okay)
		$("#confirmation-popup-cancel").text(cancel)
		$("#confirmation-popup").addClass("show")
		$("#confirmation-popup-confirm").on("click", function () {
			$("#confirmation-popup").removeClass("show")
			resolve(true)
		})
		$("#confirmation-popup-cancel").on("click", function () {
			$("#confirmation-popup").removeClass("show")
			resolve(false)
		})
	})
}

function show_form_error(errors = {}, scroll = true) {
	for (const key in errors) {
		const form_error = $(`.form-error[data-error=${key}]`)
		if (errors[key] === null) {
			form_error.removeClass("show")
			continue
		}

		form_error.text(errors[key])
		form_error.addClass("show")
	}

	const $error = $(".form-error.show").first()
	if (scroll && $error.length) {
		const scrollPosition = $(window).scrollTop()
		const elementOffset = $error.offset().top
		const windowHeight = $(window).height()
		const headerHeight = ($("header").outerHeight() || 0) + 60
		const visibleAreaStart = scrollPosition + headerHeight
		const visibleAreaEnd = scrollPosition + windowHeight

		if (elementOffset < visibleAreaStart || elementOffset > visibleAreaEnd) {
			$("html, body").animate({ scrollTop: elementOffset - headerHeight }, 500)
		}
	}
}

function open_overlay(id, callback = () => {}) {
	$(`#${id}`).addClass("show")
	$(`#${id} .overlay-close`).off("click")
	$(`#${id} .overlay-close`).on("click", function () {
		$(`#${id}`).removeClass("show")
		callback()
	})
}

class UploadOverlay {
	constructor(file_input_selector) {
		this.file_input_selector = file_input_selector
		this.storedFiles = []

		$(".upload-overlay .upload-drop-zone").on("dragover", (event) => {
			event.preventDefault()
			$(".upload-overlay .upload-drop-zone").addClass("drag-over")
		})

		$(".upload-overlay .upload-drop-zone").on("dragleave", (event) => {
			event.preventDefault()
			$(".upload-overlay .upload-drop-zone").removeClass("drag-over")
		})

		$(".upload-overlay .upload-drop-zone").on("click", () => {
			$(this.file_input_selector).val("").click()
		})

		$(this.file_input_selector).on("click", (event) => {
			event.stopPropagation()
		})
	}

	assignFile() {
		$(this.file_input_selector).val("")
		if (this.storedFiles.length == 0) return
		const dataTransfer = new DataTransfer()
		this.storedFiles.forEach((file) => dataTransfer.items.add(file))
		$(this.file_input_selector)[0].files = dataTransfer.files
	}

	removeFile(index) {
		if (index >= 0 && index < this.storedFiles.length) {
			this.storedFiles.splice(index, 1)
			this.assignFile()
		}
	}

	removeAllFiles() {
		this.storedFiles = []
		this.assignFile()
	}

	open() {
		return new Promise((resolve) => {
			const handleFile = (file) => {
				if (file && file.type.startsWith("image/")) {
					const reader = new FileReader()

					reader.onload = async (event) => {
						const previewImage = $(".upload-overlay .upload-preview-image")
						const previewContainer = $(".upload-overlay .upload-preview-container")

						previewImage.attr("src", event.target.result)
						previewImage.off("load")
						previewImage.on("load", () => {
							$(".upload-overlay .upload-preview-zone").addClass("show")
							$(".upload-overlay .upload-drop-zone").removeClass("show")

							let horizontalFit = false
							if (previewImage.width() > previewImage.height()) {
								horizontalFit = true
								previewImage.addClass("horizontal-fit")
								previewImage.removeClass("vertical-fit")
							} else {
								previewImage.addClass("vertical-fit")
								previewImage.removeClass("horizontal-fit")
							}

							previewImage.css("transform", `translate(-50%, -50%) scale(1)`)

							let imgX = 0
							let imgY = 0
							let scale = 1
							const baseWidth = previewImage.width()
							const baseHeight = previewImage.height()

							previewImage.off("mousedown")
							previewImage.on("mousedown", (evt) => {
								let startX = evt.clientX - imgX
								let startY = evt.clientY - imgY
								previewImage.addClass("dragging")

								$(document.body).on("mousemove", (ev) => {
									let newX = ev.clientX - startX
									let newY = ev.clientY - startY

									// Prevent moving outside container
									const maxX = (previewImage.width() * scale - previewContainer.width()) / 2
									const maxY = (previewImage.height() * scale - previewContainer.height()) / 2

									imgX = Math.min(maxX, Math.max(-maxX, newX))
									imgY = Math.min(maxY, Math.max(-maxY, newY))

									previewImage.css("transform", `translate(calc(-50% + ${imgX}px), calc(-50% + ${imgY}px)) scale(${scale})`)
								})

								$(document.body).on("mouseup", () => {
									previewImage.removeClass("dragging")
									$(document.body).off("mousemove")
									$(document.body).off("mouseup")
								})
							})

							$("#zoom-slider").val(1)
							$("#zoom-slider").off("input")
							$("#zoom-slider").on("input", (evt) => {
								const prevScale = scale
								scale = parseFloat(evt.target.value)

								const scaleRatio = scale / prevScale
								imgX *= scaleRatio
								imgY *= scaleRatio

								const maxX = Math.max(0, (baseWidth * scale - previewContainer.width()) / 2)
								const maxY = Math.max(0, (baseHeight * scale - previewContainer.height()) / 2)

								imgX = Math.min(maxX, Math.max(-maxX, imgX))
								imgY = Math.min(maxY, Math.max(-maxY, imgY))

								previewImage.css("transform", `translate(calc(-50% + ${imgX}px), calc(-50% + ${imgY}px)) scale(${scale})`)
							})

							$("#confirm-upload").off("click")
							$("#confirm-upload").on("click", () => {
								$(".upload-overlay").removeClass("show")
								this.storedFiles.push(file)
								this.assignFile()
								resolve({
									src: event.target.result,
									scale: scale,
									imgX: imgX,
									imgY: imgY,
									horizontalFit: horizontalFit,
									index: this.storedFiles.length - 1
								})
							})
						})
					}

					reader.readAsDataURL(file)
				}
			}

			$(".upload-overlay .upload-drop-zone").addClass("show")
			$(".upload-overlay .upload-preview-zone").removeClass("show")
			$(".upload-overlay").addClass("show")

			$(".upload-overlay .overlay-close").off("click")
			$(".upload-overlay .overlay-close").on("click", () => {
				$(`.upload-overlay`).removeClass("show")
				this.assignFile()
				resolve(false)
			})

			$(this.file_input_selector).off("change")
			$(this.file_input_selector).on("change", (e) => {
				const file = e.target.files[0]
				handleFile(file)
			})

			$(".upload-overlay .upload-drop-zone").off("drop")
			$(".upload-overlay .upload-drop-zone").on("drop", (event) => {
				event.preventDefault()
				$(".upload-overlay .upload-drop-zone").removeClass("drag-over")

				const file = event.originalEvent.dataTransfer.files[0]
				handleFile(file)
			})
		})
	}
}

function delete_cookie(name) {
	document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"
}

function detect_form_changes(form_selector, select_elements = "input", changed_callback = () => {}, unchanged_callback = () => {}) {
	let form = $(form_selector)
	let original_data = form.serialize()

	$(`${form_selector}`).on("change input", select_elements, function () {
		if (form.serialize() === original_data) {
			unchanged_callback()
		} else {
			changed_callback()
		}
	})

	return function () {
		form = $(form_selector)
		original_data = form.serialize()
		unchanged_callback()
	}
}

function toRMFormat(value) {
	return value.toFixed(2)
}

function toRatingFormat(value) {
	return value.toFixed(1)
}

function toDateFormat(dateStr) {
	const months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"]
	const [year, month, day] = dateStr.split("-")
	return `${day} ${months[parseInt(month) - 1]} ${year}`
}

function timeAgo(datetime) {
	const formattedDatetime = datetime.replace(" ", "T")
	const now = new Date()
	const past = new Date(formattedDatetime)

	if (isNaN(past)) return "Invalid date"

	const seconds = Math.floor((now - past) / 1000)

	const intervals = {
		year: 31536000,
		month: 2592000,
		week: 604800,
		day: 86400,
		hour: 3600,
		minute: 60,
		second: 1
	}

	for (const unit in intervals) {
		const count = Math.floor(seconds / intervals[unit])
		if (count >= 1) {
			return `${count} ${unit}${count > 1 ? "s" : ""} ago`
		}
	}

	return "just now"
}

function calculatePollPercentages(voteCounts) {
	const totalVotes = voteCounts.reduce((a, b) => a + b, 0)
	if (totalVotes === 0) return voteCounts

	let percentages = []
	let sum = 0

	voteCounts.forEach((votes) => {
		const percent = Math.round((votes / totalVotes) * 100)
		percentages.push(percent)
		sum += percent
	})

	if (sum !== 100) {
		const maxIndex = percentages.indexOf(Math.max(...percentages))
		percentages[maxIndex] += 100 - sum
	}

	return percentages
}

function showToast(message) {
	const toast = $('<div class="toast"></div>').text(message)
	$(".toast-container").append(toast)
	setTimeout(() => toast.addClass("show"), 100)
	setTimeout(() => {
		toast.removeClass("show")
		setTimeout(() => toast.remove(), 500)
	}, 4000)
}

export {
	confirmation,
	show_form_error,
	open_overlay,
	UploadOverlay,
	delete_cookie,
	detect_form_changes,
	toRMFormat,
	toRatingFormat,
	toDateFormat,
	timeAgo,
	calculatePollPercentages,
	showToast
}
