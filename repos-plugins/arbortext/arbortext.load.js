// Customize title, alternative to changing in html templates and xsl files
document.title = document.title.replace('repos:', 'Simonsoft CMS |');

// Set startpage logo
Repos.service('home/', function() {
	$('#intro h1').empty()
		.append('<img alt="Simonsoft logo" width="567" height="46" border="0" align="bottom" alt="repos.se" src="/repos-plugins/arbortext/simonsoft-logo.gif"/>')
		.append('&nbsp;&nbsp;CMS&nbsp;beta')
		.css({fontSize:'42px',fontWeight:'bold',letterSpacing:'.1em',color:'#aaa'});
});

// Set special icons and repository browser logo
Repos.service('index/', function() {
	$('.file-xml, .file-fos, .file-style, .file-dcf, .file-mcf, .file-sgm, .file-dita, .file-ditamap')
		.css('background-image', 'url("/repos-plugins/arbortext/abx_icon.png")');

	$('#commandbar #logo').attr('width','134').attr('src', '/repos-plugins/arbortext/simonsoft-name.png');
});

Repos.service('open/log/', function() {
	$('#commandbar #logo').attr('width','134').attr('src', '/repos-plugins/arbortext/simonsoft-name.png');
});

// Add custom activities for special file types
Repos.target(/\.(xml|fos|style|dcf|mcf|sgm|dita|ditamap)$/, function() {
	if (Repos.isService('open/') || Repos.isService('edit/')) {
		var url = $('#urlcopy').val().replace(/^https?/, 'arbortext-editor:x-svn');
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
		// example: arbortext-editor:x-svn:///svn/demo/trunk/Storyboard_demo.xml
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
		} else {
			v = v.replace(properties.url, '<a href="$&">$&</a>');
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
		for (p in pv) {
			var v = pv[p];
			if (!v) continue; // enpty after separator
			var li = $('<li/>').appendTo(list);
			var m = new RegExp(properties.url).exec(v); // looks like the regex has some kind of memory so every second value it won't match
			if (!m) {
				li.text(v);
				continue;
			}
			var url = 
				location.protocol + '//'
				+ (m[2] ? m[2] + (m[3] || '') : location.host)
				+ '/' + m[4]
				+ (m[5] || '');
			li.html('<a href="'+url+'">'+url+'</a>');
		}
	};
	// load properties automatically
	$().bind('repos-proplist-loaded', function(event, container) {
		//$('.column:eq(0)').append(container).addClass('section');
		$('<h2/>').text('Attributes/metadata').prependTo(container);
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
		
	});
	$('.proplist .action-load').trigger('click');
});
