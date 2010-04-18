<?php
/**
 *
 *
 * @package
 */
require('../../reposweb.inc.php');
require(ReposWeb.'conf/repos.properties.php');
require(ReposWeb.'conf/Report.class.php');
require(ReposWeb.'open/ServiceRequest.class.php');

if (isset($_GET['threads']) && $_GET['threads'] > 1) {
	testMultiple($_GET['threads'], $_GET['url'], $_GET['count']);
} elseif (isset($_GET['url'])) {
	testBruteForce($_GET['url'], $_GET['count']);
} else {
	showPage();
}

function showPage() {
$r = new Report('Test repeated login attempts');
if(isset($_GET['url'])) {
	$url = $_GET['url'];
} else {
	$url = getSelfRoot().'/?login=user';
}
$r->info('<form action="./" method="get">'.
	'<p>Guess passwords for URL <input id="url" name="url" type="text" size="50" value="'.$url.'"/></p>'.
	'<p>Username will be "test" for all guesses. Password will be "textX" '.
	'with X from 0 to <input id="count" name="count" type="text" size="3" value="10"/></p>'.
	'<p>Number of threads <input id="threads" name="threads" type="text" size="2" value="1"/>'.
	'<p><input id="submit" type="submit" value="run"/></p></form>');
}

// open serveral frames.
// TODO fix so that Firefox runs them simultaneously. Opera does.
function testMultiple($threads, $url, $count) {
	$run = getSelfUrl().'?url='.rawurlencode($url).'&count='.$count;
	$cols = substr(str_repeat(','.floor(100/$threads).'%', $threads),1);
	$frames = '<?xml version="1.0" encoding="${encoding}" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=${encoding}" />
<title>Multiple password guessing threads</title>
</head>
<frameset cols="'.$cols.'">';
   for ($i=1; $i<=$threads; $i++) {
   	$frames .= '<frame name="t'.$i.'" src="'.$run.'"/>';
   }
	$frames .= '    <noframes><body><p>Uses frames</p></body></noframes>
</frameset>
</html>';
	echo $frames; exit;
}

function testBruteForce($url, $count) {
	$r = new Report('Repeated login attempts');	
	$r->info("Running $count login attemts at url '$url'");
	$s = ServiceRequest::forUrl($url, false);
	$s->exec();
	if ($s->getStatus() != 401) {
		$r->error('The URL must return 401 Authorization Required but got http code '.$s->getStatus()); 
		$r->debug($s->getResponseHeaders());
		$r->display();
		return;
	}
	$time = microtime_float();
	for ($i = 1; $i<$count; $i++) {
		$s = ServiceRequest::forUrl($url, false);
		$s->_username = 'test';
		$s->_password = 'test'.$i;
		$s->exec();
		$time2 = microtime_float();
		$status = $s->getStatus();
		// normal BASIC login return 401 on invalid credentials, Repos returns an info page
		if ($status == 401 || ($status == 200 && strpos($url,'?login'))) {
			$r->info("$i: Got status $status, response time ".sprintf("%01.3f",$time2-$time)." seconds");
		} else { // unexpected return code
			$r->error("$i: Got status $status, response time ".sprintf("%01.3f",$time2-$time)." seconds");
			if ($s->getStatus() == 200) {
				$r->debug($s->getResponse());
			} else {
				$r->debug($s->getResponseHeaders());
			}
		}
		flush();
		$time = $time2;
	}
	$r->info('Last login done with password "test"');
	$s = ServiceRequest::forUrl($url, false);
	$s->_username = 'test';
	$s->_password = 'test';
	$s->exec();
	$status = $s->getStatus();
	$r->info('Got status '.$status);
	$r->display();
}

function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

?>
