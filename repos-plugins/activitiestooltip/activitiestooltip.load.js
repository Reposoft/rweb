/* Make tooltip of the explanations for activities on details page (c) 2009 Staffan Olsson */
Repos.service('open/', function() {
 	// hide verbose info	
	$('#activities h3').next().hide();
 	// show recommendation below
 	//$('#activities .recommendation').appendTo($('#activities')).show();
	// show help as tooltip
 	$('#activities h3').tooltip({
 		delay: 500,
 		bodyHandler: function() {
 			$('#tooltip').css({
 				position: 'absolute',
 				zIndex: '3000',
 				border: '1px solid #111',
 				backgroundColor: '#eee',
 				padding: '5px',
 				opacity: '0.85'
 			});
 			return $(this).next().html();
 		}
 	});
});
 