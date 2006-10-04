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