
function reposTreeIframe() {
	// XHTML Transitional, not Strict
	var tree = $('<iframe/>')
		.attr('id','repostree')
		.attr('name','tree')
		.attr('width','300') // todo window size
		.attr('height','500')
		.prependTo('body');
	tree.attr('src','/repos-plugins/tree/index.html');
	//tree.css('float','left');
	tree.css({
		position: 'absolute',
		top: '0px',
		left: '0px'
	});
	$('body').css('margin-left','320px');
};

Repos.service('index/', function() {
	var a = $('<a>').attr('href','#').text('show tree').appendTo('#commandbar').click(function() {
		reposTreeIframe();
	});
});
