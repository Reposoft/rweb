// Copyright (c) 2006 Robin Schuil (http://lunchpauze.blogspot.com, http://www.sayoutloud.com)
// 
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to
// permit persons to whom the Software is furnished to do so, subject to
// the following conditions:
// 
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
// LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
// OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
// WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
// VERSION 0.1

Ajax.RssReader = Class.create();
Ajax.RssReader.prototype = Object.extend(new Ajax.Request(), {
	
	initialize : function( url, options ) {
		
		options = options || {};
		
		this.setOptions(options);
		
		this.items = {};

		var onSuccess = this.options.onSuccess || Prototype.emptyFunction;
		var onFailure = this.options.onFailure || Prototype.emptyFunction;
		
		this.options.onSuccess = (function(t) { if( this.onSuccess(t) ) { onSuccess(this); } else { this.onFailure(t); onFailure(this); } }).bind(this);
		this.options.onFailure = (function(t) { this.onFailure(t); onFailure(this); }).bind(this);
		
		this.request( url );

	},
	
	onSuccess : function( t ) {
		
		try {
			var node = t.responseXML;
			var xmlChannel = node.getElementsByTagName('channel').item(0);	
		}
		catch(e) {
			return false;
		}
		
		this.channel = {
			title: 					this._getElementValue(xmlChannel, 'title'),
			link:						this._getElementValue(xmlChannel, 'link'),
			description:		this._getElementValue(xmlChannel, 'description'),
			language:				this._getElementValue(xmlChannel, 'language'),
			copyright:			this._getElementValue(xmlChannel, 'copyright'),
			managingEditor:	this._getElementValue(xmlChannel, 'managingEditor'),
			webMaster:			this._getElementValue(xmlChannel, 'webMaster'),
			pubDate:				this._getElementValue(xmlChannel, 'pubDate'),
			lastBuildDate:	this._getElementValue(xmlChannel, 'lastBuildDate')
		};
		
		this.items = new Array();
		
		var xmlItems = xmlChannel.getElementsByTagName('item');

		for (var n=0; n<xmlItems.length; n++) {
			
			var xmlItem = xmlItems[n];
			
			var item = {
				title:				this._getElementValue(xmlItem, 'title'),
				link:					this._getElementValue(xmlItem, 'link'),
				description:	this._getElementValue(xmlItem, 'description'),
				author:				this._getElementValue(xmlItem, 'author'),
				category:			this._getElementValue(xmlItem, 'category'),
				comments:			this._getElementValue(xmlItem, 'comments'),
				guid:					this._getElementValue(xmlItem, 'guid'),
				pubDate:			this._getElementValue(xmlItem, 'pubDate')
			};
			
			this.items.push( item );
									
		}
		
		return true;
				
	},
	
	onFailure : function( t ) {
		
	},
	
	_getElementValue : function( node, elementName ) {
		
		try {
			var value = node.getElementsByTagName(elementName).item(0).firstChild.data;	
		}
		catch(e) {
			var value = '';
		}
		
		return value;
		
	}
	
});