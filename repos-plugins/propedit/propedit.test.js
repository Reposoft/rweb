
var rules = false;
$().bind('repos-propedit-init', function(ev, reposPropeditRules) {
	rules = reposPropeditRules;
});

$().ready(function() {
	
assert(Repos.propedit.Rules,'Rules object should be defined');
assert(rules.add, 'Start event handler should get a rules reference');
assert(rules.suggest('sv'), 'Should get a lot of property names that start with svn');
var keywordsRule = rules.get('svn:keywords');
assert(keywordsRule, 'Should get Rule instance for svn:keywords');

});
