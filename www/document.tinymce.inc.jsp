<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		theme : "repos",
		//language : "se",
		mode : "textareas",
		//insertlink_callback : "customInsertLink",
		//insertimage_callback : "customInsertImage",
		//save_callback : "customSave",
		// currently no user customized styles //content_css : "../tinymce/text.css",
		extended_valid_elements : "a[href|target|name]",
		//invalid_elements : "a",
		//theme_advanced_styles : "Header 1=header1;Header 2=header2;Header 3=header3", // Theme specific setting CSS classes
		debug : false
	});

	// Custom insert link callback, extends the link function
	function customInsertLink(href, target) {
		var result = new Array();

		alert("customInsertLink called href: " + href + " target: " + target);

		result['href'] = "http://www.sourceforge.net";
		result['target'] = '_blank';

		return result;
	}

	// Custom insert image callback, extends the image function
	function customInsertImage(src, alt, border, hspace, vspace, width, height, align) {
		var result = new Array();

		var debug = "CustomInsertImage called:\n"
		debug += "src: " + src + "\n";
		debug += "alt: " + alt + "\n";
		debug += "border: " + border + "\n";
		debug += "hspace: " + hspace + "\n";
		debug += "vspace: " + vspace + "\n";
		debug += "width: " + width + "\n";
		debug += "height: " + height + "\n";
		debug += "align: " + align + "\n";
		alert(debug);

		result['src'] = "logo.jpg";
		result['alt'] = "test description";
		result['border'] = "2";
		result['hspace'] = "5";
		result['vspace'] = "5";
		result['width'] = width;
		result['height'] = height;
		result['align'] = "right";

		return result;
	}

	// Custom save callback, gets called when the contents is to be submitted
	function customSave(id, content) {
		return content;
	}
</script>
<!-- /tinyMCE -->