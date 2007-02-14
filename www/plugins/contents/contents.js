
$(document).ready( function() {
	contentsCreateTable();
} );

var contentsH = new Array();

function contentsCreateTable() {
	$('h1').each( function() {
		var ic = new Array();
		var i = contentsH.push( { e: this, c: ic } );
		$(this).text(''+i+'. '+$(this).text());
		$('h2').each( function() {
			var jc = new Array();
			var j = ic.push( { e: this, c: jc } );
			$(this).text(''+i+'.'+j+'. '+$(this).text());
			$('h3').each( function() {
				var kc = new Array();
				var k = jc.push( { e: this, c: kc } );
				$(this).text(''+i+'.'+j+'.'+k+'. '+$(this).text());
			} );
		} );
	} );
};