SyntaxHighlighter.brushes.Diff = function()
{
	var keywords =	'Index:';

	this.regexList = [
		{ regex: new RegExp('^-.*$', 'gm'),	css: 'syntax-diff-removed' },
		{ regex: new RegExp('^[+].*$', 'gm'), css: 'syntax-diff-added' },
		{ regex: new RegExp('. No newline at end of file\s*$', 'gm'), css: 'syntax-diff-nonewline' },
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'syntax-diff-keyword' }
		];

};

SyntaxHighlighter.brushes.Diff.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Diff.aliases	= ['diff'];
