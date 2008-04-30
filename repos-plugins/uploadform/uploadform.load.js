
(function() {

var isnew = true;

Repos.service('edit/upload/', function() {
	isnew = $('#name').val().length == 0;
	$('#userfile').change(function() {
		autoFillFilename($(this).val());	
	});
});
	
function autoFillFilename(path) {
	$().say();
	var pos = Math.max(path.lastIndexOf('/'), path.lastIndexOf('\\'));
	if (0 >= pos) return;
	var filename = path.substring(pos + 1);
	
	if (isnew) {
		$('#name').val(filename);
		return;
	}
	
	if (filename == $('#name').val()) return;
	var m = filename.match(/\(r(\d+)\)\.?\w*$/);
	if (!m || 2 > m.length) return; 
	var rev = m[1];
	var button = document.getElementById('fromrev'+rev);
	if (!button) {
		$('body').say({
			title:'autodetect file revision',
			level:'warning',
			text:'From the filename it looks like changes are based on revision '+rev+'. But there is no such revision. Please check that it is the correct file.'
			});
	} else {
		button.checked = "checked";
		$('body').say({
			title:'autodetect file revision',
			text:'Automatically selected &quot;based on version&quot; '+rev+', because your filename ends with (r'+rev+').'
			+ ' This is now a true versioning operation, as it will check for conflicts with any changes made by others since you downloaded the file.'
			});
	}
}

})();
