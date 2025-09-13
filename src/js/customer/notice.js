import { timeAgo, calculatePollPercentages } from "../utils.js"

$(document).ready(async function () {
	$("input[type='radio']").each(function () {
		$(this).data("was-checked", $(this).prop("checked"))
	})

	$("input[type='radio']").on("click", function () {
		const wasChecked = $(this).data("was-checked")

		$(this).closest("form").find("input[type='radio']").data("was-checked", false)

		if (wasChecked) {
			$(this).prop("checked", false).trigger("change")
		} else {
			$(this).data("was-checked", true)
		}
	})

	$("input[type='radio']").on("change", async function () {
		const form = $(this).closest("form")
		const inputChecked = form.find("input[type='radio']:checked")

		const response = await $.ajax({
			url: "/backend/customer/do.notice-vote.php",
			type: "POST",
			data: {
				notice_id: $(this).closest(".notice-container").data("notice-id"),
				option_id: inputChecked.length ? inputChecked.val() : ""
			}
		})

		if (response.login) {
			window.location.href = "/login"
			return
		}

		if (response.success) {
			const counts = []
			form.find(".poll-option").each(function () {
				let count = parseInt($(this).data("vote-count"))
				if ($(this).data("voted")) {
					count--
				}
				if ($(this).find("input[type='radio']").prop("checked")) {
					count++
				}
				counts.push(count)
			})

			const percentages = calculatePollPercentages(counts)
			form.find(".poll-option").each(function (index) {
				const percentage = `${percentages[index]}%`
				const option = $(this)
				option.find(".poll-result-value").text(percentage)
				option.find(".poll-result-percentage").css("width", percentage)
			})
		}
	})

	const containerElement = $("main")
	let fetching = false
	let last = false
	let page = 2
	function checkScroll() {
		const lastElement = $(".notice-container").last()
		if (lastElement.length && $(window).scrollTop() + $(window).height() >= lastElement.offset().top && !last) {
			getNotices()
		}
	}

	async function getNotices() {
		if (fetching) return

		fetching = true
		containerElement.append("<div id='loading-notice' class='container'>Loading more...</div>")

		const response = await $.ajax({
			url: "/backend/customer/do.notice.php",
			type: "POST",
			data: {
				page: page
			}
		})

		if (response.success) {
			if (response.last) {
				last = true
			} else {
				page++
			}

			for (const notice of response.result) {
				if (containerElement.find(`.notice-container[data-notice-id='${notice.id}']`).length === 0) {
					let documentString = `
						<div class="container notice-container" data-notice-id="${notice.id}">
    					    <div class="title">${notice.title}</div>
    					    <div class="date">${timeAgo(notice.creation_time)}</div>
							${notice.content ? `<div class="content">${notice.content}</div>` : ""}
							${notice.photo ? `<img class="image" src="/uploads/notice/${notice.photo}" alt="${notice.title}">` : ""}	    
					`

					if (notice.poll_options.length) {
						documentString += "<form class='poll-container'>"
						for (let i = 0; i < notice.poll_options.length; i++) {
							const option = notice.poll_options[i]
							documentString += `
								<label class="poll-option" data-vote-count="${notice.poll_vote_counts[i]}" data-voted="${option.voted}">
									<input class="poll-input" type="radio" name="option" value="${option.id}" ${option.voted ? "checked" : ""}>
									<div class="poll-input-button"></div>
									<div class="poll-input-box">
										<div class="poll-text-container">
											<div class="poll-text">${option.text}</div>
											<div class="poll-result-value">${!response.login ? notice.poll_vote_percentages[i] + "%" : ""}</div>
										</div>
										<div class="poll-result-percentage" style="width: ${!response.login ? notice.poll_vote_percentages[i] : 0}%;"></div>
									</div>
								</label>
							`
						}
						documentString += "</form>"
					}

					documentString += "</div>"

					containerElement.append(documentString)
				}
			}
		}

		$("#loading-notice").remove()
		fetching = false

		checkScroll()
	}

	// check if last
	const responseLast = await $.ajax({
		url: "/backend/customer/do.notice.php",
		type: "POST",
		data: {
			page: 1
		}
	})
	if (responseLast.last) {
		last = true
	}

	checkScroll()
	$(window).on("scroll resize", checkScroll)
})
