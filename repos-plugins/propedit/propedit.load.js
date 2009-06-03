
// events
$().bind('repos-propedit-start', function(ev, reposPropeditRules) {});

Repos.propedit = {
	rules: {
		/**
		 * Add a rule to property editing
		 * @param property property name
		 * @param rule regex for text value rule, array for enum rule
		 */
		add: function(property, rule) {
			Repos.propedit.rules._r[property] = Repos.propedit.rules._r[property]
				|| new Repos.propedit.Rule(); 
		},
		// the saved rules, propname->Repos.propedit.Rule, not to be edited directly
		_r: {
		},
		// suggest property names based on input string, returns array
		suggest: function(input) {
			var s = [];
			for (n in Repos.propedit.rules._r) {
				if (n.indexOf(input) == 0) s.push(n); 
			}
			return s;
		},
		// get a Repos.propedit.Rule
		get: function(propertyName) {
			return Repos.propedit.rules._r[propertyName];
		}
	},
	Rule: function() {
		this.append = function(rule) {
			// array means enum
			
			// multiline regexp means multiline property
		}
	}
};

// launch load event so other plugins can customize
$().ready(function() {
	$().trigger('repos-propedit-start', [Repos.propedit.rules]);
});

// page update, executed after trigger above
Repos.service('exit/propedit/', function() {
	
});

// immediate plugin customization, the same thing can be done from other plugins
$().bind('repos-propedit-start', function(ev, reposPropeditRules) {
	reposPropeditRules.add('svn:keywords', ['Date', 'Revision', 'Author', 'HeadURL', 'Id']);
	reposPropeditRules.add('svn:mime-type', /\w+\/.+/);
	reposPropeditRules.add('svn:ignore', /\w+\/.+/m);
});

