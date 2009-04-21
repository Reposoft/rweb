// http://meta.wikimedia.org/wiki/Help:Wikitext_examples
SyntaxHighlighter.brushes.Wiki = function()
{
	var keywords =	'';

	this.regexList = [
		{ regex: new RegExp('^== .* ==$', 'gm'), css: 'syntax-wiki-heading1' },
		{ regex: new RegExp('\'\'[^\'][^\'(?=\')]*\'\'', 'gm'), css: 'syntax-wiki-italics' },
		{ regex: new RegExp('\'\'\'.*\'\'\'', 'gm'), css: 'syntax-wiki-bold' }
		];

};

SyntaxHighlighter.brushes.Wiki.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Wiki.aliases	= ['wiki'];
