<?php

require "../../conf/Presentation.class.php";
require "../edit.class.php";


//if (isset($target)) {
//	doAutomerge($source, $target);
//} else {
//	$p = new Presentation();
//	$p->display();
//}

// dispatch
if (isset($_GET[SUBMIT])) {
	doAutomerge($_GET['branchFile']); 
} else {
	$target = getTarget();
	$template = new Presentation();
	$template->assign('repository', getRepository());
	$template->assign('target', $target);
	$template->assign('oldname', basename($target));
	$template->assign('folder', getParent($target));	
	$template->assign('branchFileArray', svnList('/branches'));
	$template->display();
}

function svnList($listFilesInFolder) {
	$list = new Edit('list');
	$repositoryRootUrl = getRepository();
	$list->addArgUrl($repositoryRootUrl . $listFilesInFolder);
	$list->execute();
	$listResult = $list->getOutput();
	return $listResult;
}

function doAutomerge($source){
	$p = new Presentation();
	$target = substr($source, strrpos($source, "-")+1);
	$sourcePath = '/branches/'.$source;
	$sourceUrl = getRepository().$sourcePath;	// http://localhost/LocalRepos/branches/test.xml
	$targetUrl = getRepository().'/trunk/'.$target;	// http://localhost/LocalRepos/trunk/
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
	if (sizeof($revisionNumber) < 2){
		$p->showError("No changes have been made in the file.");
		exit;
	}

	// Merge source from the older revision to head into working copy
	// svn merge -r 25:26 http://localhost/LocalRepos/branches/test.xml test.xml
	$merge = new Edit('merge');
	$merge->addArgOption('-r ' . $revisionNumber[1] . ':' . $revisionNumber[0]);
	$merge->addArgUrl($sourceUrl);
	$merge->addArgPath($temporaryWorkingCopy . basename($target));	// temporaryWorkingCopy/test.xml
	$merge->execute();
	$mergeCommand = 'svn ' . $merge->getCommand();
	$mergeResult = $merge->getOutput();

	// Conflict??
	if (strpos($mergeResult, 'C') === 0){
		$p->showError("Damnit, I can not merge these files! " . $mergeResult);
		exit;
	}
	
	// Automaticly resolve conflict
	
	
	// Commit working copy
	$updatefile = toPath($temporaryWorkingCopy . basename($target));
	$oldsize = filesize($updatefile);
	$commit = new Edit('commit');
	$commit->setMessage('merge -r '.$revisionNumber[1] . ':' . $revisionNumber[0] . ' ' . $sourcePath);	// svn merge -r 66:67 "http://localhost/LocalRepos/branches/1164368098-test-test.xml" "C:/WINDOWS/TEMP/localhost_2Frepos/merge/174.tmp/test.xml"
	$commit->addArgPath($temporaryWorkingCopy);
	$commit->execute();
	$commitResult = $commit->getOutput();
	// Seems that there is a problem with svn 1.3.0 and 1.3.1 that it does not always see the update on a replaced file
	//  remove this block when we don't need to support svn versions onlder than 1.3.2
	if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
		if ($oldsize != filesize($updatefile)) {
			exec("echo \"\" >> \"$updatefile\"");
			$commit = new Edit('commit');
			$commit->setMessage($mergeCommand);
			$commit->addArgPath($temporaryWorkingCopy);
			$commit->execute();
			$commitResult = $commit->getOutput();
		}
	}
	
	//echo implode("<BR>", $checkoutResult) . "<BR><BR>";
	//echo implode("<BR>", $logResult) . "<BR><BR>";
	//echo implode("<BR>", $mergeResult) . "<BR><BR>";
	//echo implode("<BR>", $commitResult);
	$commit->present($p, $targetFolder);
}
?>
