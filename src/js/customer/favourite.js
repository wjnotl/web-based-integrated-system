import { confirmation, showToast } from "../utils.js"

$(document).ready(function () {
	$(".remove-button").on("click", async function (event) {
		event.preventDefault()
        
		if (await confirmation("Are you sure you want to remove this item from favourite?", "Yes", "No")) {
			const response = await $.ajax({
				url: "/backend/customer/do.favourite.php",
				type: "POST",
				data: {
					product_id: $(this).closest(".product-container").data("product-id"),
					force_remove: "1",
					action: "remove"
				}
			})

			if (response.login) {
				window.location.href = "/login"
				return
			}

			if (response.errors?.product_id) {
				showToast(response.errors.product_id)
				return
			}

			if (response.errors?.remove) {
				window.location.reload()
				return
			}

			if (response.success && response.favourite === "removed") {
				$(this).closest(".product-container").remove()
				showToast("Removed from favourite")
			}
		}
	})
})
