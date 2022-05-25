<?php

// conflict types
define('CONFLICT_EXCEL_TABLE', 1);
define('CONFLICT_EXCEL_FORMULA', 2);

function resolveConflicts(&$fileContents, &$log){
	$conflicts = findConflict($fileContents, $log);
	if (count($conflicts) === 0){
		return true;
	}
	
	foreach ($conflicts as $key => $value) {
		markAutoResolve($conflicts[$key]);
		// abort if there is at least one conflict that can not be autoresolved
		if (!$conflicts[$key]->isResolved()) return false;
	}
	// now we know we can solve all conflicts automatically
	
	$offset = 0;	// when array_splice is used we always remove some lines from $fileContents array and
					// that causes our $conflicts array to point to wrong lines in $fileContents array.
					// this is why we need to adjust our values for start-, limit- and endLine every time
					// we remove lines from $fileContents array. 
	foreach ($conflicts as $c) {
		$c->startLine = $c->startLine - $offset;	// calculate new values for conflict markers
		$c->limitLine = $c->limitLine - $offset;
		$c->endLine = $c->endLine - $offset;
		$conflictLinesLength = $c->getEndLine() - $c->getStartLine() + 1;	// get the size of the conflict
		$resolvedLinesLength = sizeof($c->getResolvedLines());				// get the size of the resolved conflict
		$offset = $offset + $conflictLinesLength - $resolvedLinesLength;	// calculate new offset
		array_splice($fileContents, $c->getStartLine(), $conflictLinesLength, $c->getResolvedLines());
	}
	// all conflicts replaced with selected contents
	return true;
}

/**
 * @param Conflict $conflict instance that should be updated with selected resolve strategy
 */
function markAutoResolve(&$conflict) {
	if ($conflict->isType(CONFLICT_EXCEL_TABLE)) {
		$isFormulaConflictArray = preg_grep('/<([^>]+)?Cell([^>]+)Formula="([^>]+)"([^>]+)?>/', $conflict->getBoth());
		if (count($isFormulaConflictArray) > 1) {
			preg_match('/<([^>]+)?Cell([^>]+)Formula="([^>]+)"([^>]+)?>/', $isFormulaConflictArray[0], $matchGroup);
			$formulaWorking = $matchGroup[3];
			preg_match('/<([^>]+)?Cell([^>]+)Formula="([^>]+)"([^>]+)?>/', $isFormulaConflictArray[1], $matchGroup);
			$formulaMerge = $matchGroup[3];
			if ($formulaWorking != $formulaMerge){
				// formula conflict - not allowed to change formulas
				return false;
			} else {
				$isDataConflictArray = preg_grep('/<([^>]+)?Data([^>]+)>([^<>])<\/([^>]+)?Data>/', $conflict->getBoth());
				if (count($isDataConflictArray) > 1) {
					preg_match('/<([^>]+)?Data([^>]+)>([^<>])<\/([^>]+)?Data>/', $isDataConflictArray[0], $matchGroup);
					$dataWorking = $matchGroup[3];
					preg_match('/<([^>]+)?Data([^>]+)>([^<>])<\/([^>]+)?Data>/', $isDataConflictArray[1], $matchGroup);
					$dataMerge = $matchGroup[3];
					if ($dataWorking != $dataMerge){
						// data conflict - data has changed - choose working
						$conflict->setResolveToWorking();
						return;
					}
				}
			}
		}
		return false;	
	}
	if (count(preg_grep("/<LastAuthor>/", $conflict->getWorking())) > 0) {
		$conflict->setResolveToMerge();
		return;
	}
	if (count(preg_grep("/<Company>/", $conflict->getWorking())) > 0) {
		$conflict->setResolveToWorking();
		return;
	}
	
	// default
	$conflict->setResolveToMerge();
}

function tableExceptions(){
	
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
				if (count(preg_grep('/<Cell([^>]+)Formula="([^>]+)"([^>]+)?>/i', $conflict)) != 0){
					$conflictMarker->setType(CONFLICT_EXCEL_FORMULA);
				}
				$log[] = 'Conflict inside the table';
				//return false;
			}
			// done with this conflict, save it and proceed to next
			$conflict[] = $conflictMarker;
			$conflictMarker = false;
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
	function __construct($startLine, $limitLine=0, $endLine=0) {
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
	
	function getBoth() {
		return array_merge_recursive($this->getWorking(), $this->getMerge());
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