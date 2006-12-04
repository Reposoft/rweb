<?php


function resolveConflicts($fileContents){
	$conflict = findConflict($fileContents);
	if(!$conflict){
		echo implode('<br />', $fileContents);
		exit;
	} else {
		chooseMerge($fileContents, $conflict);
	}
}

function findConflict($fileContents){
	$conflictMarker = false;
	foreach ($fileContents as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			$conflictMarker = true;
		}
		if ($conflictMarker == true){
			$conflict[$key] = $value;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			$conflictMarker = false;
			return $conflict;
		}
	}
}


function chooseWorking($fileContents, $conflict){
	$conflictSolved = $conflict;
	$deleteRow = false;
	foreach ($conflictSolved as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			unset($conflictSolved[$key]);
		}
		if (strpos($value, '=======') === 0){
			$deleteRow = true;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			unset($conflictSolved[$key]);
			$deleteRow = false;
		}
		if ($deleteRow == true){
			unset($conflictSolved[$key]);
		}
	}
	array_splice($fileContents, array_shift(array_keys($conflict)), count($conflict), $conflictSolved);
	resolveConflicts($fileContents);
}

function chooseMerge($fileContents, $conflict){
	$conflictSolved = $conflict;
	$deleteRow = false;
	foreach ($conflictSolved as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			unset($conflictSolved[$key]);
			$deleteRow = true;
		}
		if (strpos($value, '=======') === 0){
			unset($conflictSolved[$key]);
			$deleteRow = false;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			unset($conflictSolved[$key]);
		}
		if ($deleteRow == true){
			unset($conflictSolved[$key]);
		}
	}
	array_splice($fileContents, array_shift(array_keys($conflict)), count($conflict), $conflictSolved);
	resolveConflicts($fileContents);
}
?>