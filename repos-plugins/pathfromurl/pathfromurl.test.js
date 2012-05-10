
window.repos_pathfromurl = {
		currentRepository: 'http://example.net/repo'
};

$(document).ready(function(){

test("path", function() {
	equal(repos_pathfromurl.getPathFromUrl('/a/path'), '/a/path');
	equal(repos_pathfromurl.getPathFromUrl('http://example.net/repo/my/file.txt'), '/my/file.txt');
	equal(repos_pathfromurl.getPathFromUrl('https://example.net/repo/my/file.txt'), '/my/file.txt', 'treat https the same');
});

test("path looking like url", function() {
	equal(repos_pathfromurl.getPathFromUrl('ahttp:///a/path'), 'ahttp:///a/path', "don't touch unknown protocol");
});

test('Form field', function() {
	// not sure about timing here but this must happen after all other $(document).ready
	$('#testfield').trigger('change');
	equal($('#testfield').val(), '/path/ok', 'Should activate for form fields with the right class');
});

});


