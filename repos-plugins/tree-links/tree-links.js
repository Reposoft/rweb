// link to tree, transitional plugin until the new tree component has been properly integrated
(function() {

var url = '/repos-tree/';
var icon = '/repos-web/style/commands/24x24/tree.png';

function cmd() {
	var a = $('<a id="treeview"/>').addClass('command').html('Tree View').attr('href',url);
	a.css('background-image', 'url("' + icon + '")');
	return a;
}

Repos.service('home/', function() {
	cmd().appendTo('#commandbar');	
});

})();
