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
		field.attr('id', propertyName);
		var label = $('<label/>').attr('for', field.attr('id')).text(propertyName).appendTo(p);
		p.append(field);
		fieldset.append(p);
	};
	
	var getFieldName = function(propertyName) {
		return 'svnprop_' + propertyName.replace(/[:-]/g, '_');
	};
	
	var formGen = function(fieldset, rules) {
		console.log('propsearch', fieldset, rules);
		// Only explicit rules, not regex rules
		// TODO Need public accessor for fields
		var r = rules._r;
		for (prop in r) {
			if (r.hasOwnProperty(prop)) {
				var fieldName = getFieldName(prop);
				if ($('[name=' + fieldName + ']', fieldset).size() > 0) {
					console.log('rule overridden by static field:', fieldName);
					continue;
				}
				var field = fieldGen(prop, r[prop]);
				field.attr('name', fieldName);
				append(fieldset, prop, field);
		    }
		}
		// move submit button last
		$(':submit', fieldset).parent().appendTo(fieldset);
	};
	
	var setBase = function(fieldset, repositoryName) {
		console.log('base', repositoryName);
		$('<input type="hidden"/>').attr('name', 'base').attr('value', repositoryName).prependTo(fieldset);
		$('<span/>').text('(repository: ' + repositoryName + ')').appendTo($('legend', fieldset));
	};
	
	var qs = jQuery.deparam.querystring();
	if (qs.base) {
		// allow Repos.getBase in rules definitions
		$('<meta name="repos-base" content="' + qs.base + '" />').appendTo('head');
	}	
	
	$(document).ready(function() {
		var form = $('form.repos-propsearch-gen').addClass('loading');
		var fieldset = $('fieldset:first-child', form);
		if (qs.base) {
			setBase(fieldset, qs.base);
		}
		rulesLoad(function(rules) {
			form.removeClass('loading');
			formGen(fieldset, rules);
		});
	});
	
})();