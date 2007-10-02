
$().ready( function() {
	$('body.repository a[href=repos.txt]').each( function() {
		$.get(this.href, function(data) {
			$('<div id="repos-readme" class="note"/>')
				.append(data.replace(/\r?\n/,'<br />')).insertBefore('.row:first');
		});
	});
} );
