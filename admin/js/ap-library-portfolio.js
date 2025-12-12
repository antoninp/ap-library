/**
 * Portfolio Term Meta - Featured Image Upload
 *
 * @since      1.3.1
 * @package    Ap_Library
 * @subpackage Ap_Library/admin/js
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var fileFrame;

		// Upload button click
		$(document).on('click', '.aplb-upload-image-button', function(e) {
			e.preventDefault();

			var $button = $(this);
			var $wrapper = $button.closest('.aplb-portfolio-image-wrapper');
			var $input = $wrapper.find('#aplb_portfolio_featured_image');
			var $preview = $wrapper.find('.aplb-portfolio-image-preview');

			// If the media frame already exists, reopen it.
			if (fileFrame) {
				fileFrame.open();
				return;
			}

			// Create the media frame.
			fileFrame = wp.media({
				title: 'Select Portfolio Cover Image',
				button: {
					text: 'Use this image'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			// When an image is selected, run a callback.
			fileFrame.on('select', function() {
				var attachment = fileFrame.state().get('selection').first().toJSON();
				
				$input.val(attachment.id);
				
				if (attachment.sizes && attachment.sizes.medium) {
					$preview.html('<img src="' + attachment.sizes.medium.url + '" alt="" style="max-width: 100%; height: auto;" />');
				} else {
					$preview.html('<img src="' + attachment.url + '" alt="" style="max-width: 300px; height: auto;" />');
				}
				
				$button.text('Change Cover Image');
				
				// Add remove button if it doesn't exist
				if (!$wrapper.find('.aplb-remove-image-button').length) {
					$button.after(' <button type="button" class="button aplb-remove-image-button">Remove Cover Image</button>');
				}
			});

			// Finally, open the modal
			fileFrame.open();
		});

		// Remove button click
		$(document).on('click', '.aplb-remove-image-button', function(e) {
			e.preventDefault();

			var $button = $(this);
			var $wrapper = $button.closest('.aplb-portfolio-image-wrapper');
			var $input = $wrapper.find('#aplb_portfolio_featured_image');
			var $preview = $wrapper.find('.aplb-portfolio-image-preview');
			var $uploadButton = $wrapper.find('.aplb-upload-image-button');

			$input.val('');
			$preview.html('');
			$uploadButton.text('Set Cover Image');
			$button.remove();
		});
	});

})(jQuery);
