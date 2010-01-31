
var rules = false;
$().bind('repos-propedit-init', function(ev, reposPropeditRules) {
	rules = reposPropeditRules;
	
	// define some test rules
	rules.add('repostest:noedit', false);
	rules.add('repostest:noshow', 0);
	rules.add('repostest:oneliner', /n.*/);
	rules.add('repostest:multiline', /^p.*/m);
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
	
assert(Repos.propedit.Rules,'Rules object should be defined');
assert(rules.add, 'Start event handler should get a rules reference');

assert(rules.suggest('rep'), 'Should get a lot of property names that start with sv');
assert(rules.suggest('repostest'), 'Should get a lot of property names that start with sv');
assert(6, rules.suggest('repostest:').length, 'The number of test rules above');

var keywordsRule = rules.get('svn:keywords');
assert(keywordsRule, 'Should get built in Rule instance for svn:keywords');

//assert(!rules.get('svn:externals'), 'target is a file so svn:externals should not have a rule');
// ... or a forbid rule?
assert(!rules.suggest('svn:externals').length, 'target is a file so svn:externals should not be suggested');

// enum rule
var enumRule = rules.get('repostest:enum');
assert(enumRule.append && !enumRule.exec, 'get should return Rule instances, not the argument to add');
assert(enumRule.test(''), 'ok because this enum supports empty value');
assert(enumRule.test('v1'), 'ok because value is in the enum');
assert(!enumRule.test('v1x'), 'not ok because value is not in the enum');
assert(!enumRule.test('v8'), 'not ok because enum values are case sensitive');

// read-only rule
assert(!rules.get('repostest:noedit').test('a'), 'boolean false as rules means editing is not allowed');
assert(!rules.get('repostest:noshow').test('a'), '0 is same as false');

// rule matching different properties
assert(rules.get('repostest2:anything'), 'regexp property match');

// text rules
// i'm nut sure this modification of RegExp meaning is such a good idea
assert(!rules.get('repostest:oneliner').test('one'), 'not ok because it does not start with n');
assert(rules.get('repostest:oneliner').test('ne'), 'starts with n in our homemade syntax');

assert(!rules.get('repostest:oneliner').test('foo\nbar'), 'we use the multiline regexp flag to note where multiline property values are allowed');
assert(!rules.get('repostest:multiline').test('poo\nbar'), 'multiline flag present, each line must start with p according to rule');
assert(rules.get('repostest:multiline').test('poo\npar'), 'multiline flag present, each line must start with p according to rule');

// form fields
var enumField = rules.get('repostest:enum').getFormField('v9');
assert(enumField && enumField[0] && enumField.size() == 1, 'should return a field element in a jQuery instance');
console.log(enumField[0].name);
assert('select', enumField[0].node,  'enum rule should have a drop down');
assert(5, $('option', enumField).size(), 'should be one option per enum value');
assert(!$('option[value=""]',enumField).attr('selected'), 'default value should not be selected because a current value was given');
assert($('option[value="v9"]',enumField).attr('selected'), 'current value should be selected');

// text, multiline or not
var mField = rules.get('repostest:multiline').getFormField('my\nvalue');
assert('textarea', mField[0].node);

var oField = rules.get('repostest:oneliner').getFormField();
assert('input', oField[0].node);
assert('text', mField.attr('type'));

var rField = rules.get('repostest:noedit').getFormField('read\nonly\nfrom web interface');
assert('textarea', rField[0].node);
assert(rField.attr('readonly'), 'form field should be read only for this property');
assert(rField.is('.readonly'), 'readonly class should be set to allow custom css');

var noField = rules.get('repostest:noshow').getFormField('x');
console.log(noField);
assert(0, noField.length, '0 is the same rule as false, but property should not be displayed in form');

// there is an assert library bug for this type of test
assert('expected', {}.nonExistingProperty,  'Should fail with: expected "expected" but got undefined');

});
