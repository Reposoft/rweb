SyntaxHighlighter.brushes.Acl = function()
{
	var keywords =	'r rw';

	this.regexList = [
		{ regex: new RegExp('^\\[\\/.*\\]', 'gm'), css: 'syntax-acl-path' },
		{ regex: new RegExp('= *$', 'gm'), css: 'syntax-acl-noaccess' },
		{ regex: new RegExp('= *r *$', 'gm'), css: 'syntax-acl-readonly' },
		{ regex: new RegExp('= *rw *$', 'gm'), css: 'syntax-acl-readwrite' },
		{ regex: new RegExp('\\[groups\\]', 'gm'), css: 'syntax-acl-groups' },
		{ regex: new RegExp('^@[\\S]+', 'gm'), css: 'syntax-acl-group' }
		];

};

SyntaxHighlighter.brushes.Acl.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Acl.aliases	= ['acl'];
