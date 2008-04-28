// customized formatting of file actions
Repos.service('index/', function actionhover() {
	var hide = function(q) { q.removeClass('hover').find('.actions').css('visibility','hidden'); };
	var a = function() {
		hide($('ul.index li.hover'));
		$(this).addClass('hover').find('.actions').css('visibility','visible');
	};
	// show actions on mouseover and key focus
	hide($('ul.index li').mouseover(a).focus(a));
	$('ul.index').mouseout(function() {
		hide($('li.hover', this));
	});
	// show first file to hint there are hidden actions (actions on folders are used rarely)
	var first = $('.file:first').parent().find('.actions').css('visibility','visible'); // don't hover
	$('ul.index').one('mouseover', function() { first.css('visibility','hidden'); })
});
