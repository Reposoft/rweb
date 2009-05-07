
Repos.service('home/', function() {
	$('#intro h1').empty().append('<img alt="Simonsoft logo" width="1134" height="92" border="0" align="absmiddle" alt="repos.se" src="/repos-plugins/arbortext/simonsoft_logo_polo_1_150dpi.gif"/>');
});

Repos.service('index/', function() {
	$('.file-xml, .file-fos, .file-style, .file-dcf, .file-mcf, .file-sgm, .file-dita, .file-ditamap')
		.css('background-image', 'url("/repos-plugins/arbortext/abx_icon.png")');

	$('#commandbar #logo').attr('width','198').attr('src', '/repos-plugins/arbortext/simonsoft-name.gif');

});

Repos.target(/\.(xml|fos|style|dcf|mcf|sgm|dita|ditamap)$/, function() {
	if (Repos.isService('open/') || Repos.isService('edit/')) {
		var url = $('#urlcopy').val().replace(/^https?/, 'arbortext-editor:x-svn');
		$('<h3><a href="'+url+'" style="background-image:url(/repos-plugins/arbortext/abx_icon32.png);">Open in Arbortext</a></h3><p></p>')
			.appendTo('#activities');
	}
});

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
			var list = $('<dl class="properties"><lh>' + n + '</lh></dl>');
			for (p in prop[n]) {
				list.append('<dt>'+p+'</dt><dd>'+prop[n][p]+'</dd>');
			}
			container.append(list);
		}
		
	});
	$('.proplist .action-load').trigger('click');
});
