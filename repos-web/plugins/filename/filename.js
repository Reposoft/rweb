/**
 *	Form fields with class "remember-extension" get automatically modified
 *	if a user forgets the extension. The extension is read from the same form
 *	field when the page is loaded.
 */

var filenamePluginExtension = '';

$(document).ready(function() {
	fileExtensionRead();
	$('.remember-extension').change(function() { checkNewFileExtension(); });
});

function fileExtensionRead() {
	if ($('.remember-extension').val()) {
		var localFilename = $('.remember-extension').val();
		var i = getFileExtension(localFilename);
		if (i != '') return i;				// if no extension is found in this field check in the .current-name field
	}
	if ($('.current-name').val()) {
		var localFilename = $('.current-name').val();
		var i = getFileExtension(localFilename);
		if (i != '') return i;
	}
	return '';
}

function getFileExtension(filename) {
	if (!filename) return '';
	var j = filename.match(/\.(\w*$)/);
	if (j) {
		filenamePluginExtension = j[0];		// saves the extension only if it exists
		return filenamePluginExtension;		// in order to prevent losing the last stored extension
	}
	return '';
}

function addFileExtension(newFileName) {
	if (filenamePluginExtension) {
		return newFileName + filenamePluginExtension;		
	}
	var i = fileExtensionRead();		// check the fields for extension once again
	if (i != '') {						// and if it's found store it in filenamePluginExtension
		filenamePluginExtension = i;
		return newFileName + filenamePluginExtension;
	}
	return newFileName;					// if no extension is found return the filename without extension
}

function getDefaultExtension() {

}
/**
 *	Check if a new file has an extension and if it doesn't add the extension
 *	found in the form field with class "remember-extension".
 */
function checkNewFileExtension() {
	if($('.remember-extension').val() != '') {
		var newFileName = $('.remember-extension').val();
		var i = getFileExtension(newFileName);
		if (!i) {
			$('.remember-extension').val(addFileExtension(newFileName));
			// remove the class so that this is not done again if the user removes the extension
			$('.remember-extension').removeClass('remember-extension');
		} else {
			filenamePluginExtension = i;
		}
	}
}