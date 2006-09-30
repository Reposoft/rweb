<?php
// display the headers of a page in the application

require('../../account/login.inc.php');

$target = getTarget();
if ($target) {
	printHeaders($target);
} else {
	printForm();
}

function printHeaders($target) {
	head();
	if(strBegins($target, '/')) {
		$target = repos_getSelfRoot().$target;
	}
	if(strpos($target,'/')===false) {
		$target = repos_getSelfUrl().$target;
	}
	echo("<h2>$target</h2>");
	$headers = getHttpHeaders($target);
	echo("<pre>");
	foreach($headers as $h => $v) {
		echo('|');
		if (is_string($h)) echo($h.': ');
		echo($v."|\n");
	}
	echo("</pre>");
	echo('<a class="action" href="./">&lt; back</a>');
}

function printForm() {
	head();
?>
<form action="./" method="get">
<fieldset>
<legend>enter URL to query</legend>
<p>
<label for="target">Local target URL</label>
<input type="text" name="target" value="/"/>
</p>
<p>
<label for="submit"></label>
<input type="submit"/>
</p>
</fieldset>
</form>
<h3>predefined queries</h3>
<?php
	$repo_root = getConfig('repo_url');
	echo('<p><a id="repository" href="./?target='.urlencode($repo_root).'">repository root</a></p>');
	echo('<p><a id="repositorytest" href="./?target='.urlencode($repo_root.'/test/trunk/').'">[repository]/test/trunk/</a></p>');
	echo('<p><a id="100bytes.js" href="./?target=100bytes.js">100bytes.js</a></p>');
	echo('<p><a id="head.js-path" href="./?target='.urlencode(getWebapp().'/scripts/head.js').'">head.js</a></p>');
	echo('<p><a id="head.js-script" href="./?target='.urlencode(getWebapp().'/scripts/head.js/').'">head.js/</a></p>');
	echo('<p><a id="back" class="action" href="../">&lt; back</a></p>');
	foot();
}

function head() {
?>
<html>
<head>
<title>repos.se: test HTTP headers</title>
<link href="../../style/global.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Check HTTP headers of repos page</h1>
<?php
}

function foot() {
?>
</body>
</html>
<?php
}
?>
