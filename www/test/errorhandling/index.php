<?php

if (!isset($_GET['case'])) {
	$links = array(
	'01: tigger_error direcly at page start' => '?case=1',
	'02: tigger_error without specified E level' => '?case=2',
	'03: tigger_error after require repos.properties.php' => '?case=3',
	'04: repos.properties.php without specified E level' => '?case=4',
	'05: tigger_error after require Presentation.class.php' => '?case=5',
	'06: showErrorNoRedirect at an instance of Presentation.class.php' => '?case=6',
	'07: showError at an instance of Presentation.class.php' => '?case=7',
	'13: tigger_error after require repos.properties.php and "echo" output' => '?case=13',
	'16: showErrorNoRedirect at an instance of Presentation.class.php, after "display"' => '?case=16',
	'17: showError at an instance of Presentation.class.php, after "display"' => '?case=17',
	'23: tigger_error after require repos.properties.php, as JSON' => '?case=23&serv=json',
	'26: showErrorNoRedirect at an instance of Presentation.class.php, as JSON' => '?case=26&serv=json',
	'27: showError at an instance of Presentation.class.php, as JSON' => '?case=27&serv=json'
	);
	printPage($links);
	return;
}

$case = $_GET['case'];

if ($case==1) {
	trigger_error("Message $case.", E_USER_ERROR);
}
if ($case==2) {
	trigger_error("Message $case.");
}
if ($case==3) {
	require('../../conf/repos.properties.php');
	trigger_error("Message $case.", E_USER_ERROR);
}
if ($case==4) {
	require('../../conf/repos.properties.php');
	trigger_error("Message $case.");
}
if ($case==5) {
	require('../../conf/Presentation.class.php');
	trigger_error("Message $case.", E_USER_ERROR);
}
if ($case==6) {
	require('../../conf/Presentation.class.php');
	$p = new Presentation();
	$p->assign('text', 'Hello');
	$p->showErrorNoRedirect("Message $case.");
}
if ($case==7) {
	require('../../conf/Presentation.class.php');
	$p = new Presentation();
	$p->assign('text', 'Hello');
	$p->showError("Message $case.");
}
if ($case==13) {
	require('../../conf/repos.properties.php');
	echo("Page output before error.\n");
	trigger_error("Message $case.", E_USER_ERROR);
}
if ($case==16) {
	require('../../conf/Presentation.class.php');
	$p = new Presentation();
	$p->assign('text', 'Hello');
	$p->display();
	$p->showErrorNoRedirect("Message $case.");
}
if ($case==17) {
	require('../../conf/Presentation.class.php');
	$p = new Presentation();
	$p->assign('text', 'Hello');
	$p->display();
	$p->showError("Message $case.");
}
if ($case==23) {
	require('../../conf/repos.properties.php');
	trigger_error("Message $case.", E_USER_ERROR);
}
if ($case==26) {
	require('../../conf/Presentation.class.php');
	$p = new Presentation();
	$p->assign('text', 'Hello');
	$p->showErrorNoRedirect("Message $case.");
}
if ($case==27) {
	require('../../conf/Presentation.class.php');
	$p = new Presentation();
	$p->assign('text', 'Hello');
	$p->showError("Message $case.");
}


echo "<br />\n-- Unexpected: Error handling function did not exit. --";

function printPage($links) {?>
	<html>
	<head>
	<title>repos.se: test PHP error handling</title>
	<link href="../../style/global.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	<h1>Check PHP error handling</h1>
	<?php
	foreach ($links as $name => $url) {
		$id = substr($name, 0, 2);
		echo "<a id=\"$id\" href=\"$url\">$name</a><br />\n";
	}
	?>
	</body>
	</html>
	<?php
}

?>