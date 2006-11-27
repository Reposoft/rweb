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
		return this._idescape(this._urlescape(name));
	}
	
	/**
	 * Returns the element matching the id.
	 * @param prefix, without ':'
	 * @return the DOM element with id [prefix]:[file ID], or null if not found
	 */
	this.find = function(prefix) {
		return document.getElementById(prefix + ':' + this.get());
	}
	
	/**
	 * Escapes utf-8 escaped url so that it is a valid XHTML id
	 */
	this._idescape = function(text) {
		// same characters as in the xsl:translate in repos.xsl getFileId template
		return text.replace(/[%\/\(\)@&]/g,'_');
	}
	
	/**
	 * Subversion 'href' tags are already encoded with escaped utf-8 in the XML,
	 * so we need to mimic that behaviour.
	 * The AJAX data is UTF-8, and the page is assumed to be UTF-8 too.
	 */
	this._urlescape = function(text) {
		return text.replace(/[^\w]+/g, function(sub) {
			return encodeURI(sub).toLowerCase();
		}).replace(/;/g,'%3b');
	}
}
