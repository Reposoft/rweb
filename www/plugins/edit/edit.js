
Repos.edit = new Object();

$(document).ready( function() {
	if (!Repos.edit.isEditPage()) return;
	Repos.edit.enableMenu();	
} );

Repos.edit.enableMenu = function() {
// if we are here the browser supports javascript

// check if this is a new document by checking if textarea and name field are empty.
// show link HTML document while user is editing document as plain text and vice versa.
	if (!$('#usertext').val() && !$('#name').val()) {
		if (window.location.href.match("&type=html") == null) {
			$('#commandbar').append(
				'<span id="texteditor" class="command">Plain text</span>'
			);
			var htmlHref = window.location.href+'&type=html';
			$('#commandbar').append(
				'<a id="htmleditor" class="command" href="'+htmlHref+'" onClick="return Repos.edit.checkTextarea();">HTML document</a>'
			);
		} else {
			var htmlHref = window.location.href.replace(/&type=html/,"");
			$('#commandbar').append(
				'<a id="texteditor" class="command" href="'+htmlHref+'" onClick="return Repos.edit.checkTinyMCEarea();">Plain text</span>'
			);
			var htmlHref = window.location.href+'&type=html';
			$('#commandbar').append(
				'<span id="htmleditor" class="command">HTML document</span>'
			);
		}
	}
}

Repos.edit.checkTextarea = function() {
	if(!$('#usertext').val()){
		return true;
	}
	if(confirm("Textarea is not empty! If you proceed all contents will be lost. Proceed any way?")) {
		return true;
	} else {
		return false;
	}
}

Repos.edit.checkTinyMCEarea = function() {
	if(!tinyMCE.getContent('mce_editor_0')){
		return true;
	}
	if(confirm("Textarea is not empty! If you proceed all contents will be lost. Proceed any way?")) {
		return true;
	} else {
		return false;
	}
}

Repos.edit.isEditPage = function() {
	return (document.getElementById('formEdit') != null);
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
