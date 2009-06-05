
/**
 * Initialize propedit for a specific target
 * @param (Repos.propedit.Rules) rules instance with api seen below
 */
$().bind('repos-propedit-init', function(ev, reposPropeditRules) {});

Repos.propedit = {
	Rules: {
		/**
		 * (String) target the path, starts with slash, ends with slash for folder.
		 * Future implementation might support multiple targets, one per instance of Rules.
		 */
		target: Repos.getTarget(),
		/**
		 * Add a rule to property editing
		 * @param property property name
		 * @param rule regex for text value rule, array for enum rule, boolean false to forbid edit
		 */
		add: function(property, rule) {
			this._r[property] = this._r[property]
				|| new Repos.propedit.Rule(); 
		},
		// the saved rules, propname->Repos.propedit.Rule, not to be edited directly
		_r: {
		},
		// suggest property names based on input string, returns array
		suggest: function(input) {
			var s = [];
			for (n in this._r) {
				if (n.indexOf(input) == 0) s.push(n); 
			}
			return s;
		},
		// get a Repos.propedit.Rule
		get: function(propertyName) {
			return this._r[propertyName];
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
	// Currently we support only one target per page, so only one instance of Rules is needed
	$().trigger('repos-propedit-init', [Repos.propedit.Rules]);
});

// page update, executed after trigger above
Repos.service('exit/propedit/', function() {
	
});

// immediate plugin customization, the same thing can be done from other plugins
$().bind('repos-propedit-init', function(ev, reposPropeditRules) {
	reposPropeditRules.add('svn:keywords', ['Date', 'Revision', 'Author', 'HeadURL', 'Id']);
	reposPropeditRules.add('svn:mime-type', /\w+\/.+/);
	reposPropeditRules.add('svn:ignore', /\w+\/.+/m);
});

