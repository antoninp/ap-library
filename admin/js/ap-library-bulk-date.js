/**
 * Bulk Date Editor
 * Allows updating specific date components (year, month, day, hour, minute) for multiple photos at once
 * Supports post_date, published_date, and taken_date
 */
(function($){
	'use strict';
	
	const $toolbar = $('#aplb-inline-bulk-date');
	if (!$toolbar.length) return;
	
	const $dateTypeRadios = $('input[name="aplb-bulk-date-type"]');
	
	const $yearCb = $('#aplb-bulk-date-year-enable');
	const $monthCb = $('#aplb-bulk-date-month-enable');
	const $dayCb = $('#aplb-bulk-date-day-enable');
	const $hourCb = $('#aplb-bulk-date-hour-enable');
	const $minuteCb = $('#aplb-bulk-date-minute-enable');
	
	const $yearSelect = $('#aplb-bulk-date-year');
	const $monthSelect = $('#aplb-bulk-date-month');
	const $daySelect = $('#aplb-bulk-date-day');
	const $hourSelect = $('#aplb-bulk-date-hour');
	const $minuteSelect = $('#aplb-bulk-date-minute');
	
	const $applyBtn = $('#aplb-bulk-date-apply');
	const $spinner = $toolbar.find('.spinner');
	const $status = $('.aplb-bulk-date-status');
	
	/**
	 * Get the selected date type
	 */
	function getDateType() {
		return $dateTypeRadios.filter(':checked').val() || 'post_date';
	}
	
	/**
	 * Get the label for the selected date type
	 */
	function getDateTypeLabel() {
		const dateType = getDateType();
		if (dateType === 'post_date') {
			return window.APLB_BulkDate.postDateLabel;
		} else if (dateType === 'published_date') {
			return window.APLB_BulkDate.publishedDateLabel;
		} else {
			return window.APLB_BulkDate.takenDateLabel;
		}
	}
	
	/**
	 * Update visibility of hour/minute controls based on date type
	 */
	function updateTimeControlsVisibility() {
		const dateType = getDateType();
		const showTime = (dateType === 'post_date');
		
		// Show/hide hour and minute controls
		$hourCb.closest('div').toggle(showTime);
		$minuteCb.closest('div').toggle(showTime);
		
		// If hiding time controls, uncheck them
		if (!showTime) {
			$hourCb.prop('checked', false).trigger('change');
			$minuteCb.prop('checked', false).trigger('change');
		}
	}
	
	/**
	 * Get IDs of selected photos
	 */
	function getSelectedPostIds() {
		return $('#the-list input[type="checkbox"][name="post[]"]:checked')
			.map(function(){ return parseInt(this.value, 10); })
			.get();
	}
	
	/**
	 * Check if at least one date component is enabled
	 */
	function hasEnabledComponents() {
		return $yearCb.is(':checked') || 
		       $monthCb.is(':checked') || 
		       $dayCb.is(':checked') || 
		       $hourCb.is(':checked') || 
		       $minuteCb.is(':checked');
	}
	
	/**
	 * Update apply button state based on selections
	 */
	function updateButtonState() {
		const posts = getSelectedPostIds();
		const hasComponents = hasEnabledComponents();
		$applyBtn.prop('disabled', !(posts.length && hasComponents));
	}
	
	/**
	 * Toggle select dropdown enabled state based on checkbox
	 */
	function toggleSelectState($checkbox, $select) {
		$select.prop('disabled', !$checkbox.is(':checked'));
	}
	
	/**
	 * Clear photo selection
	 */
	function clearPhotoSelection() {
		$('#the-list input[type="checkbox"][name="post[]"]').prop('checked', false);
		$('#cb-select-all-1, #cb-select-all-2').prop('checked', false);
	}
	
	/**
	 * Update table row date cell (WordPress post date column)
	 */
	function updateTableCell(postId, newDate) {
		const $row = $('#post-' + postId);
		const $dateCell = $row.find('td.column-date, td.date');
		if ($dateCell.length && newDate) {
			// Format date for display (WordPress usually shows relative time)
			const dateObj = new Date(newDate);
			const formattedDate = dateObj.toLocaleDateString('en-US', {
				year: 'numeric',
				month: 'short',
				day: 'numeric'
			});
			$dateCell.find('abbr, time').attr('title', newDate).text(formattedDate);
		}
	}
	
	/**
	 * Build date components object from enabled checkboxes
	 */
	function getDateComponents() {
		const components = {};
		
		if ($yearCb.is(':checked')) {
			components.year = parseInt($yearSelect.val(), 10);
		}
		if ($monthCb.is(':checked')) {
			components.month = parseInt($monthSelect.val(), 10);
		}
		if ($dayCb.is(':checked')) {
			components.day = parseInt($daySelect.val(), 10);
		}
		if ($hourCb.is(':checked')) {
			components.hour = parseInt($hourSelect.val(), 10);
		}
		if ($minuteCb.is(':checked')) {
			components.minute = parseInt($minuteSelect.val(), 10);
		}
		
		return components;
	}
	
	// Event listeners for checkboxes to enable/disable selects
	$yearCb.on('change', function() {
		toggleSelectState($yearCb, $yearSelect);
		updateButtonState();
	});
	$monthCb.on('change', function() {
		toggleSelectState($monthCb, $monthSelect);
		updateButtonState();
	});
	$dayCb.on('change', function() {
		toggleSelectState($dayCb, $daySelect);
		updateButtonState();
	});
	$hourCb.on('change', function() {
		toggleSelectState($hourCb, $hourSelect);
		updateButtonState();
	});
	$minuteCb.on('change', function() {
		toggleSelectState($minuteCb, $minuteSelect);
		updateButtonState();
	});
	
	// Event listener for date type radio buttons
	$dateTypeRadios.on('change', function() {
		updateTimeControlsVisibility();
		updateButtonState();
		$status.text('');
	});
	
	// Update button state when photo selection changes
	$(document).on('change', '#the-list input[type="checkbox"][name="post[]"]', updateButtonState);
	
	// Position toolbar after Filter button
	$(function(){
		const $filterBtn = $('#post-query-submit');
		if ($filterBtn.length && $toolbar.length) {
			$filterBtn.after($toolbar);
		}
	});
	
	// Apply button click handler
	$applyBtn.on('click', function(){
		const postIds = getSelectedPostIds();
		const components = getDateComponents();
		const dateType = getDateType();
		const dateTypeLabel = getDateTypeLabel();
		
		if (!postIds.length || !Object.keys(components).length) return;
		
		// Build confirmation message
		const componentNames = [];
		if (components.year) componentNames.push('year');
		if (components.month) componentNames.push('month');
		if (components.day) componentNames.push('day');
		if (components.hour) componentNames.push('hour');
		if (components.minute) componentNames.push('minute');
		
		const confirmMsg = window.APLB_BulkDate.confirmMsg
			.replace('%s', dateTypeLabel)
			.replace('%d', postIds.length)
			.replace('%s', componentNames.join(', '));
		
		if (!window.confirm(confirmMsg)) return;
		
		$spinner.css('visibility', 'visible');
		$applyBtn.prop('disabled', true);
		$status.text('');
		
		wp.apiFetch({
			path: '/ap-library/v1/bulk-update-date',
			method: 'POST',
			data: { 
				postIds: postIds, 
				components: components,
				dateType: dateType
			}
		})
		.then(function(resp){
			if (resp && resp.success) {
				// Update table cells for updated posts
				resp.updated.forEach(function(item){
					updateTableCell(item.postId, item.newDate);
				});
				
				clearPhotoSelection();
				
				// Reset checkboxes
				$yearCb.prop('checked', false).trigger('change');
				$monthCb.prop('checked', false).trigger('change');
				$dayCb.prop('checked', false).trigger('change');
				$hourCb.prop('checked', false).trigger('change');
				$minuteCb.prop('checked', false).trigger('change');
				
				$status.text(window.APLB_BulkDate.successMsg + ' (' + resp.updated.length + ')').css('color', '#2271b1');
			} else {
				const errorMsg = (resp && resp.message) ? resp.message : window.APLB_BulkDate.errorMsg;
				$status.text(errorMsg).css('color', '#d63638');
			}
		})
		.catch(function(error){
			const errorMsg = (error && error.message) ? error.message : window.APLB_BulkDate.errorMsg;
			$status.text(errorMsg).css('color', '#d63638');
		})
		.finally(function(){
			$spinner.css('visibility', 'hidden');
			updateButtonState();
		});
	});
	
	// Initialize
	updateButtonState();
	updateTimeControlsVisibility();
	toggleSelectState($yearCb, $yearSelect);
	toggleSelectState($monthCb, $monthSelect);
	toggleSelectState($dayCb, $daySelect);
	toggleSelectState($hourCb, $hourSelect);
	toggleSelectState($minuteCb, $minuteSelect);
	
})(jQuery);
