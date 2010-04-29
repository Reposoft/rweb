(function() {
	
	var console = //window.console || 
		{log: function() {}};
	
	var trim = function(str) {
		// http://blog.stevenlevithan.com/archives/faster-trim-javascript
		return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
	};
	
	/**
	 * @param field Element
	 * @return true if trimmed
	 */
	var fieldTrim = function(field) {
		var v = $(field).val();
		if (!v) {
			return false;
		}
		var t = trim(v);
		console.log(field,v,t);
		if (t.length != v.length) {
			$(field).val(t);
			return true;
		}
		return false;
	};
	
	var fieldGetLabel = function(field) {
		console.log(field, 'label[for=' + field.id + ']', $('label[for=' + field.id + ']'));
		return $('label[for=' + field.id + ']').text() || field.name;
	};
	
	/**
	 * @param form Element
	 * @return array of fields that were trimmed
	 */
	var formTrim = function(form) {
		var fields = $('input:text[name], input:text[name=newname], textarea[name=message]', form).not(':disabled');
		var trimmed = [];
		fields.each(function() {
			console.log('formtrim field', this);
			if (fieldTrim(this)) {
				trimmed.push(this);
			}
		});
		return trimmed;
	};
	
	var formTrimAndInfo = function(form) {
		var trimmed = formTrim(form);
		if (trimmed.length) {
			var labels = [];
			for (var i = 0; i < trimmed.length; i++) {
				labels.push(fieldGetLabel(trimmed[i]));
			}
			$(form).say('Removed leading and trailing spaces from fields: "' + 
					labels.join('", "') + '". Submit again to accept. Change back to keep spaces.');
			return true;
		}
		return false;
	};
	
	var enable = function() {
		console.log('formtrim enabled');
		$('form').one('submit', function(ev) {
			try {
				if (formTrimAndInfo(this)) {
					ev.preventDefault();
				}
			} catch (e) {
				console.log('formtrim error', this, e);
				// abort submit
				ev.preventDefault();
			}
		});
	};
	
	//Repos.service('edit/upload/', enable); // TODO disable the overlay in addition to the submit
	Repos.service('edit/text/', enable);
	Repos.service('edit/mkdir/', enable);
	Repos.service('edit/rename/', enable);
	Repos.service('edit/copy/', enable);
	
})();
