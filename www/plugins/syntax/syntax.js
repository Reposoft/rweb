
$(document).ready(function() { 
	syntaxLoad(); 
} );

function syntaxLoad() {
	var path = 'lib/dpsyntax/dp.SyntaxHighlighter/';
	var c = Repos.addScript(path + 'Scripts/shCore.js');
	
	var b1 = Repos.addScript(path + 'Scripts/shBrushPhp.js');
	var b2 = Repos.addScript(path + 'Scripts/shBrushJScript.js');
	var b3 = Repos.addScript(path + 'Scripts/shBrushXml.js');
	
	$(c).load(function() {
	$('textarea').each(function(i) {
		dp.SyntaxHighlighter.HighlightAll(this.name);
	} );
	}
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