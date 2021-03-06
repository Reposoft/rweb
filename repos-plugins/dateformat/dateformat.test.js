
test('format', function testFormat() {
	var d = "1970-01-01T12:00:00.000Z";
	var f = $.fn.dateformat.format(d);
	equals(new Date(12 * 3600 * 1000).toLocaleString(),
			f, "Should make an iso date with no milliseconds.");
});

test('formatElement', function testFormatElement() {
	var e = document.createElement('span');
	e.innerHTML = "1970-01-01T00:00:00.000Z";
	$.fn.dateformat.formatElement(e);
	equals(new Date(0).toLocaleString(), e.innerHTML, 
			"Should format the date of the element that has not been added to the DOM");
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
	ok($(e).is('.dateformatted'));
	// now try to format the same tag again
	var d = e.innerHTML;
	ok(t != d);
	$.fn.dateformat.formatElement(e);	
	ok(e.innerHTML, d);
});

test('formatInvalid', function() {
	// not iso date and not .dateformatted
	var d = $('<span>Thu Jan 1 01:00:05 1970</span>').addClass('datetime');
	try {
		d.dateformat();
		ok(false, 'Should throw exception for invalid datetime');
	} catch(e) {
		equals(e.message, 'Failed to parse date "Thu Jan 1 01:00:05 1970"');
	}
	equals(d.text(), 'Thu Jan 1 01:00:05 1970', 'Contents should be unchanged');
});

test('formatJqueryPlugin', function() {
	var f = $('<span/>').text("2006-09-07T12:00:00.000Z").dateformat();
	ok(typeof f != 'undefined', 'should conform to chained api convention');
	ok(f.is('.dateformatted'));
});

test('formatGet', function() {
	var f = $('<span/>').text("1970-01-01T00:00:04.000Z");
	var date = f.dateformat('get');
	ok(typeof date.getUTCFullYear != 'undefined', ".dateformat('get') should return Date");
	equals(date.getUTCSeconds(), 4, ".dateformat('get') should parse to Date instance");
	ok(!f.is('.dateformatted'), ".dateformat('get') should not format");
});

test('formatGetFormatted', function() {
	var f = $('<span/>').text("1970-01-01T00:00:05.000Z").dateformat();
	ok(f.text() != "1970-01-01T00:00:05.000Z", "should be local format now");
	// now some other code comes along and wants to know what the formatted string means
	var date = f.dateformat('get');
	equals(date.getUTCSeconds(), 5, ".dateformat('get') should return Date instance for formatted");
	var f2 = $('<span/>').text("1970-01-01T00:00:09.000Z").dateformat();
	equals(f2.dateformat('get').getUTCSeconds(), 9, "different cached date");
});
