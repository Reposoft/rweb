<?php
/**
 * Simple PHP validation plugin for repos.se.
 * 
 * PHP code usage
 * - to validate a request parameter if it is set, and do nothing iif not set
 * <code>
 * new MyRule('paramname');	// stops page execution if field exists but is invalid
 * // --- or ---
 * $rule = new MyRule('paramname');	
 * $value = $rule->getValue(); 	// to get the field value when validation is successful
 * </code>
 * 
 * - to validate that a request parameter really is set, and make sure it has been validated with a rule
 * <code>Validation::expect('field1', 'field2' ... );	// stops page execution if not all exist and are valid
 * 
 * If client side validation is used: field1, field2 ... should also be marked 'required' in the form.
 * 
 * This means that you never need to call Rule->validate.
 * Simply instantiate the rule and tell the Validation engine if the field is required.
 * 
 * @package validation
 */

// this class is not dependent on any specific Repos functionality
require_once(dirname(dirname(dirname(__FILE__))).'/lib/json/json.php');

/**
 * The request parameter that identifies a request as _only_ validation, no processing.
 */
define('VALIDATION_KEY', 'validation');

// Validation class uses this array to store all instantiated Rules
$_validation_rules=array();

function validation_getHeadTags($webapp) {
	//if (strpos($_SERVER['REQUEST_URI'], '/edit/') && !isset($_GET[SUBMIT])) {
		return array('<script type="text/javascript" src="'.$webapp.'plugins/validation/validation.js"></script>');
	//} else {
	//	return array();
	//}
}

/**
 * @return true if this request is _only_ for validation of a field
 */
function validationRequest() {
	return array_key_exists(VALIDATION_KEY, $_GET);
}

/**
 * Represents a syntactic rule for a field.
 * 
 * It may be a form field or a service parameter.
 * 
 * Custom rules should extend this class and override valid($value).
 * 
 * Note that rules are applied in the order they are instantiated,
 * validation rules that involve many fields can be created by 
 * defining rules that have references to each other.
 * If rules are called using AJAX from a form, in wrong order, they 
 * can return something like "The 'name' parameter must be set first".
 * 
 * The default rule checks that the field has a non-empty value.
 */
class Rule {
	var $_message;
	var $fieldname;
	var $_value = null; // set if validation passes
	/**
	 * Creates a rule instance for a field in the current page.
	 * Subclasses with their own constructor must call <code>$this->Rule($fieldname, $message);</code>
	 *  in their constructor, AFTER setting fields that validate depends on.
	 * @param String $fieldname the parameter name when the field value is received
	 * @param String $message the error message if validation fails, defaults to "This is a required field"
	 */
	function Rule($fieldname, $message='This is a required field') {
		$this->_message = $message;
		$this->fieldname = $fieldname;
		Validation::_add($this);
	}
	/**
	 * Validates a field value according to the rule, and returns the error message if invalid.
	 * Also sets the $value field.
	 * Default implementation calls Rule->valid($value).
	 * It is best to only override valid($value). If overriding this method, make sure it sets _value field if valid.
	 * @param String $value the value to check
	 * @return String error message if invalid, null if valid (use empty() to check)
	 */
	function validate($value) { if ($this->valid($value)) { $this->_value=$value; return null; } else { return $this->_message; } }
	/**
	 * Represents the actual validation logic.
	 * 
	 * Don't rely on that this methid runs the validation loginc.
	 * Subclasses may override validate($value) instead,
	 * to have different messages for different types of errors,
	 * which leaves this function undefined.
	 * 
	 * Default implementation is a "required field" check, returning false if value is empty. 
	 * 
	 * @param String $value the value to check
	 * @return true if valid, false if invalid
	 */
	function valid($value) { return !empty($value); }
	/**
	 * Allows calling code to get the validated value without using the superglobal arrays.
	 * @return mixed the parameter value, if valid, null if not
	 */
	function getValue() { return $this->_value; }
}

/**
 * Creates a validation rule from an 'ereg' family regular expression.
 *
 * Example: <code>new RuleEreg('name', 'required field, must not contain @', '[^@]+');</code>
 */
class RuleEreg extends Rule {
	var $regex;
	function RuleEreg($fieldname, $message, $eRegEx) {
		$this->regex = $eRegEx;
		$this->Rule($fieldname, $message);
	}
	function valid($value) {
		return ereg($this->regex, $value);
	}
}

/**
 * Creates a validation rule from an 'preg' family regular expressions.
 *
 * Example: <code>new RuleRegexp('name', 'required field, must not contain @', '/^[^@]+$/g');</code>
 */
class RuleRegexp extends Rule {
	var $regexp;
	function RuleRegexp($fieldname, $message, $pRegexp) {
		$this->regexp = $pRegexp;
		$this->Rule($fieldname, $message);
	}
	function valid($value) {
		return preg_match($this->regexp, $value);
	}
}

class RuleRegexpInvert extends RuleRegexp {
	function RuleRegexpInvert($fieldname, $message, $pRegexpNoMatch) {
		$this->RuleRegexp($fieldname, $message, $pRegexpNoMatch);
	}
	function valid($value) {
		return !parent::valid($value);
	}
}

/**
 * The validation library, with static functions for applying rules.
 *
 * Before processing a submit, pages should always do
 * <code>Validation::expect('field1', 'field2', ... )</code>
 * where the argument list contains all parameters that should be in the request.
 * This enforces two things:
 * 1. Each expected field exists, so there will be no undefined index errors.
 * 2. All submitted fields that have a matching Rule instance have been validated according to the rule.
 * To create a custom validation rule, simply subclass Rule and instantiate.
 * @see Rule
 */
class Validation {
	/**
	 * Validate a list of required fields.
	 * Note that this does not mean that each field value must be set (see class description).
	 * @param unknown_type $requiredFieldsSeparatedByComma
	 */
	function expect($requiredFieldsSeparatedByComma) {
		if (validationRequest()) {
			_validation_trigger_error("This is a validation request ".$_SERVER['QUERY_STRING'].", but no rule has been enforced. Operation aborted.");
		}
		$n = func_num_args();
		for($i=0; $i<$n; $i++) {
			$fieldname = func_get_arg($i);
			if (!array_key_exists($fieldname, $_REQUEST)) {
				_validation_trigger_error("Can not continue because the expected field '$fieldname' is not submitted");
			}
		}
	}
	/**
	 * Adds a new rule to the rules hash.
	 */
	function _add(&$rule) {
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
	function _run(&$rule, $value) {
		$r = $rule->validate($value);
		if (!empty($r)) {
			_validation_trigger_error('Error in field "'.$rule->fieldname.'", value "'.$value.'": '.$r
			//." \n(with script support enabled this should have been reported when the form was submitted)"
			." \n\nClick 'back' and try again.");
		}
	}
	/**
	 * Excecutes a rule and sends a JSON reponse, then does exit(0);
	 * @param Rule $rule to run
	 * @param String $value to be validated by the rule
	 */
	function _respond(&$rule, $value) {
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

function _validation_trigger_error($msg) {
	if (headers_sent()) {
		trigger_error($msg . ' (Unexpected: headers have already been sent, so HTTP 412 could not be set)', E_USER_WARNING);
		return; 
	}
	header('HTTP/1.1 412 Precondition Failed');
	trigger_error($msg, E_USER_WARNING);
}

?>
