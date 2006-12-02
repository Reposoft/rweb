<?php


function resolveConflicts($fileContents){
	
}

function findConflict($fileContents){
	$conflictMarker = false;
	foreach ($fileContents as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			$conflictMarker = true;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			$conflict[] = $value;
			return $conflict;
		}
		if ($conflictMarker == true){
			$conflict[] = $value;
		}
	}
}


function chooseWorking($fileContents){
	$deleteRow = false;
	foreach ($fileContents as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			unset($fileContents[$key]);
		}
		if (strpos($value, '=======') === 0){
			$deleteRow = true;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			unset($fileContents[$key]);
			$deleteRow = false;
		}
		if ($deleteRow == true){
			unset($fileContents[$key]);
		}
	}
	return $fileContents;
}

function chooseMerge($fileContents){
	$deleteRow = false;
	foreach ($fileContents as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			unset($fileContents[$key]);
			$deleteRow = true;
		}
		if (strpos($value, '=======') === 0){
			unset($fileContents[$key]);
			$deleteRow = false;
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			unset($fileContents[$key]);
		}
		if ($deleteRow == true){
			unset($fileContents[$key]);
		}
	}
	return $fileContents;
}
?>