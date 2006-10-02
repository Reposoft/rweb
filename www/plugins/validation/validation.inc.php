<?php
/**
 * Simple PHP validation plugin for repos.se.
 * 
 * To use this for client side validation, the HTML must mark which fields are required.
 */

require_once(dirname(dirname(dirname(__FILE__))).'/lib/json/json.php');

$_validation_rules=array();

/**
 * @return true if this request is only for validation of a field
 */
function validationRequest() {
	return array_key_exists('validation', $_GET);
}

/**
 * Represents a syntactic rule for a field.
 * 
 * Custom rules should extend this class and override valid($value).
 * 
 * Note that rules are applied in the order they are instantiated,
 * validation rules that involve many fields can be created by 
 * defining rules that have references to each other.
 */
class Rule {
	var $_message;
	var $fieldname;
	/**
	 * Creates a rule instance for a field in the current page.
	 * Subclasses with their own constructor must call $this->Rule($fieldname, $message); first in their constructor.
	 * @param String $fieldname the parameter name when the field value is received
	 * @param String $message the error message if validation fails, defaults to "This is a required field"
	 */
	function Rule($fieldname, $message = "This is a required field") {
		$this->_message = $message;
		$this->fieldname = $fieldname;
		Validation::_add($this);
	}
	/**
	 * Validates a field value according to the rule, and returns the error message if invalid.
	 * @param String $value the value to check
	 * @return String error message if invalid, null if valid (use empty() to check)
	 */
	function validate($value) { if (!$this->valid($value)) return $this->_message; }
	/**
	 * Represents the actual validation logic.
	 * @param String $value the value to check
	 * @return true if valid, false if invalid
	 */
	function valid($value) { return !empty($value); }
}
/**
 * Creates a validation rule from an 'ereg' family regular expression.
 *
 * Example: <code>new RuleEreg('name', 'required field, must not contain @', '[^@]+');</code>
 */
class RuleEreg extends Rule {
	var $regex;
	function RuleEreg($fieldname, $message, $eRegEx) {
		$this->Rule($fieldname, $message);
		$this->regex = $eRegEx;
	}
	function valid($value) {
		return ereg($this->regex, $value);
	}
}
/**
 * The validation library, with static functions for applying rules.
 *
 * Before processing a submit, pages should always do
 * <code>Validation::expect('field1', 'field2', ... )</code>
 * where the argument list contains all required fields.
 * This enforces two things:
 * 1. Each expected field is submitted and has a String value of length 1 or more.
 * 2. All submitted fields that have a matching Rule instance have been validated according to the rule.
 * So if expect() is called without arguments, all rules are enforced, but no fields are required
 * (it is still up to the rule to accept empty values or not).
 * But that's not needed, because rules are always enfoced.
 */
class Validation {
	/**
	 * Validate a list of required fields
	 * @param unknown_type $requiredFieldsSeparatedByComma
	 */
	function expect($requiredFieldsSeparatedByComma) {
		if (validationRequest()) {
			trigger_error("This is a validation request ".$_SERVER['QUERY_STRING'].", but no rule has been enforced. Operation aborted.");
		}
		$n = func_num_args();
		for($i=0; $i<$n; $i++) {
			$fieldname = func_get_arg($i);
		
		}
	}
	/**
	 * Adds a new rule to the rules hash.
	 */
	function _add($rule) {
		global $_validation_rules;
		$f = $rule->fieldname;
		if (array_key_exists($f, $_REQUEST)) {
			$value = $_REQUEST[$f];
			if (validationRequest()) {
				Validation::_respond($rule, $value);		
			} else {
				Validation::_run($rule, $value);
			}
		} else {
			$_validation_rules[$f] = $rule;
		}
	}
	/**
	 * Executes a rule and do trigger_error on validation failure.
	 * @param Rule $rule to run
	 * @param String $value to be validated by the rule
	 */
	function _run($rule, $value) {
		$r = $rule->validate($value);
		if (!empty($r)) {
			trigger_error('Error in field "'.$rule->fieldname.'", value "'.$value.'": '.$r
			." \nWith script support this should have been reported when the form was submitted."
			." \nClick 'back' and try again.", E_USER_ERROR);
		}
	}
	/**
	 * Excecutes a rule and sends a JSON reponse, then does exit(0);
	 * @param Rule $rule to run
	 * @param String $value to be validated by the rule
	 */
	function _respond($rule, $value) {
		$response = array('id'=>$rule->fieldname, 'value'=>$value);
		$r = $rule->validate($value);
		if (empty($r)) {
			$response['success']=true;
			$response['msg']='';
		} else {
			$response['success']=false;
			$response['msg']=$r;
		}
		echo Validation::_serialize($response);
		exit(0);
	}
	/**
	 * Serializes a result array to JSON.
	 */
	function _serialize($responseArray) {
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $json->encode($responseArray);		
	}
}

?>
