dp.sh.Brushes.Acl = function()
{
	var keywords =	'r rw';

	this.regexList = [
		{ regex: new RegExp('^\\[\\/.*\\]', 'gm'), css: 'path' },
		{ regex: new RegExp('^.*=(?=\\s*$)', 'gm'), css: 'noaccess' },
		{ regex: new RegExp('^.*=(?=\\s*r\\s*$)', 'gm'), css: 'readonly' },
		{ regex: new RegExp('^.*=(?=\\s*rw\\s*$)', 'gm'), css: 'readwrite' },
		// { regex: new RegExp('^@[\\S]+', 'gm'), css: 'group' },
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'), css: 'keyword' }
		];

	this.CssClass = 'syntax-acl';
}

dp.sh.Brushes.Acl.prototype	= new dp.sh.Highlighter();
dp.sh.Brushes.Acl.Aliases	= ['acl'];
