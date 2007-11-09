
Repos.service('edit/upload/', function() {
	var p = '/repos-plugins/jquery.jqUploader/';
	$.getScript(p+'jquery.flash.js', function(){
		$.getScript(p+'jquery.jqUploader.js', function(){
			$("#userfile").jqUploader({ 
				src: p + 'jqUploader.swf',
				background: "FFFFFF",
				barColor: "64A9F6"
			});
		});
	});
});
