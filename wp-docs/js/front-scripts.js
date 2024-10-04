// JavaScript Document




jQuery(document).ready(function($){


	var is_pro = (wpdocs.wpdocs_pro=='1');
	var history_state = wpdocs.is_ajax_url;

	/*if(wpdocs.restriction_load){
		$.blockUI({ 
			message: wpdocs.block_ui,
			css: { 

				}
		 });
		setTimeout(function(){
			$.unblockUI();
		}, 1500);
	}*/
	
	setTimeout(function(){
		if($('.empty-dir-files').length>0 && wpdocs.current_user_id>0 && wpdocs.del_from_front){
			$.each($('.empty-dir-files'), function(){				
				$(this).parents().eq(2).find('.wp_docs_del_file').addClass('empty-dir').css('opacity', '1').show();
			});
		}
		
		var dir_obj = $('div.wpdoc_container[data-dir="'+wpdocs.dir_id+'"]');
		if(wpdocs.dir_id>0 && dir_obj.length>0){
			$('html, body').animate({
				scrollTop: dir_obj.offset().top-240
			}, 100);
		}
	}, 500);
	 


	function wpd_load_dir_simple(dir_id){
		
		//console.log(dir_id);

		var separator = '?';
		if(wpdocs.this_url.includes('?')){
			separator = '&';
		}
		if(dir_id === 0){

			window.location.href = wpdocs.this_url;

		}else{

			window.location.href = wpdocs.this_url+separator+'dir='+dir_id;

		}
	}

	$('body').on('click', '.file_wrapper.is_dir, .wpd_bread_item', function(e){
	
		e.preventDefault();
		var linked_id = $(this).data('linked');
		var dir_id = (linked_id?linked_id:$(this).data('id'));
		var linked_url = $(this).data('guid');
		var parent_container = $(this).parents('div.wpdoc_container:first');
		var home_id = parent_container.data('home');
		
		//console.log('A3');
		
		if(linked_url && !linked_id){
			window.open(linked_url, '_blank').focus();
			return;
		}
		
		if(is_pro && wpdocs.is_ajax=='1'){
		
			wpd_load_dir_ajax(dir_id, parent_container);
		
		}else{
		
		
			wpd_load_dir_simple(dir_id);
		
		}
		
	});






	$('body').on('mouseover','figure.file_view:not(.selected)', function(){

		$(this).addClass('bg-dark text-white rounded');
		$(this).find('.figure-caption').addClass('text-white');
	});

	$('body').on('mouseout','figure.file_view:not(.selected)', function(){
		$(this).removeClass('bg-dark text-white rounded');
		$(this).find('.figure-caption').removeClass('text-white');
	});



	$('body').on('dblclick','div.list_view figure.file_view', function(e){

		//if(wpdocs.del_from_front){
			e.preventDefault();
			//console.log('A2');

			if(typeof $(this).parent().attr('data-url')!='undefined'){
				window.open($(this).parent().data('url'), '_blank');
			}
			
		//}
	});

	$('body').on('dblclick','tr.file_view', function(e){
		//if(wpdocs.del_from_front){
			e.preventDefault();
			if(typeof $(this).data('url')!='undefined' && $(this).data('url')!=''){
				//window.open($(this).data('url'), '_blank');
			}
		//}
	});
	
	$('body').on('click','tr.file_view', function(e){
	
		var this_parent = $(this).parents('.wpdoc_container:first');
		
		if(this_parent.data('del_from_front')){

		
		}else{
			e.preventDefault();
			//console.log('A1');
			if(typeof $(this).data('url')!='undefined' && $(this).data('url')!=''){
				window.open($(this).data('url'), '_blank');
			}
		}
	
	});

	$('body').on('click','figure.file_view', function(e){
		
		var this_parent = $(this).parents('.wpdoc_container:first');
		var is_dir = $(this).parent().hasClass('is_dir');
		
		//console.log(this_parent);
		//console.log(this_parent.data('del_from_front'));
		//console.log(!is_dir && this_parent.data('del_from_front'));
		if(!is_dir && this_parent.data('del_from_front')!=''){

				
			e.preventDefault();
	
			this_parent.find('figure.file_view').removeClass('bg-dark text-white rounded selected');
			this_parent.find('figure.file_view').find('.figure-caption').removeClass('text-white');
	
	
	
			$(this).addClass('bg-dark text-white rounded selected');
			$(this).find('.figure-caption').addClass('text-white');
	
			if($('figure.file_view.selected').length > 0 && wpdocs.current_user_id>0){
				this_parent.find('.wp_docs_del_file').show();
				this_parent.find('.wp_docs_del_file').css('opacity', '1');
			}else{
				this_parent.find('.wp_docs_del_file').css('opacity', '0.5');
				this_parent.find('.wp_docs_del_file').hide();
	
			}
	
			this_parent.find('.wpd_del_file_id').val($(this).parents('.is_file:first').data('id'));

			

		}else{
			$(this).trigger('dblclick');		
		}
	});
	
	$('body').on('click','figure.file_view figcaption', function(e){
		
		e.preventDefault();
		
		var this_parent = $(this).parents('.wpdoc_container:first');
		
		if(this_parent.data('del_from_front')){
		}else{			
			//console.log('A');
			if($(this).parent().find('a').length>0 && typeof $(this).parent().find('a')!='undefined'){
				window.open($(this).parent().find('a').attr('href'), '_blank');
			}	
		}
	});







	
	$('body').on('click','.folder_view_btn', function(){
	
		var this_view_link = $(this);
		
		//console.log(this_view_link);
		
		var parent_container = this_view_link.parents('div.wpdoc_container:first');
		
		//console.log(parent_container);
		
		parent_container.find('.folder_view').addClass('d-none');
		var data_class = $(this).data('source');
		parent_container.find('.'+data_class).removeClass('d-none');
	
	
	});




	if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
		$('body').on('click','.wpdocs-front-add-media:visible',  function(e) {
			var parent_container = $(this).parents('div.wpdoc_container:first');
			
			var id = parent_container.data('dir');
			var this_id = parent_container.find('.wpdocs-front-add-media').prop('id');
			
			e.preventDefault();

			//alert(id);alert(attachment.id);return;

			if(id != 0){

				
				if(is_pro && wpdocs_apply_restriction(parent_container, 0)){
					return;
				};

				var attachment_ids = [];
				var add_file_status = true;

				wp.media.editor.send.attachment = function(props, attachment) {

					attachment_ids.push(attachment.id);

					if(add_file_status){

						add_file_status = false;

						setTimeout(function(){

							var data = {
								'action': 'wpdocs_add_files',
								'dir_id': id,
								'files': attachment_ids,
								'nonce': wpdocs.nonce,
							};
							$.post(wpdocs.ajax_url, data, function(response) {

								if(is_pro){

									wpd_load_dir_ajax(id, parent_container);

								}else{


									wpd_load_dir_simple(id);

								}

							});

						})

					}



				};
				//wp.media.editor.open($(this));
				wp.media.editor.open(this_id);

			}
			//return false;
		});

	}
	
	$('body').on('click', '.wpdocs-views a', function(){
	//console.log($(this).data('source'));
	
		var this_view_link = $(this);
		var parent_container = this_view_link.parents('div.wpdoc_container');
		var parent_dir = parent_container.data('home');
		
		var data = {
			'action': 'wpdocs_update_view',
			'parent_dir': parent_dir,
			'update_view': $(this).data('source'),
			'nonce': wpdocs.nonce,
		};
		$.post(wpdocs.ajax_url, data, function(response) {
		
		});
	});
	
	$('body').on("keyup", '.wpdocs-searchbox input', function() {


		var value = $(this).val().toLowerCase();

		var this_search_box = $(this);
		var this_parent = this_search_box.parents('div.wpdoc_container:first');
		
		

		if(this_parent.find(".row.folder_view:visible > div.table-responsive").length>0){
			if(wpdocs.ajax_based_deep_search){
				this_parent.find(".row.folder_view:visible > div table tbody tr.is_deep").addClass('is_deep_item').removeClass('is_deep');
			}
			this_parent.find(".row.folder_view:visible > div table tbody tr").filter(function() {
			  $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
			if(value==''){
				this_parent.find(".row.folder_view:visible > div table tbody tr.is_deep_item").addClass('is_deep').removeClass('is_deep_item');
			}
		}else{
			this_parent.find(".row.folder_view:visible > div.is_deep").addClass('is_deep_item').removeClass('is_deep');
			this_parent.find(".row.folder_view:visible > div").filter(function() {
			  $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
			if(value==''){
				this_parent.find(".row.folder_view:visible > div.is_deep_item").addClass('is_deep').removeClass('is_deep_item');
			}
		}
		
	
	});	

	if(is_pro && wpdocs.restriction_load){

		var rest_dir_id = wpdocs.restriction_id;
		var parent_index = wpdocs.restriction_container;

		var parent_container = $('div.wpdoc_container').eq(parent_index);
		var parent_container_dir = parent_container.data('dir');

		if(parent_container_dir != rest_dir_id){

			wpd_load_dir_ajax(rest_dir_id, parent_container);

		}


		wpdocs_disable_container(rest_dir_id);

		history.pushState({}, '', wpdocs.this_url);


	}

	

});