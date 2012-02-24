
Repos.service('index/', function() {
	var clickhandler = function(ev) {
		var item = $(this).parent().find('a:first');
		var href = item.attr('href') + '&serv=embed';
		var title = item.text();
		var extendbox = $('<div/>').addClass('extendbox');
		//extendbox.insertBefore('ul.index');
		extendbox
			//.text(href)
			.html('<iframe src="' + href + '" width="100%" height="100%"></iframe>')
        	.dialog({
	            autoOpen: false,
	            modal: false,
	            height: 500,
	            width: '55em',
	            title: title
	        });
        extendbox.dialog('open');		
	};
	$('.index li').each(function() {
		var extend = $('<span>&raquo;</span>').addClass('itemextend').appendTo(this);
		extend.click(clickhandler);
	});
});
