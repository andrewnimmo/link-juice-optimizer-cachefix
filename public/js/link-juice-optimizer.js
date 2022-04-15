(function ($) {
	'use strict';

	function ljo_decode(str) {
		return unescape(window.atob(decodeURIComponent(str)));
	}

	$(document).ready(function () {
		var ljo_clase = php_vars.ljo_clase;
		$("." + ljo_clase + " span, span." + ljo_clase + ", a." + ljo_clase).click(function (event) {
			if ($(this).attr("data-loc") !== undefined && $(this).attr("data-loc") !== false) {
				if ($(this).attr("data-window") == 'new') {
					window.open(ljo_decode($(this).attr("data-loc")), '_blank');
				} else {
					window.location.href = ljo_decode($(this).attr("data-loc"));
				}
			}
		});
	});

})(jQuery);

function ljo_open(str, target) {
	if (target == 'new') {
		window.open(str, '_blank');
	} else {
		window.location.href = str;
	}
}