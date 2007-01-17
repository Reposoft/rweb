
console.log('syntax plugin loading');

$(document).ready(function() { 
	syntaxLoad(); 
} );

function syntaxLoad() {
	console.log('adding');
	var path = 'lib/dpsyntax/dp.SyntaxHighlighter/';
	
	var c = Repos.addScript(path + 'Scripts/shCore.js');
	$(c).load(function() { console.log('core loaded') });
	
	var b1 = Repos.addScript(path + 'Scripts/shBrushPhp.js');
	var b2 = Repos.addScript(path + 'Scripts/shBrushJScript.js');
	var b3 = Repos.addScript(path + 'Scripts/shBrushXml.js');
	var b4 = Repos.addScript(path + 'Scripts/shBrushCss.js');
	
	$(b4).load(
		syntaxActivateWhenLoaded
	);
}

function syntaxActivateWhenLoaded() {
	if (typeof(dp)=='undefined' || typeof(dp.SyntaxHighlighter)=='undefined') {
		console.log('reload, wait for SyntaxHighlighter to load');
		window.setTimeout(syntaxActivateWhenLoaded, 100);
		return;
	}
	console.log('activating syntax highlighting');
	$('textarea').each(function(i) {
		console.log('found textarea ' + this.id);
		dp.SyntaxHighlighter.HighlightAll(this.name);
	} );
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