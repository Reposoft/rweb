/**
 * Repos syntax highlighting plugin (c) repos.se 2006-2009
 * Using SyntaxHinglighter http://alexgorbatchev.com/wiki/SyntaxHighlighter
 */
// * Dynamic loading of brushes has been disabled, and can be found in reposweb-1.1-B1 */

Repos.syntax = new Object();
/* dp.SyntaxHinglighter library */
Repos.syntax.dp = new Object();
Repos.syntax.dp.path = 'lib/syntaxhighlighter/sh/';
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

$().ready(function() { 
	Repos.syntax.load();
} );

Repos.syntax.load = function() {
	Repos.syntax.activate();
};

Repos.syntax.activate = function() {
	//console.log('activating syntax highlighting');
	SyntaxHighlighter.config.tagName = 'textarea';
	$('textarea[readonly]').each( function() {
		var textarea = this;
		//console.log('Found readonly textarea class="'+textarea.getAttribute('class')+'" name="'+textarea.name+'"');
		for(type in Repos.syntax.map) {
			if ($(textarea).is('.'+type)) {
				var brush = Repos.syntax.map[type];
				textarea.setAttribute('class','brush:'+brush+'');
				//console.log('loading syntax "'+textarea.getAttribute('class')+'" for area "'+textarea.name+'"'); 
				break; // only one syntax per text area
			}
		}
	} );
	Repos.syntax.render();
};

Repos.syntax.render = function() {
	SyntaxHighlighter.all();
};
