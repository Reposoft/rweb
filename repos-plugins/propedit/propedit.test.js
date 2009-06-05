
var rules = false;
$().bind('repos-propedit-init', function(ev, reposPropeditRules) {
	rules = reposPropeditRules;
	
	// define some test rules
	rules.add('repostest:oneliner', /.*/);
	rules.add('repostest:multiline', /^p.*/m);
	rules.add('repostest:enum', ['v1', 'v9', 'V8', 'w3']);
});

$().ready(function() {
	
assert(Repos.propedit.Rules,'Rules object should be defined');
assert(rules.add, 'Start event handler should get a rules reference');

assert(rules.suggest('rep'), 'Should get a lot of property names that start with sv');
assert(rules.suggest('repostest'), 'Should get a lot of property names that start with sv');
assert(3, rules.suggest('repostest:').length, 'The number of test rules above');

var keywordsRule = rules.get('svn:keywords');
assert(keywordsRule, 'Should get built in Rule instance for svn:keywords');

//assert(!rules.get('svn:externals'), 'target is a file so svn:externals should not have a rule');
// ... or a forbid rule?
assert(!rules.suggest('svn:externals').length, 'target is a file so svn:externals should not be suggested');

// text rules, note that get
var enumRule = rules.get('repostest:enum');
assert(enumRule.append && !enumRule.exec, 'get should return Rule instances, not the argument to add');
assert(!rules.get('repostest:oneliner').test('foo\nbar'), 'we use the multiline regexp flag to note where multiline property values are allowed');
assert(rules.get('repostest:multiline').test('foo\nbar'), 'multiline flag present');

});
