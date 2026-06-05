jQuery(function($){

	$(document).on('click', '.wp_docs_verify_memphis', function(e){

		e.preventDefault();

		var $btn = $(this);

		if($btn.data('running')){
			return false;
		}

		$btn.data('running', true);

		$('#wpdocs_memphis_verify_results').html('');
		$('#wpdocs_memphis_verify_tree').html('');

		$('#wpdocs_memphis_verify_progress_wrap').show();

		$('#wpdocs_memphis_verify_progress_bar')
			.css('width', '0%')
			.text('0%');

		$('#wpdocs_memphis_verify_current')
			.html(wpdocs_vars.preparing_queue);

		if($.blockUI){
			$.blockUI({
				message: ''
			});
		}

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wp_docs_verify_memphis_docs_start',
				nonce: wpdocs_vars.nonce
			},
			success: function(response){

				if(!response.success){

					alert(
						response.data
						?
						response.data
						:
						wpdocs_vars.unable_to_start
					);

					$btn.data('running', false);

					if($.unblockUI){
						$.unblockUI();
					}

					return;
				}

				wpdocs_verify_batch();
			},
			error: function(){

				alert(wpdocs_vars.ajax_error);

				$btn.data('running', false);

				if($.unblockUI){
					$.unblockUI();
				}
			}
		});
	});


	function wpdocs_verify_batch(){

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wp_docs_verify_memphis_docs_batch',
				nonce: wpdocs_vars.nonce
			},
			success: function(response){

				if(!response.success){

					alert(
						response.data
						?
						response.data
						:
						wpdocs_vars.verification_failed
					);

					$('.wp_docs_verify_memphis')
						.data('running', false);

					if($.unblockUI){
						$.unblockUI();
					}

					return;
				}

				var data = response.data;

				update_progress(data);

				render_stats(data.stats);

				render_tree(data.tree);

				if(data.completed){

					$('.wp_docs_verify_memphis')
						.data('running', false);

					$('#wpdocs_memphis_verify_current')
						.html(
							'<strong>' + wpdocs_vars.verification_completed + '</strong>'
						);

					if($.unblockUI){
						$.unblockUI();
					}

					if(
						parseInt(data.stats.folders_missing)
						||
						parseInt(data.stats.files_missing)
					){

						if(
							$('#wpdocs_memphis_import_missing_wrap').length
						){
							$('#wpdocs_memphis_import_missing_wrap').show();
						}
					}

				}else{

					setTimeout(function(){

						wpdocs_verify_batch();

					}, 150);
				}
			},
			error: function(){

				$('.wp_docs_verify_memphis')
					.data('running', false);

				if($.unblockUI){
					$.unblockUI();
				}

				alert(wpdocs_vars.verification_interrupted);
			}
		});
	}


	function update_progress(data){

		var percent = parseFloat(data.percent);

		$('#wpdocs_memphis_verify_progress_bar')
			.css('width', percent + '%')
			.text(percent + '%');

		var current_text = '';

		if(
			data.current_item
			&&
			data.current_item.type
		){

			if(data.current_item.type === 'folder'){

				current_text =
					wpdocs_vars.checking_folder + ' <strong>'
					+
					data.current_item.data.name
					+
					'</strong>';

			}else{

				var file_name =
					data.current_item.data.name
					?
					data.current_item.data.name
					:
					(
						data.current_item.data.filename
						?
						data.current_item.data.filename
						:
						wpdocs_vars.unknown_file
					);

				current_text =
					wpdocs_vars.checking_file + ' <strong>'
					+
					file_name
					+
					'</strong>';
			}
		}

		current_text +=
			'<br><small>'
			+
			data.processed
			+
			' / '
			+
			data.total
			+
			' ' + wpdocs_vars.items_processed + '</small>';

		$('#wpdocs_memphis_verify_current')
			.html(current_text);
	}


	function render_stats(stats){

		var html = '';

		html += '<table class="widefat striped">';

		html += '<tbody>';


		html += '<tr>';
		html += '<th>' + wpdocs_vars.total_folders + '</th>';
		html += '<td>' + stats.folders_total + '<\/td>';
		html += '<\/tr>';

		html += '<tr>';
		html += '<th>' + wpdocs_vars.verified_folders + '</th>';
		html += '<td style="color:#198754;font-weight:bold;">'
			 + stats.folders_found +
			 '<\/td>';
		html += '<\/tr>';

		html += '<tr>';
		html += '<th>' + wpdocs_vars.missing_folders + '</th>';
		html += '<td style="color:#dc3545;font-weight:bold;">'
			 + stats.folders_missing +
			 '<\/td>';
		html += '<\/tr>';

		html += '<tr>';
		html += '<th>' + wpdocs_vars.total_files + '</th>';
		html += '<td>' + stats.files_total + '<\/td>';
		html += '<\/tr>';

		html += '<tr>';
		html += '<th>' + wpdocs_vars.verified_files + '</th>';
		html += '<td style="color:#198754;font-weight:bold;">'
			 + stats.files_found +
			 '<\/td>';
		html += '<\/tr>';

		html += '<tr>';
		html += '<th>' + wpdocs_vars.missing_files + '</th>';
		html += '<td style="color:#dc3545;font-weight:bold;">'
			 + stats.files_missing +
			 '<\/td>';
		html += '<\/tr>';

		html += '</tbody>';

		html += '<\/table>';

		$('#wpdocs_memphis_verify_results')
			.html(html);
	}


	function render_tree(tree){
		
		if(!tree || !tree.length){
			$('#wpdocs_memphis_verify_tree').html('<p>' + wpdocs_vars.no_items_display + '<\/p>');
			return;
		}
		
		var html = '';
		var last_path = '';
		var current_depth = 0;
		
		$.each(tree, function(index, item){
			
			var padding = parseInt(item.depth) * 20;
			
			if(item.type === 'folder' && item.path && item.path !== last_path){
				if(last_path !== ''){
					html += '<div style="margin-top:10px;"><\/div>';
				}
				last_path = item.path;
			}
			
			if(item.type === 'folder'){
				
				// Get the folder slug to find its WP Docs ID
				var folder_slug = item.slug;
				
				if(item.status === 'found'){
					// Create clickable folder link
					html += '<div style="padding-left:' + padding + 'px; margin-bottom:4px;">';
					html += '<a href="' + wpdocs_vars.url + '&dir=' + item.id + '" style="color:#198754; text-decoration:none;" title="' + item.name + '">';
					html += '&#128193; ' + (item.name);
					html += '<\/a>';
					html += '<\/div>';
				}else{
					html += '<div style="padding-left:' + padding + 'px; margin-bottom:4px; color:#dc3545; font-weight:bold;">';
					html += '&#128193; ' + escape_html(item.name);
					html += ' <span style="color:#dc3545;">(' + wpdocs_vars.missing_folder + ')<\/span>';
					html += '<\/div>';
				}
				
			}else{
				
				if(item.status === 'found'){
					html += '<div style="padding-left:' + padding + 'px; margin-bottom:2px; color:#198754;">';
					html += '&#128196; ' + escape_html(item.name);
					html += '<\/div>';
				}else{
					html += '<div style="padding-left:' + padding + 'px; margin-bottom:2px; color:#dc3545; font-weight:bold;">';
					html += '&#128196; ' + escape_html(item.name);
					html += ' <span style="color:#dc3545;">(' + wpdocs_vars.missing_file + ')<\/span>';
					html += '<\/div>';
				}
			}
		});
		
		$('#wpdocs_memphis_verify_tree').html(html);
	}


	function escape_html(text){

		if(typeof text === 'undefined'){
			return '';
		}

		return $('<div>')
			.text(text)
			.html();
	}
	
	
	var wpdocs_import_running = false;
	
	$(document).on('click', '.wp_docs_import_missing_memphis', function(e){
		
		e.preventDefault();
		
		if(wpdocs_import_running){
			alert(wpdocs_vars.import_in_progress);
			return false;
		}
		
		var $btn = $(this);
		var original_text = $btn.text();
		
		if(confirm(wpdocs_vars.import_missing_confirm)){
			
			wpdocs_import_running = true;
			
			$btn.data('running', true);
			$btn.html('<span class="spinner" style="float:none; margin:0 5px 0 0; visibility:visible;"></span> ' + wpdocs_vars.initializing).prop('disabled', true);
			
			$('#wpdocs_memphis_verify_results').html('');
			$('#wpdocs_memphis_verify_tree').html('');
			$('#wpdocs_memphis_verify_progress_wrap').show();
			
			$('#wpdocs_memphis_verify_progress_bar')
				.css('width', '0%')
				.text('0%');
			
			$('#wpdocs_memphis_verify_current')
				.html('<strong>' + wpdocs_vars.preparing_import_queue + '<\/strong>');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wp_docs_import_missing_memphis_start',
					nonce: wpdocs_vars.nonce
				},
				success: function(response){
					
					if(!response.success){
						alert(response.data || wpdocs_vars.unable_to_start_import);
						wpdocs_import_running = false;
						$btn.html(original_text).prop('disabled', false);
						if($.unblockUI) $.unblockUI();
						return;
					}
					
					wpdocs_import_batch();
				},
				error: function(){
					alert(wpdocs_vars.ajax_error);
					wpdocs_import_running = false;
					$btn.html(original_text).prop('disabled', false);
					if($.unblockUI) $.unblockUI();
				}
			});
		}
	});
	
	function wpdocs_import_batch(){
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wp_docs_import_missing_memphis_batch',
				nonce: wpdocs_vars.nonce
			},
			success: function(response){
				
				if(!response.success){
					alert(response.data || wpdocs_vars.import_failed);
					wpdocs_import_running = false;
					$('.wp_docs_import_missing_memphis').html(wpdocs_vars.import_missing_items).prop('disabled', false);
					if($.unblockUI) $.unblockUI();
					return;
				}
				
				var data = response.data;
				
				wpdocs_update_import_progress(data);
				
				if(data.completed){
					
					wpdocs_import_running = false;
					
					$('#wpdocs_memphis_verify_current')
						.html('<strong style="color:#198754;">' + wpdocs_vars.import_completed + '<\/strong><br><small>' + wpdocs_vars.refreshing_results + '<\/small>');
					
					$('.wp_docs_import_missing_memphis').html(wpdocs_vars.import_missing_items).prop('disabled', false);
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'wp_docs_import_missing_memphis_clear',
							nonce: wpdocs_vars.nonce
						},
						complete: function(){
							setTimeout(function(){
								$('.wp_docs_verify_memphis').click();
							}, 1500);
						}
					});
					
					if($.unblockUI) $.unblockUI();
					
				}else{
					setTimeout(function(){
						wpdocs_import_batch();
					}, 200);
				}
			},
			error: function(){
				alert(wpdocs_vars.import_interrupted);
				wpdocs_import_running = false;
				$('.wp_docs_import_missing_memphis').html(wpdocs_vars.import_missing_items).prop('disabled', false);
				if($.unblockUI) $.unblockUI();
			}
		});
	}
	
	function wpdocs_update_import_progress(data){
		
		var percent = parseFloat(data.percent);
		
		$('#wpdocs_memphis_verify_progress_bar')
			.css('width', percent + '%')
			.text(percent + '%');
		
		var current_text = '';
		
		if(data.current_item && data.current_item.type){
			if(data.current_item.type === 'folder'){
				current_text = wpdocs_vars.creating_folder + ' <strong>' + escape_html(data.current_item.display_name) + '<\/strong>';
				if(data.current_item.path){
					current_text += '<br><small>' + wpdocs_vars.path + ' ' + escape_html(data.current_item.path) + '<\/small>';
				}
			}else{
				current_text = wpdocs_vars.importing_file + ' <strong>' + escape_html(data.current_item.display_name) + '<\/strong>';
				if(data.current_item.path){
					current_text += '<br><small>' + wpdocs_vars.target + ' ' + escape_html(data.current_item.path) + '<\/small>';
				}
			}
		}
		
		current_text += '<br><small>' + data.processed + ' / ' + data.total + ' ' + wpdocs_vars.items_processed + '<\/small>';
		
		if(data.stats){
			current_text += '<br><small style="color:#198754;">' + wpdocs_vars.folders_imported + ' ' + data.stats.folders_imported + ' | ' + wpdocs_vars.files_imported + ' ' + data.stats.files_imported + '<\/small>';
			if(data.stats.folders_failed > 0 || data.stats.files_failed > 0){
				current_text += '<br><small style="color:#dc3545;">' + wpdocs_vars.failed + ' ' + (data.stats.folders_failed + data.stats.files_failed) + '<\/small>';
			}
		}
		
		$('#wpdocs_memphis_verify_current').html(current_text);
	}
	
});