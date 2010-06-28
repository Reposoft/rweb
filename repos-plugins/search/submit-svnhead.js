/**
 * Creates an Repos Search "svnhead" standard query from form.
 * Special treatment of fields "op" and "base".
 * The rest are query fields if not empty.
 */
(function() {
	
	var coreUrl = '/solr/svnhead/';
	var queryUrl = coreUrl + 'select';
	
	ReposSearch_onready = false;
	// There's no better api to configure the request instances
	//ReposSearchRequest.prototype.url = '/repos-plugins/arbortext/index/svnhead/';
	ReposSearchRequest.prototype.url = '/solr/svnhead/select';
	
	var run = function(form) {

		var query = [];
		var base = false;
		var op = 'AND';
		var push = function(col, field, value) {
			col.push(field + ':' + value); // TODO encoding;
		};
		
		form.find('input, textarea').each(function() {
			var f = $(this);
			var n = f.attr('name');
			var v = f.val();
			if (f.attr('type') == 'submit') {
				// ignore
			} else if (v && n == 'base') {
				base = v;
			} else if (v) {
				push(query, n, v);
			}
		});
		form.find('select').each(function() {
			var f = $(this);
			var n = f.attr('name');			
			var v = f.find('option:selected').val();
			if (n == 'op') {
				op = v;
			} else if (v) {
				push(query, n, v);
			}
		});
		
		if (!query.length) {
			alert('At least one field value must be entered');
			return;
		}
		
		var q = query.join(' ');
		var ui = new ReposSearch.LightUI({
			parent: false,
			css: ReposSearch.cssDefault
		});
		// TODO set op
		var cust = ui.queryCreate(ui.settings.id + 'standard', 'Custom');
		ui.startQueries(q, cust);
		cust.trigger('enable');
	};
	
	$().ready(function() {
		
		var form = $('form');
		
		form.submit(function() {
			try {
				run(form);
			} catch (e) {
				console.error(e);
			}
			return false;
		});
		
	});
	
})();