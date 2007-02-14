
$(document).ready( function() {
	contentsCreateTable();
} );

function ContentsLevel(parentLevel) {
	this._count = 0;
	this._parentLevel = parentLevel;
	this._members = new Array();
	this.add = function(headElement) {
		this._count++;
		this._members.push({
			tag: headElement,
			parent: this.getLastParent()
		});
		return this._count;
	}
	this.getLastMember = function() {
		if (this._count == 0) return null;
		return this._members[count-1];
	}
	this.getLastParent = function() {
		if (this._parentLevel == null) return null;
		return this._parentLevel.getLastMember();
	}
}

var contentsTags = {
	H1: new ContentsLevel(null),
	H2: new ContentsLevel(this.H1),
	H3: new ContentsLevel(this.H2)
}

function contentsCreateTable() {
	var contents = new Object();
	$('h1,h2,h3').each( function() {
		var level = contentsTags[this.tagName];
		var n = level.add(this);
		var position = '' + n + '.';
		$(this).text(position + ' ' + $(this).text());
	} );
};