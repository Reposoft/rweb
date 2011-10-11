<?php
/** 
 * Form validation logic for repos.
 * This is not really a plugin, because it is tightly integrated with the repos code.
 * @package conf
 */
require('validation.inc.php');

/**
 * Filename rule is in SvnEdit script
 */
require('../../edit/SvnEdit.class.php');

require('../../conf/Presentation.class.php');


// define custom validation rules
class TestUsernameRule extends RuleRegexp {
	function TestUsernameRule($fieldname) {
		$this->RuleRegexp($fieldname,
			'Username is 4-20 characters and can not contain special characters',
			'/^$|^[a-zA-Z0-9]{4,20}$/'); // not required
	}
}

// create validation instances
new Rule('name');
new FilenameRule('filename', false);
// description has no rule
new TestUsernameRule('testuser');

// dispatch to form processing if 'submit' was pressed (button must have name="submit")
if (isset($_REQUEST[SUBMIT])) {
	processForm();
} else {
	presentForm();
}

function presentForm($message='enter some values and press submit') {
	$p = Presentation::getInstance();
	$p->assign('message', $message);
	$p->display();
}

function processForm() {
	Validation::expect('name', 'filename', 'description', 'testuser');
	presentForm("we're happy campers");
}

?>