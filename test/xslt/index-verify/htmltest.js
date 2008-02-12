
var iframe = $('<iframe/>');

load = function(url) {
	iframe[0].src = url;
};

$().ready( function() {
	$('body').append(iframe);
	$(iframe).load( function() {
		window.T = (iframe[0].contentWindow  || iframe[0].contentDocument);
		if (window.T.document) window.T = T.document;
	} );
} );


