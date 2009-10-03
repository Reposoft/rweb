// Set classes for CSS that shows focus on pages showing multiple targets
// Adds class background to file container that is not in focus
// Adds class focus to file container on mouse over
// not yet: Adds class focus to actions box inside file container on mouse over
Repos.service('index/', function actionhover() {
	var cf = 'focus';
	var cb = 'background';
	var f, p, pp, n, nn;
	var foc = function() {
		f = $(this).removeClass(cb).addClass(cf);
		p = f.prev().addClass('near' + cf);
		pp = p.prev().addClass('nearnear' + cf);
		n = f.next().addClass('near' + cf);
		nn = n.next().addClass('nearnear' + cf);
	};
	var focus = function() {
		f.removeClass(cf).addClass(cb);
		p.removeClass('near' + cf);
		pp.removeClass('nearnear' + cf);
		n.removeClass('near' + cf);
		nn.removeClass('nearnear' + cf);
		foc.call(this);
	};
	var all = $('ul.index > li');
	all.addClass(cb).mouseenter(focus);
	// show first file to hint there are hidden actions (actions on folders are used rarely)
	foc.call($('.file:first').parent().removeClass(cb));
});
