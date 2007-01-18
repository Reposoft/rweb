/**
 * Repos syntax highlighting plugin (c) repos.se 2006
 * Using dp.SyntaxHinglighter http://www.dreamprojections.com/SyntaxHighlighter/
 */

Repos.syntax = new Object();
/* dp.SyntaxHinglighter library */
Repos.syntax.dp = new Object();
Repos.syntax.dp.path = 'lib/dpsyntax/dp.SyntaxHighlighter/';
/* The available highlighting types, each with its own js */
Repos.syntax.brush = {
	php: Repos.syntax.dp.path + 'Scripts/shBrushPhp.js',
	js: Repos.syntax.dp.path + 'Scripts/shBrushJScript.js',
	xml: Repos.syntax.dp.path + 'Scripts/shBrushXml.js',
	css: Repos.syntax.dp.path + 'Scripts/shBrushCss.js',
	wiki: 'plugins/syntax/shBrushWiki.js',
	diff: 'plugins/syntax/shBrushDiff.js'
};
/* Maps classes to syntax types, class => brush */
Repos.syntax.map = new Object();
Repos.syntax.map["php"] = "php";
Repos.syntax.map["js"] = "js";
Repos.syntax.map["xml"] = "xml";
Repos.syntax.map["html"] = "xml";
Repos.syntax.map["htm"] = "xml";
Repos.syntax.map["css"] = "css";
Repos.syntax.map["diff"] = "diff";
Repos.syntax.map["txt"] = "wiki";

// for(b in Repos.syntax.brush) { Repos.info('syntax "'+b+'": '+Repos.syntax.brush[b]); }

$(document).ready(function() { 
	Repos.syntax.load(); 
} );

Repos.syntax.load = function() {
	Repos.info('syntax plugin loading');	
	Repos.addScript(
		Repos.syntax.dp.path + 'Scripts/shCore.js',
		function() { Repos.syntax.activate(); }
	);
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
				Repos.addScript(
					Repos.syntax.brush[brush],
					function() {
						Repos.syntax.render(textarea); 
						Repos.info('loading syntax "'+textarea.getAttribute('class')+'" for area "'+textarea.name+'"'); 
					}
				);
				break; // only one syntax per text area
			}
		}
	} );
}

Repos.syntax.render = function(textarea) {
	dp.SyntaxHighlighter.HighlightAll(textarea.name);
}

/*
// ../../lib/dpsyntax/dp.SyntaxHighlighter/
// Styles/SyntaxHighlighter.css
<script class="javascript" src="Scripts/shCore.js"></script>
<script class="javascript" src="Scripts/shBrushCSharp.js"></script>
<script class="javascript" src="Scripts/shBrushPhp.js"></script>
<script class="javascript" src="Scripts/shBrushJScript.js"></script>
<script class="javascript" src="Scripts/shBrushJava.js"></script>
<script class="javascript" src="Scripts/shBrushVb.js"></script>
<script class="javascript" src="Scripts/shBrushSql.js"></script>
<script class="javascript" src="Scripts/shBrushXml.js"></script>
<script class="javascript" src="Scripts/shBrushDelphi.js"></script>
<script class="javascript" src="Scripts/shBrushPython.js"></script>
<script class="javascript" src="Scripts/shBrushRuby.js"></script>
<script class="javascript" src="Scripts/shBrushCss.js"></script>
<script class="javascript" src="Scripts/shBrushCpp.js"></script>
<script class="javascript">
dp.SyntaxHighlighter.HighlightAll('code');
</script>
*/