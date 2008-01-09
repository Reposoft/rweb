
(function($) {
	
	/**
	 * Format tag text as a file size, with B, kB or MB.
	 * The element text() must be a number.
	 * @return true if contents were updated in all elements
	 */
	$.fn.byteformat = function() {
		/**
		 * @param {Number} b bytes
		 * @return {String} formatted size
		 */
		var format = function(b) {
			if (b < 1000) return b + ' B';
			var f = 1.0 * b / 1024;
			if (f < 0.995) return f.toPrecision(2) + ' kB';
			if (f < 999.5) return f.toPrecision(3) + ' kB';
			f = f / 1024;
			if (f < 0.995) return f.toPrecision(2) + ' MB';
			if (f < 99.95) return f.toPrecision(3) + ' MB';
			return f.toFixed(0) + ' MB';
		};
		return this.each(function() {
			var b = Number($(this).text());
			if (b == Number.NaN) return false;
			return $(this).html(format(b));				
		});
	};
	
})(jQuery);
