
function reposTreeIframe() {
	var url = '/repos-plugins/tree/index.html';
	url += '?menu=false';
	url += '&frame=_top';
	url += '&target='+encodeURI(Repos.getTarget());
	// XHTML Transitional, not Strict
	var tree = $('<iframe/>')
		.attr('id','repostree')
		.attr('name','tree')
		.attr('width','400') // todo window size
		.attr('height','500')
		//float issue?//.insertAfter('#commandbar');
		.insertAfter('h2');
	tree.attr('src',url);
	tree.css('float','left');
};

Repos.service('index/', function() {
	var a = $('<a>').attr('href','#').text('show tree').appendTo('#commandbar').click(function() {
		reposTreeIframe();
	});
});
