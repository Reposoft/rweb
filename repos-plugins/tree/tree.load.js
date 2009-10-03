
function reposTreeGetUrl() {
	var url = '/repos-plugins/tree/';
	url += '?menu=false';
	url += '&frame=_top';
	url += '&target='+encodeURI(Repos.getTarget());
	// explicit 'base' support
	if ($('#base').length) url += '&base=' + $('#base').text();
	return url;
}

function reposTreeIframe() {
	var url = reposTreeGetUrl();
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
	// tree button only shown at root and project roots
	if (!/^(\/*|.*\/trunk\/)$/.test(Repos.getTarget())) return;
	var tree = false;
	var a = $('<a href="javascript:void(0)">show&nbsp;tree</a>').attr('id','repostree').appendTo('#commandbar');
	// for browser that supports sidebar show the bookmark box directly, no iframe and no toggle
	if ($.browser.mozilla || $.browser.opera) {
		a.attr('href', reposTreeGetUrl() + '&sidebar=true');
		a.attr('rel', 'sidebar');
		a.attr('title', document.title); // the suggested bookmark name
		return;
	}
	a.toggle(function() {
		if (!tree) tree = reposTreeIframe();
		tree.show();
		$(this).html('hide&nbsp;tree');
	},function() {
		tree.hide();
		$(this).html('show&nbsp;tree');
	});
});
