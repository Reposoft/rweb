
// configuration
Repos.edit = {
	tinyMceUrl: Repos.getWebapp() + 'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js'
	//tinyMceUrl: Repos.getWebapp() + 'lib/tinymce/tinymce/jscripts/tiny_mce/tiny_mce.js'
};

// load TinyMCE into <head> as it designed to be
(function() {
	var h = document.getElementsByTagName("head")[0];
	var s = document.createElement('script');
	s.type = 'text/javascript';
	s.src = Repos.edit.tinyMceUrl;
	h.appendChild(s);
})();

// function to initialize editor on demand
Repos.edit.loadTinyMce = function() {
	// from Load on demand example at http://tinymce.moxiecode.com/examples/example_13.php
	tinyMCE_GZ.init({
		plugins : "autosave,inlinepopups,fullscreen,save",
		themes : "advanced",
		languages : "en",
		disk_cache : true,
		debug : false
	}, function() {
		tinyMCE.init({
			mode: "exact",
			elements: 'usertext',
			plugins: "autosave,inlinepopups,fullscreen,save",
			theme: "advanced",
			theme_advanced_buttons1: "formatselect,separator,cut,copy,paste,separator,undo,redo,separator,bold,italic,underline,separator,bullist,numlist,separator,link,image,hr,separator,fullscreen",
			theme_advanced_buttons2: "",
			theme_advanced_buttons3: "",
			remove_linebreaks: false,
			forced_root_block: "p",
			theme_advanced_blockformats: "h1, h2, h3, p",
			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align: "center",
			convert_urls: "false",
			entity_encoding: "raw",
			//content_css: "/home/documents.css",
			width: "900"
		});
	});
};

Repos.service('edit/text/', function() {
	if (/tinymce/.test(window.location.href)) { // under development, only enabled in plugin folder
	if (Repos.isTarget(/.*\.x?html$/)) {
		Repos.edit.loadTinyMce();
	} else {
		Repos.edit.enableMenu();
		//Repos.edit.enableOnGenerator();
	}
	}
});

Repos.edit.enableMenu = function() {
	if (!window.location.href.match("&type=html")) {
		if (!$('#usertext').val() && !$('#name').val()) {
			$('#commandbar').append(
				'<span id="texteditor">Plain text</span>'
			);
			var htmlHref = window.location.href+'&type=html';
			$('#commandbar').append(
				'<a id="htmleditor" href="'+htmlHref+'" onClick="return Repos.edit.checkTextarea();">HTML document</a>'
			);
		}
	} else {
		if (!$('#usertext').val() && !$('#name').val()) {
			var htmlHref = window.location.href.replace(/&type=html/,"");
			$('#commandbar').append(
				'<a id="texteditor" href="'+htmlHref+'" onClick="return Repos.edit.checkTinyMCEarea();">Plain text</span>'
			);
			var htmlHref = window.location.href+'&type=html';
			$('#commandbar').append(
				'<span id="htmleditor">HTML document</span>'
			);
		}
		Repos.edit.loadTinyMce();
	}
};

Repos.edit.enableOnGenerator = function() {
	// check if the file contains <meta name="Generator" content="Repos" />
	// and if it does, open it with TinyMCE.
	var filetext = $('#usertext').val();
	var pattern = /<meta name="Generator" content="Repos"/;
	if (filetext.search(pattern) >= 0) {
		Repos.edit.loadTinyMce();
	}
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
	//this method does not exist in new tinymce //if(!tinyMCE.getContent('mce_editor_0')){
	//	return true;
	//}
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
