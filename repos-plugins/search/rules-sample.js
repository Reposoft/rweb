// TODO change to 'repos-propsearch-init'
$().bind('repos-propedit-init', function(ev, rules) {

	rules.add('abx:TagName', ['', 'document', 'section']);


	// per-repository rules
	var repo = Repos.getBase();
	
	if (repo == 'repo1' || repo == 'repo2') {
		rules.add('abx:lang', [''].concat('sv | en | en_GB | en_US | es | de | fr | it | fi | no'.split(' | ')));
	}
	
	if (repo == 'demo1') {
		rules.add('abx:lang', [''].concat('sv-SE | en-GB | en-US | es-ES | de-DE | fr-FR | it-IT | fi-FI | no-NO'.split(' | ')));
	}

});