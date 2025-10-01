(function( $ ) {
	'use strict';

	$(function($){
		var $quickEditThumbnail = $('#aplb-quickedit-thumbnail');
		if (!$quickEditThumbnail.length) return;

		// When Quick Edit is opened
		$(document).on('click', '.editinline', function(){
			var postId = $(this).closest('tr').attr('id').replace('post-', '');
			var thumbHtml = $('#post-' + postId + ' .column-thumbnail').html();
			$('#aplb-quickedit-thumbnail').html(thumbHtml);
		});
	});
	
})( jQuery );
