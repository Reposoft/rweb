
/**
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
	 */
	this.find = function(prefix) {
		return document.getElementById(prefix + ':' + this.get());
	}
	
	/**
	 * Escapes utf-8 esaped url so that it is a valid XHTML id
	 */
	this._idescape = function(text) {
		return text.replace('/','_').replace('%','_');
	}
	
	/**
	 * Escapes text to UTF-8 URL string, so that spaces, slashes,
	 * and non-ascii chars get one or two %hex values.
	 */
	this._urlescape = function(text) {
		return text;
	}
}
