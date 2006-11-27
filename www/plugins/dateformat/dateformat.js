/**
 * Repos dateformat (c) Staffan Olsson repos.se
 * Takes all elements with a specific classname and formats the contained date
 * setting the time zone to the value in the usertimezone cookie.
 * See: http://www.atomenabled.org/developers/syndication/atom-format-spec.php#date.constructs
 * Nice page: http://www.xaprb.com/demos/rx-toolkit/
 * $Id$
 */
 
// --- extensions to the date class ---
Date.prototype.setISO8601 = function (string) {
    var regexp = "([0-9]{4})(-([0-9]{2})(-([0-9]{2})" +
        "(T([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]+))?)?" +
        "(Z|(([-+])([0-9]{2}):([0-9]{2})))?)?)?)?";
    var d = string.match(new RegExp(regexp));

    var offset = 0;
    var date = new Date(d[1], 0, 1);

    if (d[3]) { date.setMonth(d[3] - 1); }
    if (d[5]) { date.setDate(d[5]); }
    if (d[7]) { date.setHours(d[7]); }
    if (d[8]) { date.setMinutes(d[8]); }
    if (d[10]) { date.setSeconds(d[10]); }
    if (d[12]) { date.setMilliseconds(Number("0." + d[12]) * 1000); }
    if (d[14]) {
        offset = (Number(d[16]) * 60) + Number(d[17]);
        offset *= ((d[15] == '-') ? 1 : -1);
    }

    offset -= date.getTimezoneOffset();
    time = (Number(date) + (offset * 60 * 1000));
    this.setTime(Number(time));
}

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
    if (!format) { var format = 6; }
    if (!offset) {
        var offset = 'Z';
        var date = this;
    } else {
        var d = offset.match(/([-+])([0-9]{2}):([0-9]{2})/);
        var offsetnum = (Number(d[2]) * 60) + Number(d[3]);
        offsetnum *= ((d[1] == '-') ? -1 : 1);
        var date = new Date(Number(Number(this) + (offsetnum * 60000)));
    }

    var zeropad = function (num) { return ((num < 10) ? '0' : '') + num; }

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
}
 
// ----- Dateformat class -----
$(document).ready(function(){
	d = new Dateformat();
	$(".datetime").each(function() {
		d.formatElement(this);
	});
});

function Dateformat() {
	
	/**
	 * Verify that a string is an ISO8601 date, with date+time or only date
	 */
	this.isDatetime = function(text) {
		if (typeof(text) != 'string') return false;
		if (text.length < 8) return false;
		if (text.length > 32) return false;
		// and some regexp
		return true;
	}
	
	/**
	 * @param texttag element containing date time
	 */
	this.formatElement = function(texttag) {
		if ($(texttag).is('.formatted')) return;
		var d = texttag.innerHTML;
		if (d == null || d=='') return;
		if (!this.isDatetime(d)) {
			throw "Invalid datetime string in tag " + (texttag.id ? texttag.id : texttag.tagName) + ": " + d;	
		}
		var f = this.format(d);
		texttag.innerHTML = f;
		$(texttag).addClass('formatted');
	}
	
	/**
	 * @param xsd:dateTime/ISO 8601 date
	 * @return string with the formatted datetime
	 */
	this.format = function(dateTime) {
		if (!this.isDatetime(dateTime)) {
			throw "Invalid datetime string: " + dateTime;	
		}
		var d = new Date();
		d.setISO8601(dateTime);
		return d.toLocaleString();
	}
}
