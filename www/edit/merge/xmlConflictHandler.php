<?php


function resolveConflicts(&$fileContents, &$log){
	$conflict = findConflict($fileContents, $log);
	if ($conflict === false){
		return false;
	}
	if(!$conflict){
		$log[] = 'conflict solved';
		return true;
		//echo '<pre>';
		//echo htmlentities(implode($fileContents));
		//echo '</pre>';
		//exit;
	} else {
		return chooseMerge($fileContents, $conflict, $log);
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $fileContents
 * @param unknown_type $log
 * @return false if a conflict is found inside table, returns an array $conflict if
 *         it finds conflicts that can be resolved
 */
function findConflict(&$fileContents, &$log){
	$conflictMarker = false;
	$tableMarker = false;
	foreach ($fileContents as $key=>$value){
		if (strpos(ltrim($value), '<Table ') === 0){
			$tableMarker = true;
		}
		if (strpos($value, '<<<<<<< .working') === 0){
			$conflictMarker = true;
			if ($tableMarker == true){
				$log[] = 'Conflict inside the table';
				return false;
			}
		}
		if ($conflictMarker == true){
			$log[] = "Found conflict at $key";
			$conflict[$key] = $value;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			$conflictMarker = false;
			return $conflict;
		}
		if (strpos(ltrim($value), '</Table ') === 0){
			$tableMarker = false;
		}
	}
}


function chooseWorking($fileContents, $conflict, &$log){
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
	resolveConflicts($fileContents, $log);
}

function chooseMerge(&$fileContents, $conflict, &$log){
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
			$log[] = 'Chosing the value from the trunk';
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			unset($conflictSolved[$key]);
		}
		if ($deleteRow == true){
			unset($conflictSolved[$key]);
		}
	}
	array_splice($fileContents, array_shift(array_keys($conflict)), count($conflict), $conflictSolved);
	return resolveConflicts($fileContents, $log);
}
?>