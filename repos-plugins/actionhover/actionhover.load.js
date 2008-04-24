// customized formatting of file actions
Repos.service('index/', function actionhover() {
	var hide = function(q) { q.removeClass('hover').find('.actions').css('visibility','hidden'); };
	var a = function() {
		hide($('ul.index li.hover'));
		$(this).addClass('hover').find('.actions').css('visibility','visible');
	};
	hide($('ul.index li').mouseover(a).focus(a));
	$('ul.index').mouseout(function() {
		hide($('li.hover', this));
	});
	// show first file to hint there are hidden actions (actions on folders are used rarely)
	$('.file:first').parent().find('.actions').css('visibility','visible');
});
