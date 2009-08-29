
// Set special icons for repository browsing
Repos.service('index/', function() {
	$('.file-xml, .file-fos, .file-style, .file-dcf, .file-mcf, .file-sgm, .file-dita, .file-ditamap')
		.css('background-image', 'url("/repos-plugins/arbortext/abx_icon.png")');
});

// Add custom activities for special file types
Repos.target(/\.(xml|fos|style|dcf|mcf|sgm|dita|ditamap)$/, function() {
	if (Repos.isService('open/') || Repos.isService('edit/')) {
		var url = $('#urlcopy').val().replace(/^https?/, 'arbortext-editor:x-svn');
		// insert root marker, TODO need Repos.getRepository, how does this work with UTF-8 chars?
		var t = Repos.getTarget();
		var url = url.slice(0, url.length - t.length) + '^' + t;
		// add to page
		var url = encodeURI(url); // we do an extra urlencode to preserve logical-ID encoding through arbortext's single decode
		// See Arbortext Customizing Guide section "Integrating Arbortext Editor with web pages"
		$('<h3><a href="'+url+'" style="background-image:url(/repos-plugins/arbortext/abx_icon32.png);">Open in Arbortext</a></h3><p></p>')
			.appendTo('#activities');
	}
});

// Restructure view page and customize svn proplist display
Repos.service('open/', function() {
	// hide verbose info	
	$('#activities p').hide();
	// reorganize page
	$('.column:eq(1)').append($('#activities'));
	$('.column:eq(0)').append($('#filedetails'));	
	// provide access to verbose info
	$('#activities p:first').appendTo($('#activities')).show();
	$('#activities h3').each(function() {
		$(this).hover(function() {
				$('<div id="action-help"/>').addClass('section')
					.appendTo($(this).parent().parent())
					.append('<h3>' + $(this).text() + '</h3>')
					.append('<p>' + $(this).next().html() + '</p>');
			}, function() {
				$('#action-help').remove();
			});
	});
	// properties formatting
	var properties = {
		separator: /\r?\n/g,
		url: /([a-z+:-]+):\/\/([a-z0-9.+-]+)?(:\d+)?\/([^=;#:\s?]+)(\?[^;#:\s\/?]+)?/ig
		// url: exclude reserved characters from http://www.w3.org/Addressing/URL/5_BNF.html#z75
		// but allow slash and ? to make regex short
		// hostname is optional in arbortext linkbase urls
		// example: arbortext-editor:x-svn:///svn/demo`/trunk/Storyboard_demo.xml
		// ^ is not a valid URL character but used to mark repository root
		// TODO match only the complete value (between separators), not URLs in values 
	};
	var namespaces = {
		cms: 'Content Management',
		img: 'Image Information',
		abx: 'Arbortext',
      		svn: 'Subversion'
	};
	var namespace = function(v) {
		return namespaces[v] || v;
	};
	var value = function(v, key) {
		if (key == 'abx:Dependencies') {
			dependencies(v) 
		} else { // generic URL detection in text, the regex should only allow url-encoded urls
			v = v.replace(properties.url, '<a href="$&">$&</a>'); // Readability can be improved by URL-decoding link text
		}
		v = v.replace(properties.separator, '<br />'); // should be in properties plugin, should be another <dd>
		return v;
	};
	var dependencies = function(v) {
		var section = $('<div/>').attr('id','dependencies').addClass('section');
		$('<h2/>').text('Dependencies').appendTo(section);
		section.appendTo('.column:eq(1)');
		pv = v.split(properties.separator);
		var list = $('<ul id="dependencylist"/>').appendTo(section).css('list-style-type','circle');
		var sofar = []; // keep track of duplicates
		for (p in pv) {
			var v = pv[p];
			if (!v) continue; // enpty after separator
			if (sofar.indexOf && sofar.indexOf(v) > 0) continue;
			sofar.push(v);
			var li = $('<li/>').appendTo(list);
			var m = new RegExp(properties.url).exec(v); // looks like the regex has some kind of memory so every second value it won't match
			if (!m) {
				li.text(v);
				continue;
			}
			var host = location.protocol + '//' // always use current protocol (http/https)
				+ (m[2] ? m[2] + (m[3] || '') : location.host);
			var url = host + '/' + m[4]
				+ (m[5] || '');
			// TODO remove the circumflex in the url variable (a better design is to match it in the regex, and reuse with view button below)
			// href value should be url-encoded, like the Dependencies value
			li.html('<a href="' + url.replace('^','') + '">'
				+ decodeURI(url.substr(url.indexOf('^')+1)) + '</a>');
			// TODO if it is this repository we know it is Repos urls
			// use the convention for marking repository root on any Repos host
			var item = /(\/\w+)\^(.*)$/.exec(v);
			if (item) {
				var href = host + '/repos-web/open/?target=' // assume host's Repos Web is at repos-web
					+ encodeURIComponent(decodeURI(item[2])) // already urlencoded, but as path
					+ '&base=' + item[1].substr(1); // TODO use the mandatory notation for repository root to deduce base (assuming this is a parentpath setup)
				var view = $('<a>view</a>').addClass('action').attr('href', href);
				$('<span class="actions"/>').append(view).appendTo(li);
			}
		}
	};
	// load properties automatically
	$().bind('repos-proplist-loaded', function(event, container) {
		//$('.column:eq(0)').append(container).addClass('section');
		var header = $('<h2/>').text('Attributes/metadata').prependTo(container);
		// parse properties and group into namespaces
		var prop = {};
		$('dl.properties dt', container).each(function() {
			var p = /^([^:]+):?(.*)$/.exec($(this).text()); // TODO how to handle properties with no namespace?
			prop[p[1]] = prop[p[1]] || {};
			prop[p[1]][p[0]] = $(this).next().text();
		});
		$('dl.properties', container).remove();
		// separate lists per namespace
		for (n in prop) {
			var list = $('<dl class="properties"><lh>' + namespace(n) + '</lh></dl>');
			for (p in prop[n]) {
				list.append('<dt>'+p+'</dt><dd>'+value(prop[n][p], p)+'</dd>');
			}
			container.append(list);
		}
		// quick link to propedit
		$('<span class="actions"/>').append('<a class="action" href="' 
				+ Repos.getWebapp() + 'edit/propedit/?target=' + encodeURI(Repos.getTarget())
				+ '&base=' + Repos.getBase() + '">edit</a>')
				.css('float','right').insertAfter(header);
	});
	$('.proplist .action-load').trigger('click');
});

// Customize readme
$().bind('repos-readme-loaded', function(ev, container) {
	// support logical ids from readme edited in arbortext
	var abxToHttp = function(e, attribute) {
		// need generic functions for to and from logical ID
		var url = $(e).attr(attribute);
		var m = /^x-svn:\/\/([^\/]*)\/(.*)$/.exec(url);
		if (m) {
			$(e).attr(attribute,
				location.protocol + '//'
				+ (m[1] ? m[1] : location.host) + '/'
				+ m[2].replace('^',''));
			// link text not edited. generic function for adapting links and adding view button?
		}
	};
	$('a', container).each(function() {
		abxToHttp(this, 'href');
	});
	$('img', container).each(function() {
		abxToHttp(this, 'src');
	});
});

// Where Used
Repos.service('open/', function() {
	// The search term
	var child = Repos.getTarget();
	// REST resource for search, currently not a Repos service
	var search = '/repos-plugins/arbortext/index/parentchild/';
	var params =  {
			target: child
	};
	// how to present results
	var presentation = function(solrXml) {
		var list = $('<ul/>').appendTo('#whereused');
		$('doc', solrXml).each(function() {
			var parent = $('str[name="parentId"]', this).text();
			var latestAdd = $('arr[name="addedRev"] > int:last', this).text();
			var latestRemove = $('arr[name="removedRev"] > int:last', this).text();
			var li = $('<li/>').appendTo(list);
			var repository = $('#urlcopy').val().replace(Repos.getTarget(), ''); // need Repos.getRepsitory
			li.append('<a href="' + repository + parent + '">'+parent+'</a>');
			// roughly the same code as for dependencies above, should have a generalized event handler
			var href = '/repos-web/open/?target=' // assume host's Repos Web is at repos-web
				+ encodeURIComponent(parent)
				+ '&base=' + Repos.getBase();
			var view = $('<a>view</a>').addClass('action').attr('href', href).appendTo(li);
			// currently this is what we know about the dependency history
			li.append(' since <span class="revision">' + latestAdd + "</span>");
			if (latestRemove && latestRemove > latestAdd) {
				li.append(' from <span class="revision">' + latestRemove + "</span>");
			}
		});
	};
	// start with an empty box
	var section = $('<div/>').attr('id','whereused').addClass('section');
	$('<h2/>').text('Used In').appendTo(section);
	section.appendTo('.column:eq(1)');
	// perform search
	$.get(search, params, presentation, 'xml');
});




