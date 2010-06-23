/**
 * Creates an Repos Search "svnhead" standard query from form.
 * Special treatment of fields "op" and "base".
 * The rest are query fields if not empty.
 */
(function() {
	
	var coreUrl = '/solr/svnhead/';

	$().ready(function() {
		
		var form = $('form');
	
		form.submit(function() {
			alert($(this).serialize());
			return false;
		});
		
	});
	
})();