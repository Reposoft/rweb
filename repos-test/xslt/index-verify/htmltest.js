
// get arguments when running in html page
var params = {};
$('script').each( function() {
	console.log(this.src);
	var m = /(.*)htmltest\.js\??(.*)/.exec(this.src);
	if (m && m.length > 2) {
		p = eval('({'+m[2].replace(/=/g,':"').replace(/&/g,'",')+'"})');
		$.extend(params, p);
	}
} );

var iframe = $('<iframe/>');

load = function(url, callback) {
	iframe[0].src = url;
	if (typeof callback != 'undefined') {
		console.log('Loaded test page ',url);
		$(iframe).one('load', callback);
	}
};

$().ready( function() {
	$('body').append(iframe);
	$(iframe).one('load', function() {
		window.T = (iframe[0].contentWindow  || iframe[0].contentDocument);
		if (window.T.document) window.T = T.document;
		
		console.log('Test frame is now ready');
		
		var t = params.args; // testscript
		document.write = function(m) { $(m).appendTo('body') };
		$.getScript(t);
	} );
} );

