// JavaScript Document
var wpdocs_move_copy = 'move';
jQuery(document).ready(function($){
	
	var files_multi_select = false;
	
	$('.new-folder').on('click', function(){
		var data = {
			'action': 'wpdocs_create_folder',
			'parent_dir': $(this).data('id'),
			'nonce': wpdocs_ajax_object.nonce
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		$('.ab-new').removeClass('ab-new');
		$.post(wpdocs_ajax_object.ajax_url, data, function(response) {
			
			$('div.wpdocs_list > ul').prepend(response);
			
			$('div.wpdocs_list ul li.ab-dir a.wpd-edit').eq(0).click();
		});		
	});	

	$('body').on('click', '.wpdocs-reset', function(){
		var reset_confirmation = confirm(wpdocs_ajax_object.reset_confirm);
		
		if(!reset_confirmation){
			return false;
		}
	});
	
	$('body').on('click', '.wpdocs_list ul li .file', function(event){
		
		
		if(files_multi_select){
			event.preventDefault();
			$(this).parent().toggleClass('selected-item');
		}else{
			return true;
		}
	});
	
	$('body').on('click', '.wpdocs_toolbar .wp-docs-multi-select', function(event){
		
		if(!wpdocs_ajax_object.wpdocs_pro){
			alert(wpdocs_ajax_object.premium_feature);
			return;
		}
	
		$(this).toggleClass('selection-enabled');
		files_multi_select = $(this).hasClass('selection-enabled');
		
		if(!files_multi_select){
			$('.wpdocs_list ul li.selected-item').removeClass('selected-item');
		}
	});
	
	
	$('body').on('click', '.wpdocs_list ul li.ab-dir > a.dtitle', function () {
		const obj = $(this);
		const id = obj.parent().data('id');
		const resource_id = obj.parent().data('resource');
	
		// Get plain folder name from HTML
		let html_str = $.parseHTML(obj.html());
		let folder_name = '';
	
		$.each(html_str, function (i, el) {
			if (el.wholeText) {
				folder_name = el.wholeText;
			}
		});
	
		let rename_to = prompt(wpdocs_ajax_object.rename_confirm, folder_name);
	
		// Cancelled or empty input
		if (!rename_to || $.trim(rename_to) === '') {
			return;
		}
	
		// Strip HTML tags
		rename_to = rename_to.replace(/(<([^>]+)>)/gi, "");
	
		const data = {
			action: 'wpdocs_update_folder',
			dir_id: id,
			resource_id: resource_id,
			new_name: rename_to,
			nonce: wpdocs_ajax_object.nonce
		};
	
		$.post(wpdocs_ajax_object.ajax_url, data, function (response) {
			if (response.success && response.data && response.data.msg) {
				// Update folder name in DOM
				obj.text(rename_to);
				alert(response.data.msg);
			} else {
				alert(response.data?.msg || 'Rename failed.');
			}
		});
	});


	
	$('body').on('click', '.wpdocs_list ul li.ab-dir a.wpd-edit', function(){
		$(this).closest('li.ab-dir').find('a.dtitle').click();
	});

	$('body').on('click', '.wpdocs_list ul li.ab-dir a.wpd-trash', function(){
		var delete_confirm = confirm(wpdocs_ajax_object.wpdocs_delete_msg);
		var obj = $(this);
		if(delete_confirm){
			var data = {
				'action': 'wpdocs_delete_folder',
				'dir_id': $(this).closest('li.ab-dir').data('id'),
				'resource_id': $(this).closest('li.ab-dir').data('resource'),
				'nonce': wpdocs_ajax_object.nonce
			};
			
			$.post(wpdocs_ajax_object.ajax_url, data, function(response) {
				obj.closest('li.ab-dir').fadeOut();	
			});
			
			
		}
	});	
		
	$('body').on('click', '.wpdocs_list ul li:not(.ab-dir):not(.ab-short) a.wpd-trash', function(event){
		event.preventDefault();
		var multi_delete = (files_multi_select && $('.wpdocs_list ul li.selected-item').length>1);
		var delete_confirm = confirm(multi_delete?wpdocs_ajax_object.del_confirm_all:wpdocs_ajax_object.del_confirm);
		var obj = $(this);
		if(delete_confirm){
			var id = obj.parents().eq(1).data('dir');
			
			var attachment_id = obj.parents().eq(1).data('id');
			var attachment_ids = {};
			
			if($('.wpdocs_list ul li.selected-item').length>0){
				$.each($('.wpdocs_list ul li.selected-item'), function(i,v){					
					attachment_ids[i] = $(this).data('id');
				});
			}else{
				attachment_ids[0] = attachment_id;
			}
			//console.log(attachment_ids);
			obj.parents().eq(1).fadeOut();
			var data = {
				'action': 'wpdocs_delete_files',
				'dir_id': id,
				'files': attachment_ids,
				'nonce': wpdocs_ajax_object.nonce,
			};
			//alert(attachment.id);//return;
			// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			$.post(wpdocs_ajax_object.ajax_url, data, function(response) {
				//window.location.reload();				
				if(multi_delete){
					$('.wpdocs_list ul li.selected-item').fadeOut();
				}else{
					obj.closest('li:not(.ab-dir)').fadeOut();
				}
			});
		}
	});	

	$('body').on('click', '.wpdocs_list ul li > a.folder', function(){
		var is_shortcut = $(this).parent().hasClass('ab-short');
		var linked_id = $(this).parent().data('linked');		
		var linked_url = (is_shortcut?$(this).parent().data('guid'):'');
		var dir_id = (linked_id?linked_id:$(this).parent().data('id'));
		
		if(linked_url && !linked_id){
			window.open(linked_url, '_blank').focus();
			return;
		}else{		
			window.location.href = (linked_url?linked_url:'options-general.php?page=wpdocs&dir='+dir_id);
		}
	});
	$('body').on('click', '.back-folder', function(){
		window.location.href = 'options-general.php?page=wpdocs&dir='+$(this).data('parent');		
	});
	
	setTimeout(function(){
		if ($('.new-file:visible').length > 0) {
			if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
				$('body').on('click', '.new-file:visible', function(e) {
					var id = $(this).data('id');
					var this_id = $(this).prop('id');
					
					e.preventDefault();
					//alert(id);alert(attachment.id);return;
	
					var attachment_ids = [];
					var add_file_status = true;
	
					wp.media.editor.send.attachment = function(props, attachment) {
	
	
						attachment_ids.push(attachment.id);
						//alert(attachment.id);//return;
						// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	
						if(add_file_status){
	
							add_file_status = false;
	
							setTimeout(function(){
	
								var data = {
									'action': 'wpdocs_add_files',
									'dir_id': id,
									'files': attachment_ids,
									'nonce': wpdocs_ajax_object.nonce,
								};
								$.post(wpdocs_ajax_object.ajax_url, data, function(response) {
									//window.location.reload();
									//console.log(response);
									if(response!=''){
										$('div.wpdocs_list > ul > li:not(.ab-dir)').remove();
										$('div.wpdocs_list > ul').append(response);
									}
								});
	
							})
	
						}
	
	
					};
					
					wp.media.editor.open(this_id);
					//.open($(this));
					//return false;
				});
				
			}		
		}
	}, 1000);

	//save selected file or directory globally every time on selection these will be replaced

	var selected_move_dir = null;
	var selected_move_is_file = false;
	var selected_move_file_dir = null;

	$('.wpdocs-specific-sections .save_changes').on('click', function(){
		var dir_id = $('.wpdocs-specific-sections button.modal-btn').data('dir_id');

		wp_docs_update_options(dir_id);
	});

	function wp_docs_update_options(dir_id = 0){
		//console.log($(this));
		//console.log($(this).parents().eq(1));

		$conditional_class = (dir_id == 0 ? ':not(.wpdocs_dir_options)' : '.wpdocs_dir_options');
		
		var wpdocs_option_ajax = $('input[name^="wpdocs_options"][value="ajax"]'+$conditional_class);
		var wpdocs_option_ajax_url = $('input[name^="wpdocs_options"][value="ajax_url"]'+$conditional_class);

		var wpdocs_option_file_upload = $('input[name^="wpdocs_options"][value="file_upload"]'+$conditional_class);
		var wpdocs_option_current_user_files = $('input[name^="wpdocs_options"][value="current_user_files"]'+$conditional_class);
		var wpdocs_option_del_from_front = $('input[name^="wpdocs_options"][value="del_from_front"]'+$conditional_class);


		if(wpdocs_option_file_upload.prop('checked') == false){

			wpdocs_option_current_user_files.prop('checked', false);
			wpdocs_option_del_from_front.prop('checked', false);

		}


		if(wpdocs_option_ajax.prop('checked') == false){

			wpdocs_option_ajax_url.prop('checked', false);

		}

		var wpdocs_option_checked = $('input[name^="wpdocs_options"][type="checkbox"]:checked'+$conditional_class);
		var wpdocs_option_text = $('input[name^="wpdocs_options"][type="text"]'+$conditional_class);
		var wpdocs_option_select = $('select[name^="wpdocs_options"]'+$conditional_class);
		
		var wpdocs_options_post = {};

		if(wpdocs_ajax_object.empty_settings){

			wpdocs_options_post['wpdocs_options_update'] = true;

		}


			if(wpdocs_option_select.length > 0 ){
				$.each(wpdocs_option_select, function () {
					
					var name = $(this).data('name');
					

					wpdocs_options_post[name] = $(this).val();

				});
			}


			if(wpdocs_option_text.length > 0 ){
				$.each(wpdocs_option_text, function () {

					wpdocs_options_post[$(this).data('name')] = $(this).val();

				});
			}

			if(wpdocs_option_checked.length > 0 ){
				$.each(wpdocs_option_checked, function () {

					wpdocs_options_post[$(this).val()] = true;

				});
			}
		
		var wpdocs_option_colors = $('input[name^="wpdocs_options"][type="color"]'+$conditional_class);

		if(wpdocs_option_colors.length > 0 && !wpdocs_ajax_object.empty_settings){
			$.each(wpdocs_option_colors, function () {

				wpdocs_options_post[$(this).attr('id')] = $(this).val();

			});
		}

		if(!wpdocs_options_post.allowed_role){
			wpdocs_options_post.allowed_role = 'empty';
		}


		var data = {

			action : 'wpdocs_update_option',
			wpdocs_update_option_nonce : wpdocs_ajax_object.nonce,
			wpdocs_options : wpdocs_options_post,
			wpdocs_dir_id : dir_id

		}


		
		$.post(ajaxurl, data, function(code, response){

			//console.log(response);

			if(response == 'success'){

				var alerts = (dir_id == 0 ? $('.wpdocs-options .alert.alert_main'): $('.wpdocs-specific-sections .alert.alert_dir'));

				//console.log(alert);
				alerts.removeClass('d-none').addClass('show');
				setTimeout(function(){
					alerts.addClass('d-none');
				}, 10000);

			}

		});
		

	}

	$('input[name^="wpdocs_options"]:checkbox').on('change', function(){

		var this_obj = $(this);
		if(this_obj.prop('checked')){
			setTimeout(function(){
				this_obj.parents().eq(1).find('ul').removeClass('d-none');
			});

		}else{
			this_obj.parents().eq(1).find('ul').addClass('d-none');
		}
	});

	$('input[name^="wpdocs_options"]:checkbox').change();
	
	$('body').on('change', 'input[name^="wpdocs_options"]:not(.wpdocs_dir_options), select[name^="wpdocs_options"]:not(.wpdocs_dir_options)', function(){
		wp_docs_update_options();
	});

	if(wpdocs_ajax_object.empty_settings){

		$('input[name^="wpdocs_options"]').change();

	}


	function wpdocs_disable_child(dir_id){

		var dir_list_select = $('li.wpdocs_move_folder_to select');
		var dir_list_options = dir_list_select.find('option');
		var current = dir_list_select.find('option[value="'+dir_id+'"]');
		var current_parent = current.data('parent');

		var childs = dir_list_select.find('option[data-parent="'+dir_id+'"]');

		//check if the selected is not a file than disable all child directories
		if (!selected_move_is_file) {

			if (dir_list_options.length > 0) {

				$.each(dir_list_options, function () {

					var this_val = $(this).val();
					var this_parent = $(this).data('parent');

					if (this_val == current_parent) {

						$(this).prop('disabled', true);
						//$(this).hide();
						$(this).prop('title', wpdocs_ajax_object.move_str);
					}


					if ($(this).val() == dir_id || $(this).data('parent') == dir_id) {

						$(this).prop('disabled', true);
						//$(this).hide();
						$(this).prop('title', wpdocs_ajax_object.move_str);

						if (childs.length > 0 && this_val != dir_id) {

							wpdocs_disable_child(this_val);

						}
					}

				});
			}

		} else {

			//if selected is file than disable only its parent directory and root directory

			current = dir_list_select.find('option[value="' + selected_move_file_dir + '"]');
			var current_root = dir_list_select.find('option[value="0"]');
			current.prop('disabled', true);
			//current.hide();
			current_root.prop('disabled', true);
			//current_root.hide();
			current_root.prop('title', wpdocs_ajax_object.move_str);

		}

	}

	$('body').on('click', '.wpdocs_list ul li a.wpd-move, .wpdocs_list ul li a.wpd-copy', function(){

		var dir_id = $(this).parents('li').data('id');
		var file_dir = $(this).parents('li').data('dir');
		var is_file = file_dir !== undefined;
		selected_move_file_dir = is_file ? file_dir: null;
		selected_move_is_file = is_file;

		selected_move_dir = dir_id;
		var dir_list_select = $('li.wpdocs_move_folder_to select');
		var dir_list_options = dir_list_select.find('option');
		dir_list_options.prop('disabled', false);
		dir_list_options.show();


		$('li.wpdocs_move_folder_to').show();

		wpdocs_disable_child(selected_move_dir);
		
		
		
		wpdocs_move_copy = ($(this).hasClass('wpd-copy')?'copy':'move');
		
		$('.wpdocs_move_folder_to button').removeClass('copy move').addClass(wpdocs_move_copy).html(wpdocs_move_copy);

		
	});

	$('body').on('click', 'li.wpdocs_move_folder_to button', function(){

		var selected_dir = $('li.wpdocs_move_folder_to select').val();



		if(selected_dir != -1){

			var wpdocs_move_selected_dir_obj = {

					'dir_selected' : selected_move_dir,
					'dir_id' : selected_dir,
					'files' : selected_move_dir,
					'is_file' : selected_move_is_file,
					'file_dir': selected_move_file_dir,
					'action_type': wpdocs_move_copy,
			};

			var data = {

				action: 'wpdocs_update_option',
				wpdocs_update_option_nonce : wpdocs_ajax_object.nonce,
				wpdocs_move_selected_dir: wpdocs_move_selected_dir_obj

			}
			
			if(wpdocs_move_copy=='copy' && wpdocs_ajax_object.wpdocs_pro!='1'){
				alert(wpdocs_ajax_object.premium_feature);return;
			}
			
			$.blockUI({message:(wpdocs_move_copy=='copy'?wpdocs_ajax_object.copied:wpdocs_ajax_object.moved)});
			
			$.post(ajaxurl, data, function(response) {

				response = JSON.parse(response);

				if(response){
					
					

					window.location.href= wpdocs_ajax_object.url+'&dir='+selected_dir ;

				}else{

					alert(wpdocs_ajax_object.move_error);
					$(this).parents('li.wpdocs_move_folder_to').hide();

				}

			});


		}else{

			alert(wpdocs_ajax_object.target_dir_msg);
		}
	});

	if($('.wpdocs_options_allowed_role:not(.wpdocs_dir_options)').length > 0){
		
		new SlimSelect({
	
			select:'.wpdocs_options_allowed_role:not(.wpdocs_dir_options)',
			placeholder: wpdocs_ajax_object.select_role_str,
		});
	}


	if($('.wpdocs_options_allowed_role.wpdocs_dir_options').length > 0){

		new SlimSelect({

			select:'.wpdocs_options_allowed_role.wpdocs_dir_options',
			placeholder: wpdocs_ajax_object.select_role_str,
		});

	}


	$('.wpdocs_meta_links .wpdocs_show_option').on('click', function(){

		var control = $($(this).data('control'));
		var show = $(this).attr('data-show');

		if(show == 'true'){
			$(this).attr('data-show', false);
			control.slideUp();
		}else{
			$(this).attr('data-show', true);	
			control.slideDown();

		}

	});



	$('.wp_docs_import_memphis').on('click', function(){

		var import_confirm = confirm(wpdocs_ajax_object.import_confirm);
		var progress_text = $(this).data('text');

		if(!import_confirm) return;

		var data = {

			action : 'wp_docs_import_memphis_docs',
			wp_docs_nonce:wpdocs_ajax_object.nonce,
		};

		$('.wp_docs_importing').show();
		$('.wp_docs_importing .progress-bar').text(progress_text);
		$('.wp_docs_importing_alert').hide();


		$.post(ajaxurl, data, function(resp, code){
			$('.wp_docs_importing').hide();
			if(code == 'success' && resp.status){
				window.location.href = wpdocs_ajax_object.url;
			}else if(resp.remarks){
				$('.wp_docs_importing_alert').show();
				$('.wp_docs_importing_alert').text(resp.remarks);
			}

			setTimeout(function() {

				$('.wp_docs_importing_alert').fadeOut();
				
			}, 5000);

		});

	});

	$('.wp_docs_import_memphis_rollback').on('click', function(){

		var import_confirm = confirm(wpdocs_ajax_object.undo_import_confirm);
		var progress_text = $(this).data('text');

		if(!import_confirm) return;

		var data = {

			action : 'wp_docs_import_memphis_rollback',
			wp_docs_nonce:wpdocs_ajax_object.nonce,
		};

		$('.wp_docs_importing .progress-bar').text(progress_text);
		$('.wp_docs_importing').show();
		
		$.post(ajaxurl, data, function(resp, code){
			$('.wp_docs_importing').hide();
			if(code == 'success' && resp.status){
				window.location.href = wpdocs_ajax_object.url;
			}

		});

	});
	
	
	$('.wpdocs-wrapper').on('click', 'h2.nav-tab-wrapper a.nav-tab', function(){
		wpdocs_ajax_object.wc_os_pg = parseInt(wpdocs_ajax_object.wc_os_pg);
		$(this).siblings().removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.nav-tab-content, form:not(.wrap.wpdocs-wrapper .nav-tab-content):not(.ignore)').hide();
		//console.log($(this).index());
		$('.nav-tab-content').eq($(this).index()).removeClass('hides').show();
		var state_url = wpdocs_ajax_object.url+'&t='+$(this).index()+(wpdocs_ajax_object.wc_os_pg>0?'&pg='+wpdocs_ajax_object.wc_os_pg:'');
		window.history.replaceState('', '', state_url);
		$('form input[name="wos_tn"]').val($(this).index());
		wpdocs_ajax_object.wc_os_tab = $(this).index();
		$('.wrap.wpdocs-wrapper').attr('class', 'wrap wpdocs-wrapper tab-'+$(this).index());
		
		$('.wpdocs-wrapper form').prop('action', state_url);		
					
	});	
	function parse_query_string(query) {
	  var vars = query.split("&");
	  var query_string = {};
	  for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split("=");
		// If first entry with this name
		if (typeof query_string[pair[0]] === "undefined") {
		  query_string[pair[0]] = decodeURIComponent(pair[1]);
		  // If second entry with this name
		} else if (typeof query_string[pair[0]] === "string") {
		  var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
		  query_string[pair[0]] = arr;
		  // If third or later entry with this name
		} else {
		  query_string[pair[0]].push(decodeURIComponent(pair[1]));
		}
	  }
	  return query_string;
	}	
	
	if($('.wpdocs-wrapper').length>0){
		var query = window.location.search.substring(1);
		var qs = parse_query_string(query);		
		
		if(typeof(qs.t)!='undefined'){
			$('.wpdocs-wrapper a.nav-tab').eq(qs.t).click();			
		}
	}
	
	$('#translate_wpdocs_btn').on('click', function(){
		var val = $.trim($('#translate_wpdocs').val());
		if(val){
			
			var url = new URL(val);			
			var dir = url.searchParams.get("dir");
			var updated_str = val;

			if(typeof(dir)!='undefined' && (dir in wpdocs_ajax_object.all_dirs)){
				updated_str = updated_str.replace('dir='+dir, wpdocs_ajax_object.all_dirs[dir]);			
			}
			$('.translate_wpdocs').append(updated_str+'<br />');
			$('#translate_wpdocs').val('');
		}
		
	
	});
});