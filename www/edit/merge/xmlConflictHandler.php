<?php

// conflict types
define('CONFLICT_EXCEL_TABLE', 1);
define('CONFLICT_EXCEL_FORMULA', 2);

function resolveConflicts(&$fileContents, &$log){
	$conflicts = findConflict($fileContents, $log);
	if (count($conflicts) === 0){
		return true;
	}
	foreach ($conflicts as $c) {
		markAutoResolve($c);
		// abort if there is at least one conflict that can not be autoresolved
		if (!$c->isResolved()) return false;
	}
	// now we know we can solve all conflicts automatically
	foreach ($conflicts as $c) {
		array_splice($fileContents, $c->getStartLine(), $c->getEndLine() - $c->getStartLine() + 1, $c->getResolvedLines());
	}
	// all conflicts replaced with selected contents
	return true;
}

/**
 * @param Conflict $conflict instance that should be updated with selected resolve strategy
 */
function markAutoResolve(&$conflict) {
	//$conflict->g
	if ($conflict->isType(CONFLICT_EXCEL_TABLE)) {
			
	} else {
		// for example header contents
		$conflict->setResolveToMerge();
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
	$conflict = array();
	// flags for analyzed contents
	$tableMarker = false;
	// find all conflicts in file
	foreach ($fileContents as $key=>$value){
		if (strpos(ltrim($value), '<Table ') === 0){
			$tableMarker = true;
		}
		if (strpos($value, '<<<<<<< .working') === 0){
			if ($conflictMarker) trigger_error('already in a conflict', E_USER_ERROR);
			$conflictMarker = new Conflict($key);
			$log[] = 'created conflict instance';
		}
		else if (strpos($value, '=======') === 0){
			//if (!$conflictMarker) trigger_error('found === but there is no conflict', E_USER_ERROR);
			$conflictMarker->setLimitLine($key);
		}
		else if (strpos($value, '>>>>>>> .merge') === 0){
			$conflictMarker->setEndLine($key);
			if ($tableMarker == true){
				$conflictMarker->setType(CONFLICT_EXCEL_TABLE);
				// Every exception to no conflicts inside table rule should be here
				if (count(preg_grep("/<Cell.*Formula.*>/", $conflict)) != 0){
					$conflictMarker->setType(CONFLICT_EXCEL_FORMULA);
					//chooseWorking($fileContents, $conflict, $log);
					//return resolveConflicts($fileContents, $log);
				}
				$log[] = 'Conflict inside the table';
				//return false;
			}
			// done with this conflict, save it and proceed to nexr
			$conflict[] = $conflictMarker;
			$conflictMarker = false;
			//chooseMerge($fileContents, $conflict, $log);
			//return resolveConflicts($fileContents, $log);
		}
		else if ($conflictMarker){
			$log[] = "Line $key is inside conflict";
			$conflictMarker->addLineContents($value);
		}
		if (strpos(ltrim($value), '</Table ') === 0){
			$tableMarker = false;
		}
	}
	return $conflict;
}

/**
 * 
 * @param Conflict $conflict the conflict instance
 */
function chooseWorking(&$fileContents, $conflict, &$log){
	
	/*
	$conflictSolved = $conflict;
	$deleteRow = false;
	foreach ($conflictSolved as $key=>$value){
		if (strpos($value, '<<<<<<< .working') === 0){
			unset($conflictSolved[$key]);
			$log[] = 'Chosing the value from the trunk';
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
	*/
	//array_splice($fileContents, array_shift(array_keys($conflict)), count($conflict), $conflictSolved);
}

function chooseMerge(&$fileContents, $conflict, &$log){
	array_splice($fileContents, $conflict->getStartLine(), $conflict->getEndLine() - $conflict->getStartLine() + 1, $conflict->getMerge());
	return; 
	
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
			$log[] = 'Chosing the value from the branch';
		}
		if (strpos($value, '>>>>>>> .merge') === 0){
			unset($conflictSolved[$key]);
		}
		if ($deleteRow == true){
			unset($conflictSolved[$key]);
		}
	}

	array_splice($fileContents, array_shift(array_keys($conflict)), count($conflict), $conflictSolved);
}

/**
 * Represents a generic conflict as a result of a merge operation.
 * File can be any plaintext format.
 * 
 * Conflict resolution logic can mark the conflict as resolvable using the setResolveTo* methods.
 */
class Conflict {
	var $startLine;
	var $limitLine;
	var $endLine;
	var $types = Array();
	
	var $working = array();
	var $merge = array();
	
	// false until someone says this conflict can be resolved
	var $resolve = false;
	
	/**
	 * Enter description here...
	 *
	 * @param int $startLine
	 * @param int $limitLine
	 * @param int $endLine
	 * @return Conflict
	 */
	function Conflict($startLine, $limitLine=0, $endLine=0) {
		$this->startLine = $startLine;
		$this->limitLine = $limitLine;
		$this->endLine = $endLine;
		
	}
	
	function setLimitLine($lineNumber) {
		$this->limitLine = $lineNumber;
	}
	
	function setEndLine($lineNumber) {
		$this->endLine = $lineNumber;
	}
	
	function getStartLine() {
		return $this->startLine; 
	}

	function getLimitLine() {
		return $this->limitLine; 
	}
	
	function getEndLine() {
		return $this->endLine; 
	}
	/**
	 * 
	 * @param int $typeConstant the type of conflict, based on analysis of the entire file
	 */
	function setType($typeConstant) {
		$this->types[] = $typeConstant;
	}
	
	/**
	 * @return true if this conflict is of the given type
	 */
	function isType($typeConstant) {
		return in_array($typeConstant, $this->types);
	}
	
	/**
	 * 
	 * @param $line String without newline
	 */
	function addLineContents($line) {
		if ($this->limitLine == 0) {
			$this->working[] = $line;
		} else {
			$this->merge[] = $line;
		}
	}
	
	function getWorking() {
		return $this->working;
	}
	
	function getMerge() {
		return $this->merge;
	}
	
	/**
	 * @return true if this conflict has been marked as something we can resolve automatically
	 */
	function isResolved() {
		return $this->resolve;
	}
	
	/**
	 * Marks this conflict to be resolved automatically by selecting 'mine' contents.
	 */
	function setResolveToWorking() {
		$this->resolve = 1;
	}
	
	/**
	 * Marks this conflict to be resolved automatically by selecting 'yours' (branched) contents.
	 */
	function setResolveToMerge() {
		$this->resolve = 2;
	}
	
	/**
	 * Get the contents selected by last setResolvedTo method call.
	 */
	function getResolvedLines() {
		if (!$this->isResolved()) trigger_error('This conflict can not be automatically resolved', E_USER_ERROR);
		if ($this->resolve == 1) {
			return $this->getWorking();
		} else {
			return $this->getMerge();
		}
	}
}
?>