/*------------------------------------------------------------------------------
|
|                           net4visions.com source code
|
|-------------------------------------------------------------------------------
|
| file:             _dialog.js - dialogs, floating windows
| category:         javascript
|
| last modified:    03/02/2006
|
| description:
| this js file handles the dialog boxes
|
| requirements:
| - prototype.js 		(http://conio.net)
| - scriptaculous.js	(http://script.aculo.us/)
|
| usage:
| please see docs/readme.htm
|
| contributions:
| - Jerod Venema
| - Ryan Gahl (EventPublisher class)
|
------------------------------------------------------------------------------*/

	/*------------------------------------------------------------------------------
	| EventPublisher Class
	| provided by Ryan Gahl
	------------------------------------------------------------------------------*/

	EventPublisher = Class.create();
	EventPublisher.prototype = {
		initialize: function() {
		},
		
		// pass the asynch flag (true/false) as the 3rd argument, or omit it to default to false
		attachEventHandler: function(eventName, handler) {
			// using an event cache array to track all handlers for proper cleanup
			if (this.allEvents == null)
				this.allEvents = new Array();
			// loop through the event cache to prevent adding duplicates
			var len = this.allEvents.length;
			var foundEvent = false;
			for (var i = 0; i < len; i++) {
				if (this.allEvents[i] == eventName) {
					foundEvent = true;
					break;
				}
			}
			if (!foundEvent)
				this.allEvents.push(eventName);
				
			eventName = eventName + "_evt"; // appending _evt to event name to avoid collisions
			if (this[eventName] == null)
				this[eventName] = new Array();
				
			//create a custom object containing the handler method and the asynch flag
			var asynchVar = arguments.length > 2 ? arguments[1] : false;
			var handlerObj = {
				method: handler,
				asynch: asynchVar
			};
			
			this[eventName].push(handlerObj);
		},
		
		// Removes a single handler from a specific event
		removeEventHandler: function(eventName, handler) {
			eventName = eventName + "_evt"; // appending _evt to event name to avoid collisions
			if (this[eventName] != null)
				this[eventName] = this[eventName].reject(function(obj) { return obj.method == handler; });
		},
		
		// Removes all handlers from a single event
		clearEventHandlers: function(eventName) {
			eventName = eventName + "_evt"; // appending _evt to event name to avoid collisions
			this[eventName] = null;
		},
		
		// Removes all handlers from ALL events
		clearAllEventHandlers: function() {
			if (this.allEvents) {
				var len = this.allEvents.length;
				for (var i = 0; i < len; i++) {
					this.clearEventHandlers(this.allEvents[i]);
				}
			}
		},    
		
		//to pass arguments to the handlers, include a 2nd argument (anonymous object that can contain whatever you want)
		fireEvent: function(eventName) {
			var evtName = eventName + "_evt"; // appending _evt to event name to avoid collisions
			if (this[evtName] != null) {
				var len = this[evtName].length; //optimization
								
				for (var i = 0; i < len; i++)
				{
					try
					{
						if (arguments.length > 1)
						{
							if (this[evtName][i].asynch)
							{
								//using a double closure to maintain "this" scope and pass all arguments properly (not sure if this is overkill or not)
								var eventArgs = arguments[1];
								var eventHandler = function(evt, index, args) { this[evt][index].method(args); }.bind(this);
								var eventHandlerPointer = function() { eventHandler(evtName, i, eventArgs); }.bind(this);
								setTimeout(eventHandlerPointer, 1);
							}
							else
								if (this && this[evtName] && this[evtName][i] && this[evtName][i].method)
								this[evtName][i].method(arguments[1]);                        
						} else {
							if (this[evtName][i].asynch)
							{
								if (this && this[evtName] && this[evtName][i] && this[evtName][i].method){
									var eventHandler = this[evtName][i].method;
									setTimeout(eventHandler, 1);
								}
							}
							else
								if (this && this[evtName] && this[evtName][i] && this[evtName][i].method)
									this[evtName][i].method();
								//this[evtName][i].method();
						}
					}
					catch (e) {
						if (this.id) {
							alert("ERROR: error in " + this.id + ".fireEvent():\n\n"  + e.message);
						} else {
							alert("ERROR: error in [unknown object].fireEvent():\n\n" + e.message);
						}
					}
				}
			}
		}
	};

	/*------------------------------------------------------------------------------
	| Dialogs
	------------------------------------------------------------------------------*/

	var Dialogs = {
		dialogs: 		[],
		currentZIndex: 	1000,				// default z-index
		boolPosition: 	false,				// set to false if no re-positioning is required on window resize
		themes:         'blue, red, green',	// non-default themes. specified as a comma delimited list -
											// can also be added to the script src tag using ...?themes=blue,red,orange
		
		// no changes below this line !!!
		pos: 			[0, 0], 	// left, top
		boolFirst: 		true,		// needed to identify the first dialog when auto positioning
		dTitleOffset: 	0,			// title height
		defaultTheme:   'default',	// theme to use when an invalid theme is chosen or no theme is specified
		themeRoot:    	'themes',	// theme directory
		
		messages:       {msgMinimize: 		'minimize', 
						 msgMaximize: 		'maximize/restore',
						 msgClose:    		'close',
						 msgResize:   		'resize',
						 msgModal:    		'modal',
						 msgSetTitle: 		'title set',
						 msgClearTitle: 	'title cleared',
						 msgSetContent: 	'content set',
						 msgClearContent: 	'content cleared',
						 msgAppendContent:	'content appended',
						 msgPrependContent: 'content prepended',
						 msgScrollbar:		'overflow'},
		
		_registerDialog: function(dialog) {
			if (this.dialogs.length == 0) {				
				this._addObservers();

				// set browser
				this.isIE = this._isIE();
				
				// set up modal window
				this.dModal = this._createHTML();
				
				// get height of dTitle
				dialog._unhide();
				this.dTitleOffset = Element.getHeight(dialog.dTitle);
				dialog._hide();
			}
			
			this.dialogs.push(dialog);			
		},
		_addObservers: function() {
			// add events
			this.eventMouseMove    = this._updateResize.bindAsEventListener(this);	
			this.eventWindowResize = this._position.bindAsEventListener(this);

			Event.observe(document, 'mousemove', this.eventMouseMove);
			Event.observe(window, 'resize', this.eventWindowResize);			
		},
		_stopObservers: function() {
			// remove events
			Event.stopObserving(document, 'mousemove', this.eventMouseMove);
			Event.stopObserving(window, 'resize', this.eventWindowResize);
			this.eventMouseMove    = null;
			this.eventWindowResize = null;
		},
		_unregisterDialog: function(dialog) {
			if (dialog.options.remove) {
				this.dialogs = this.dialogs.reject(function(d) { return d == dialog });
				if (dialog.boolRemove) Element.remove(dialog.dContainer);	
				Dialogs._notify('onRemove', {sender: dialog, message: 'removed'});
			}
			if (this.dialogs.length == 0) {
				Element.remove(this.dModal); // no more dialogs - remove modal dialog from DOM
				this._stopObservers();
			} else { // activate next visible dialog
				var dlgs = this.dialogs.select( function(d) { return (d.boolVisible) });
				if (dlgs.length != 0) dlgs[dlgs.length-1].top();
			}
		},
		_top: function(dialog) {			
			var nextModal = this._nextModal(dialog);
			if (dialog != nextModal) {
				Element.setStyle(this.activeDialog.dContainer, {zIndex: (this.currentZIndex -2)});
				nextModal.top();
			} else {
				if (this.activeDialog != dialog) {
					if (this.currentZIndex > 1000) {							
						this.currentZIndex = (this.currentZIndex - 2);					
						Element.setStyle(this.activeDialog.dContainer, {zIndex: this.currentZIndex});	
						Dialogs._notify('onDeactivate', {sender: this.activeDialog});
						if (this.activeDialog.options.debug) this.activeDialog.clearStatus();
					}
					this.currentZIndex = (this.currentZIndex + 2);
					Element.setStyle(this.dModal, {zIndex: (this.currentZIndex -1)});
					
					// set dModal to current theme
					if (this.activeDialog) Element.removeClassName(this.dModal, this.activeDialog.options.theme);
					Element.addClassName(this.dModal, dialog.options.theme);
					
					this.activeDialog = dialog;
					Dialogs._notify('onActivate', {sender: this.activeDialog, message: 'done'});
					if (this.activeDialog.options.debug) this.activeDialog.setStatus('done', true);
				}
				return this.currentZIndex;
			}
		},
		_nextModal: function(obj) {			
			this.dialogs.select( function(d) { return (d.options.modal && d.boolVisible) }).each( function(d) {
				obj = d;
			}.bind(this));
			return obj;
		},
		_updateResize: function(event) {
			if (Event.element(event).nodeName == 'iframe') return false;
			if (!this.activeDialog) return false;
			var pointer = [Event.pointerX(event), Event.pointerY(event)];
			this.activeDialog._updateResize(pointer);
			Event.stop(event);
		},
		_position: function(event) {
			if (this.activeDialog.boolMaximize) return false;
			if (!this.boolPosition) return false;
				this.boolPosition = false;
				this.boolFirst    = true;
				this.dialogs.select( function(d) { return d.options.show }).each( function(d) {
					this._updatePosition(d);
				}.bind(this));
			this.boolPosition = true;
			Event.stop(event);
		},
		_updatePosition: function(dialog) {
			// determine position
			if (this.boolFirst) { // first dialog	
				this.boolFirst    = false;
				var windowDim     = Element.getDimensions(document.body); 		// dimensions browser window			
				var dContainerDim = Element.getDimensions(dialog.dContainer);	// dimension dialog window
				
				var cumVal;
				var maxDim;
				var cumOffset;

				var shows = Dialogs.dialogs.select(function (s) {
					return s.options.show; 	
				}.bind(this));
				
				$A(shows).each( function(s, index) {
					cumVal = (this.dTitleOffset * (shows.length - (index+1)));
					maxDim = Element.getDimensions(s.dContainer);					
					// set width and height to the dimensions of the largest dialog
					dContainerDim.width  = (maxDim.width  >= dContainerDim.width)  ? (maxDim.width  - cumVal) : dContainerDim.width;
					dContainerDim.height = (maxDim.height >= dContainerDim.height) ? (maxDim.height - cumVal) : dContainerDim.height;
				 }.bind(this));
				
				var cumOffset = parseInt(this.dTitleOffset * (shows.length-1));
				var posX      = parseInt((windowDim.width  - dContainerDim.width  - cumOffset)/2);
				var posY      = parseInt((windowDim.height - dContainerDim.height - cumOffset)/2);				

			} else { // subsequent dialogs
				var posX = (Dialogs.pos[0] + this.dTitleOffset);
				var posY = (Dialogs.pos[1] + this.dTitleOffset);		
			}
			// position dialogs
			dialog.move(posX, posY, true)
			// set new positions
			this.pos = [posX, posY];
		},
		_chkDuplicatePos: function(dialog, posX, posY) { // adds offset in case of duplicate (identical) position 
			this.dialogs.each( function(d) {
				if (dialog != d) {						
					if (!d.boolVisible) {					
						d._unhide();
					}
					
					var pos = Position.cumulativeOffset(d.dContainer);
					if (pos[0] == posX) posX = (posX + this.dTitleOffset);
					if (pos[1] == posY) posY = (posY + this.dTitleOffset);
					
					if (!d.boolVisible) {
						d._hide();	
					}
				}
			}.bind(this));
			
			return [posX, posY];
		},
		_isIE: function() {
			return navigator.appVersion.indexOf('MSIE') > 0;      		
		},
		_notify: function(eventName) {
			var options = arguments[1] || '[]';
			var sender  = options.sender || this.activeDialog;
			var message = options.message || sender.dTitleText.innerHTML + ': ' + eventName;
			sender.fireEvent(eventName, { eventName: eventName, sender: sender, message: message });
		},
		_createHTML: function() {
			var modal, iframe, div, node;
			modal = document.createElement('DIV');
			Element.addClassName(modal, 'dModal');
			Element.setStyle(modal, {position: 'absolute', width: '100%', height: '100%', top: '0px', left: '0px', zIndex: 999, display: 'none'});							
			
			// add modal div to DOM
			node = document.body.firstChild;
			node = (node.nodeType == 3) ? node.nextSibling : node;
			document.body.insertBefore(modal, node);
			
			if (this.isIE) {
				iframe = document.createElement('IFRAME');
				div    = document.createElement('DIV');
				iframe.frameborder = 0;					
				Element.setStyle(iframe, {border: '0px', position: 'absolute', width: '100%', height: '100%', top: '0px', left: '0px', zIndex: -1});							
				Element.setStyle(div, {position: 'absolute', width: '100%', height: '100%', top: '0px', left: '0px', zIndex: 0});							
				// add element to DOM
				modal.appendChild(div);
				modal.appendChild(iframe);
			}
			if (navigator.userAgent.indexOf('Opera') > 0) Element.setStyle(modal, {backgroundColor: 'transparent'}); // fix Opera for no opacity
			
			return modal;
		},
		load: function() {
			if((typeof Scriptaculous=='undefined') || parseFloat(Scriptaculous.Version.split(".")[0] + "." +
					 Scriptaculous.Version.split(".")[1]) < 1.5)
						throw("This dialog script requires the script.aculo.us JavaScript framework >= 1.5.0");

			var path = null;
			$A(document.getElementsByTagName('head')[0].childNodes).findAll( function(s) {
				return (s.src && s.src.match(/_dialog\.js(\?.*)?$/))
			}).each( function(s) {
				path = s.src.replace(/_dialog\.js(\?.*)?$/,'');
				var includes = s.src.match(/\?.*themes=(.+)$/);
				var themes   = new Array();
				(includes ? includes[1] : Dialogs.themes).split(',').each(
					function(include) { themes.push(include.strip()); Dialogs.require(path,include.strip()); }
				);
				Dialogs.themes = themes;
			});
			Dialogs.require(path, Dialogs.defaultTheme);
			document.write('<link href="' + path + Dialogs.themeRoot + '/_dCommon.css" rel="stylesheet" type="text/css" media="all" />'); // common css 
			this.path = path; //save this path for later use when referencing theme items
		},
		require: function(path, theme){
			document.write('<link href="' + path + Dialogs.themeRoot + '/' + theme + '/_dStyle.css" rel="stylesheet" type="text/css" media="all" />');
		}
	}

	Dialogs.load();
	
	/*------------------------------------------------------------------------------
	| Dialog Class
	------------------------------------------------------------------------------*/

	var Dialog = Class.create();
	Object.extend(Object.extend(Dialog.prototype, EventPublisher.prototype), {
		initialize: function(element) {
			//set default options
			this.options = Object.extend({
				top:      		10,			// default y position
				left:     		10,			// default x position
				width:    		200,		// default width
				height:   		200,		// default height
				mwidth:   		80,			// default minimum width
				mheight:  		30,			// default minimum height
				moveable:  		true, 		// allow to move dialog (dragging)
				resizable:   	true,		// allow resizing
				minimizable:	true, 		// allow minimizing
				maximizable: 	true,		// allow maximizing
				closeable:		true,		// allow closing
				scrollbar:   	'hidden',	// hidden, scroll, auto
				statusbar:   	true,		// enable status row
				modal:    		false,		// true: dialog is opened in modal mode
				show:			true,		// true: show and auto position on init
				remove:			true,		// true: remove dialog from DOM on closing dialog
				effects:		false,		// true: use transitions; false: no animations
				limit:          false,      // true: limits dragging and moving to dimensions of container (client window);
				theme:          'default',  // the name of the folder inside the themes directory containing any necessary images/css
				debug:    		false		// debug
			}, arguments[1] || {});
			
			// set up arrays for initial position and size				
			this.sPos = []; // start position
			this.sDim = []; // start size
			
			// set up dialog elements
			if ($(element)) {
				this.element = $(element);
				this.boolRemove = false; // don't remove existing html elements from DOM
			 } else {
				this.element = this._createHTML('dialog');
				this.boolRemove = true; // dialog can be removed from DOM
			 }
			
			// clean whitespace
			Element.cleanWhitespace(this.element);			
			$A(this.element.getElementsByTagName('*')).each( function(e, index) {
				Element.cleanWhitespace(e);	
			}.bind(this));
			this.dContainer   = this.element;
			this.dTitle       = this.dContainer.childNodes[0];
			this.dTitleText   = this.dTitle.childNodes[0];			
			this.dContentWrap = this.dContainer.childNodes[1];			
			this.dContent     =	this.dContentWrap.childNodes[0];			
			this.dContentText = this.dContent.childNodes[0];			
			this.dStatus      = this.dContentWrap.childNodes[1]; 
			this.dStatusText  = this.dStatus.childNodes[0];			
			this.dBtnExpand   = this.dTitle.getElementsByTagName('INPUT')[0];
			this.dBtnMaximize = this.dTitle.getElementsByTagName('INPUT')[1];	
			this.dBtnClose	  = this.dTitle.getElementsByTagName('INPUT')[2];
			this.dBtnResize   = this.dStatus.childNodes[1];
			
			// set up events
			this._addObservers();
			this._addEventHandler();

			// register dialogs
			Dialogs._registerDialog(this);			
			
			// setup dialog
			this._setup();			
		},
		/*------------------------------------------------------------------------------
		| private functions                         
		------------------------------------------------------------------------------*/
		_addObservers: function() {
			// set up events
			this.dContainerClick    = this.top.bindAsEventListener(this);
			this.dContainerHover    = this._hover.bindAsEventListener(this);
			this.dTitleFocus		= this._focus.bindAsEventListener(this);
			this.dBtnExpandClick   	= this.minimize.bindAsEventListener(this);
			this.dBtnMaximizeClick  = this.maximize.bindAsEventListener(this);
			this.dBtnCloseClick 	= this.close.bindAsEventListener(this);			
			this.dBtnResizeClick	= this._resize.bindAsEventListener(this);
			this.dBtnResizeUp		= this._endResize.bindAsEventListener(this);
			
			Event.observe(this.dContainer, 'click', this.dContainerClick);
			Event.observe(this.dContainer, 'mouseover', this.dContainerHover);
			Event.observe(this.dTitle, 'focus', this.dTitleFocus);
			Event.observe(this.dTitle, 'mousedown', this.dContainerClick);
			Event.observe(this.dBtnExpand, 'click', this.dBtnExpandClick);
			Event.observe(this.dBtnMaximize, 'click', this.dBtnMaximizeClick);	
			Event.observe(this.dBtnClose, 'click', this.dBtnCloseClick);
			Event.observe(this.dBtnResize, 'mousedown', this.dBtnResizeClick);
			Event.observe(this.dBtnResize, 'mouseup', this.dBtnResizeUp);
		},
		_addEventHandler: function() {
			if (this.options.onActivate) 		this.attachEventHandler('onActivate', this.options.onActivate.bind(this));
			if (this.options.onDeactivate) 		this.attachEventHandler('onDeactivate', this.options.onDeactivate.bind(this));
			if (this.options.onDragStart) 		this.attachEventHandler('onDragStart', this.options.onDragStart.bind(this));
			if (this.options.onDrag) 			this.attachEventHandler('onDrag', this.options.onDrag.bind(this));
			if (this.options.onDragEnd) 		this.attachEventHandler('onDragEnd', this.options.onDragEnd.bind(this));
			if (this.options.onShow) 			this.attachEventHandler('onShow', this.options.onShow.bind(this));
			if (this.options.onClose) 			this.attachEventHandler('onClose', this.options.onClose.bind(this));
			if (this.options.onRemove) 			this.attachEventHandler('onRemove', this.options.onRemove.bind(this));
			if (this.options.onResize) 			this.attachEventHandler('onResize', this.options.onResize.bind(this));
			if (this.options.onMove) 			this.attachEventHandler('onMove', this.options.onMove.bind(this));
			if (this.options.onMaximize)		this.attachEventHandler('onMaximize', this.options.onMaximize.bind(this));
			if (this.options.onMinimize) 		this.attachEventHandler('onMinimize', this.options.onMinimize.bind(this));
			if (this.options.onBeforeContent) 	this.attachEventHandler('onBeforeContent', this.options.onBeforeContent.bind(this));
			if (this.options.onAfterContent) 	this.attachEventHandler('onAfterContent', this.options.onAfterContent.bind(this));
		},
		_setup: function() {
			// IE: set up iframe to cover select boxes
			if (Dialogs.isIE) this.dFrame = this._createHTML('iframe');
			
			// set theme
			this.setTheme(this.options.theme);
			
			// set buttons
			(this.options.resizable)   ? Element.show(this.dBtnResize)   : Element.hide(this.dBtnResize);
			(this.options.minimizable) ? Element.show(this.dBtnExpand)   : Element.hide(this.dBtnExpand);
			(this.options.maximizable) ? Element.show(this.dBtnMaximize) : Element.hide(this.dBtnMaximize);
			(this.options.closeable)   ? Element.show(this.dBtnClose)    : Element.hide(this.dBtnClose);
			
			// set scrollbar
			this.scrollbar(this.options.scrollbar);
			
			// set title				
			var strTitle = (this.options.title) ? this.options.title : 'window ' + parseInt(Dialogs.dialogs.length);			
			strTitle = strTitle.charAt(0).toUpperCase() + strTitle.substr(1).toLowerCase(); // capitalize first letter
			this.setTitle(strTitle);
			
			// set status
			if (!this.options.statusbar) Element.addClassName(this.dStatus, 'hide');
			
			// set modal
			this.options.modal = (Dialogs.dModal) ? this.options.modal : false;
			this.options.limit = (this.options.modal) ? true : this.options.limit;
			// set moveable/draggable
			if (this.options.moveable) this._move();
			
			// set size
			this.resize(this.options.width, this.options.height, false);
			
			// set position / show dialog
			if (this.options.show) { // auto mode
				this._position();
				this.show(true);
				this.boolVisible = true;
			} else { // manual mode
				this.boolVisible = false;
				this.position(this.options.left, this.options.top);
			}
		},
		_move: function() {
			Element.setStyle(this.dTitle, {cursor: 'move'});
			this.moveable = new Draggable(this.dContainer, {
				handle: this.dTitle,
				zindex: this.currentZIndex,
				snap: function(x, y) {
					var pos = this._chkLimitPos(x, y);
					return [pos[0], pos[1]];
				}.bind(this)
			});
			this.moveableObserver = {
				onStart: function(eventName, draggable, event) {
					if (draggable == this.moveable) {
						Dialogs._notify('onDragStart', {sender: this});
					}
				}.bind(this),
				onDrag: function(eventName, draggable, event) {
					if (draggable == this.moveable) {
						var pos = Position.cumulativeOffset(this.dContainer);
						if (this.options.debug) this.setStatus('x: ' + pos[0]+ ' y: ' + pos[1]);
						Dialogs._notify('onDrag', {sender: this});	
					}
				}.bind(this),
				onEnd: function(eventName, draggable, event) {
					if (draggable == this.moveable) {
						var pos = Position.cumulativeOffset(this.dContainer);
						if (this.options.debug) this.setStatus('x: ' + pos[0]+ ' y: ' + pos[1], true);
						Dialogs._notify('onDragEnd', {sender: this});	
					}
				}.bind(this)
			}
			Draggables.addObserver(this.moveableObserver);
		},
		_resize: function(event) {
			if (Event.isLeftClick(event)) {   
				this.top();
				this.sDim = [Element.getDimensions(this.dContainer).width, Element.getDimensions(this.dContainer).height];
				this.sPos = [Event.pointerX(event), Event.pointerY(event)];
				this.boolResize = true;
			}
		},
		_updateResize: function(pointer) {
			if (this.boolResize) {
				var w = Math.max(this.options.mwidth,  this.sDim[0] + pointer[0] - this.sPos[0]);
				var h = Math.max(this.options.mheight, this.sDim[1] + pointer[1] - this.sPos[1]);
				this.resize(w, h, false);
				if (this.options.debug) this.setStatus(w + ' x ' + Element.getDimensions(this.dContainer).height + 'px'); 
			};
		},
		_endResize: function(event) {			
			if (this.boolResize) {
				this.boolResize   = false;
				this.boolMaximize = false;
				this.clearStatus(true);		
			}
			Event.stop(event);
		},
		_close: function() {
			// close modal window if visible					
			if (this.options.modal)	Element.hide(Dialogs.dModal);
			if (this.boolMinimize) { // if dialog is closed in minimized mode
				this.boolMinimize = false;
				Element.show(this.dContentWrap);
			};
			
			this._unregisterDialog();
		},
		_unregisterDialog: function() {
			if (this.options.remove) this._stopObservers();			
			Dialogs._unregisterDialog(this);
		},
		_stopObservers: function() {
			Event.stopObserving(this.dContainer, 'click', this.dContainerClick);
			Event.stopObserving(this.dContainer, 'mouseover', this.dContainerHover);
			Event.stopObserving(this.dTitle, 'focus', this.dTitleFocus);
			Event.stopObserving(this.dTitle, 'mousedown', this.dContainerClick);
			Event.stopObserving(this.dBtnExpand, 'click', this.dBtnExpandClick);
			Event.stopObserving(this.dBtnMaximize, 'click', this.dBtnMaximizeClick);	
			Event.stopObserving(this.dBtnClose, 'click', this.dBtnCloseClick);
			Event.stopObserving(this.dBtnResize, 'mousedown', this.dBtnResizeClick);
			Event.stopObserving(this.dBtnResize, 'mouseup', this.dBtnResizeUp);
			
			this.dContainerClick   = null;
			this.dContainerHover   = null;
			this.dTitleFocus       = null;
			this.dContainerClick   = null;
			this.dBtnExpandClick   = null;
			this.dBtnMaximizeClick = null;	
			this.dBtnCloseClick    = null;
			this.dBtnResizeClick   = null;
			this.dBtnResizeUp      = null;
			
			 // remove moveable observers
			if (this.options.moveable) {
				Draggables.observers = Draggables.observers.reject( function(o) { return o == this.moveableObserver }.bind(this));
				Draggables.unregister(this.moveable);
			}
		},
		_unhide: function() { // show dialog temporarily to get dimensions			
			Element.setStyle(this.dContainer, {visibility: 'hidden', display: 'block'});	
		},
		_hide: function() { // re-hide dialog after getting dimensions (see _unhide)
			Element.setStyle(this.dContainer, {visibility: 'visible', display: 'none'});	
		},
		_position: function() {
			this._unhide();
			
			if (Dialogs.boolFirst == true) { // first dialog	
				Dialogs.boolFirst = false;
				this.center(false);
				var pos = Position.cumulativeOffset(this.dContainer); 
				Dialogs.pos[0] = pos[0];
				Dialogs.pos[1] = pos[1];
			} else { // subsequent dialogs
				Dialogs.pos[0] = (Dialogs.pos[0] + Dialogs.dTitleOffset);
				Dialogs.pos[1] = (Dialogs.pos[1] + Dialogs.dTitleOffset);
				this.move(Dialogs.pos[0], Dialogs.pos[1], false);
			}
			
			this._hide();
		},
		_sizeFrame: function() { // only IE
			var dim = Element.getDimensions(this.dContainer);
			Element.setStyle(this.dFrame, {width: dim.width + 'px', height: dim.height + 'px'});
		},
		_focus: function(event) { // remove border of buttons if focused (firefox)
			var element = Event.element(event);
			if (element.nodeName == 'INPUT') element.blur();
			Event.stop(event);
		},
		_hover: function(event) {
			var element = Event.element(event);
			var msg;
			if (element == this.dBtnExpand) {				
				msg = Dialogs.messages.msgMinimize;	
			} else if (element == this.dBtnMaximize) {
				msg = Dialogs.messages.msgMaximize;
			} else if (element == this.dBtnClose) {
				msg = Dialogs.messages.msgClose;
			} else if (element == this.dBtnResize && !this.boolResize) {
				msg = Dialogs.messages.msgResize;
			} else {
				return false;	
			}
			
			if (msg.length > 0 && this.options.debug) this.setStatus(msg, true);
			Event.stop(event);
		},
		_chkLimitPos: function(posX, posY) { // limits positioning to container dim (default: client window) 
			var minX, minY, maxX, maxY;
			minY = 0;
			if (this.options.limit) {
				minX = 0;
				maxX = (Element.getDimensions(document.body).width  - Element.getDimensions(this.dContainer).width);
				maxY = (Element.getDimensions(document.body).height - Element.getDimensions(this.dContainer).height);
				posX = (posX < minX) ? minX : (posX > maxX) ? maxX : posX;
				posY = (posY < minY) ? minY : (posY > maxY) ? maxY : posY;
			} else { // no limit
				minX = (Dialogs.dTitleOffset - Element.getDimensions(this.dContainer).width);
				if (Element.getStyle(document.body, 'overflow') == 'hidden') {					
					maxX = (Element.getDimensions(document.body).width  - Dialogs.dTitleOffset);
					maxY = (Element.getDimensions(document.body).height - Dialogs.dTitleOffset);
					posX = (posX < minX) ? minX : (posX > maxX) ? maxX : posX;
					posY = (posY < minY) ? minY : (posY > maxY) ? maxY : posY;
				} else {
					posX = (posX < minX) ? minX : posX;
					posY = (posY < minY) ? minY : posY;
				}
			}
			return [posX, posY];
		},
		_createHTML: function(type) {
			var obj = null;
			if (type == 'iframe') {
				if (Dialogs.isIE) {
					obj = document.createElement('IFRAME');
					obj.frameborder = 0;					
					Element.setStyle(obj, {border: 0 + 'px', position: 'absolute', backgroundColor: '#ffffff', top: 0 + 'px', left: 0 + 'px', zIndex: -1});							
					this.dContainer.insertBefore(obj, this.dTitle);
				}		
			} else if (type == 'dialog') {
				// create dialog html code
				var spacer = Dialogs.path + Dialogs.themeRoot + '/' + this.options.theme + '/spacer.gif';
				obj = 	Builder.node('div', {className: 'dContainer', style: 'display: none'}, [							
							Builder.node('div', {className: 'dTitle'}, [
								Builder.node('span'),										
								Builder.node('div', {className: 'dTitleBtn'} , [										   
									Builder.node('input', {type: 'image', src: spacer, className: 'dBtnExpand'}),
									Builder.node('input', {type: 'image', src: spacer, className: 'dBtnMaximize'}),
									Builder.node('input', {type: 'image', src: spacer, className: 'dBtnClose'})
								])
							]),
							Builder.node('div', {className: 'dContentWrap'}, [
								Builder.node('div', {className: 'dContent'}, [
									Builder.node('div')
								]),
								Builder.node('div', {className: (this.options.statusbar) ? 'dStatus' : 'dStatus hide'}, [
									Builder.node('span'),
									Builder.node('div', {className: 'dBtnResize'})
								])
							])
				  		]);				

				// add html to DOM
				var node = document.body.lastChild;
				node = (node.nodeType == 3) ? node.nextSibling : node;
				document.body.insertBefore(obj, node);
			}
			return obj;
		},
		/*------------------------------------------------------------------------------
		| public functions                         
		------------------------------------------------------------------------------*/
		show: function(t) {			
			if (this.boolVisible) return false;
			this.boolVisible = true;
			Dialogs._notify('onShow', {sender: this});	
			if (t) this.top();			
			if (this.options.modal && (!t)) this.top();			
			(this.options.effects) ? Effect.Appear(this.dContainer) : Element.show(this.dContainer);
		},
		open: function(t) {			
			this.show(t);
		},
		close: function(event) {
			if (!this.boolVisible) return false;
			this.boolVisible = false;
			Dialogs._notify('onClose', {sender: this});	
			if (this.options.effects) {
				Effect.Fade(this.dContainer, {
					afterFinish: function() {
						this._close();				
					}.bind(this)
				});
			} else {
				Element.hide(this.dContainer);	
				this._close();
			}
			
			if (event) Event.stop(event);
		},
		hide: function() {
			this.close();
		},
		remove: function() {
			if (!this.options.remove) this.options.remove = true;	
			if (!this.boolVisible) this.boolVisible = true;
			this.close();
		},
		top: function() {
			this.currentZIndex = Dialogs._top(this);
			Element.setStyle(this.dContainer, {zIndex: (this.currentZIndex)});		
			// show modal window if needed
			if (this.options.modal && !Element.visible(Dialogs.dModal)) {
				Element.show(Dialogs.dModal);				
				if (this.options.debug) this.setStatus(Dialogs.messages.msgModal, true);
			}		
		},
		move: function(posX, posY, m) {
			var pos = this._chkLimitPos(posX, posY);
			if (!this.options.show && Dialogs.dialogs.length > 1) {
				pos = Dialogs._chkDuplicatePos(this, pos[0], pos[1]); // avoid positioning to same location
			}
			Dialogs._notify('onMove', {sender: this});	
			m = (this.options.effects) ? m : false;
			if (m == false) {
				Element.setStyle(this.dContainer, {left: pos[0] + 'px', top: pos[1] + 'px', marginTop: 0 + 'px', marginLeft: 0 + 'px'});
				if (this.options.debug) this.setStatus('x: ' + pos[0] + ' y: ' + pos[1], true);
			} else {
				new Effect.Move(this.dContainer, {x: pos[0], y: pos[1], mode: 'absolute',
					afterFinish: function () {
						if (this.options.debug) this.setStatus('x: ' + pos[0] + ' y: ' + pos[1], true);	
					}.bind(this)
				});
			}
		},
		position: function(posX, posY) {
			this.move(posX, posY, false);	
		},
		center: function(m) {
			var posX = parseInt((Element.getDimensions(document.body).width  - Element.getDimensions(this.dContainer).width)/2);
			var posY = parseInt((Element.getDimensions(document.body).height - Element.getDimensions(this.dContainer).height)/2);	
			this.move(posX, posY, m);
		},
		centerX: function(m) {
			var pos  = Position.cumulativeOffset(this.dContainer);
			var posX = parseInt((Element.getDimensions(document.body).width - Element.getDimensions(this.dContainer).width)/2);			
			var posY = pos[1];	
			this.move(posX, posY, m);
		},
		centerY: function(m) {
			var pos  = Position.cumulativeOffset(this.dContainer);
			var posX = pos[0];
			var posY = parseInt((Element.getDimensions(document.body).height - Element.getDimensions(this.dContainer).height)/2);	
			this.move(posX, posY, m);
		},
		resize: function(w, h, m) {			
			if (!this.boolVisible) {				
				this._unhide();
				m = false;
			}
			// check min height
			this.options.mheight = (this.options.mheight < (Dialogs.dTitleOffset + Element.getHeight(this.dStatus)) ? (Dialogs.dTitleOffset + Element.getHeight(this.dStatus)): this.options.mheight);
			
			m = (this.options.effects) ? m : false;
			var h = (Element.getDimensions(this.dContent).height - ((Element.getDimensions(this.dContainer).height - h)));
			if (m == false) {
				Element.setStyle(this.dContainer, {width: w + 'px'});
				Element.setStyle(this.dContent, {height: h + 'px'});
				Element.setStyle(this.dContentWrap, {height: (h + Element.getDimensions(this.dStatus).height) + 'px'});
				if (Dialogs.isIE) this._sizeFrame();
				if (!this.boolResize) {					
					if (this.options.debug) this.setStatus(w + ' x ' + h + 'px', true);
				}
			} else {
				Element.setStyle(this.dContentWrap, {height: 'auto'});
				var pos = Position.cumulativeOffset(this.dContainer);
				new Effect.MoveAndResizeTo(this.dContainer, this.dContent, pos[0], pos[1], w, h, {
					queue: 'front',
					afterFinish: function() {
						if (Dialogs.isIE) this._sizeFrame();
						if (this.options.debug) this.setStatus(w + ' x ' + h + 'px', true);
					}.bind(this)
				});
			}
			
			if (!this.boolVisible) {	
				this._hide();
			}
			Dialogs._notify('onResize', {sender: this});	
		},
		maximize: function(event) {
			if (!this.boolVisible) return false;				
			this.top();
			if (this.boolMaximize) {
				var w    = this.sDim[0];
				var h    = this.sDim[1];
				var posX = this.sPos[0];
				var posY = this.sPos[1];
			} else {
				if (!this.boolMinimize) {
					this.sDim = [Element.getDimensions(this.dContainer).width, Element.getDimensions(this.dContainer).height];
				}
				this.sPos = Position.cumulativeOffset(this.dContainer);
				var w     = parseInt(Element.getDimensions(document.body).width);
				var h     = parseInt(Element.getDimensions(document.body).height);
				var posX  = 0;
				var posY  = 0;
			}

			if (this.boolMinimize) {
				Element.setStyle(this.dContentWrap, {display: ''});
				this.boolMinimize = false;
				Element.setStyle(this.dBtnExpand, {backgroundPosition: '-20px 50%'});
			}
			
			Element.setStyle(this.dContentWrap, {height: 'auto'});				
			var offset = (Dialogs.dTitleOffset + Element.getHeight(this.dStatus));
			if (this.options.effects) {
				new Effect.MoveAndResizeTo(this.dContainer, this.dContent, posX, posY, w, (h - offset), {
					queue: 'front',
					afterFinish: function() {
						if (Dialogs.isIE) this._sizeFrame();
						if (this.options.debug) this.setStatus('x: ' + posX + ' y: ' + posY + ' | ' + w + ' x ' + h + 'px', true);
					}.bind(this)
				});
			} else {				
				this.resize(w, h, false);
				this.position(posX, posY);
			}
			this.boolMaximize = (this.boolMaximize) ? false : true;			
			Dialogs._notify('onMaximize', {sender: this});	
			if (event) Event.stop(event);	
		},
		minimize: function(event) {
			if (!this.boolVisible) return false;
			this.top();
			if (!this.boolMinimize)	this.sDim = [Element.getDimensions(this.dContainer).width, Element.getDimensions(this.dContainer).height]; // keep dimensions if 
			if (this.options.effects) {
				Effect.toggle(this.dContentWrap, 'blind', {
					queue: 'front',
					afterFinish: function() {
						this.boolMinimize = (Element.visible(this.dContentWrap)) ? false : true;
						(this.boolMinimize) ? Element.setStyle(this.dBtnExpand, {backgroundPosition: '0px 50%'}) : Element.setStyle(this.dBtnExpand, {backgroundPosition: '-20px 50%'});
						Dialogs._notify('onMinimize', {sender: this});
					}.bind(this)
				});
			} else {
				Element.toggle(this.dContentWrap);	
						this.boolMinimize = (Element.visible(this.dContentWrap)) ? false : true;
						(this.boolMinimize) ? Element.setStyle(this.dBtnExpand, {backgroundPosition: '0px 50%'}) : Element.setStyle(this.dBtnExpand, {backgroundPosition: '-20px 50%'});
			}
			if (event) Event.stop(event);			
		},
		visible: function() {
			return this.boolVisible; 
		},
		setTitle: function(str) {			
			Element.update(this.dTitleText, str);	
			if (this.options.debug) this.setStatus(Dialogs.messages.msgSetTitle, true);
		},
		clearTitle: function() {
			Element.update(this.dTitleText, '');		
			if (this.options.debug) this.setStatus(Dialogs.messages.msgClearTitle, true);
		},
		addContent: function(str) {
			this.setContent(str);	
		},
		setContent: function(str) {			
			Dialogs._notify('onBeforeContent', {sender: this});	
			Element.update(this.dContentText, str);	
			if (this.options.debug) this.setStatus(Dialogs.messages.msgSetContent, true);
			Dialogs._notify('onAfterContent', {sender: this});	
		},
		clearContent: function() {
			Element.update(this.dContentText, '');		
			if (this.options.debug) this.setStatus(Dialogs.messages.msgClearContent, true);
		},
		prependContent: function(str) {
			new Insertion.Top(this.dContentText, str);
			if (this.options.debug) this.setStatus(Dialogs.messages.msgPrependContent, true);
		},
		appendContent: function(str) {
			new Insertion.Bottom(this.dContentText, str);
			if (this.options.debug) this.setStatus(Dialogs.messages.msgAppendContent, true);
		},
		setStatus: function(str, boolFade) {
			if (!this.options.statusbar) return false;
			if (this.currentEffect) {
				this.currentEffect.cancel();
				Element.setOpacity(this.dStatusText, 1);
			}
			Element.update(this.dStatusText, str);
			if (boolFade) this.clearStatus(boolFade);
		},
		clearStatus: function(boolFade) {
			if (!this.options.statusbar) return false;
			if (boolFade) {
				if (this.currentEffect) {
					this.currentEffect.cancel();
					Element.setOpacity(this.dStatusText, 1); // reset opacity when effect is cancelled
				}
				this.currentEffect = new Effect.Fade(this.dStatusText, {
					duration: 3,
					afterFinish: function() {
						Element.update(this.dStatusText, '');
						Element.show(this.dStatusText);	
						
						this.currentEffect = null;
					}.bind(this)
				});
			} else {	
				Element.setOpacity(this.dStatusText, 1);
				Element.update(this.dStatusText, '');
			}
		},
		setTheme: function(theme) {
			Element.removeClassName(this.dContainer, this.options.theme);
			if (Dialogs.themes.detect(function(t) { return t == theme; })) {
				this.options.theme = theme;
			} else {
				this.options.theme = Dialogs.defaultTheme;
			}
			Element.addClassName(this.dContainer, this.options.theme);
		},
		getTheme: function() {
			return this.options.theme;
		},
		scrollbar: function(o) {
			o = (o == 'hidden' || o == 'auto' || o == 'scroll') ? o : 'hidden';
			Element.setStyle(this.dContent, {overflow: o});
			if (this.options.debug) this.setStatus(Dialogs.messages.msgScrollbar + ': ' + o, true);
		}
	});
	
	/*------------------------------------------------------------------------------
	| Effect: MoveAndResizeTo
	| based on scriptaculous treasure chest - by jake richardson
	------------------------------------------------------------------------------*/

	Effect.MoveAndResizeTo = Class.create();
	Object.extend(Object.extend(Effect.MoveAndResizeTo.prototype, Effect.Base.prototype), {
		initialize: function(objW, objH, toLeft, toTop, toWidth, toHeight) {
			this.objWidth   = $(objW);
			this.objHeight	= $(objH);
			this.toTop      = toTop;
			this.toLeft     = toLeft;
			this.toWidth    = toWidth;
			this.toHeight   = toHeight;
			this.options 	= arguments[6] || {};
			this.start(this.options);
		},
		setup: function() {
			this.originalTop  		 = parseFloat(Element.getStyle(this.objWidth,'top')     || 0);
			this.originalLeft 		 = parseFloat(Element.getStyle(this.objWidth,'left')    || 0);
			this.originalWidth  	 = parseFloat(Element.getStyle(this.objWidth,'width')   || 0);
			this.originalHeight 	 = parseFloat(Element.getStyle(this.objHeight,'height') || 0);
			this.effectiveTop 		 = this.toTop;
			this.effectiveLeft 		 = this.toLeft;
			this.effectiveWidth 	 = this.toWidth;
			this.effectiveHeight	 = this.toHeight;
			
			if (this.originalTop    == this.effectiveTop &&
				this.originalLeft   == this.effectiveLeft &&
				this.originalWidth  == this.effectiveWidth &&
				this.originalHeight == this.effectiveHeight) {
				
				this.cancel();
			}
			if (this.options.duration == 0) {
				this.setPosition(this.effectiveTop, this.effectiveLeft, this.effectiveWidth, this.effectiveHeight);
				this.cancel();
			}
		},
		update: function(position) {
			topd    = this.effectiveTop    * (position) + this.originalTop    * (1 - position);
			leftd   = this.effectiveLeft   * (position) + this.originalLeft	  * (1 - position);
			widthd  = this.effectiveWidth  * (position) + this.originalWidth  * (1 - position);
			heightd = this.effectiveHeight * (position) + this.originalHeight * (1 - position);
			
			this.setPosition(topd, leftd, widthd, heightd);
		},
		setPosition: function(topd, leftd, widthd, heightd) {
			Element.setStyle(this.objWidth, {top: topd + 'px', left: leftd + 'px', width: widthd + 'px'});
			Element.setStyle(this.objHeight, {height: heightd + 'px'});
		}
	});
/*----------------------------------------------------------------------------*/
