
function reposTreeIframe() {
	var url = '/repos-plugins/tree/index.html';
	url += '?menu=false';
	url += '&frame=_top';
	url += '&target='+encodeURI(Repos.getTarget());
	// window width and height, no shared repos function for this yet
	var de = document.documentElement;
	var winw = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var winh = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	// XHTML Transitional, not Strict
	var tree = $('<iframe/>')
		.attr('id','repostree')
		.attr('name','tree')
		.attr('width',''+Math.min(500, Math.max(winw,980) - 800))
		.attr('height',''+Math.min(winh - 200)) // just guessing
		//float issue?//.insertAfter('#commandbar');
		.insertAfter('h2');
	tree.attr('src',url);
	tree.css('float','left');
};

Repos.service('index/', function() {
	var a = $('<a href="#">show tree</a>').appendTo('#commandbar').click(function() {
		reposTreeIframe();
	});
});
