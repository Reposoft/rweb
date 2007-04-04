/**
 * Repos syntax highlighting plugin (c) repos.se 2006
 * Using dp.SyntaxHinglighter http://www.dreamprojections.com/SyntaxHighlighter/
 */
// * Dynamic loading of brushes has been disabled, and can be found in reposweb-1.1-B1 */

Repos.syntax = new Object();
/* dp.SyntaxHinglighter library */
Repos.syntax.dp = new Object();
Repos.syntax.dp.path = 'lib/dpsyntax/dp.SyntaxHighlighter/';
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

$(document).ready(function() { 
	Repos.syntax.load(); 
} );

Repos.syntax.load = function() {
	Repos.syntax.activate();
}

Repos.syntax.activate = function() {
	Repos.info('activating syntax highlighting');
	$('textarea[@readonly]').each( function() {
		var textarea = this;
		Repos.info('Found readonly textarea class="'+textarea.getAttribute('class')+'" name="'+textarea.name+'"');
		for(type in Repos.syntax.map) {
			if ($(textarea).is('.'+type)) {
				var brush = Repos.syntax.map[type];
				textarea.setAttribute('class',''+brush+':nocontrols');
				Repos.syntax.render(textarea); 
				Repos.info('loading syntax "'+textarea.getAttribute('class')+'" for area "'+textarea.name+'"'); 
				break; // only one syntax per text area
			}
		}
	} );
}

Repos.syntax.render = function(textarea) {
	dp.SyntaxHighlighter.HighlightAll(textarea.name);
}
