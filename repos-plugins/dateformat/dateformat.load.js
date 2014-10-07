/**
 * Repos dateformat (c) Staffan Olsson repos.se
 * Takes all elements with a specific classname and formats the contained date
 * setting the time zone to the value in the usertimezone cookie.
 * See: http://www.atomenabled.org/developers/syndication/atom-format-spec.php#date.constructs
 * Nice page: http://www.xaprb.com/demos/rx-toolkit/
 * $Id$
 */

// events:
// repos-dateformat-done, Date instance

var svndate = /(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\.(\d+))?(Z|(([-+])(\d{2}):(\d{2})))/;

$(document).ready(function(){
	$(".datetime").dateformat();
});

// --- extensions to the date class ---
Date.prototype.setISO8601 = function (string) {

    var d = string.match(svndate);
    if (!d) {
        throw new Error('Failed to parse date "' + string + '"');
    }

    var offset = 0;
    
    // Examples 1997-07-16T19:20:30.45+01:00 / 1997-07-16T19:20:30.203125Z
    
    var date = new Date(d[1], 0, 1);		// d[1] = // 1997
	
    if (d[2]) { date.setMonth(d[2] - 1); }	// d[2] = 07
    if (d[3]) { date.setDate(d[3]); }		// d[3] = 16
    if (d[4]) { date.setHours(d[4]); }		// d[4] = 19
    if (d[5]) { date.setMinutes(d[5]); }	// d[5] = 20
    if (d[6]) { date.setSeconds(d[6]); }	// d[6] = 30
    if (d[11]) {							// d[11] = +01:00 / null
        offset = (Number(d[12]) * 60) + Number(d[13]);	// d[15] = +, d[16] = 01, d[17] = 00
        offset *= ((d[11] == '-') ? 1 : -1);
    }

    offset -= date.getTimezoneOffset();
    var time = (Number(date) + (offset * 60 * 1000));
    this.setTime(Number(time));
};

// from http://delete.me.uk/2005/03/iso8601.html
Date.prototype.toISO8601String = function (format, offset) {
    /* accepted values for the format [1-6]:
     1 Year:
       YYYY (eg 1997)
     2 Year and month:
       YYYY-MM (eg 1997-07)
     3 Complete date:
       YYYY-MM-DD (eg 1997-07-16)
     4 Complete date plus hours and minutes:
       YYYY-MM-DDThh:mmTZD (eg 1997-07-16T19:20+01:00)
     5 Complete date plus hours, minutes and seconds:
       YYYY-MM-DDThh:mm:ssTZD (eg 1997-07-16T19:20:30+01:00)
     6 Complete date plus hours, minutes, seconds and a decimal
       fraction of a second
       YYYY-MM-DDThh:mm:ss.sTZD (eg 1997-07-16T19:20:30.45+01:00)
    */
    if (typeof format == 'undefined') {
    	var format = 6;
    }
    if (typeof offset == 'undefined') {
        var offset = 'Z';
        var date = this;
    } else {
        var d = offset.match(/([-+])([0-9]{2}):([0-9]{2})/);
        var offsetnum = (Number(d[2]) * 60) + Number(d[3]);
        offsetnum *= ((d[1] == '-') ? -1 : 1);
        var date = new Date(Number(Number(this) + (offsetnum * 60000)));
    }

    var zeropad = function (num) { return ((num < 10) ? '0' : '') + num; };

    var str = "";
    str += date.getUTCFullYear();
    if (format > 1) { str += "-" + zeropad(date.getUTCMonth() + 1); }
    if (format > 2) { str += "-" + zeropad(date.getUTCDate()); }
    if (format > 3) {
        str += "T" + zeropad(date.getUTCHours()) +
               ":" + zeropad(date.getUTCMinutes());
    }
    if (format > 5) {
        var secs = Number(date.getUTCSeconds() + "." +
                   ((date.getUTCMilliseconds() < 100) ? '0' : '') +
                   zeropad(date.getUTCMilliseconds()));
        str += ":" + zeropad(secs);
    } else if (format > 4) { str += ":" + zeropad(date.getUTCSeconds()); }

    if (format > 3) { str += offset; }
    return str;
};

Date.prototype.toUserString = function (format, offset) {
    /* accepted values for the format [1-6]:
     1 Year:
       YYYY (eg 1997)
     2 Year and month:
       YYYY-MM (eg 1997-07)
     3 Complete date:
       YYYY-MM-DD (eg 1997-07-16)
     4 Complete date plus hours and minutes:
       YYYY-MM-DDThh:mmTZD (eg 1997-07-16T19:20+01:00)
     5 Complete date plus hours, minutes and seconds:
       YYYY-MM-DDThh:mm:ssTZD (eg 1997-07-16T19:20:30+01:00)
     6 Complete date plus hours, minutes, seconds and a decimal
       fraction of a second
       YYYY-MM-DDThh:mm:ss.sTZD (eg 1997-07-16T19:20:30.45+01:00)
    */
    if (typeof format == 'undefined') {
    	var format = 5;
    }
    if (typeof offset == 'undefined') {
        
        var date = this;
    } else {
        var d = offset.match(/([-+])([0-9]{2}):([0-9]{2})/);
        var offsetnum = (Number(d[2]) * 60) + Number(d[3]);
        offsetnum *= ((d[1] == '-') ? -1 : 1);
        var date = new Date(Number(Number(this) + (offsetnum * 60000)));
    }

    var zeropad = function (num) { return ((num < 10) ? '0' : '') + num; };

    var str = "";
    str += date.getUTCFullYear();
    if (format > 1) { str += "-" + zeropad(date.getUTCMonth() + 1); }
    if (format > 2) { str += "-" + zeropad(date.getUTCDate()); }
    if (format > 3) {
        str += " " + zeropad(date.getHours()) +
               ":" + zeropad(date.getUTCMinutes());
    }
    if (format > 5) {
        var secs = Number(date.getUTCSeconds() + "." +
                   ((date.getUTCMilliseconds() < 100) ? '0' : '') +
                   zeropad(date.getUTCMilliseconds()));
        str += ":" + zeropad(secs);
    } else if (format > 4) { str += ":" + zeropad(date.getUTCSeconds()); }

    return str;
};
 
/**
 * Dateformat jQuery plugin.
 * 
 * Use .dateformat('get') to get the Date instance of the first element.
 * 
 * @param {jQuery} $ the jQuery object 
 */
(function($) {
$.fn.dateformat = function(options) {	
	
	// allow unit testing by adding all internal functions to this object
	var F = $.fn.dateformat;
	
	/**
	 * Verify that a string is an ISO8601 date, with date+time or only date
	 */
	F.isDatetime = function(text) {
		if (typeof(text) != 'string') return false;
		if (text.length < 8) return false;
		if (text.length > 32) return false;
		// and some regexp
		return true;
	};
	
	/**
	 * @param texttag {Element} element containing date time 
	 */
	F.get = function(texttag) {
		return texttag._dateformat || F.parse(texttag);
	};
	
	F.parse = function(texttag) {
		var d = $(texttag).text();
		if (d == null || d=='') return false;
		if (!this.isDatetime(d)) {
			throw "Invalid datetime string in tag " + (texttag.id ? texttag.id : texttag.tagName) + ": " + d;	
		}
		var date = new Date();
		date.setISO8601(d);
		texttag._dateformat = date;
		return date;
	};
	
	/**
	 * @param texttag element containing date time
	 */
	F.formatElement = function(texttag) {
		if ($(texttag).is('.dateformatted')) return;
		var date = F.parse(texttag);
		if (!date) return false;
		$(texttag).text(date.toUserString())
			.addClass('dateformatted')
			.trigger('repos-dateformat-done', [date]);
	};
	
	/**
	 * @param xsd:dateTime/ISO 8601 date
	 * @return string with the formatted datetime
	 * @deprecated No longer used because the method above needs the Date object too,
	 *  but still called from unit tests
	 */
	F.format = function(dateTime) {
		if (!this.isDatetime(dateTime)) {
			throw "Invalid datetime string: " + dateTime;	
		}
		var d = new Date();
		d.setISO8601(dateTime);
		return d.toLocaleString();
	};
	
	if (typeof options == 'string') {
		if (options == 'get') {
			return F.get(this[0]);
		}
		throw 'Unknown option ' + options;
	}
	
	$(this).each( function() {
		F.formatElement(this);
	});
	
	return this;
};
})(jQuery);
