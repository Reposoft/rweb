/*
 * Repos Search GUI
 * Include in Repos Style header to get a search box in the menu bar.
 * Requires jQuery 1.3+
 */

// instead of a separate css, jQuery syntax
reposSearchFormCss = {
	display: 'inline',
	marginLeft: 10
};
reposSearchInputCss = {
	
};
reposSearchDialogCss = {
	position: 'absolute',
	overflow: 'auto',
	zIndex: 1,
	top: 50,
	left: 30,
	right: 30,
	bottom: 30,
	opacity: .9,
	paddingLeft: 30,
	paddingRight: 10,
	backgroundColor: '#fff',
	border: '2px solid #eee'
};
reposDialogTitleCss = {
	width: '100%',
	textAlign: 'center',
	opacity: .7,
};
reposSearchCloseCss = {
	textAlign: 'right',
	float: 'right',
	fontSize: '82.5%',
	cursor: 'pointer'
};
reposSearchListCss = {
	listStyleType: 'none',
	listStylePosition: 'inside'
}

$().ready(function() {
	reposSearchShow();
});

reposSearchShow = function() {
	// the page that includes Repos Search can provide an element with
	// class "repossearchcontainer" to control the placement of the input box
	var container = $('.repossearchcontainer').add('#commandbar').add('body').eq(0);
	var box = $('<input id="searchinput" type="text" size="20" name="q"/>').css(reposSearchInputCss);
	var form = $('<form id="searchform" action="/repos-search/"><input type="submit" style="display:none"/></form>').append(box);
	form.css(reposSearchFormCss).appendTo(container); // TODO display settings should be set in css
	form.submit(reposSearchSubmit);
};

reposSearchClose = function() {
	$('#searchdialog').remove();
}

reposSearchSubmit = function(ev) {
	ev.stopPropagation();
	// create search result container
	reposSearchClose();
	var dialog = $('<div id="searchdialog"/>').css(reposSearchDialogCss);
	// start search request
	var query = $('#searchinput').val();
	var titles = $('<div id="searchtitles"/>');
	reposSearchTitles(query, titles);
	// build results layout
	var title = $('<div class="searchdialogtitle">Repos Search</div>').css(reposDialogTitleCss);
	var close = $('<div class="searchclose">close</div>').css(reposSearchCloseCss).click(reposSearchClose);
	dialog.append(title);
	title.append(close);
	//dialog.append('<h1>Search results</h1>'); // would be better as title bar
	$('<h2/>').text('Titles matching "' + query + '"').appendTo(dialog);
	dialog.append(titles);
	var fulltexth = $('<h2/>').text('Documents containing "' + query + '"').hide();
	var fulltext = $('<div id="searchtext"/>');
	var enablefulltext = $('<input id="enablefulltext" type="checkbox">').change(function() {
		if ($(this).is(':checked')) {
			fulltexth.show();
			fulltext.show();
			reposSearchFulltext(query, fulltext);
		} else {
			fulltexth.hide();
			fulltext.hide();
		}
	});
	$('<p/>').append(enablefulltext).append('<label for="enablefulltext"> Search contents</label>').appendTo(dialog);
	dialog.append(fulltexth).append(fulltext);
	close.clone(true).addClass("searchclosebottom").appendTo(dialog);
	$('body').append(dialog);
	return false; // don't submit form
};

reposSearchTitles = function(query, resultDiv) {
	reposSearchAjax('/repos-search/?q=title:' + encodeURIComponent(query), resultDiv);
}

reposSearchFulltext = function(query, resultDiv) {
	reposSearchAjax('/repos-search/?q=text:' + encodeURIComponent(query), resultDiv);
}

reposSearchAjax = function(url, resultDiv) {
	// provide navigation info for search filtering
	var mb = $('meta[name=repos-base]');
	if (mb.size()) url += '&base=' + encodeURIComponent(mb.attr('content'));
	var mt = $('meta[name=repos-target]');
	if (mb.size()) url += '&target=' + encodeURIComponent(mt.attr('content'));
	// query
	resultDiv.addClass('loading'); // this requires a css so we'll also append image
	resultDiv.append('<img class="loading" src="/repos-search/loading.gif" alt="loading"/>');
	$.ajax({
		url: url,
		dataType: 'json',
		success: function(json) {
			resultDiv.removeClass('loading');
			$('.loading', resultDiv).remove();
			reposSearchResults(json, resultDiv);
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			resultDiv.removeClass('loading');
			$('.loading', resultDiv).remove();
			resultDiv.text('Error ' + textStatus + ": " + errorThrown);
		}
	});
};

reposSearchResults = function(json, resultDiv) {
	resultDiv.empty();
	console.log(json);
	var num = json.response.numFound;
	if (num == 0) {
		$('<p>No matches found</p>').appendTo(resultDiv);
		return;
	}
	var list = $('<u/>').css(reposSearchListCss).appendTo(resultDiv);
	for (var i = 0; i < num; i++) {
		var e = reposSearchPresentItem(json.response.docs[i]);
		e.addClass(i % 2 ? 'even' : 'odd');
		e.appendTo(list);
	}
};

/**
 * Produce the element that presents a search hit.
 * To customize, replace this method in a script block below Repos Search import.
 * @param json the item from the solr "response.docs" array
 * @return jQuery element
 */
reposSearchPresentItem = function(json) {
	var m = /([^\/]*)(\/?.*\/)([^\/]*)/.exec(json.id);
	if (!m) return $("<li/>").text("Unknown match: " + json.id);
	var li = $('<li/>');
	var root = '/svn';
	if (m[1]) {
		root += '/' + m[1];
		li.append('<a class="searchresultbase" href="' + root + '">' + m[1] + '</a>');
	}
	li.append('<a class="searchresultpath" href="' + root + m[2] + '">' + m[2] + '</a>');
	li.append('<a class="searchresultfile" href="' + root + m[2] + m[3] + '">' + m[3] + '</a>');
	if (json.title && json.title != m[3]) {
		$('<span class="searchresulttitle">').text('  ' + json.title).appendTo(li);
	}
	return li;
};
