// Set classes for CSS that shows focus on pages showing multiple targets
// Adds class background to file container that is not in focus
// Adds class focus to file container on mouse over
// not yet: Adds class focus to actions box inside file container on mouse over
Repos.service('index/', function actionhover() {
	var b = 'background';
	var f = 'focus';
	var all = $('ul.index li');
	console.log('all', all);
	all.addClass(b).mouseenter(function() {
		console.log('enter', b);
		$(this).removeClass(b).addClass(f);
	}).mouseleave(function() {
		$(this).removeClass(f).addClass(b);
	});
	// show first file to hint there are hidden actions (actions on folders are used rarely)
	var first = $('.file:first').parent().removeClass(b).addClass(f);
	$('ul.index').one('mouseover', function() { first.removeClass(f).addClass(b); })
});
