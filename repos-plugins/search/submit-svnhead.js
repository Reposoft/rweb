/**
 * Creates an Repos Search "svnhead" standard query from form.
 * Special treatment of fields "op" and "base".
 * The rest are query fields if not empty.
 */
(function() {
	
	var coreUrl = '/solr/svnhead/';
	var queryUrl = coreUrl + 'select';
	
	var solrResponseHandler = function(json) {
		console.log(json);
		//alert(json);
	};
	
	var queryRunner = function(form, responseHandler) {

		var query = [];
		var fq = [];
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
				push(fq, 'id_repo', v);
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
		
		var solrQuery = {
			wt: 'json',
			q: query.join(' '),
			fq: fq.join(' AND ')
		};
		console.log('solr query', solrQuery);
		
		$.ajax({
			url: $.param.querystring(queryUrl, solrQuery),
			dataType: 'json',
			success: responseHandler
		});
		
	};
	
	$().ready(function() {
		
		var form = $('form');
		
		form.submit(function() {
			try {
				queryRunner(form, solrResponseHandler);
			} catch (e) {
				console.error(e);
			}
			return false;
		});
		
	});
	
})();