
function reposTreeIframe() {
	var url = '/repos-plugins/tree/';
	url += '?menu=false';
	url += '&frame=_top';
	url += '&target='+encodeURI(Repos.getTarget());
	// explicit 'base' support
	if ($('#base').length) url += '&base=' + $('#base').text();
	// window width and height, no shared repos function for this yet
	var de = document.documentElement;
	var winw = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var winh = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	// XHTML Transitional, not Strict
	var tree = $('<iframe/>')
		.attr('id','repostree')
		.attr('name','tree')
		.attr('width',''+Math.min(500, Math.max(winw,1024) - 840))
		.attr('height',''+Math.min(winh - 200)) // just guessing
		//float issue?//.insertAfter('#commandbar');
		.insertAfter('h2');
	tree.attr('src',url);
	tree.css('float','left');
	return tree;
};

Repos.service('index/', function() {
	var tree = false;
	var a = $('<a href="#">show&nbsp;tree</a>').attr('id','repostree').appendTo('#commandbar').toggle(function() {
		if (!tree) tree = reposTreeIframe();
		tree.show();
		$(this).html('hide&nbsp;tree');
	},function() {
		tree.hide();
		$(this).html('show&nbsp;tree');
	});
});
