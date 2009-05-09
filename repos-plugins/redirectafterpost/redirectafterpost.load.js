// remove the Processing... output from the result page
Repos.service('result/', function() {
	$('.wait').hide();
	// meta refresh added by result page will not be effective,
	// so it has to be accompanied by an a-tag with redirect url as href
	var redirect = $('#meta-refresh-url').text();
	if (redirect) setTimeout('location.href = "'+redirect+'";', 2000);
});
