
// TODO:
// Autocomplete for property names, show all that have a rule
// Add button to delete a property
//  (backend does so if the property value is not sent - empty string is still a value -
//  so delete needs only to set the value input to disabled=true
// Add button to add new property, or add field automatically if the last empty one is used
// There is an issue with readonly property not being displayed as readonly
// Multiple values of enum

// Arbortext view, button for edit
// abx:* should never be writable

// Backend
//  Dash in beginning och propvalue
//  Set only changed

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
			if (property.constructor == RegExp) {
				// support regexp matching of properties 
				this._rr.push({x: property, r: new Repos.propedit.Rule(rule)});
				return;
			}
			this._r[property] = this._r[property] || new Repos.propedit.Rule(rule);
		},
		// the saved rules, propname->Repos.propedit.Rule, not to be edited directly
		_r: {
		},
		// regexp property name matching
		_rr: [],
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
			var p = this._r[propertyName];
			if (p) return p;
			// if no exact match see if a regexp matches
			for (n in this._rr) {
				if (this._rr[n].x.test(propertyName)) return this._rr[n].r;
			}
		}
	},
	Rule: function(rule) {
		/**
		 * Set first rule or combine existing with new rule.
		 */
		this.append = function(rule) {
			// array means enum
			
			// multiline regexp means multiline property
			
			// boolean false means don't allow edit
			
			// currently only supporting one rule, the last one added
			this.rule = rule;
		};
		/**
		 * Validates a property value with this rule.
		 * This function can not return any info about validation error,
		 * so the editor user interface must prevent errors.
		 */
		this.test = function(propertyValue) {
			if (!this.rule) return false;
			if (this.rule.constructor == RegExp) {
				var m = this.rule.exec(propertyValue);
				return m && m[0] == propertyValue;
			}
			if (this.rule.length) { // array
				for (i in this.rule) {
					if (this.rule[i] == propertyValue) return true;
				}
				return false;
			}
			return true;
		};
		/**
		 * @return a jQuery instance with a form field suitable for editing this property
		 *  Caller must set name and id on the field.
		 */
		this.getFormField = function(currentValue) {
			var val = currentValue || '';
			if (this.rule === false) {
				if (val.indexOf('\n')>=0) {
					return $('<textarea/>')
						.attr('cols', 60) // same width as in static repos propedit form
						.attr('rows', val.split('\n').length)
						.attr('readonly', 'true')
						.addClass('readonly')
						.val(val);
				} else {
					return $('<input/>').attr('type','text')
						.attr('size', '60')
						.attr('readonly', 'true')
						.addClass('readonly')
						.val(val);
				}
			}
			if (this.rule.constructor == RegExp) {
				if (this.rule.multiline) {
					return $('<textarea/>')
						.attr('cols', 60) // same width as in static repos propedit form
						.attr('rows', val.split('\n').length + 1)
						.val(val);
				} else {
					return $('<input/>').attr('type','text')
						.attr('size', '60')
						.val(val);
				}
			}
			if (this.rule.length) { // array
				var f = $('<select/>');
				for (i in this.rule) {
					var v = this.rule[i];
					var o = $('<option/>').val(v).text(v);
					if (v == currentValue) 
						o.attr('selected', 'true');
					o.appendTo(f);
				}
				return f;
			}
		};
		// constructor
		if (typeof rule != 'undefined') this.append(rule);
	}
};

// launch load event so other plugins can customize
$().ready(function() {
	// Currently we support only one target per page, so only one instance of Rules is needed
	$().trigger('repos-propedit-init', [Repos.propedit.Rules]);
});

// page update, executed after trigger above
Repos.service('edit/propedit/', function() {
	var propValFields = $('textarea'); // currently there is no class for property value fields
	propValFields.each(function() {
		var id = $(this).attr('id');
		var parent = $(this).parent();
		// existing properties have a label and a hidden field with the property name
		var property = $('input[type="hidden"]', parent).val()
			|| $('input[type="text"]', parent).val(); // editable name, still no form field classes
		var value = $(this).val();
		// get the rule and a new form field
		var rule = Repos.propedit.Rules.get(property);
		if (!rule) return;
		var f = rule.getFormField(value);
		if (!f) return;
		// replace old value input with new
		$(this).attr('id', id + "_old");
		f.attr('id', id).attr('name', $(this).attr('name'));
		//$(this).replaceAll(f);
		$(this).remove();
		parent.append(f);
	});
	
});

// immediate plugin customization, the same thing can be done from other plugins
$().bind('repos-propedit-init', function(ev, rules) {
	rules.add('svn:keywords', ['Date', 'Revision', 'Author', 'HeadURL', 'Id']);
	rules.add('svn:mime-type', /\w+\/\w+/);
	rules.add('svn:ignore', /.*/m);
});

