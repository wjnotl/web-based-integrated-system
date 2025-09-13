$(document).ready(function () {
	$("input[name='filter_voucher']").on("change", function () {
        if ($(this).val() === "") {
            window.location.href = "/voucher"
		} else {
			window.location.href = "/voucher?value=" + $(this).val()
		}
	})
})
