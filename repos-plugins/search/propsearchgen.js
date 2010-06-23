/**
 * Generate search form based on a propedit plugin rule set.
 * 
 * Detect form with class "repos-propsearch-gen",
 * trigger the "repos-propsearch-init" event with a rules instance
 * specific for search.
 * 
 * When rules have been collected, generate search form.
 * 
 * Provide a submit handler that converts form values to search query.
 */
(function() {

	/**
	 * Load property rules and trigger callback function with rules instance.
	 */
	var rulesLoad = function(callback) {
		// start with propedit rules, seems like they are initialized for every page
		callback(Repos.propedit.Rules);
	};
	
	var fieldGen = function(propertyName, rule) {
		var propeditField = rule.getFormField();
		return propeditField;
	};
	
	/**
	 * Format form field according to repos conventions and append to form.
	 */
	var append = function(fieldset, propertyName, field) {
		var p = $('<p/>');
		var label = $('<label/>').attr('for', propertyName).text(propertyName).appendTo(p);
		p.append(field);
		fieldset.append(p);
	};
	
	var formGen = function(fieldset, rules) {
		console.log('propsearch', fieldset, rules);
		// Only explicit rules, not regex rules
		// TODO Need public accessor for fields
		var r = rules._r;
		for (prop in r) {
			if (r.hasOwnProperty(prop)) {
				if ($('[name=' + prop + ']', fieldset).size() > 0) {
					console.log('rule overridden by static field:', prop);
					continue;
				}
				var field = fieldGen(prop, r[prop]);
				field.attr('name', prop);
				append(fieldset, prop, field);
		    }
		}
		// move submit button last
		$(':submit', fieldset).parent().appendTo(fieldset);
	};
	
	var setBase = function(fieldset, repositoryName) {
		console.log('base', repositoryName);
		$('<input type="hidden"/>').attr('name', 'base').attr('value', repositoryName).prependTo(fieldset);
	};
	
	$().ready(function() {
		var form = $('form.repos-propsearch-gen').addClass('loading');
		var fieldset = $('fieldset:first-child', form);
		var qs = jQuery.deparam.querystring();
		if (qs.base) {
			setBase(fieldset, qs.base);
		}
		rulesLoad(function(rules) {
			form.removeClass('loading');
			formGen(fieldset, rules);
		});
	});
	
})();