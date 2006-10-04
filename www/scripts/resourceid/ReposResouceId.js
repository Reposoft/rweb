/**
 * Repos show version number (c) Staffan Olsson http://www.repos.se
 * @version $Id$
 */

function ReposResourceId(text) {
	this.text = text;
	this.getRelease = function() {
		if (/\/trunk\//.test(this.text)) return 'dev';
		var b = /\/branches\/[^\/\d]+(\d[^\/]+)/.exec(this.text);
		if (b) return b[1] + ' dev';
		var t = /\/tags\/[^\/\d]+(\d[\d\.]+)/.exec(this.text);
		if (t) {
			this.isTag = true;
			return t[1];
		}
		return '';
	}
	this.getRevision = function() {
		var rev = /Rev:\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		rev = /Id:\s\S+\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		return '';
	}
	this.getTextBefore = function() {
		return /(^[^\$]*)/.exec(this.text)[1];
	}
	this.getTextAfter = function() {
		return /([^\$]*$)/.exec(this.text)[1];
	}
}

// ----- marking screens -----
_getReleaseVersion = function(versionText) {
	var rid = new ReposResourceId(versionText);
	return rid.getTextBefore() + rid.getRelease() + rid.getTextAfter();
}

_getResourceVersion = function(versionText) {
	var rid = new ReposResourceId(versionText);
	var release = rid.getRelease();
	if (rid.isTag) return rid.getTextBefore() + release + rid.getTextAfter();
	return rid.getTextBefore() + release + ' ' + rid.getRevision() + rid.getTextAfter();
}

_showVersion = function() {
	try {
		var release = $('#releaseversion');
		if (release) {
			release.innerHTML = _getReleaseVersion(release.innerHTML);
			release.style.display = '';
		}
		var revision = $('#resourceversion');
		if (revision) {
			revision.innerHTML = _getResourceVersion(revision.innerHTML);
			revision.style.display = '';
		}
	} catch (err) {
		// Repos.reportError(err);
	}
},

$(document).ready( function() { _showVersion() } );