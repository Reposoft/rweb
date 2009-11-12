
// mock
Repos = {
	target: function() {},
	service: function() {},
	getTarget: function() { return '/demoproject/trunk/images/'; },
	getBase: function() { return 'repo1'; },
	getRepository: function() { return 'http://localhost/svn/repo1'; }
};

test('getTemplateParents', function() {
	var parents = Repos.templates.getTemplateParents();
	equals(parents[0], '/demoproject/templates/', 'If current target is inside a project (trunk or bramches) look for project templates');
	equals(parents[1], '/templates/', 'Should always look for templates at [repository root]/templates/');
});
