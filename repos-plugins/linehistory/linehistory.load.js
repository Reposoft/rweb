
// Depends on syntaxhighlighter plugin
Repos.service('open/file/', function() {
	var target = Repos.getTarget();
	// must use explicit revision number to get matching blame
	var rev = $('.revision:first').text(); // Repos API method needed
	var command = $('<a id="linehistory">show line history</a>').appendTo('#commandbar');
	command.toggle(function() {
			var element = $('.syntaxhighlighter:first .lines');
			// trust transparent @base support
			var blame = $.get(Repos.getWebapp() + 'open/blame/?target=' + encodeURI(target), // need API method for this too
				{}, function(xml) { Repos.linehistory.show(element, xml); }, 'xml');
			command.text('hide line history');
		},function() {
			$('.linehistory').remove();
			command.text('show line history');
		});

});

Repos.linehistory = {
	show: function(highlighted, xml) {
		console.log(highlighted, xml);i
		var list = highlighted;
		var lines = [];
		$('.line', list).each(function() {
			var n = $('.number', this).text().match(/\d+/)[0];
			n = new Number(n);
			lines[n] = this;
		});
		//console.log(lines);
		var highestrev = 0;
		$('entry',xml).each(function() {
			var n = $(this).attr('line-number');
			var line = lines[n];
			var rev = $('commit', this).attr('revision');
			if (rev > highestrev) highestrev = rev;
			var author = $('author', this).text();
			//console.log(n,line,rev,author);
			$('<span/>').addClass('username').text(author).prependTo(line);
			$('<span/>').addClass('revision').text(rev).prependTo(line);
		});	
	}
};

