<?php

require "../../conf/Presentation.class.php";
require "../SvnEdit.class.php";
require "xmlConflictHandler.php";


if (isset($_GET[SUBMIT])) {
	doAutomerge($_GET['branchFile']);
} else {
	$target = getTarget();
	$template = Presentation::getInstance();
	$template->assign('repository', getRepository());
	$template->assign('target', $target);
	$template->assign('oldname', basename($target));
	$template->assign('folder', getParent($target));
	if (!$target){
		trigger_error('Branches folder must be set as target parameter', E_USER_ERROR);
	}
	$template->assign('branchFileArray', svnList($target));
	$template->display();
}

function svnList($listFilesInFolder) {
	$list = new SvnEdit('list');
	$repositoryRootUrl = getRepository();
	$list->addArgUrl($repositoryRootUrl . $listFilesInFolder);
	$list->exec();
	$listResult = $list->getOutput();
	return $listResult;
	//if ($list->isSuccessful()){
	//	$listResult = $list->getOutput();
	//	return $listResult;
	//} else {
	//	trigger_error("There are no files to merge." . implode('<br />', $list->getOutput()) , E_USER_ERROR);
	//}
}

function doAutomerge($sourceFile){
	$p = Presentation::getInstance();
	$targetFile = substr($sourceFile, strrpos($sourceFile, "-")+1);
	$source = '/demoproject/branches/'.$sourceFile;
	$sourceUrl = getRepository().$source;	// http://localhost/LocalRepos/branches/test.xml
	$targetUrl = getRepository().'/demoproject/trunk/public/'.$targetFile;	// http://localhost/LocalRepos/trunk/
	$targetFolder = getParent($targetUrl);
	$temporaryWorkingCopy = getTempnamDir('merge');

	// Checkout target folder to a temporary working copy
	// svn checkout http://localhost/LocalRepos/trunk/ temporaryWorkingCopy

	$checkout = new SvnEdit('checkout');
	$checkout->addArgOption('--non-recursive');
	$checkout->addArgUrl($targetFolder);
	$checkout->addArgPath($temporaryWorkingCopy);
	$checkout->exec();

	// Get older revision of source (the revision when the branch was made)
	// svn log --stop-on-copy http://localhost/LocalRepos/branches/test.xml
	$log = new SvnEdit('log');
	$log->addArgOption('--stop-on-copy');
	$log->addArgUrl($sourceUrl);
	$log->exec();
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
		$p->showError("No changes have been made to the file.");
		displayEdit($p, $targetFolder);
		exit;
	}

	// Merge source from the older revision to head into working copy
	// svn merge -r 25:26 http://localhost/LocalRepos/branches/test.xml test.xml
	$merge = new SvnEdit('merge');
	$merge->addArgOption('-r ' . $revisionNumber[1] . ':' . $revisionNumber[0]);
	$merge->addArgUrl($sourceUrl);
	$merge->addArgPath($temporaryWorkingCopy . $targetFile);	// temporaryWorkingCopy/test.xml
	$merge->exec();

	// Conflict??

	foreach ($mergeResult as $value){
		if (strpos($value, 'C ') === 0){
			$filePath = $temporaryWorkingCopy . $targetFile;
			$fileContents = array_map('rtrim', file($filePath));
			if (strpos($fileContents[0], '<?xml') === 0){
				$result = resolveConflicts($fileContents);
				if (!$result){
					$p->showError("Cannot merge files " . $value);
					exit;
				} else {
					$fh = fopen($filePath, 'w');
					fwrite($fh, implode("\r\n", $fileContents));
					fclose($fh);
					$resolved = new Command('svn');
					$resolved->addArgOption('resolved');
					$resolved->addArg($filePath);
					$resolved->exec();
				}
			} else {
				$p->showError($value . " does not appear to be valid xml file.");
				exit;
			}
		}
	}


	// Automatically resolve conflict

	// Commit working copy
	$updatefile = toPath($temporaryWorkingCopy . $targetFile);
	$oldsize = filesize($updatefile);
	$commit = new SvnEdit('commit');
	$commit->setMessage('merge -r '.$revisionNumber[1] . ':' . $revisionNumber[0] . ' ' . $source);	// svn merge -r 66:67 "http://localhost/LocalRepos/branches/1164368098-test-test.xml" "C:/WINDOWS/TEMP/localhost_2Frepos/merge/174.tmp/test.xml"
	$commit->addArgPath($temporaryWorkingCopy);
	$commit->exec();
	// Seems that there is a problem with svn 1.3.0 and 1.3.1 that it does not always see the update on a replaced file
	//  remove this block when we don't need to support svn versions onlder than 1.3.2
	if ($commit->isSuccessful() && !$commit->getCommittedRevision()) {
		if ($oldsize != filesize($updatefile)) {
			exec("echo \"\" >> \"$updatefile\"");
			$commit = new SvnEdit('commit');
			$commit->setMessage($mergeCommand);
			$commit->addArgPath($temporaryWorkingCopy);
			$commit->exec();
		}
	}

	displayEdit($p, $targetFolder);
}
?>
