<?php
/**
 * Lists all the different error handling scenarios that can occur in repos (and that we know of).
 * 
 * Integration tests may use this page to assert proper error handling.
 * 
 * @package test
 */

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
	'15: Presentation->display with template variable not set' => '?case=15',
	'16: showErrorNoRedirect at an instance of Presentation.class.php, after "display"' => '?case=16',
	'17: showError at an instance of Presentation.class.php, after "display"' => '?case=17',
	'23: tigger_error after require repos.properties.php, as JSON' => '?case=23&serv=json',
	'26: showErrorNoRedirect at an instance of Presentation.class.php, as JSON' => '?case=26&serv=json',
	'27: showError at an instance of Presentation.class.php, as JSON' => '?case=27&serv=json',
	'60: Command exec with unknown command' => '?case=60',
	'61: SvnOpen exec with invalid svn operation' => '?case=61',
	'62: SvnEdit exec with invalid svn operation + displayEdit' => '?case=62',
	'63: SvnEdit exec with redirectWaiting' => '?case=63',
	'70: PHP5 Object Oriented notice' => '?case=70',
	'90: output as XML' => '?case=90&serv=xml',
	'91: output as plain text' => '?case=91&serv=text',
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
	$p = Presentation::getInstance();
	$p->assign('text', 'Hello');
	$p->showErrorNoRedirect("Message $case.");
}
if ($case==7) {
	require('../../conf/Presentation.class.php');
	$p = Presentation::getInstance();
	$p->assign('text', 'Hello');
	$p->showError("Message $case.");
}
if ($case==13) {
	require('../../conf/repos.properties.php');
	echo("Page output before error.\n");
	trigger_error("Message $case.", E_USER_ERROR);
}
if ($case==15) {
	require('../../conf/Presentation.class.php');
	$p = Presentation::getInstance();
	// not setting the template variable 'text'
	$p->display();
}
if ($case==16) {
	require('../../conf/Presentation.class.php');
	$p = Presentation::getInstance();
	$p->assign('text', 'Hello');
	$p->display();
	$p->showErrorNoRedirect("Message $case.");
}
if ($case==17) {
	require('../../conf/Presentation.class.php');
	$p = Presentation::getInstance();
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
	$p = Presentation::getInstance();
	$p->assign('text', 'Hello');
	$p->showErrorNoRedirect("Message $case.");
}
if ($case==27) {
	require('../../conf/Presentation.class.php');
	$p = Presentation::getInstance();
	$p->assign('text', 'Hello');
	$p->showError("Message $case.");
}
if ($case==60) {
	require('../../conf/Command.class.php');
	$cmd = new Command('testcommand');
	$cmd->exec();
}
if ($case==61) {
	require('../../open/SvnOpen.class.php');
	$open = new SvnOpen('test');
	$open->addArgOption('--invalidoperation');
	$open->exec();
}
if ($case==62) {
	require('../../conf/Presentation.class.php');
	require('../../edit/SvnEdit.class.php');
	$open = new SvnEdit('test');
	$open->addArgOption('--invalidoperation');
	$open->exec();
	displayEdit(Presentation::getInstance(), './');
}
if ($case==63) {
	require('../../conf/Presentation.class.php');
	require('../../edit/SvnEdit.class.php');
	$p = Presentation::getInstance();
	$p->enableRedirectWaiting();
	$open = new SvnEdit('test');
	$open->addArgOption('--invalidoperation');
	$open->exec();
	displayEdit(Presentation::getInstance(), './');
	exit; // not allowed to echo anything because it spoils redirected display
}
if ($case==70) {
	class T {
		function est() {
			echo "If you haven't seen an error message, E_STRICT notices are ignored";
			echo ". \nThat's not nessecarily wrong.";
		}
	}
	require('../../conf/repos.properties.php');
	echo(T::est());
	// should produce a notice in PHP5 because A is not a static method
}
if ($case==90) {
	require('../../conf/repos.properties.php');
	// currently there is no framework for presenting xml
	header('Content-type: text/xml');
	echo('<?xml version="1.0"?>
	<message>
		<case>90</case>
		<text>Hello</text>
		');
	trigger_error("Message $case.", E_USER_ERROR);
	echo('</message>');
}
if ($case==91) {
	require('../../conf/repos.properties.php');
	header('Content-type: text/plain');
	echo("Case 91.\nHello\n");
	trigger_error("Message $case.", E_USER_ERROR);
}

echo "<br />\n-- Unexpected: Error handling function did not exit. --";

function printPage($links) {?>
	<html>
	<head>
	<title>repos: test PHP error handling</title>
	<link href="../../style/global.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	<h1>Check PHP error handling</h1>
	<?php
	foreach ($links as $name => $url) {
		$id = substr($name, 0, 2);
		echo "<a id=\"$id\" href=\"$url\">$name</a><br />\n";
	}
	echo('<p><a id="back" class="action" href="../">&lt; back</a></p>');
	?>
	<p>Note that the error display functions must be tested for two cases: output_buffering on <em>and</em> off.</p>
	</body>
	</html>
	<?php
}

?>