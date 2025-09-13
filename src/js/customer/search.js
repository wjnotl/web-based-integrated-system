$(document).ready(function () {
	$("#search-form").on("submit", function (e) {
		$("<input>").attr("type", "hidden").attr("name", "keyword").val($("#header-search-form input[name=keyword]").val()).appendTo("#search-form")
		$(this)
			.find("input")
			.each(function () {
				if (!$(this).val()) {
					$(this).removeAttr("name")
				}
			})
	})

	$("#header-search-form").on("submit", function (e) {
		e.preventDefault()
		$("#category-filter-container input[type=checkbox]").prop("checked", false)
		$("#search-form").submit()
    })
    
    $("#sort-result-select input").on("change", function () {
        if ($(this).prop("checked")) {
            $("#search-form").submit()
        }
    })
})
