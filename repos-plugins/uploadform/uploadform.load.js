
(function() {

var v; // true for upload new version, false for add file

Repos.service('edit/upload/', function() {
	v = $('#name').is(':disabled');
	// copy filename from selected local file
	$('#userfile').change(function() {
		autoFillFilename($(this).val());	
	});
	// enable simplified form
	if (v) hideBased();
	// enable progress screen (not bar) at upload
	enableSubmitOverlay();
});
	
function autoFillFilename(path) {
	$().say();
	var pos = Math.max(path.lastIndexOf('/'), path.lastIndexOf('\\'));
	var filename = path.substring(pos + 1);
	
	if (!v) {
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
		enableBased();
		$(button).attr('checked','checked').trigger('change');
		$('body').say({
			title:'autodetect file revision',
			text:'Automatically selected &quot;based on version&quot; '+rev+', because your filename ends with (r'+rev+').'
			+ ' This is now a true versioning operation, as it will check for conflicts with any changes made by others since you downloaded the file.'
			});
	}
}

function enableSubmitOverlay() {
	$('form').submit(function(ev) {
		$('body > *').hide();
		var o = $('<div id="overlay"/>').appendTo('body');
		var w = $('<span class="wait">Uploading file...</span>').appendTo(o);
		window.setInterval(function() {
			w.append('.');
		}, 1000);
		return true;
	});
}

function hideBased() {
	var org = $('#based-on-revision');

	var check = $('<input name="tempcheckbox" type="checkbox"/>');
	var simple = $('<p/>').append('<label>&nbsp;</label>').append(check).append('<span> check for conflicts based on when the file was downloaded</span>');
	simple.attr('title','leave unchecked to simply overwrite the latest version');
	simple.insertBefore(org.hide());
	simple.css('margin-bottom','.2em');
		
	var val = $("input[name='fromrev']:checked", org).val();
	if (val && val != 'HEAD') enableBased();
	// make sure something is always selected, even after back button click (sometimes in firefox fromrev has no value)
	if (!val) $('#fromrevHEAD').attr('checked','checked');

	check.change(function() {
		if ($(this).is(':checked')) {
			enableBased();
		} else {
			org.hide();
		}
	});
	
	// IE6 fails to trigger onchange for checkboxes and radio buttons
	if ($.browser.sucks) check.click(function() {
		$(this).trigger('change');
	});
}

function enableBased() {
	$('#based-on-revision').show();
	
	$("input[name='fromrev']").change(function(){
		var c = $(this);
		if (c.is(':checked')) {
			if (c.val()=='HEAD') {
				$("input[name='tempcheckbox']").removeAttr('disabled');
			} else {
				$("input[name='tempcheckbox']").attr('disabled','disabled');
			}
		}
	}).click(function() {
		if ($.browser.sucks) $(this).trigger('change');
	});

	// as long as there is a checkbox for this we must make sure it is checked if enabled programmatically
	$("input[name='tempcheckbox']").attr('checked','checked');
}

})();
