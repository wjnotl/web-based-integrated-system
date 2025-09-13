import { toRMFormat } from "../utils.js"

$(document).ready(async function () {
	$("input[name='filter_transaction']").on("change", function () {
		if ($(this).val() === "") {
			window.location.href = "/wallet"
		} else {
			window.location.href = "/wallet?time=" + $(this).val()
		}
	})

	const containerElement = $("#wallet-transaction-container")
	let fetching = false
	let last = false
	let page = 2
	function checkScroll() {
		const lastElement = $(".transaction-container").last()
		if (lastElement.length && $(window).scrollTop() + $(window).height() >= lastElement.offset().top && !last) {
			getTransactions()
		}
	}

	async function getTransactions() {
		if (fetching) return

		fetching = true
		containerElement.append("<div id='loading-transaction'>Loading more...</div>")

		const response = await $.ajax({
			url: "/backend/customer/do.transaction.php",
			type: "POST",
			data: {
				time: containerElement.data("filter-transaction"),
				page: page
			}
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.success) {
			if (response.last) {
				last = true
			} else {
				page++
			}
			for (const transaction of response.result) {
				if (containerElement.find(`.transaction-container[data-transaction-id='${transaction.id}']`).length === 0) {
					containerElement.append(`
						<${transaction.order_id ? `a href=/order-detail?id=${transaction.order_id}` : `div`} class=transaction-container data-transaction-id=${
						transaction.id
					}>
							<div>
								<div class="title">${transaction.detail}</div>
								<div class="timestamp">${transaction.creation_time}</div>
							</div>
							<div class="price ${transaction.value < 0 ? "minus" : ""}">${toRMFormat(Math.abs(transaction.value))}</div>
						</${transaction.order_id ? `a` : `div`}>
					`)
				}
			}
		}

		$("#loading-transaction").remove()
		fetching = false

		checkScroll()
	}

	// check if last
	const responseLast = await $.ajax({
		url: "/backend/customer/do.transaction.php",
		type: "POST",
		data: {
			time: containerElement.data("filter-transaction"),
			page: 1
		}
	})
	if (responseLast.last) {
		last = true
	}

	checkScroll()
	$(window).on("scroll resize", checkScroll)
})
