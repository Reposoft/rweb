/**
 * (c) 2006 repos.se
 * Converts file or folder names for URLs to IDs,
 * to give the exact same result as Subversion's UTF-8 urlencode in the svn index XML
 * combined with the getFileId template in repos.xsl.
 *
 * All HTML ids must start with a letter, so ids must _always_ be prefixed
 * before they are used. The conventions is prefix:id.
 */
function ReposFileId(name) {
	this.name = name;
	
	this.get = function() {
		return this._idescape(this._urlescape(this._prepare(name)));
	}
	
	/**
	 * Returns the element matching the id.
	 * @param prefix, without ':'
	 */
	this.find = function(prefix) {
		return document.getElementById(prefix + ':' + this.get());
	}
	
	/**
	 * Escapes the trailing slash in folder URLs
	 */
	this._prepare = function(text) {
		return text.replace('/','_');
	}
	
	/**
	 * Escapes utf-8 escaped url so that it is a valid XHTML id
	 */
	this._idescape = function(text) {
		return text.replace('%','_');
	}
	
	/**
	* Subversion 'href' tags are already encoded with escaped utf-8 in the XML,
	* so we need to mimic that behaviour.
	 * Escapes text to UTF-8 URL string, so that spaces, slashes,
	 * and non-ascii chars get one or two %hex values.
	 * The AJAX data is UTF-8, and the page is assumed to be UTF-8 too.
	 */
	this._urlescape = function(text) {
		return encodeURI(text);
	}
}
