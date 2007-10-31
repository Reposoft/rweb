// http://meta.wikimedia.org/wiki/Help:Wikitext_examples
dp.sh.Brushes.Wiki = function()
{
	var keywords =	'';

	this.regexList = [
		{ regex: new RegExp('^== .* ==$', 'gm'),	css: 'heading1' },
		{ regex: new RegExp('\'\'[^\'][^\'(?=\')]*\'\'', 'gm'), css: 'italics' },
		{ regex: new RegExp('\'\'\'.*\'\'\'', 'gm'), css: 'bold' }
		];

	this.CssClass = 'syntax-wiki';
};

dp.sh.Brushes.Wiki.prototype	= new dp.sh.Highlighter();
dp.sh.Brushes.Wiki.Aliases	= ['wiki'];