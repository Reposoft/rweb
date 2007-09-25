
// run this after the listjs script

$().ready( function() {
	var svnlist = $('#testlist');
	$('<p>Click folders to expand</p>').insertAfter(svnlist);
	svnlist_add_expand(svnlist);
} );

var svnlist_add_expand_count = 0;

// has to be on the same host
function svnlist_add_expand(parent) {
	$('li.dir',parent).each( function() {
	 	var href = $('a:first', this).attr('href') + '?svn=listjs&';
		var nextid = 'svn' + svnlist_add_expand_count++;
		$('<a/>').append('+').attr('href','#tree').prependTo(this).toggle(
			function() {
				$(this).html('-');
				$('<ul/>').attr('id',nextid).appendTo($(this).parent()).append('<li class="loading">loading...</li>');
				$.getScript(href + 'selector='+nextid, function(){
					$('.loading','#'+nextid).remove();
					svnlist_add_expand($('#'+nextid));
				});
				return false;
			},
			function() {
				$(this).html('+');
				$('#'+nextid).remove();
				return false; 
			});
	} );
}
