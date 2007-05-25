dp.sh.Brushes.Diff = function()
{
	var keywords =	'Index:';

	this.regexList = [
		{ regex: new RegExp('^-.*$', 'gm'),	css: 'removed' },
		{ regex: new RegExp('^[+].*$', 'gm'), css: 'added' },
		{ regex: new RegExp('. No newline at end of file\s*$', 'gm'), css: 'nonewline' },
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'), css: 'keyword' }
		];

	this.CssClass = 'syntax-diff';
}

dp.sh.Brushes.Diff.prototype	= new dp.sh.Highlighter();
dp.sh.Brushes.Diff.Aliases	= ['diff'];
