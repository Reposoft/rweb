<?php
/**
 * Decides if a files is a a viewable image
 * and what the URL would be.
 * 
 * Currently trusts the thumbnail plugin to be installed.
 * 
 * Not using javascript to generate URL because Repos Web still
 * has the policy of making the central functionality available
 * without javascript.
 * 
 * Uses SvnOpenFile->getTypeDiscrete() to see if the file is
 * an image, with fallback to a safe list of extensions
 * because it is common that svn:mime-type is not set.
 * 
 * @param $file SvnOpenFile
 * @return false if not an image, URL to screen size image if image
 */
function reposViewGetImageUrl($file) {
	
	// might be a more restricted list of extensions that the thumbnail plugin
	static $safeExtensions = array('jpg', 'jpeg', 'png', 'gif', 'tif');
	if ($file->getTypeDiscrete() != 'image') {
		if (!in_array($file->getExtension(), $safeExtensions)) {
			return false;
		}
	}
	
	// TODO the dependence on thumbnail plugin location and base would be avoided if we
	// created a service that is simply a query parameter appended to the real URL
	$url = '/repos-plugins/thumbnails/convert/';
	$url .= '?gt=screen'; // see thumbnails plugin
	$url .= '&target='.rawurldecode($file->getPath());
	if (isset($_REQUEST['base'])) $url .= '&base=' . $_REQUEST['base'];
	
	// this would be optimal for caching, we can't use last-changed as peg because the url might have been different
	//$url .= '&p=' . $file->getRevision() . '&r=' . $file->getRevisionLastChanged();
	// simple but less cache friendly
	$url .= '&p=' . $file->getRevision();
	return $url;
}

function smarty_modifier_reposViewGetImageUrl($file) {
	return reposViewGetImageUrl($file);
}
?>