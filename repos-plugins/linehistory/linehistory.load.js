
// Depends on syntaxhighlighter plugin
Repos.service('open/file/', function() {
	var target = Repos.getTarget();
	// must use explicit revision number to get matching blame
	var rev = Repos.getRevision();
	var command = $('<a id="linehistory" href="#">show line history</a>').appendTo('#commandbar');
	command.click(function() {
		if (!command.is('.active')) {
			var element = $('.syntaxhighlighter:first .lines');
			// trust transparent @base support
			var blame = $.get(Repos.getWebapp() + 'open/blame/?target=' + encodeURI(target), // need API method for this too
				{}, function(xml) { Repos.linehistory.show(element, xml); }, 'xml');
			command.addClass('active').text('hide line history');			
		} else {
			$('.linehistory').remove();
			command.removeClass('active').text('show line history');			
		}
	});
});

Repos.linehistory = {
	show: function(highlighted, xml) {
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
			//var tag = '<span class="linehistory"/>'; // avoid span,div,and code to bypass the terribly intrusive SyntaxHighlighter css
			var tag = '<var class="linehistory"/>';
			// insert into SyntaxHighlighter output
			var after = $('.number', line);
			var css = {'float': null};
			$(tag).addClass('username').css(css).text(author).insertAfter(after);
			$(tag).addClass('revision').css(css).text(rev).insertAfter(after);
		});
	}
};

