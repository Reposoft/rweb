<?php
// display the headers of a page in the application
require('../../account/login.inc.php');
require('../../open/ServiceRequest.class.php');

// quick links to common header checks
$repo_root = getRepository();
$links = array(
	'repository' => $repo_root,
	'repositorytest' => $repo_root.'/test/trunk/',
	'repositorypublicfolder' => $repo_root.'/demoproject/trunk/public/',
	'repositorypublicfile' => $repo_root.'/demoproject/trunk/public/xmlfile.xml',
	'100bytes.js' => '100bytes.js',
	'head.js-file' => getWebapp().'scripts/head.js',
	'head.js-folder' => getWebapp().'scripts/head.js/',
	'favicon.ico' => '/favicon.ico',
	'repos.xsl' => getWebapp().'view/repos.xsl',
	'repos1.gif' => getWebapp().'style/logo/repos1.gif',
	'repos1.png' => getWebapp().'style/logo/repos1.png',
	'global.css' => getWebapp().'style/global.css',
	'listxml' => getWebapp().'open/list/?target=/demoproject/trunk/public/xmlfile.xml',
	'validationerror' => getWebapp().'plugins/validation/?name=&testuser=123',
	'validationerror-json' => getWebapp().'plugins/validation/?name=&testuser=123&serv=json',
	'error' => getWebapp().'test/errorhandling/?case=3',
	'logxml' => getWebapp().'open/log/?target=/demoproject/trunk/public/'
	);
	
// can not use 'target' because that is for autologin
if (isset($_GET['check'])) {
	printHeaders($_GET['check']);
} else {
	printForm($links);
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
	if (strContains($target, '?'));
	
	$request = new ServiceRequest($target, array(), false);
	$request->setResponseTypeDefault();
	$request->exec();
	$headers = $request->getResponseHeaders();
	echo("<pre>");
	foreach($headers as $h => $v) {
		echo('|');
		if (is_string($h)) echo($h.': ');
		echo($v."|\n");
	}
	echo("</pre>");
	echo('<p>');
	echo('<br />Address: '.$request->getResponseUrl());
	echo('<br />HTTP status: '.$request->getStatus());
	echo('<br />Content type: '.$request->getResponseType());
	echo('<br />Response time: '.$request->getResponseTime().' s');
	echo('</p>');
	if (isset($headers['Content-Length'])) {
		$givenLength = intval($headers['Content-Length']);
		$actualLengt = $request->getResponseSize(); // strlen($request->getResponse())
		if ($givenLength == $actualLengt) {
			echo '<p>Content length '.$headers['Content-Length'].' matches response size.</p>';
		} else {
			echo '<p class="error">Content length mismatch. Headers says '.$headers['Content-Length']
				.' but response body is '.strlen($request->getResponse());
		}
	}
	echo('<a class="action" href="./">&lt; back</a>');
	echo('<p>&nbsp;</p><h3>Response:</h3><small><pre>');
	echo(htmlspecialchars($request->getResponse()));
	echo('</pre></small>');
}

function printForm($links) {
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
	foreach ($links as $id => $url) {
		echo('<a id="'.$id.'" href="./?check='.urlencode($url).'">'.$url.'</a> <small> [#'.$id.']</small><br />');
	}
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
