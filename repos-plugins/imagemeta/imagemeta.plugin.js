
window.Repos = window.Repos || {service: function(){}};

Repos.imagemeta = {};

Repos.imagemeta.search = function(options) {

	var queryUrl = '/solr/svnhead/select/?wt=json&indent=on';
	queryUrl += '&fl=id,geo_lat,geo_long,description,svnrevision';
	if (options.contentType) {
		queryUrl += '&content_type:' + options.contentType;
	}
	if (/\/$/.test(options.target)) {  // is folder
		queryUrl += '&id_repo:' + (options.base || '');
		queryUrl += '&q=folder:' + encodeURIComponent('"' + options.target + '"');
	} else {
		queryUrl += '&q=id:' + (options.base || '') + '\\^' + encodeURIComponent('"' + options.target + '"');
	}
	
	$.ajax({
		dataType: 'json',
		url: queryUrl,
		success: options.success
	});
	
};

/**
 * @return String decoded filename
 */
Repos.imagemeta.getFilename = function(solrDocId) {
	return solrDocId.split('/').pop();
};

Repos.service('open/list/', function() {
	
	/**
	 * @return jQuery row for the given id
	 */
	var getRow = function(solrDocId) {
		var id = 'row:' + getFilename(solrDocId);
		var r = document.getElementById(id);
		if (!r) alert('error: ' + id + ' not found'); // todo support non-ascii chars
		return $(r);
	};
	
	var geotagged = [];
	
	var gps = function(doc, row) {
		if (doc.geo_lat && doc.geo_long) {
			geotagged.push(doc);
			row.addClass('geotagged');
		}
	};
	
	var show = function(docs) {
		for (var i = 0; i < docs.length; i++) {
			var d = docs[i];
			var row = getRow(d.id);
			if (d.description) {
				row.addClass('commented').find('a, img').attr('title', d.description);
			}
			gps(d, row);
		}
	};
	
	Repos.imagemeta.search({
		base: Repos.getRepo(),
		target: Repos.getTarget(),
		success: function(solr) {
			show(solr.response.docs);
		}
	});
	
});

Repos.service('open/file/', function() {
	if (!$('body').is('.image')) return;

	var show = function(docs) {
		if (docs.length != 1) return;
		var d = docs[0];
		if (d.description) {
			// TODO handle newlines
			$('<p/>').addClass('comment').text(d.description).insertBefore('#footer');
		}
	};
	
	Repos.imagemeta.search({
		base: Repos.getBase(),
		target: Repos.getTarget(),
		success: function(solr) {
			show(solr.response.docs);
		}
	});	
	
});

