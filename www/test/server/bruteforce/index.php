<?php
/**
 *
 *
 * @package
 */
require('../../../conf/repos.properties.php');
require('../../../conf/Report.class.php');
require('../../../open/ServiceRequest.class.php');

if(isset($_GET['url'])) {
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
	'<p><input id="submit" type="submit" value="run"/></p></form>');
}

function testBruteForce($url, $count) {
	$param = array();
	if ($q = strpos($url, '?')) {
		$p = explode('&', substr($url, $q+1));
		foreach($p as $pa) {
			list($key, $value) = explode('=', $pa);
			$param[$key] = $value;
		}
		$url = substr($url, 0, $q);
	}
	
	$r = new Report('Repeated login attempts');	
	$r->info("Running $count login attemts at url '$url'");
	if (count($param)) $r->debug($param);
	$s = new ServiceRequest($url, $param, false);
	$s->exec();
	if ($s->getStatus() != 401) {
		$r->error('The URL must return 401 Authorization Required but got http code '.$s->getStatus()); 
		$r->debug($s->getResponseHeaders());
		$r->display();
		return;
	}
	$time = microtime_float();
	for ($i = 1; $i<=$count; $i++) {
		$s = new ServiceRequest($url, $param, false);
		$s->_username = 'test';
		$s->_password = 'test'.$i;
		$s->exec();
		$time2 = microtime_float();
		$status = $s->getStatus();
		// normal BASIC login return 401 on invalid credentials, Repos returns an info page
		if ($status == 401 || ($status == 200 && isset($param['login']))) {
			$r->info("Got status $status, response time ".sprintf("%01.3f",$time2-$time)." seconds");
		} else { // unexpected return code
			$r->error("Got status $status, response time ".sprintf("%01.3f",$time2-$time)." seconds");
			if ($s->getStatus() == 200) {
				$r->debug($s->getResponse());
			} else {
				$r->debug($s->getResponseHeaders());
			}
		}
		flush();
		$time = $time2;
	}
	$r->display();
}

function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

?>
