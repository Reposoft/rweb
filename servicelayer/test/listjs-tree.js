$().ready( function() {
	svnlist_add_expand('#testlist');
} );

var svnlist_add_expand_count = 0;

// has to be on the same host
function svnlist_add_expand(parent) {
	$(parent + ' .svnlist ul.dir li.name a').each( function() {
		$(this).click( function() {
			var nextid = 'svn' + svnlist_add_expand_count++;
			$(this).parent().append('<ul id="'+nextid+'"></ul>');
			// TODO this doesn't work because the script still sees the id from HEAD
			$.getScript(this.href + "?svn=listjs&selector=#" + nextid, function(){
			   svnlist_add_expand('#nextid');
			});
			return false;
		} );
	} );
}
