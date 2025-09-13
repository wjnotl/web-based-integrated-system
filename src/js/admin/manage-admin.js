import { confirmation, showToast } from "../utils.js"

$(document).ready(function () {
	$("form").on("submit", function (e) {
		$("<input>").attr("type", "hidden").attr("name", "sort").val($("[data-sort].selected").data("sort")).appendTo(this)
		$("<input>")
			.attr("type", "hidden")
			.attr("name", "desc")
			.val($("[data-sort].selected").hasClass("desc") ? "1" : "")
			.appendTo(this)

		$(this)
			.find("input")
			.each(function () {
				if (!$(this).val()) {
					$(this).removeAttr("name")
				}
			})
	})

	$("input").on("change", function () {
		$("form").submit()
	})

	$("[data-sort]").on("click", function () {
		if ($(this).hasClass("selected")) {
			if ($(this).hasClass("desc")) {
				$("[data-sort]").removeClass("desc")
			} else {
				$("[data-sort]").addClass("desc")
			}
		} else {
			$("[data-sort]").removeClass("selected")
			$("[data-sort]").removeClass("desc")
			$(this).addClass("selected")
		}
		$("form").submit()
	})

	$(".delete-admin-button").on("click", async function () {
		$(this).prop("disabled", true)
		$(this).text("Deleting...")
		if (await confirmation("Are you sure you want to delete this admin?", "Yes")) {

			const response = await $.ajax({
				url: "/backend/admin/do.delete-admin.php",
				method: "POST",
				data: {
					id: $(this).data("id")
				}
			})

			if (response.errors?.id) {
				showToast(response.errors.id)
			}

			if (response.success) {
				window.location.reload()
				return
			}

		}
		$(this).prop("disabled", false)
		$(this).text("Delete")
	})
})
