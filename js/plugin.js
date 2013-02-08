(function ($) {
	"use strict";
	$(function () {
		$("body").on('click','.menu-item a', function(){
			var trackingCode = $(this).next(".ga-tracking");
			if(trackingCode.length > 0){
				var t1 = trackingCode.data("tracking-1"),
				    t2 = trackingCode.data("tracking-2"),
				    t3 = trackingCode.data("tracking-3"),
				    t4 = trackingCode.data("tracking-4");
				_gaq.push([t1,t1,t3,t4]);
			}

		});
	});
}(jQuery));
