/**
 * Unified Bulk Taxonomy Assignment Handler
 * Works for both genres and portfolios with a reusable module pattern
 */
(function($){
	'use strict';
	
	/**
	 * Create a bulk assignment handler for a specific taxonomy
	 */
	function createBulkAssignmentHandler(config) {
		const $toolbar = $(config.toolbarId);
		if (!$toolbar.length) return;
		
		const $select = $(config.selectId);
		const $applyBtn = $(config.applyBtnId);
		const $replaceCb = $(config.replaceCbId);
		const $spinner = $toolbar.find('.spinner');
		const $status = $toolbar.find(config.statusClass);
		
		function getSelectedPostIds() {
			return $('#the-list input[type="checkbox"][name="post[]"]:checked')
				.map(function(){ return parseInt(this.value, 10); })
				.get();
		}
		
		function getSelectedTermIds() {
			return $select.find('option:selected')
				.map(function(){ return parseInt(this.value, 10); })
				.get();
		}
		
		function getSelectedTermNames() {
			return $select.find('option:selected')
				.map(function(){ return $(this).text(); })
				.get();
		}
		
		function updateButtonState() {
			const posts = getSelectedPostIds();
			const terms = getSelectedTermIds();
			$applyBtn.prop('disabled', !(posts.length && terms.length));
		}
		
		function clearPhotoSelection() {
			$('#the-list input[type="checkbox"][name="post[]"]').prop('checked', false);
			$('#cb-select-all-1, #cb-select-all-2').prop('checked', false);
		}
		
		function updateTableCell(postId, termNames, mode) {
			const $row = $('#post-' + postId);
			const $taxCell = $row.find('td.taxonomy-' + config.taxonomy);
			if (!$taxCell.length) return;
			
			if (mode === 'replace') {
				// Replace: show only the new terms
				$taxCell.html(termNames.map(function(n){ 
					return '<span class="aplb-term-badge" style="display:inline-block;margin:2px 4px 2px 0;padding:2px 6px;background:#f0f0f1;border-radius:3px;font-size:11px;">' + 
						_.escape(n) + '</span>'; 
				}).join(' '));
			} else {
				// Add: merge with existing terms (simpler approach: just show new terms added)
				const existingHtml = $taxCell.html().trim();
				const newBadges = termNames.map(function(n){ 
					return '<span class="aplb-term-badge" style="display:inline-block;margin:2px 4px 2px 0;padding:2px 6px;background:#f0f0f1;border-radius:3px;font-size:11px;">' + 
						_.escape(n) + '</span>'; 
				}).join(' ');
				
				if (existingHtml && existingHtml !== '—') {
					$taxCell.html(existingHtml + ' ' + newBadges);
				} else {
					$taxCell.html(newBadges);
				}
			}
		}
		
		// Event listeners
		$(document).on('change', 
			'#the-list input[type="checkbox"][name="post[]"], ' + config.selectId + ', ' + config.replaceCbId, 
			updateButtonState
		);
		
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
			const termIds = getSelectedTermIds();
			const termNames = getSelectedTermNames();
			const mode = $replaceCb.is(':checked') ? 'replace' : 'add';
			
			if (!postIds.length || !termIds.length) return;
			
			// Confirmation for replace mode
			if (mode === 'replace') {
				const confirmMsg = config.replaceConfirmMsg
					.replace('%d', postIds.length)
					.replace('%s', termNames.length)
					.replace('%taxonomy%', config.taxonomyLabel);
				if (!window.confirm(confirmMsg)) return;
			}
			
			$spinner.css('visibility', 'visible');
			$applyBtn.prop('disabled', true);
			$status.text('');
			
			wp.apiFetch({
				path: config.restPath,
				method: 'POST',
				data: { postIds: postIds, termIds: termIds, mode: mode }
			})
			.then(function(resp){
				if (resp && resp.success) {
					resp.updated.forEach(function(pid){
						updateTableCell(pid, termNames, mode);
					});
					clearPhotoSelection();
					$replaceCb.prop('checked', false);
					$status.text(config.successMsg + ' (' + resp.updated.length + ')').css('color', '#2271b1');
				} else {
					$status.text(config.errorMsg).css('color', '#d63638');
				}
			})
			.catch(function(){
				$status.text(config.errorMsg).css('color', '#d63638');
			})
			.finally(function(){
				$spinner.css('visibility', 'hidden');
				updateButtonState();
			});
		});
		
		updateButtonState();
	}
	
	// Initialize Portfolio bulk assignment (insert first so Genre ends up left)
	if (typeof APLB_BulkPortfolios !== 'undefined') {
		createBulkAssignmentHandler({
			toolbarId: '#aplb-inline-bulk-portfolios',
			selectId: '#aplb-bulk-portfolio-select',
			applyBtnId: '#aplb-bulk-portfolio-apply',
			replaceCbId: '#aplb-bulk-portfolio-replace',
			statusClass: '.aplb-bulk-portfolio-status',
			taxonomy: 'aplb_portfolio',
			taxonomyLabel: 'portfolios',
			restPath: '/ap-library/v1/assign-portfolios',
			successMsg: APLB_BulkPortfolios.successMessage || 'Portfolios updated',
			errorMsg: APLB_BulkPortfolios.errorMessage || 'Error applying portfolios',
			replaceConfirmMsg: 'You are about to REPLACE existing %taxonomy% on %d photo(s) with %s selected %taxonomy%. This will discard any current %taxonomy% not selected. Continue?'
		});
	}

	// Initialize Genre bulk assignment (initialized last so it sits closest to Filter)
	if (typeof APLB_BulkGenres !== 'undefined') {
		createBulkAssignmentHandler({
			toolbarId: '#aplb-inline-bulk-genres',
			selectId: '#aplb-bulk-genre-select',
			applyBtnId: '#aplb-bulk-genre-apply',
			replaceCbId: '#aplb-bulk-genre-replace',
			statusClass: '.aplb-bulk-genre-status',
			taxonomy: 'aplb_genre',
			taxonomyLabel: 'genres',
			restPath: APLB_BulkGenres.restUrl.replace(/^.*\/wp-json\//, ''),
			successMsg: APLB_BulkGenres.successMessage,
			errorMsg: APLB_BulkGenres.errorMessage,
			replaceConfirmMsg: 'You are about to REPLACE existing %taxonomy% on %d photo(s) with %s selected %taxonomy%. This will discard any current %taxonomy% not selected. Continue?'
		});
	}

})(jQuery);
