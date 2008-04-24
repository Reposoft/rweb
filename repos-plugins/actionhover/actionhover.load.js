// customized formatting of file actions
Repos.service('index/', function actionhover() {
	$('ul.index li').mouseover(function() {
		$('ul.index li.hover').removeClass('hover').find('.actions').css('visibility','hidden');
		$(this).addClass('hover').find('.actions').css('visibility','visible');
	}).find('.actions').css('visibility','hidden');
	$('ul.index').mouseout(function() {
		$('li.hover', this).removeClass('hover').find('.actions').css('visibility','hidden');
	});
});
