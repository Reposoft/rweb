<?php
// display the headers of a page in the application

require('../../account/login.inc.php');

// can not use 'target' because that is for autologin
if (isset($_GET['check'])) {
	printHeaders($_GET['check']);
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
	echo("<p><strong>URL: <a check=\"blank\" href=\"$target\">$target</a></strong></p>");
	if (isset($_GET['auth'])) {
		echo('<p>Authenticating as &quot;'.getReposUser().'&quot;</p>');
		$headers = getHttpHeaders($target, getReposUser(), _getReposPass());
	} else {
		$headers = getHttpHeaders($target);
	}
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
<label for="check">Local target URL</label>
<input type="text" name="check" value="/" size="80"/>
</p>
<?php if(isLoggedIn()) { ?>
<p>
<label for="auth">Authenticate as current user (<?php echo(getReposUser()); ?>)?</label>
<input type="checkbox" name="auth"/>
<p>
<?php } ?>
<label for="submit"></label>
<input type="submit"/>
</p>
</fieldset>
</form>
<h3>predefined queries</h3>
<?php
	$repo_root = getRepository();
	echo('<p><a id="repository" href="./?check='.urlencode($repo_root).'">repository root</a></p>');
	echo('<p><a id="repositorytest" href="./?check='.urlencode($repo_root.'/test/trunk/').'">[repository]/test/trunk/</a></p>');
	echo('<p><a id="repositorypublicfolder" href="./?check='.urlencode($repo_root.'/demoproject/trunk/public/').'">/demoproject/trunk/public/</a></p>');
	echo('<p><a id="repositorypublicfile" href="./?check='.urlencode($repo_root.'/demoproject/trunk/public/xmlfile.xml').'">/demoproject/trunk/public/xmlfile.xml</a></p>');
	echo('<p><a id="100bytes.js" href="./?check=100bytes.js">100bytes.js</a></p>');
	echo('<p><a id="head.js-file" href="./?check='.urlencode(getWebapp().'scripts/head.js').'">head.js</a></p>');
	echo('<p><a id="head.js-folder" href="./?check='.urlencode(getWebapp().'scripts/head.js/').'">head.js/</a></p>');
	echo('<p><a id="favicon.ico" href="./?check='.urlencode('/favicon.ico').'">/favicon.ico</a></p>');
	echo('<p><a id="repos.xsl" href="./?check='.urlencode('/repos/view/repos.xsl').'">repos.xsl</a></p>');
	echo('<p><a id="repos1.gif" href="./?check='.urlencode('/repos/style/logo/repos1.gif').'">repos1.gif</a></p>');
	echo('<p><a id="repos1.png" href="./?check='.urlencode('/repos/style/logo/repos1.png').'">repos1.png</a></p>');
	echo('<p><a id="global.css" href="./?check='.urlencode('/repos/style/global.css').'">global.css</a></p>');
	echo('<p><a id="listxml" href=".?check='.urlencode('/repos/open/list/?target=/demoproject/trunk/public/xmlfile.xml').'">open/list/</a></p>');
	echo('<p><a id="validationerror" href=".?check='.urlencode('/repos/plugins/validation/?name=&filename=&description=&testuser=123').'">validation error</a></p>');
	echo('<p><a id="validationerror-json" href=".?check='.urlencode('/repos/plugins/validation/?name=&filename=&description=&testuser=123&serv=json').'">validation error JSON</a></p>');
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
