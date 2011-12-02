
var rules = false;
$(document).bind('repos-propedit-init', function(ev, reposPropeditRules) {
	rules = reposPropeditRules;
	
	// define some test rules
	rules.add('repostest:noedit', false);
	rules.add('repostest:noshow', 0);
	rules.add('repostest:oneliner', /n.*/);
	rules.add('repostest:multiline', /^n.*/m);
	rules.add('repostest:enum', ['', 'v1', 'v9', 'V8', 'w3']);
	rules.add('repostest:enumMulti', [['', 'v1', 'v9', 'V8', 'w3']]);
	rules.add(/^repostest2:/, false); // noedit for all properties in this namespace
});

$(document).ready(function() {
	
// test form should display what has been posted
$('form').submit(function(ev) {
	console.log(ev, this);
	ev.stopPropagation();
	alert(decodeURIComponent($(this).serialize().replace('&','\n', 'g')));
	return false;
});


// unit tests
test("all", function() {
	
	ok(Repos.propedit.Rules,'Rules object should be defined');
	ok(rules.add, 'Start event handler should get a rules reference');
	
	ok(rules.suggest('rep'), 'Should get a lot of property names that start with sv');
	ok(rules.suggest('repostest'), 'Should get a lot of property names that start with sv');
	equals(6, rules.suggest('repostest:').length, 'The number of test rules above');
	
	var keywordsRule = rules.get('svn:keywords');
	ok(keywordsRule, 'Should get built in Rule instance for svn:keywords');
	
	//assert(!rules.get('svn:externals'), 'target is a file so svn:externals should not have a rule');
	// ... or a forbid rule?
	ok(!rules.suggest('svn:externals').length, 'target is a file so svn:externals should not be suggested');
	
	// enum rule
	var enumRule = rules.get('repostest:enum');
	ok(enumRule.append && !enumRule.exec, 'get should return Rule instances, not the argument to add');
	ok(enumRule.test(''), 'ok because this enum supports empty value');
	ok(enumRule.test('v1'), 'ok because value is in the enum');
	ok(!enumRule.test('v1x'), 'not ok because value is not in the enum');
	ok(!enumRule.test('v8'), 'not ok because enum values are case sensitive');
	
	// read-only rule
	ok(!rules.get('repostest:noedit').test('a'), 'boolean false as rules means editing is not allowed');
	ok(!rules.get('repostest:noshow').test('a'), '0 is same as false');
	
	// rule matching different properties
	ok(rules.get('repostest2:anything'), 'regexp property match');
	
	// text rules
	// i'm nut sure this modification of RegExp meaning is such a good idea
	ok(!rules.get('repostest:oneliner').test('one'), 'not ok because it does not start with n');
	ok(rules.get('repostest:oneliner').test('ne'), 'starts with n in our homemade syntax');
	
	ok(!rules.get('repostest:oneliner').test('n\ne'), 'we use the multiline regexp flag to note where multiline property values are allowed');
	ok(!rules.get('repostest:multiline').test('e\ne'), 'multiline but rule should invalidate it because it does not start with n');
	ok(rules.get('repostest:multiline').test('n\ne'), 'multiline flag present, first line starts with n, should pass');
	
	// form fields
	var enumField = rules.get('repostest:enum').getFormField('v9');
	ok(enumField && enumField[0] && enumField.size() == 1, 'should return a field element in a jQuery instance');
	//console.log(enumField[0].name);
	equals(enumField[0].node, 'select', 'enum rule should have a drop down');
	equals($('option', enumField).size(), 5, 'should be one option per enum value');
	ok(!$('option[value=""]',enumField).attr('selected'), 'default value should not be selected because a current value was given');
	ok($('option[value="v9"]',enumField).attr('selected'), 'current value should be selected');
	
	// text, multiline or not
	var mField = rules.get('repostest:multiline').getFormField('my\nvalue');
	equals(mField[0].node, 'textarea');
	
	var oField = rules.get('repostest:oneliner').getFormField();
	equals(oField[0].node, 'input');
	equals(mField.attr('type'), 'text');
	
	var rField = rules.get('repostest:noedit').getFormField('read\nonly\nfrom web interface');
	equals(rField[0].node, 'textarea');
	ok(rField.attr('readonly'), 'form field should be read only for this property');
	ok(rField.is('.readonly'), 'readonly class should be set to allow custom css');
	
	var noField = rules.get('repostest:noshow').getFormField('x');
	//console.log(noField);
	equals(noField.length, 0, '0 is the same rule as false, but property should not be displayed in form');
	
});

});
