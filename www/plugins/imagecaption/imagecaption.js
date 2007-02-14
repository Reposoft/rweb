
$(document).ready( function() {
	imagecaptionAdd();
} );

function imagecaptionAdd() {
	$('img').each( function() {
		var title = this.getAttribute('title');
		if (title) imagecaptionCreate(this, title);
	} );
};

function imagecaptionCreate(imgElement, caption) {
	 $(imgElement)
	 	.wrap('<dt></dt>')
	 	.parent().wrap('<dl class="image"></dl>')
	 	.append('<dd>'+caption+'</dd>');
};
