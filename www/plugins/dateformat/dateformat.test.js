
test('format', function testFormat() {
	var d = "2006-09-07T12:00:00.000Z";
	var f = $.fn.dateformat.format(d);
	equals("den 7 september 2006 14:00:00", f, "Should make an iso date with no milliseconds.");
});

test('formatElement', function testFormatElement() {
	var e = document.createElement('span');
	e.innerHTML = "2006-09-07T12:00:00.000Z";
	$.fn.dateformat.formatElement(e);
	equals("den 7 september 2006 14:00:00", e.innerHTML, "Should format the date of the element that has not been added to the DOM");
});

test('formatAll', function testFormatAll() {
	// The plugin has already executed, through onload
	var formatted1 = $('#d1').text();
	var formatted2 = $('#d2').text();
	equals($.fn.dateformat.format('2006-09-07T16:00:00.123456Z'), formatted1, "Should format all 'datetime' texts on load");
	equals($.fn.dateformat.format('2006-09-06T20:30:59.000Z'), formatted2, "Should format all 'datetime' texts on load");
});

test('formatEmpty', function testFormatEmpty() {
	var e = document.createElement('span');
	e.innerHTML = "";
	$.fn.dateformat.formatElement(e);
	ok("" == e.innerHTML);
});

test('formatTwice', function testFormatTwice() {
	var e = document.createElement('span');
	var t = "2006-09-07T12:00:00.000Z";
	e.innerHTML = t;
	$.fn.dateformat.formatElement(e);
	// dateformat should set the class 'formatted' when successful
	// does not work in IE // assert('formatted', e.getAttribute('class'));
	ok($(e).is('.formatted'));
	// now try to format the same tag again
	var d = e.innerHTML;
	ok(t != d);
	$.fn.dateformat.formatElement(e);	
	ok(e.innerHTML, d);
});

test('formatJqueryPlugin', function() {
	var f = $('<span/>').text("2006-09-07T12:00:00.000Z").dateformat();
	ok(typeof f != 'undefined', 'should conform to chained api convention');
	//ok(f.is('.formatted')); // TODO change to dateformatted
});

test('formatGet', function() {
	var f = $('<span/>').text("2006-09-07T12:00:00.000Z");
	
});
