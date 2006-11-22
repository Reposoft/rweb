<?php

require "../../conf/Presentation.class.php";
require "../edit.class.php";

$target = getTarget();	// /trunk/test.xml
$source = '/branches/test.xml';

if (isset($target)) {
	doAutomerge($source, $target);
} else {
	$p = new Presentation();
	$p->display();
}

function doAutomerge($source, $target){
	$sourceUrl = getRepository().$source;	// http://localhost/LocalRepos/branches/test.xml
	$targetUrl = getRepository().$target;	// http://localhost/LocalRepos/trunk/
	$targetFolder = getParent($targetUrl);
	$temporaryWorkingCopy = getTempnamDir('merge');

	// Checkout target folder to a temporary working copy
	// svn checkout http://localhost/LocalRepos/trunk/ temporaryWorkingCopy

	$checkout = new Edit('checkout');
	$checkout->addArgOption('--non-recursive');
	$checkout->addArgUrl($targetFolder);
	$checkout->addArgPath($temporaryWorkingCopy);
	$checkout->execute();
	$checkoutResult = $checkout->getOutput();

	// Get older revision of source (the revision when the branch was made)
	// svn log --stop-on-copy http://localhost/LocalRepos/branches/test.xml
	$log = new Edit('log');
	$log->addArgOption('--stop-on-copy');
	$log->addArgUrl($sourceUrl);
	$log->execute();
	$logResult = $log->getOutput();
	foreach ($logResult as $key => $value){
		$revisionPointStart = strpos($value, 'r');
		$revisionPointEnd = strpos($value, ' | ');
		if ($revisionPointStart === 0){
			$number = substr($value, $revisionPointStart+1, $revisionPointEnd-1);
			if (is_numeric($number)){
				$revisionNumber[] = $number;
			} else {
				trigger_error("Can not find revision number in log file " . $sourceUrl, E_USER_ERROR);
			}
		}
	}

	// Merge source from the older revision to head into working copy
	// svn merge -r 25:26 http://localhost/LocalRepos/branches/test.xml test.xml
	$merge = new Edit('merge');
	$merge->addArgOption('-r ' . $revisionNumber[1] . ':' . $revisionNumber[0]);
	$merge->addArgUrl($sourceUrl);
	$merge->addArgPath($temporaryWorkingCopy . basename($target));	// temporaryWorkingCopy/test.xml
	$merge->execute();
	$mergeResult = $merge->getOutput();
	
	// Conflict??
	
	
	// Automaticly resolve conflict
	
	
	// Commit working copy
	$updatefile = toPath($temporaryWorkingCopy . basename($target));
	$oldsize = filesize($updatefile);
	$commit = new Edit('commit');
	$commit->setMessage('File merged');
	$commit->addArgPath($temporaryWorkingCopy);
	$commit->execute();
	$commitResult = $commit->getOutput();
	// Seems that there is a problem with svn 1.3.0 and 1.3.1 that it does not always see the update on a replaced file
	//  remove this block when we don't need to support svn versions onlder than 1.3.2
	if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
		if ($oldsize != filesize($updatefile)) {
			exec("echo \"\" >> \"$updatefile\"");
			$commit = new Edit('commit');
			$commit->setMessage('File merged');
			$commit->addArgPath($temporaryWorkingCopy);
			$commit->execute();
			$commitResult = $commit->getOutput();
		}
	}
	
	echo implode("<BR>", $checkoutResult) . "<BR><BR>";
	echo implode("<BR>", $logResult) . "<BR><BR>";
	echo implode("<BR>", $mergeResult) . "<BR><BR>";
	echo implode("<BR>", $commitResult);
	$p = new Presentation();
	$p->display();
}
?>
