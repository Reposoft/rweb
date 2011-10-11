<?php
/**
 * Ideas, not used in 1.1, on how to set svn:mime-type property only when needed.
 */

/**
 * Decides if svn:mime-type property should be set for an uploaded file,
 * based on the suggestion made by the client.
 * 
 * @param String $file the local file, if it exists we can inspect the contents.
 *  The file should have the name (or at least the extension) that it will have in the repository.
 *  OR the extension that it had on the client, depending on how we want to implement this...
 * @param String $suggestedType the mime type that the client reports for the file.
 *  It is assumed that all mimetypes are lowercase.
 * @return false if we're not interested in the suggested type,
 *  or the recommended value of the svn:mime-type property if we are.
 */
function getSpecificMimetype($file, $suggestedType) {
	// note that pathinfo might not be UTF-8 safe
	$path = pathinfo($file);
	$ext = strtolower($path['extension']);
	// first allow pluggable handlers
	$handler = 'getSpecificMimetype_'.$ext;
	if (file_exists($file) && function_exists($handler)) {
		return call_user_func($handler, $file, $suggestedType);
	} else {
		return getSpecificMimetypeForExtension($ext, $suggestedType);	
	}
}

/**
 * Copares the suggested mimetype with what we expect for the filename extension,
 * returning the suggested mimetype if it is non-standard.
 *
 * @param String $fileExtension lowercase file type
 * @param String $suggestedType mime type
 * @return false if mimetype is standard, our suggested mimetype if it is non-standard
 */
function getSpecificMimetypeForExtension($fileExtension, $suggestedType) {
	global $mimetypedefaults;
	// if the filetype is not relevant to this application we never set mime-type
	if (!array_key_exists($fileExtension, $mimetypedefaults)) return false;
	$ourtype = $mimetypedefaults[$fileExtension];
	if ($ourtype==$suggestedType) return false;
	// type suggested by the client is not same as we expect, so recommend saving the value
	return $suggestedType;
}

// the filetypes that are relevant to this applicaiton, and the default mimetypes
// (which are the default, and therefore don't need a svn:mime-type value)
$mimetypedefaults = array(
	'tst' => 'text/repos-testfile',
	'xml' => 'text/xml'
);

/**
 * If the function exists, it provides type detection specific to a file extension.
 *
 * @param String $file an existing file that we can check the contents of
 * @param String $suggestedType the mime type suggested by the client
 */
//template:
//function getSpecificMimetype_ext($file, $suggestedType)


function getSpecificMimetype_xml($file, $suggestedType) {
	// TODO detect excel 2003 xml and return excel mimetype
	return false;
}

?>