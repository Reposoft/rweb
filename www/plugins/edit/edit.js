
Repos.edit = new Object();

$(document).ready( function() {
	if (!Repos.edit.isEditPage()) return;
	Repos.edit.enableMenu();	
} );

Repos.edit.enableMenu = function() {
	// if we are here the browser supports javascript
	if (Repos.edit.getCurrentType() == 'txt') {
		$('#commandbar').append(
			'<span id="texteditor" class="command">plain text</span>'
		);
		var htmlHref = window.location.href+'&type=html';
		$('#commandbar').append(
			'<a id="htmleditor" class="command" href="'+htmlHref+'">HTML document</a>'
		);
	}
}

Repos.edit.isEditPage = function() {
	return typeof(document.getElementById('editForm')) != 'undefined';
}

Repos.edit.getCurrentType = function() {
	return document.getElementsByName('type')[0].value;
}

/* moved to header include to do like in tinymce manual
tinyMCE.init({
	mode : "textareas",
	theme : "simple",
	editor_selector : "html",
	content_css : "/documents.css"
});
*/
