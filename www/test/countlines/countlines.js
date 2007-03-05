
$(document).ready(function() {
	cSetup();
	countlines();
} );

cTables = ['code', 'comm', 'test'];
cCols = ['date', 'conf', 'account', 'admin', 'edit', 'open', 'plugins', 'test', 'view', 'total'];

function cSetup() {
	cTables.forEach( function(t) {
		$('body').append('<table id="'+t+'"></table>');
	} );
	$('table').each( function() {
		var row = $('<tr></tr>');
		cCols.forEach( function(c) {
			row.append('<th class="'+c+'">'+c+'</th>');
		} );
		$(this).append(row);
	} );
}

function countlines() {
	var source = 'lines-test.txt';
	$.get(source, function(data){
   	cPrint(data);
 	});
}

function cPrint(data) {
	var lines = data.split("\n");
	for (var i=0; i<lines.length; i++) {
		if (/\d{4}\-/.test(lines[i])) cDate(lines[i]);
		if (/^\w+$/.test(lines[i])) {
			var c = lines[i];
			cTables.forEach( function(t) {
				$('#'+t+' tr:last-child .'+c).text(lines[++i]); 
			} );
		}
	}
}

function cDate(date) {
	$('table').each( function() {		
		var row = $('<tr class="data"></tr>');
		cCols.forEach( function(c) {
			row.append('<td class="'+c+'">'+(c=='date' ? date : '')+'</th>');
		} );
		$(this).append(row);
	} );
}



