// service Level Agreement
(function() {
	var repos_sla_url = Repos.getWebapp()+'docs/agreement/'; // deprecated location
	var addToFooter = function() {
		$('<span>').attr('id','repos-sla').html(', see <a id="agreement" href="'+repos_sla_url+'" target="_blank">terms and conditions</a>').appendTo('#footer .legal');
	};
	Repos.service('open/start/', addToFooter);
	Repos.service('home/', addToFooter);
	Repos.service('account/login/', function() {
		$('<a>').attr('id','repos-sla').addClass('command').attr('href',repos_sla_url).text('terms and conditions').appendTo('#commandbar');
	});
})/*()disabled*/;
