$(document).ready(function () {
	$("form").on("submit", function (e) {
		$("<input>").attr("type", "hidden").attr("name", "sort").val($("[data-sort].selected").data("sort")).appendTo(this)
		$("<input>").attr("type", "hidden").attr("name", "desc").val($("[data-sort].selected").hasClass("desc") ? "1" : "").appendTo(this)

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
})
