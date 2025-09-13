import { toRMFormat, showToast, confirmation } from "../utils.js"

$(document).ready(async function () {
	$("input[name='filter_order']").on("change", function () {
		if ($(this).val() === "") {
			window.location.href = "/order-history"
		} else {
			window.location.href = "/order-history?status=" + $(this).val()
		}
	})

	const containerElement = $("main")
	let fetching = false
	let last = false
	let page = 2
	function checkScroll() {
		const lastElement = $(".order-history-container").last()
		if (lastElement.length && $(window).scrollTop() + $(window).height() >= lastElement.offset().top && !last) {
			getTransactions()
		}
	}

	async function getTransactions() {
		if (fetching) return

		fetching = true
		containerElement.append("<div id='loading-order' class='container'>Loading more...</div>")

		const response = await $.ajax({
			url: "/backend/customer/do.order.php",
			type: "POST",
			data: {
				status: $("#filter-order-container").data("status"),
				page: page
			}
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.success && response.result) {
			if (response.last) {
				last = true
			} else {
				page++
			}
			for (const order of response.result) {
				containerElement.append(`
					<div class="container order-history-container" data-order-id="${order.id}">
    				    <span class="title">${order.id}</span>
    				    <span class="float-right status ${order.status_code}"></span>
    				    <div class="timestamp">${order.creation_time}</div>
    				    <div class="price">${toRMFormat(order.total_price)}</div>
    				    <div class="buttons-container">
    				        <a href="/order-detail?id=${order.id}">
    				            <button class="button-blue">Details</button>
    				        </a>
    				        <div>
    				            <button data-button-type="cancel" class="button-red">Cancel</button>
    				            <button data-button-type="reorder" class="button-yellow">Reorder</button>
    				        </div>
    				    </div>
    				</div>	
				`)
			}
		}

		$("#loading-order").remove()
		fetching = false

		checkScroll()
	}

	containerElement.on("click", "button[data-button-type='cancel']", async function () {
		$(this).prop("disabled", true)
		if (await confirmation("Are you sure you want to cancel this order?", "Yes", "No")) {
			const response = await $.ajax({
				url: "/backend/customer/do.cancel-order.php",
				type: "POST",
				data: {
					order_id: $(this).closest(".order-history-container").data("order-id")
				}
			})

			if (response.login) {
				window.location.href = "/login"
				return
			}

			if (response.errors?.order_id) {
				showToast(response.errors.order_id)
				$(this).prop("disabled", false)
				return
			}

			if (response.success) {
				showToast("Order cancelled successfully")

				if ($("#filter-order-container").data("status") === "all") {
					const statusElement = $(this).closest(".order-history-container").find(".status")
					statusElement.removeClass("preparing")
					statusElement.removeClass("unpaid")
					statusElement.addClass("canceled")
				} else {
					$(this).closest(".order-history-container").remove()
				}
			}
		}
		$(this).prop("disabled", false)
		checkScroll()
	})

	containerElement.on("click", "button[data-button-type='reorder']", async function () {
		$(this).prop("disabled", true)
		const response = await $.ajax({
			url: "/backend/customer/do.reorder.php",
			type: "POST",
			data: {
				order_id: $(this).closest(".order-history-container").data("order-id")
			}
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.errors?.order_id) {
			showToast(response.errors.order_id)
			$(this).prop("disabled", false)
			return
		}

		if (response.errors?.no_product) {
			showToast(response.errors.no_product)
			$(this).prop("disabled", false)
			return
		}

		if (response.success) {
			window.location.href = "/cart"
		}
		$(this).prop("disabled", false)
	})

	// check if last
	const responseLast = await $.ajax({
		url: "/backend/customer/do.order.php",
		type: "POST",
		data: {
			status: $("#filter-order-container").data("status"),
			page: 1
		}
	})
	if (responseLast.last) {
		last = true
	}

	checkScroll()
	$(window).on("scroll resize", checkScroll)
})
