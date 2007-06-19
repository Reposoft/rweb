/**
 * Repos show version number (c) 2006 Staffan Olsson www.repos.se
 * @version $Id$
 */
 
function ReposResourceId(text) {
	this.text = text;
	// the release is the first version number "digits.digits..." up to next /
	this.getRelease = function() {
		if (/@Dev@/.test(this.text)) return 'dev';
		if (/\/trunk\//.test(this.text)) return 'dev';
		var b = /\/branches\/\D+(\d[^\/]+)/.exec(this.text);
		if (b) return b[1] + ' dev';
		// tags URL or explicit version number counts as a distributable build
		var t = /\/tags\/\D+(\d[^\/]+)/.exec(this.text) || /\b(\d+\.\d+\.?-?\w?\d*)\b/.exec(this.text);
		if (t) {
			this.isTag = true;
			return t[1];
		}
		return '';
	};
	this.getRevision = function() {
		var rev = /Rev:\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		rev = /Id:\s\S+\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		return '';
	};
	this.getTextBefore = function() {
		return /(^[^\$\d@]*)/.exec(this.text)[1];
	};
	this.getTextAfter = function() {
		return /([^\$\d@]*$)/.exec(this.text)[1];
	};
}

// ----- marking screens -----
_getReleaseVersion = function(versionText) {
	var rid = new ReposResourceId(versionText);
	return rid.getTextBefore() + rid.getRelease() + rid.getTextAfter();
};

_getResourceVersion = function(versionText) {
	var rid = new ReposResourceId(versionText);
	var release = rid.getRelease();
	if (rid.isTag) return rid.getTextBefore() + release + rid.getTextAfter();
	return rid.getTextBefore() + release + ' ' + rid.getRevision() + rid.getTextAfter();
};

_showVersion = function() {
	try {
		$('#releaseversion').each( function() {
			this.innerHTML = _getReleaseVersion(this.innerHTML);
			this.style.display = 'inline';
		} );
		$('#resourceversion').each( function() {
			this.innerHTML = _getResourceVersion(this.innerHTML);
			this.style.display = 'inline'; // can't set to empty because it might be display:none in css
		} );
	} catch (err) {
		Repos.reportError(err);
	}
};

$(document).ready( function() { _showVersion(); } );
