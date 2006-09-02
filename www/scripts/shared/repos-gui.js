/**
 * Repos common dynamic GUI classes (c) Staffan Olsson http://www.repos.se
 * @version $Id$
 */
 
var RFrame = Class.create();
RFrame.prototype = {
	initialize: function(elementId) {
		this.div = Repos.create('div', elementId);
		// set default colors so that the objects are visible
		this.div.style.border = 'solid 1px #999999';
		this.div.style.backgroundColor = '#eeeeee';
	}
};

var RPopup = Class.create();
Object.extend(RPopup, RFrame);
Object.extend(RPopup.prototype, {
	initialize: function(elementId, titleText) {
		RFrame.prototype.initialize.call(this, elementId);
		createTitleBar(titleText);
	},
	createTitleBar: function(titleText) {
		this.div.titlebar = Repos.create('div', this.div.id + 'Titlebar');
		
	}
});


var HideWithEscDecorator = Class.create();
HideWithEscDecorator.prototype = {
	initialize: function(elem) {
		
	}
};

var RemoveWithEscDecorator = Class.create();
RemoveWithEscDecorator.prototype = {
	initialize: function(elem) {
		
	}
};

/**
 * Adds a windows-style titlebar to a div, with a close button
 */
var TitleBarDecorator = Class.create();
TitleBarDecorator.prototype = {
	initialize: function(elem, titlebarText) {
		var id = elem.id;
		var z = elem.style.zIndex;
		// make the titlebar
		var title = Repos.create('div');
		title.id = id + 'Titlebar';
		Element.setStyle(title, {position: 'absolute'});
		Element.setStyle(title, {zIndex: z + 1, top: '0px', left: '0px', height: '22px', width; '100%', backgroundColor: '#dddddd', border: 'none'});
		// title bar text
		var caption = Repos.create('span');
		caption.id = id + 'TitlebarText';
		Element.setStyle(caption, {zIndex: z + 2, top: '4px', left: '10px', color: '#333333', fontFamily: 'Arial, Helvetica, sans-serif', fontSize = '11px'});
		caption.appendChild(document.createTextNode(titleText));
		// close button
		var divClose = geturl_createElement('div');
		divClose.id = 'repos-geturl-close';
		geturl_setStyle(divClose, 2, '2px', (width-20)+'px', '16px', '16px', '#eeeeee');
		divClose.style.border = 'solid 1px #999999';
		divClose.style.textAlign = 'center';
		divClose.onclick = function() { getUrlClose(); return false; };
		// the X in the close button
		var aClose = geturl_createElement('a');
		aClose.id = 'repos-geturl-closelink';
		aClose.href = '#';
		aClose.title = 'Esc';
		aClose.style.color = '#666666';
		aClose.style.textDecoration = 'none';
		aClose.style.fontFamily = 'Arial, Helvetica, sans-serif';
		aClose.style.fontSize = '13px';
		aClose.style.fontWeight = 'bold';
		aClose.onclick = function() { getUrlClose(); return false; };
		aClose.appendChild(document.createTextNode('X'));
		divClose.appendChild(aClose);
		// put it together
		title.appendChild(caption);
		title.appendChild(divClose);
	}
}

// temp
function geturl_setStyle(objElem, zOffset, top, left, height, width, backgroundColor) {
	objElem.style.zIndex = _geturlz + zOffset;
	objElem.style.position = 'absolute';
	objElem.style.top = top;
	objElem.style.left = left;
	objElem.style.height = height;
	objElem.style.width = width;
	objElem.style.backgroundColor = backgroundColor;
}

var f = new RFrame('testframe');
f.div.top = '10px';
f.div.left = '10px';
f.div.width = '10px';
f.div.height = '10px';
f.div.positon = 'absolute';
document.getElementsByTagName('body')[0].appendChild(f.div);
Element.show('testframe');
