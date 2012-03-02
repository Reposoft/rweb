// Requires tree plugin

/**
 * Form integration.
 * @param {jQuery} input The form field to write selected path to
 */
var reposTreeToFormInput = function(treeContainer, input) {
	treeContainer.reposTree({
		startpath: $(input).val(),
		callbackSelect: function(link, name, target, base, isFile) {
			input.val(target).select();
			return false;
		},
		callbackLoad: function(link, name, target, base, isFile) {
			$(link).attr('title', 'Click to set destination folder, click arrow to collapse/expand');
		}
	});
};

Repos.service('edit/copy/', function() {
	var section = $('<div id="tofolder-select"/>').addClass('section').appendTo('.column:last');
	section.append('<h2>Change destination folder</h2>');
	var tree = $('<ul id="tofolder-tree"/>').appendTo(section);
	reposTreeToFormInput(tree, $('#tofolder'));
});
