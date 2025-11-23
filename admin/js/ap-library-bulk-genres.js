(function($){
	'use strict';
	if ( typeof APLB_BulkGenres === 'undefined' ) { return; }

	// New inline container id
	const $toolbar = $('#aplb-inline-bulk-genres');
	if ( !$toolbar.length ) { return; }
	const $select    = $('#aplb-bulk-genre-select');
	const $applyBtn  = $('#aplb-bulk-genre-apply');
	const $replaceCb = $('#aplb-bulk-genre-replace');
	const $spinner   = $toolbar.find('.spinner');
	const $status    = $toolbar.find('.aplb-bulk-genre-status');

	function getSelectedPostIds(){
		return $('#the-list input[type="checkbox"][name="post[]"]:checked').map(function(){ return parseInt(this.value,10); }).get();
	}
	function getSelectedTermIds(){
		return $select.find('option:selected').map(function(){ return parseInt(this.value,10); }).get();
	}
	function getSelectedTermNames(){
		return $select.find('option:selected').map(function(){ return $(this).text(); }).get();
	}
	function updateButtonState(){
		const posts = getSelectedPostIds();
		const terms = getSelectedTermIds();
		$applyBtn.prop('disabled', !(posts.length && terms.length));
	}

	$(document).on('change', '#the-list input[type="checkbox"][name="post[]"], #aplb-bulk-genre-select, #aplb-bulk-genre-replace', updateButtonState);

	// Ensure container appears after the Filter button for desired ordering.
	$(function(){
		const $filterBtn = $('#post-query-submit');
		if ($filterBtn.length && $toolbar.length) {
			$filterBtn.after($toolbar);
		}
	});

	$applyBtn.on('click', function(){
		const postIds   = getSelectedPostIds();
		const termIds   = getSelectedTermIds();
		const termNames = getSelectedTermNames();
		const mode      = $replaceCb.is(':checked') ? 'replace' : 'add';
		if ( !postIds.length || !termIds.length ) { return; }
		// Confirmation when in replace mode
		if ( mode === 'replace' ) {
			const confirmMsg = 'You are about to REPLACE existing genres on ' + postIds.length + ' photo(s) with ' + termNames.length + ' selected genre(s). This will discard any current genres not selected. Continue?';
			if ( ! window.confirm(confirmMsg) ) {
				return;
			}
		}
		$spinner.css('visibility','visible');
		$applyBtn.prop('disabled', true);
		$status.text('');

		wp.apiFetch({
			path: APLB_BulkGenres.restUrl.replace(/^.*\/wp-json\//,'') ,
			method: 'POST',
			headers: { 'X-WP-Nonce': APLB_BulkGenres.nonce },
			data: { postIds: postIds, termIds: termIds, mode: mode }
		}).then(function(resp){
			if ( resp && resp.success ) {
				resp.updated.forEach(function(pid){
					const $row = $('#post-' + pid);
					const $taxCell = $row.find('td.taxonomy-aplb_genre');
					if ($taxCell.length) {
						$taxCell.html( termNames.map(function(n){ return '<span class="aplb-term-badge" style="display:inline-block;margin:2px 4px 2px 0;padding:2px 6px;background:#f0f0f1;border-radius:3px;font-size:11px;">'+ _.escape(n) +'</span>'; }).join(' ') );
					}
				});
				$status.text(APLB_BulkGenres.successMessage).css('color','#2271b1');
			} else {
				$status.text(APLB_BulkGenres.errorMessage).css('color','#d63638');
			}
		}).catch(function(){
			$status.text(APLB_BulkGenres.errorMessage).css('color','#d63638');
		}).finally(function(){
			$spinner.css('visibility','hidden');
			updateButtonState();
		});
	});

	updateButtonState();

})(jQuery);
