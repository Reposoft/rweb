// A library that uses Prototype

var MyTestlib = Class.create();
MyTestlib.prototype = {
	initialize: function(myarg) {
		this.initarg = myarg;
	},
	getInitarg: function(initarg) {
		return this.initarg;	
	}
}
