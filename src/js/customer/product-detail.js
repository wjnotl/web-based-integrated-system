import { confirmation, show_form_error, showToast, timeAgo } from "../utils.js"

$(document).ready(async function () {
	$("#input-grid-container .custom-radio-container input[type='radio']").on("click", function () {
		const wasChecked = $(this).data("was-checked")

		$(this).closest(".custom-radio-container").find("input[type='radio']").data("was-checked", false)

		if (wasChecked) {
			$(this).prop("checked", false).trigger("change")
		} else {
			$(this).data("was-checked", true)
		}
	})

	$("#product-gallery-container img").on("click", function () {
		$("#product-gallery-container img.selected").removeClass("selected")
		$(this).addClass("selected")
		$("#product-picture").attr("src", $(this).attr("src"))
	})

	const product_colour = $("#input-grid-container .custom-radio-container input[type='radio'][name='product_colour']")
	const product_size = $("#input-grid-container .custom-radio-container input[type='radio'][name='product_size']")

	product_colour.on("change", function () {
		disableVariants("colour")
	})

	product_size.on("change", function () {
		disableVariants("size")
	})

	async function disableVariants(changeType) {
		const checked_colour = product_colour.filter(":checked")
		const checked_size = product_size.filter(":checked")

		const availableSizes = (checked_colour.data("sizes") || "").split(",")
		const availableColours = (checked_size.data("colours") || "").split(",")

		$("#product-stock-count").attr("data-stock", "")
		$("#add-to-cart-button").prop("disabled", true)

		// if both colour and size are selected or neither is selected, remove all disabled variants
		if ((product_colour.is(":checked") && product_size.is(":checked")) || (!product_colour.is(":checked") && !product_size.is(":checked"))) {
			product_colour.prop("disabled", false)
			product_size.prop("disabled", false)
			if (changeType === "colour") {
				product_size.each(function () {
					if (!availableSizes.includes($(this).val()) && $(this).prop("checked")) {
						$(this).prop("checked", false).trigger("change")
						$(this).data("was-checked", false)
					}
				})
			} else if (changeType === "size") {
				product_colour.each(function () {
					if (!availableColours.includes($(this).val()) && $(this).prop("checked")) {
						$(this).prop("checked", false).trigger("change")
						$(this).data("was-checked", false)
					}
				})
			}

			// if both still checked, show stock count
			if (product_colour.is(":checked") && product_size.is(":checked")) {
				$("#product-stock-count").attr("data-stock", "getting")

				const response = await $.ajax({
					url: "/backend/customer/do.stock.php",
					method: "POST",
					data: {
						product_id: $("#product-info-container").data("product-id"),
						colour: checked_colour.val(),
						size: checked_size.val()
					}
				})

				if (response.success) {
					$("#product-stock-count").attr("data-stock", response.stock)
					$("#add-to-cart-button").prop("disabled", false)
				} else {
					$("#product-stock-count").attr("data-stock", "failed")
				}
			}
		}
		// if only colour is selected, disable all unavailable sizes
		else if (product_colour.is(":checked")) {
			product_size.each(function () {
				const available = availableSizes.includes($(this).val())
				$(this).prop("disabled", !available)
				if (!available && $(this).prop("checked")) {
					$(this).prop("checked", false)
					$(this).data("was-checked", false)
				}
			})
		}
		// if only size is selected, disable all unavailable colours
		else if (product_size.is(":checked")) {
			product_colour.each(function () {
				const available = availableColours.includes($(this).val())
				$(this).prop("disabled", !available)
				if (!available && $(this).prop("checked")) {
					$(this).prop("checked", false)
					$(this).data("was-checked", false)
				}
			})
		}
	}

	$("#product-info-container form").on("submit", async function (e) {
		e.preventDefault()

		const response = await $.ajax({
			url: "/backend/customer/do.add-to-cart.php",
			type: "POST",
			data: $(this).serialize() + "&product_id=" + $("#product-info-container").data("product-id")
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.stock != null) {
			$("#product-stock-count").attr("data-stock", response.stock)
		}

		show_form_error(response.errors)

		if (response.success) {
			showToast("Item added to cart")
		}
	})

	$("#add-to-favourite-button").on("click", async function () {
		const response = await $.ajax({
			url: "/backend/customer/do.favourite.php",
			type: "POST",
			data: {
				product_id: $("#product-info-container").data("product-id"),
				action: $(this).hasClass("favourite") ? "remove" : "add"
			}
		})

		if (response.login) {
			showToast("Please login before adding to favourite")
			return
		}

		if (response.errors?.product_id) {
			showToast(response.errors.product_id)
			return
		}

		if (response.success) {
			if (response.favourite === "added") {
				$(this).addClass("favourite")
				showToast("Added to favourite")
			} else if (response.favourite === "removed") {
				$(this).removeClass("favourite")
				showToast("Removed from favourite")
			}
		}
	})

	let last = false
	let page = 2
	let fetching = false
	function forceGetReviews() {
		last = true
		fetching = false
		containerElement.append("<div id='loading-review'>Loading...</div>")
		containerElement.find(".review-container").remove()
		page = 1
		last = false
		getReviews(false)
	}

	const containerElement = $("#product-review-container")
	async function getReviews(loadingElement = true) {
		if (fetching) return

		fetching = true
		if (loadingElement && fetching) {
			containerElement.append("<div id='loading-review'>Loading more...</div>")
		}

		const response = await $.ajax({
			url: "/backend/customer/do.review.php",
			type: "POST",
			data: {
				star: $("#filter-review-container input[type='radio'][name='filter_review']:checked").val(),
				page: page,
				product_id: $("#product-info-container").data("product-id"),
				account_id: containerElement.data("account-id") || ""
			}
		})

		if (response.errors?.product_id) {
			showToast(response.errors.product_id)
			return
		}

		if (response.success && fetching) {
			if (response.last) {
				last = true
			} else {
				page++
			}
			for (const review of response.result) {
				if (fetching && containerElement.find(`.review-container[data-account-id='${review.account_id}']`).length === 0) {
					containerElement.append(`
						<div class="review-container" data-account-id="${review.account_id}" data-likes="${review.likes}" data-liked-by-me="${review.liked_by_me}">
    					    <img class="pfp-image" src="${review.photo ? `/uploads/account/${review.photo}` : "/src/img/icon/pfp.png"}" alt="Profile">
    					    <div class="user-info">
    					        <span class="name">${review.name}</span>
    					        <div data-rating="${review.rating}" class="rating-stars-container">
    					            <div class="empty-star">
    					                <div class="star-setter">
    					                    <div class="star"></div>
    					                </div>
    					            </div>
    					            <div class="empty-star">
    					                <div class="star-setter">
    					                    <div class="star"></div>
    					                </div>
    					            </div>
    					            <div class="empty-star">
    					                <div class="star-setter">
    					                    <div class="star"></div>
    					                </div>
    					            </div>
    					            <div class="empty-star">
    					                <div class="star-setter">
    					                    <div class="star"></div>
    					                </div>
    					            </div>
    					            <div class="empty-star">
    					                <div class="star-setter">
    					                    <div class="star"></div>
    					                </div>
    					            </div>
    					        </div>
    					        <span class="log-entry">${timeAgo(review.creation_time)}</span>
    					        <p class="comment">${review.content}</p>
    					        <div class="helpful">
    					            <div class="thumb-up ${review.liked_by_me ? "selected" : ""}"></div>
    					            <div class="thumb-up-count">${review.likes}</div>
    					        </div>
    					    </div>
    					</div>	
					`)
				}
			}
		}

		$("#loading-review").remove()
		fetching = false

		checkScroll()
	}

	function checkScroll() {
		const lastElement = $(".review-container").last()

		if (lastElement.length && $(window).scrollTop() + $(window).height() >= lastElement.offset().top && !last) {
			getReviews()
		}
	}

	$("#filter-review-container input[type='radio'][name='filter_review']").on("change", forceGetReviews)

	$("#product-review-container").on("click", ".thumb-up", async function () {
		const reviewContainer = $(this).closest(".review-container")

		const response = await $.ajax({
			url: "/backend/customer/do.like-review.php",
			type: "POST",
			data: {
				product_id: $("#product-info-container").data("product-id"),
				reviewer_id: reviewContainer.data("account-id"),
				action: $(this).hasClass("selected") ? "remove" : "add"
			}
		})

		if (response.login) {
			showToast("This action requires login")
			return
		}

		if (response.errors?.product_id) {
			showToast(response.errors.product_id)
			return
		}

		if (response.errors?.reviewer_id) {
			showToast(response.errors.reviewer_id)
			return
		}

		if (response.success) {
			$(this).toggleClass("selected")

			if (reviewContainer.data("liked-by-me") == "1") {
				if ($(this).hasClass("selected")) {
					reviewContainer.find(".thumb-up-count").text(parseInt(reviewContainer.data("likes")))
				} else {
					reviewContainer.find(".thumb-up-count").text(parseInt(reviewContainer.data("likes")) - 1)
				}
			} else {
				if ($(this).hasClass("selected")) {
					reviewContainer.find(".thumb-up-count").text(parseInt(reviewContainer.data("likes")) + 1)
				} else {
					reviewContainer.find(".thumb-up-count").text(parseInt(reviewContainer.data("likes")))
				}
			}
		}
	})

	$("#delete-review-button").on("click", async function () {
		if (await confirmation("Are you sure you want to delete this review?", "Yes", "No")) {
			const response = await $.ajax({
				url: "/backend/customer/do.remove-review.php",
				type: "POST",
				data: {
					product_id: $("#product-info-container").data("product-id")
				}
			})

			if (response.login) {
				showToast("This action requires login")
				return
			}

			if (response.errors?.product_id) {
				showToast(response.errors.product_id)
				return
			}

			if (response.success) {
				window.location.reload()
			}
		}
	})

	$("#own-review-container .empty-star").on("click", function () {
		const rating = $(this).index() + 1
		$(this).closest(".rating-stars-container").attr("data-rating", rating)
	})

	$("#send-review-button").on("click", async function () {
		const response = await $.ajax({
			url: "/backend/customer/do.send-review.php",
			type: "POST",
			data: {
				product_id: $("#product-info-container").data("product-id"),
				own_comment: $("#own-review-input").val(),
				own_rating: $("#own-review-container .rating-stars-container").attr("data-rating")
			}
		})

		if (response.login) {
			showToast("This action requires login")
			return
		}

		if (response.errors?.product_id) {
			showToast(response.errors.product_id)
			return
		}

		show_form_error(response.errors)

		if (response.success) {
			window.location.reload()
		}
	})

	// check if last
	const responseLast = await $.ajax({
		url: "/backend/customer/do.review.php",
		type: "POST",
		data: {
			product_id: $("#product-info-container").data("product-id"),
			page: 1
		}
	})
	if (responseLast.last) {
		last = true
	}

	checkScroll()
	$(window).on("scroll resize", checkScroll)
})
