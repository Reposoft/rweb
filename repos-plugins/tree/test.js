var svn = {"path":"http://localhost/data/demoproject/trunk/public", "list":{
"documents":{
"commit":{
"author":"admin","date":"2008-04-16T10:20:57.333174Z","revision":"1"},
"kind":"dir"},
"images":{
"commit":{
"author":"admin","date":"2008-04-16T10:20:57.333174Z","revision":"1"},
"kind":"dir"},
"locked-file-sadfsdfafe.txt":{
"size":"56","commit":{
"author":"admin","date":"2008-04-16T10:20:57.333174Z","revision":"1"},
"lock":{
"token":"opaquelocktoken:64e85084-a145-3b46-8f73-b63b4c546b97","owner":"svensson","comment":"Testing lock features. You should not be allowed to modify this file.","created":"2008-04-16T10:21:15.369108Z"},
"kind":"file"},
"website":{
"commit":{
"author":"test","date":"2008-04-16T10:21:21.547993Z","revision":"3"},
"kind":"dir"},
"xmlfile.xml":{
"size":"12","commit":{
"author":"admin","date":"2008-04-16T10:20:57.333174Z","revision":"1"},
"kind":"file"}
}};
(function(jQ,url,list,set) {

	var s = jQ.extend({
		selector: "#reposlist"
	},set);

	var o = function(c,value) {
		return jQ("<span/>").addClass(c).append(value);
	};

	jQ().ready( function() {
		var p = jQ(s.selector);
		for (var f in list) {
			var d = list[f];
			var e = jQ("<li/>").addClass(d.kind).appendTo(p);
			jQ("<a/>").attr("href",url+"/"+f).append(f).appendTo(e);
			e.append(o("revision",d.commit.revision));
			if (d.commit.author) {
				e.append(o("username",d.commit.author));
				e.append(o("datetime",d.commit.date));
			} else {
				e.addClass("noaccess");
			}
			if (d.kind=="file") {
				e.append(o("filesize",d.size));
			}
			if (d.lock) {
				e.addClass("locked");
				var l = o("lock","").appendTo(e);
				l.append(o("username",d.lock.owner));
				l.append(o("datetime",d.lock.created));
				l.append(o("message",d.lock.comment));
			}
		}
	} );
})(jQuery, svn.path, svn.list, {'target':'/demoproject/trunk/public/','selector':'#root'});
