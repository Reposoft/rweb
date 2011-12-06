<?php
/**
 * Reads a summary of the svn log
 * @return array[int revision => array]
 */
function getLog($targetUrl, $limit = 10) {
	$svnlog = new SvnOpen('log');
	//$svnlog->addArgOption('-q');
	$svnlog->addArgOption('--stop-on-copy'); // based-on-revision can't handle renamed files
	$svnlog->addArgOption('--limit', $limit, false);
	$svnlog->addArgUrl($targetUrl);
	if ($svnlog->exec()) {
		trigger_error(implode("\n", $svnlog->getOutput()), E_USER_ERROR);
	}
	$log = $svnlog->getOutput();
	$pattern = '/^r(\d+)\s+\|\s+(.*)\s+\|\s+(\d{4}-\d{2}-\d{2}).(\d{2}:\d{2}:\d{2})\s([+-]?\d{2})(\d{2})/';
	$result = array();
	$msg = '';
	$rev = false;
	for ($i = 0; $i<count($log); $i++) {
		if (strContains($log[$i],'---')) {
			if ($rev) $result[$rev]['message'] = $msg;
			$msg = '';
		} else if (preg_match($pattern, $log[$i], $m)) {
			$rev = $m[1];
			$result[$rev] = array('rev'=>$rev, 'user'=>$m[2], 'date'=>$m[3], 'time'=>$m[4], 'z'=>$m[5].':'.$m[6]);
		} else {
			$msg = trim($msg."\n".$log[$i]);
		}
	}
	return $result;
}
?>
