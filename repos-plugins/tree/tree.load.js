
/**
 * Jquery plugin.
 * Note that in order to get file type icons, repository.css is required.
 */
(function( $ ){
$.fn.reposTree = function( options ) {  

	var settings = $.extend({
		callbackLoad: function(link, name, target, base, isFile) {
		},
		callbackSelect: function(link, name, target, base, isFile) { // args are not URL-encoded
			window.console && console.log('Selected', node, name, target, base, isFile);
			return false; // don't follow click
		},
		web: '/repos-web/',
		autoexpand: false,
		showfiles: false,
		showdetails: false,
		base: Repos.getBase(),
		target: '/', // TODO support http://localhost/repos-web/open/start/?serv=json
		startpath: false
	}, options);
	
	var decorateJsonListItem = function(here) {
		return function() {
			var li = $(this);
			var a = $('> a', li); // created by list script
			var name = a.text(); // URLdecoded
			var target = here + name + '/'; // update in closure for recursion
			var isFile = li.is('.file');
			
			// link clicks should be treated with default action, not bubbled to list item
			a.click(function(ev) {
				ev.stopPropagation();
				return settings.callbackSelect(a[0], name, target, settings.base, isFile);
			}).each(function() {
				settings.callbackLoad(a[0], name, target, settings.base, isFile);
			});
			
			if (isFile) {
				$(this)
					.addClass('expandedempty') // needed because list-style css is inherited
					.click(function(ev) { // file click events should not be bubbled
						ev.stopPropagation();
					});
			} else {
				li.addClass('collapsed');
				// enable expansion
				var id = Math.random().toString().substr(2);
				$('<ul/>').attr('id', id).appendTo(this);
				$(this).toggle(function() {
					if (li.is('.loading')) return;
					li.addClass('loading');
					expand(id, target);
				}, function() {
					collapse(id, target);
				});
				if (settings.autoexpand) a.trigger('click');
				// Indicate which element would expand if a click is done, tricky because of nested structure
				li.mouseover(function(ev) {
					ev.stopPropagation();
					$(this).parents('.folder.hovr').removeClass('hovr');
					$(this).addClass('hovr');
				});
				li.mouseout(function(ev) {
					$(this).removeClass('hovr');
				});
				a.mouseover(function(ev) {
					li.addClass('hovrselect');
				});
				a.mouseout(function(ev) {
					li.removeClass('hovrselect');
				});
			}
		};
	};
	
	var json = settings.web+'open/json/'; // not using servicelayer
	
	var expand = function(id, target) {
		var list = $('#' + id);
		var here = target;
		$.ajax({
			dataType : 'script',
			url : json + '?selector=' + id + '&target=' + encodeURI(here)
					+ (settings.base ? '&base=' + settings.base : ''),
			success : function() {
				// list item has the expand/collapse logic
				var added = $('> li', list);
				if (settings.startpath) {
					added.filter('.repo').remove(); // root expansion
					var preexisting = added.filter('.repostree-startpath');
					preexisting.each(function() {
						added = added.not(this);
						var existingname = $('> a', this).text();
						var dup = added.filter(function() {
							return $('> a', this).text() == existingname;
						});
						added = added.not(dup);
						$(this).insertAfter(dup);
						dup.remove();
					});
				}
				added.each(decorateJsonListItem(here));
				// done, set class to override inherited expand/collapse status
				var cl = 'expanded';
				if (added.filter(':visible').size() == 0) cl += 'empty';
				list.parent().filter('li').addClass(cl).removeClass('loading');
			},
			error : function(req, textStatus, errorThrown) {
				window.console && console.error('Tree load error', req, textStatus, errorThrown);
				var status = req.status; // currently the JSON service does 
				//   not distinguish between server error, folder not found and access denied
				list.parent().filter('li').removeClass('collapsed').addClass('expand-error')
						.addClass('expandedempty').addClass('folder-noaccess')
						.removeClass('loading');
			}
		});
	};

	var collapse = function(id, target) {
		// no use in preserving content as user expects refresh at
		// collapse+expand
		$('#' + id).empty().parent().removeClass('expanded').addClass('collapsed');
	};
	
	// Like getRepository but with support for static index.html
	var getHrefBase = function() {
		// tree page lacks this metadata, fall back to details page
		return Repos.getRepository() || settings.web + 'open/?' + (settings.base ? 'base=' + settings.base + '&' : '') + 'target='; 
	};

	return this.each(function() { 
		var id; // list json script uses IDs
		var root = $(this).is('ul') ? $(this) : $('<ul/>').appendTo(this);
		root.addClass("repostree").addClass('repostreeroot');
		if (root.attr('id')) {
			id = root.attr('id');
		} else {
			id = 'repostreeroot' + Math.random().toString().substr(2);
			root.attr('id', id);
		}
		if (!settings.showfiles) root.addClass('hidefiles');
		if (!settings.showdetails) root.addClass('hidedetails');
		if (settings.startpath && !settings.autoexpand) {
			var hrefbase = getHrefBase(); // not known anywhere else now because listjson script contains it
			var path = settings.startpath.split('/'); // should be a folder
			var currentpath = '/';
			var currentlist = root;
			for (var i = 0; i < path.length; i++) {
				if (path[i]) {
					// mimic json list's format, note that these will be switched for real nodes after collapse/expand
					var li = $('<li class="folder repostree-startpath"><a href="' + hrefbase + currentpath + path[i] + '/">' + 
							path[i] + '</a></li>').appendTo(currentlist);
					decorateJsonListItem(currentpath).apply(li);
					currentpath += path[i] + '/';
					currentlist = $('ul', li);
				}
			}
			// root expansion, more of the core behavior simulated
			$('<li class="folder repo collapsed"><span style="cursor:pointer">' + (settings.base || '(root)') + '</span></li>')
				.prependTo(root).click(function() {
					expand(id, settings.target); // expected to remove
				}).mouseover(function(ev) {
					$(this).addClass('hovr');
				}).mouseout(function(ev) {
					$(this).removeClass('hovr');
				});;
		} else {
			expand(id, settings.target);
		}
	});

};
})( jQuery );

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

/**
 * Sidebar integration.
 */
Repos.service('index/', function() {
	// tree button only shown at root and project roots
	if (!/^(\/*|.*\/trunk\/)$/.test(Repos.getTarget())) return;
	var tree = false;
	var a = $('<a href="javascript:void(0)">tree</a>').attr('id','repostree').appendTo('#commandbar');
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
		$(this).html('hide');
	},function() {
		tree.hide();
		$(this).html('tree');
	});
});
