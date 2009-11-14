

// configuration
Repos.edit = {
	tinyMceUrl: Repos.getWebapp() + 'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js'
	//tinyMceUrl: Repos.getWebapp() + 'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce.js'
};

function Repos_loadTinyMce() {
	// from Load on demand example at http://tinymce.moxiecode.com/examples/example_13.php
   tinyMCE_GZ.init({
      themes : "advanced",
      plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
      languages : "en",
      disk_cache : true
   }, function() {
      tinyMCE.init({
         mode : "textareas",
         theme : "advanced",
         plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
         theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
         theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
         theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
         theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
         theme_advanced_toolbar_location : "top",
         theme_advanced_toolbar_align : "left",
         theme_advanced_statusbar_location : "bottom",
         theme_advanced_resizing : true
      });
   });
}

Repos.service('edit/text/', function() {
	if (Repos.isTarget(/.*\.x?html/)) {
		Repos.edit.enableMenu();
	}
});

Repos.edit.enableMenu = function() {
	if (!window.location.href.match("&type=html")) {
		if ($('#usertext').val() == "" && $('#name').val() == "") {
			$('#commandbar').append(
				'<span id="texteditor">Plain text</span>'
			);
			var htmlHref = window.location.href+'&type=html';
			$('#commandbar').append(
				'<a id="htmleditor" href="'+htmlHref+'" onClick="return Repos.edit.checkTextarea();">HTML document</a>'
			);
		}
	} else {
		Repos.edit.loadTinyMce(Repos.edit.openTinyMce);
		if (tinyMCE.getContent('mce_editor_0') == "" && $('#name').val() == "") {
			var htmlHref = window.location.href.replace(/&type=html/,"");
			$('#commandbar').append(
				'<a id="texteditor" href="'+htmlHref+'" onClick="return Repos.edit.checkTinyMCEarea();">Plain text</span>'
			);
			var htmlHref = window.location.href+'&type=html';
			$('#commandbar').append(
				'<span id="htmleditor">HTML document</span>'
			);
		}
	}

	// check if the file contains <meta name="Generator" content="Repos" />
	// and if it does, open it with TinyMCE.
	var filetext = $('#usertext').val();
	var pattern = /<meta name="Generator" content="Repos"/;
	if (filetext.search(pattern) >= 0) {
		Repos.edit.loadTinyMce(Repos.edit.openTinyMce);
	}
};

Repos.edit.loadTinyMce = function(callback) {
	console.log('loading tinymce scripts from ', Repos.edit.tinyMceUrl);
	Repos_loadTinyMce();
};

Repos.edit.checkTextarea = function() {
	if(!$('#usertext').val()){
		return true;
	}
	if(confirm("Textarea is not empty! If you proceed all contents will be lost. Proceed any way?")) {
		return true;
	} else {
		return false;
	}
};

Repos.edit.checkTinyMCEarea = function() {
	if(!tinyMCE.getContent('mce_editor_0')){
		return true;
	}
	if(confirm("Textarea is not empty! If you proceed all contents will be lost. Proceed any way?")) {
		return true;
	} else {
		return false;
	}
};

Repos.edit.isEditPage = function() {
	return (document.getElementById('formEdit') != null);
};

Repos.edit.getCurrentType = function() {
	return document.getElementsByName('type')[0].value;
};

/* moved to header include to do like in tinymce manual
tinyMCE.init({
	mode : "textareas",
	theme : "simple",
	editor_selector : "html",
	content_css : "/documents.css"
});
*/
