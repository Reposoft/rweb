/* Show hint about lock age (c) Staffan Olsson repos.se
  Requires the dateformat plugin.
 */
(function lockAge() {
	// interval, hours
	var old = [24, 48, 7 * 24, 31 * 24];
	// the string to append to lock- class for each interval entry passed
	var append = 'o';
	// timestamp to compare with
	var now = new Date();
	
	/*$.fn.lockAge = function() {
		alert(this.length);
		$(this).each(function() {
			var datetime = $('.datetime', this).text();
			alert(datetime);
		});
	};
	
	$(document).ready(function() {
		$('.lock').lockAge();
	}); */
	
	//$('.lock').live('repos-dateformat-done', function(ev, date) {
	// Until jQuery 1.3.3 live events do not include data
	$(document).bind('repos-dateformat-done', function(ev, date) {
		// these two lines would not be needed for the live event bind above
		var lock = $(ev.target).parent();
		if (!lock.is('.lock')) return;
		var age = now.valueOf() - date.valueOf();
		var o = '';
		for (var i = 0; i < old.length; i++) {
			if (age > old[i] * 3600 * 1000) o += append;
		}
		if (o) lock.addClass('lock-' + o);
		return true; // allow further processing (or so I think, see docs for live events)
	});

})();
