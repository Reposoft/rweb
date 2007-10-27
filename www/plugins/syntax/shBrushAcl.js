dp.sh.Brushes.Acl = function()
{
	var keywords =	'r rw';

	this.regexList = [
		{ regex: new RegExp('^\\[\\/.*\\]', 'gm'), css: 'path' },
		{ regex: new RegExp('= *$', 'gm'), css: 'noaccess' },
		{ regex: new RegExp('= *r *$', 'gm'), css: 'readonly' },
		{ regex: new RegExp('= *rw *$', 'gm'), css: 'readwrite' },
		{ regex: new RegExp('\\[groups\\]', 'gm'), css: 'groups' },
		{ regex: new RegExp('^@[\\S]+', 'gm'), css: 'group' }
		];

	this.CssClass = 'syntax-acl';
};

dp.sh.Brushes.Acl.prototype	= new dp.sh.Highlighter();
dp.sh.Brushes.Acl.Aliases	= ['acl'];
