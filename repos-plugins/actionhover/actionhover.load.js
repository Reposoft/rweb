// Set classes for CSS that shows focus on pages showing multiple targets
// Adds class background to file container that is not in focus
// Adds class focus to file container on mouse over
// not yet: Adds class focus to actions box inside file container on mouse over
Repos.service('index/', function actionhover() {
	var cf = 'focus';
	var cb = 'background';
	var f, p, n, pp, nn, ppp, nnn;
	var fo = function() {
		f = $(this).removeClass(cb).addClass(cf);
		p = f.prev().addClass('n' + cf);
		pp = p.prev().addClass('nn' + cf);
		ppp = pp.prev().addClass('nnn' + cf);
		n = f.next().addClass('n' + cf);
		nn = n.next().addClass('nn' + cf);
		nnn = nn.next().addClass('nnn' + cf);
	};
	var focus = function() {
		f.removeClass(cf).addClass(cb);
		p.removeClass('n' + cf);
		pp.removeClass('nn' + cf);
		ppp.removeClass('nnn' + cf);
		n.removeClass('n' + cf);
		nn.removeClass('nn' + cf);
		nnn.removeClass('nnn' + cf);
		fo.call(this);
	};
	var all = $('ul.index > li');
	all.addClass(cb).mouseenter(focus);
	// show first file to hint there are hidden actions (actions on folders are used rarely)
	fo.call($('.file:first').parent());
});
