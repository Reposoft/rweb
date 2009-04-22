/**
 * Repos syntax highlighting plugin (c) repos.se 2006-2009
 * Using SyntaxHinglighter http://alexgorbatchev.com/wiki/SyntaxHighlighter
 */
// * Dynamic loading of brushes has been disabled, and can be found in reposweb-1.1-B1 */

Repos.syntax = new Object();
/* dp.SyntaxHinglighter library */
Repos.syntax.dp = new Object();
Repos.syntax.dp.path = 'lib/syntaxhighlighter/sh/';
Repos.syntax.path = Repos.getWebapp() + Repos.syntax.dp.path;
Repos.syntax.plugin = '/repos-plugins/syntaxhighlighter/';
/* Maps classes/types to syntax types, class => brush */
Repos.syntax.map = new Object();
Repos.syntax.map["php"] = "php";
Repos.syntax.map["js"] = "js";
Repos.syntax.map["xml"] = "xml";
Repos.syntax.map["html"] = "xml";
Repos.syntax.map["htm"] = "xml";
Repos.syntax.map["css"] = "css";
Repos.syntax.map["diff"] = "diff";
Repos.syntax.map["txt"] = "wiki";
Repos.syntax.map["accs"] = "acl";
Repos.syntax.map["htp"] = "htp";
// Note that SyntaxHighlighter also has aliases
// cat www/lib/syntaxhighlighter/sh/scripts/shBrush*.js | grep ".aliases"
// but we need to know them before they are loaded

Repos.syntax.bundled = ['Css', 'Java', 'Plain', 'Sql', 'Bash', 'Delphi', 'JScript', 'Python', 'Vb', 'Cpp', 'Perl', 'Ruby', 'Xml', 'CSharp', 'Groovy', 'Php', 'Scala', 'Diff'];
//Repos.syntax.custom = ['Diff', 'Acl', 'Wiki']; 
Repos.syntax.custom = ['Acl', 'Wiki']; // have to use the bundled diff instead of ours or rendering fails silently

$().ready(function() {
	//$.getScript(Repos.syntax.path + 'scripts/shCore.js', function() {
		// looks like it is not immediately available
		//window.setTimeout(Repos.syntax.setup, 100);
		Repos.syntax.setup();
	//} );
} );

// sets classes on all elements that  sho
Repos.syntax.setup = function() {
	// configure
	SyntaxHighlighter.config.tagName = 'textarea';
	// modify page so SyntaxHighlighter will detect the boxes
	// and load the required brushes
	$('textarea[readonly]').each( function() {
		var textarea = this;
		//console.log('Found readonly textarea class="'+textarea.getAttribute('class')+'" name="'+textarea.name+'"');
		for(type in Repos.syntax.map) {
			if ($(textarea).is('.'+type)) {
				var brush = Repos.syntax.map[type];
				Repos.syntax.load(brush);
				// syntaxhighlighter's notation for areas
				textarea.setAttribute('class','brush:'+brush+'');
				//console.log('loading syntax "'+textarea.getAttribute('class')+'" for area "'+textarea.name+'"'); 
				break; // only one syntax per text area
			}
		}
	} );
	// when all brushes have finished loading, highlight all
	// (API does not support highlight single element)
	//$().ajaxStop(function(){
		Repos.syntax.enable();
	//});
};

Repos.syntax.load = function(brush) {
	if (brush == 'js') brush == 'jscript'; // until we handle SyntaxHighlighter's aliases
	for(i in Repos.syntax.bundled) {
		if (brush == Repos.syntax.bundled[i].toLowerCase()) {
			//$.getScript(Repos.syntax.path + 'scripts/shBrush' + Repos.syntax.bundled[i] + '.js');
		}
	}
};

Repos.syntax.enable = function() {
	//console.log('Enabling all');
	//window.setTimeout(SyntaxHighlighter.all, 500);
	SyntaxHighlighter.all();
};
