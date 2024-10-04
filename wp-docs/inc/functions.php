<?php

	if(!function_exists('wp_docs_get_memphis_dir_option_id')){
		function wp_docs_get_option_id($option_name){
			global $wpdb;
			$option_name = esc_sql( $option_name );
			$query = "SELECT option_id FROM $wpdb->options WHERE option_name = '$option_name'";
			return $wpdb->get_var($query);
		}
	}

	function sanitize_wpdocs_data( $input ) {
	
			if(is_array($input)){
			
				$new_input = array();
		
				foreach ( $input as $key => $val ) {
					$new_input[ $key ] = (is_array($val)?sanitize_wpdocs_data($val):sanitize_text_field( $val ));
				}
				
			}else{
				$new_input = sanitize_text_field($input);
			}
	
			if(!is_array($new_input)){
	
				if(stripos($new_input, '@') && is_email($new_input)){
					$new_input = sanitize_email($new_input);
				}
	
				if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
					$new_input = sanitize_url($new_input);
				}
	
			}
	
			
			return $new_input;
	}	
	
	function wpdocs_admin_enqueue_script()
	{
		if (isset($_GET['page']) && $_GET['page'] == 'wpdocs') {
			
			global $wpdocs_pro, $wpdocs_options;
				
			wp_enqueue_script('wpdocs_boostrap', plugin_dir_url(dirname(__FILE__)) . 'js/bootstrap.min.js', array('jquery'));
			wp_enqueue_style('wpdocs-boostrap', plugins_url('css/bootstrap.min.css', dirname(__FILE__)));
			wp_enqueue_script('wpdocs_slim', plugin_dir_url(dirname(__FILE__)) . 'js/slimselect.js', array('jquery'));
			wp_enqueue_style('wpdocs-slim', plugins_url('css/slimselect.css', dirname(__FILE__)));
			
			wp_enqueue_style( 'jquery-ui', plugins_url('css/jquery-ui.css', dirname(__FILE__)), array('jquery') );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			
			wp_enqueue_script('wpdocs_block_scripts', plugin_dir_url(dirname(__FILE__)) . 'js/jquery.blockUI.js', array('jquery'));
	
			
			wp_enqueue_style('fontawesome', plugins_url('css/fontawesome.min.css', dirname(__FILE__)));
	
			wp_enqueue_media();
	
			wp_enqueue_style('wpdocs-common', plugins_url('css/common-styles.css', dirname(__FILE__)), array(), date('Ymdhi'));
			wp_enqueue_style('wpdocs-admin', plugins_url('css/admin-styles.css', dirname(__FILE__)), array(), date('Ymdhi'));
	
			wp_enqueue_script('wpdocs_admin_scripts', plugin_dir_url(dirname(__FILE__)) . 'js/admin-scripts.js', array('jquery', 'jquery-ui-dialog'), time());
			
			if($wpdocs_pro){
				wp_enqueue_script('wpdocs_pro_scripts', plugin_dir_url(dirname(__FILE__)) . 'pro/wp-docs-admin.js?t='.time(), array('jquery'));
			}
			
			$dir_id_to_titles = array();
			$all_dirs = wpdocs_list('');
			if(!empty($all_dirs)){
				foreach($all_dirs as $all_dir){
					$dir_id_to_titles[$all_dir['id']] = $all_dir['title'];
				}
			}
			
			wp_localize_script(
				'wpdocs_admin_scripts',
				'wpdocs_ajax_object',
				array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'url' => admin_url('options-general.php?page=wpdocs'),
					'wpdocs_pro' => $wpdocs_pro,
					'wpdocs_delete_msg' => __('Do you want to delete this directory and data as well?', 'wp-docs'),
					'wpdocs_delete_shortcut_msg' => __('Do you want to delete this shortcut?', 'wp-docs'),
					'target_dir_msg' => __('Select a target directory.', 'wp-docs'),					
					'move_error' => __('Sorry! File could not move, please try again.', 'wp-docs'),
					'move_str' => __('Cannot move to the selected directory', 'wp-docs'),
					'copied' => __('Successfully copied!', 'wp-docs'),
					'moved' => __('Successfully moved!', 'wp-docs'),
					'premium_feature' => __('This feature is not available in basic version. Please upgrade.', 'wp-docs'),
					'del_confirm' => __('Do you want to delete this file?', 'wp-docs'),
					'del_confirm_all' => __('Do you want to delete selected files?', 'wp-docs'),
					'import_confirm' => __('Do you want to import all directories and files from Memphis Documents Library?', 'wp-docs'),
					'undo_import_confirm' => __('Do you want to rollback the import?', 'wp-docs'),					
					'select_role_str' =>  __('Select roles to allow upload', 'wp-docs'),
					'rename_confirm' => __('Do you want to rename this directory?', 'wp-docs'),
					'reset_confirm' => __('Do you want to reset all settings and clear directories?', 'wp-docs'),					
					'nonce' => wp_create_nonce('wpdocs_update_options_nonce'),
                    'empty_settings' => empty($wpdocs_options),
					'wc_os_pg' => (isset($_GET['pg'])?esc_attr($_GET['pg']):'0'),
					'wc_os_tab' => (isset($_GET['t'])?esc_attr($_GET['t']):'0'),
					'all_dirs' => $dir_id_to_titles
					
				)
			);
		}
	}
	
	add_filter( 'ajax_query_attachments_args', 'wpdocs_filter_media');
	function wpdocs_filter_media( $query ) {
		// admins get to see everything
		if ( ! current_user_can( 'manage_options' ) )
			$query['author'] = get_current_user_id();
	
		return $query;
	}

    function wpdocs_get_user_roles(){
        $ret = array();
        global $wp_roles;
        if(!empty($wp_roles) && isset($wp_roles->roles) && !empty($wp_roles->roles)){

            foreach($wp_roles->roles as $key=>$arr){
                $ret[$key] = $arr['name'];
            }
        }
        return $ret;
    }

    function wpdocs_get_user_roles_options(array $selected){

        $wpdocs_get_user_roles = wpdocs_get_user_roles();

        $options = '';

        if(!empty($wpdocs_get_user_roles)){

            foreach ($wpdocs_get_user_roles as $role_key => $role_name){

                $selected_option = in_array($role_key, $selected) ? 'selected="selected"' : '';

                $options .= "<option value='$role_key' $selected_option>$role_name</option>";

            }
        }

        return $options;


    }

    function wpdocs_allowed_mime_types($mime_types){

	    global $wpdocs_options;

		
		$dir = (isset($_GET['wpdocs_restriction']) ? esc_attr($_GET['wpdocs_restriction']) : 0);

        $is_file = wpdocs_dir_options_by_name('file_upload', false, $dir);
        $allowed_ext = wpdocs_dir_options_by_name('allowed_ext', '', $dir);


        if($is_file && $allowed_ext){


            $allowed_ext = explode(',', $allowed_ext);

            $allowed_ext = array_map('trim', $allowed_ext);

            $allowe_mime_types = array();

            if(!empty($mime_types)){

                foreach ($mime_types as $mime_key => $mime_value){

                    $mime_key_array = explode('|', $mime_key);
                    $mime_key_array = array_map('trim', $mime_key_array);


                    if(!empty($allowed_ext)){
                        foreach ($allowed_ext as $ext){

                            if(in_array($ext, $mime_key_array)){
                                $allowe_mime_types[$mime_key] = $mime_value;
                            }

                        }
                    }


                }

            }

			

            return $allowe_mime_types;

			

        }else{

            return $mime_types;

        }




    }

    function wpdocs_get_current_user_role() {
        global $wp_roles;

        $current_user = wp_get_current_user();
        $roles = $current_user->roles;

        $role = array_shift( $roles );

        return isset( $wp_roles->role_names[ $role ] ) ? $role : FALSE;
    }

    function wpdocs_can_current_user_upload_file($dir = 0){

        global $wpdocs_options;
        $is_file = wpdocs_dir_options_by_name('file_upload', false, $dir);


        if(!is_user_logged_in() && !$is_file){return false;}

        $current_user_role = wpdocs_get_current_user_role();

		


        // $allowed_role = array_key_exists('allowed_role', $wpdocs_options) ? $wpdocs_options['allowed_role'] : array();
        $allowed_role = wpdocs_dir_options_by_name('allowed_role', array(), $dir);
		

        if(!empty($allowed_role)){

            if(in_array($current_user_role, $allowed_role)){

                $can_current_user_upload = current_user_can('upload_files');

                if(!$can_current_user_upload){

                    $user_role = get_role($current_user_role);
                    $user_role->add_cap('upload_files');
                    $user_role->add_cap('delete_posts');

                }

                return true;

            }else{

                return false;
            }


        }else{


            return current_user_can('upload_files');

        }



    }
	
	add_action('admin_enqueue_scripts', 'wpdocs_admin_enqueue_script');
	
	add_action('wp_enqueue_scripts', 'wpdocs_wp_enqueue_script');
	
		
	function wp_docs_recursive_array_search($needle, $haystack) {
		
		if(is_array($haystack) && !empty($haystack)){
			foreach($haystack as $key=>$value) {
				
				if(!is_array($value) && stripos(' '.$value, $needle)) {
					return array($key);
				} else if (is_array($value) && $subkey = wp_docs_recursive_array_search($needle,$value)) {
					array_unshift($subkey, $key);
					return $subkey;
				}
			}
		}
	}
	
	
	function wpdocs_test($post_content , $short_code = ''){

		global $post;
		$result = array();
		//get shortcode regex pattern wordpress function
		$pattern = get_shortcode_regex();
		$result = array();

		if (   preg_match_all( '/'. $pattern .'/s', $post_content, $matches ) )
		{
			pree($matches);
			$keys = array();
			foreach( $matches[0] as $key => $value) {
				// $matches[3] return the shortcode attribute as string
				// replace space with '&' for parse_str() function
				$get = preg_replace('/"\s/', '"&', $matches[3][$key] );
				parse_str($get, $output);

				pree($get);
				pree($output);

				//get all shortcode attribute keys
				$keys = array_unique( array_merge(  $keys, array_keys($output)) );
				$result[] = $output;

			}
			//var_dump($result);
			if( $keys && $result ) {
				// Loop the result array and add the missing shortcode attribute key
				foreach ($result as $key => $value) {
					// Loop the shortcode attribute key
					foreach ($keys as $attr_key) {
						$result[$key][$attr_key] = isset( $result[$key][$attr_key] ) ? $result[$key][$attr_key] : NULL;
					}
					//sort the array key
					ksort( $result[$key]);              
				}
			}

			//display the result
		}

		return $result;


	}
	
	function wpdocs_wp_enqueue_script()
	{
		global $post, $wpdocs_pro, $wpdocs_url, $wpdocs_options;
		
		$wpdocs_relevant_page = false;
		$localize_handler = 'wpdocs_front_scripts';
		//pree($post->post_content);
		//pree(stripos($post->post_content, '[wpdocs]'));
		$details_view_sorting = array_key_exists('details_view_sorting', $wpdocs_options);
		$ajax_based_deep_search = ($wpdocs_pro && array_key_exists('ajax_based_deep_search', $wpdocs_options));



		if(!empty($post) && isset($post->post_content)){
			$meta_data = get_post_meta($post->ID);
			

			$wpdocs_inside_meta = wp_docs_recursive_array_search('[wpdocs', $meta_data);
			$wpdocs_inside_meta = is_array($wpdocs_inside_meta)?array_filter($wpdocs_inside_meta):array();
			
			//pree($wpdocs_inside_meta);
			
			if(stripos(' '.$post->post_content, '[wpdocs') || !empty($wpdocs_inside_meta)){
				$wpdocs_relevant_page = true;
			}
		}
		//pree($wpdocs_relevant_page);
		if($wpdocs_relevant_page){
			
			$is_bootstrap = array_key_exists('bootstrap', $wpdocs_options);
			$is_file_upload = array_key_exists('file_upload', $wpdocs_options);
	
			if(is_admin() || ($wpdocs_pro)){


                add_filter('upload_mimes', 'wpdocs_allowed_mime_types', 1, 1);
	
				wp_enqueue_media();
	
			}
	
			if($is_bootstrap){ 		
				wp_enqueue_script('wpdocs_boostrap', plugin_dir_url(dirname(__FILE__)) . 'js/bootstrap.min.js');
				wp_enqueue_style('wpdocs-boostrap', plugins_url('css/bootstrap.min.css', dirname(__FILE__)));
			}
	
			$is_ajax_url = false;
			$is_ajax = false;
	
	
			if($wpdocs_pro){
	
				$is_ajax = array_key_exists('ajax', $wpdocs_options);
				$is_ajax_url = array_key_exists('ajax_url', $wpdocs_options);

				wp_enqueue_script('wpdocs_pro_scripts', $wpdocs_url . 'pro/wp-docs-pro.js', array('jquery'), time());
				$localize_handler = 'wpdocs_pro_scripts';
				wp_enqueue_script('wpdocs_block_scripts', $wpdocs_url . 'js/jquery.blockUI.js', array('jquery'));

	
			}
			
			
			
            wp_enqueue_style('fontawesome', plugins_url('css/fontawesome.min.css', dirname(__FILE__)));


            wp_enqueue_script('wpdocs_front_scripts', plugin_dir_url(dirname(__FILE__)) . 'js/front-scripts.js', array('jquery'), date('Ymdhi'));
			wp_enqueue_style('wpdocs-common', plugins_url('css/common-styles.css', dirname(__FILE__)));
			wp_enqueue_style('wpdocs-front', plugins_url('css/front-styles.css', dirname(__FILE__)), array(), date('Ymdhi'));
			
			$dir_id = (array_key_exists('dir_id', $_GET)?sanitize_wpdocs_data($_GET['dir_id']):0);
			$dir_id = (!$dir_id && array_key_exists('dir', $_GET)?sanitize_wpdocs_data($_GET['dir']):0);
			
			$params_array = array(
					'dir_id' => $dir_id,
					'parent_dir' => get_permalink($post->ID).'/?dir=',
					'wpdocs_pro' => $wpdocs_pro,
					'details_view_sorting' => $details_view_sorting,
					'ajax_based_deep_search' => $ajax_based_deep_search,
					'ajax_url' => admin_url('admin-ajax.php'),
					'this_url' => get_permalink(),
                    'del_confirm' => __('Do you want to delete this file?', 'wp-docs'),
					'del_dir_confirm' => __('Do you want to delete this directory?', 'wp-docs'),
                    'select_file_alert' => __('Please select a file to delete.', 'wp-docs'),
                    'not_belong_dir_string' => __('Sorry, you can not delete this directory.', 'wp-docs'),
					'not_belong_string' => __('Sorry, you can not delete this file.', 'wp-docs'),
					'copied' => __('Successfully copied!', 'wp-docs'),
					'moved' => __('Successfully moved!', 'wp-docs'),
                    'block_ui' => __('Please wait...', 'wp-docs'),
                    'is_ajax' => $is_ajax,
					'is_ajax_url' => $is_ajax_url,
					'del_from_front' => array_key_exists('del_from_front', $wpdocs_options),
					'nonce' => wp_create_nonce('wpdocs_update_options_nonce'),
					'restriction_load' => isset($_GET['wpdocs_restriction']),
					'restriction_id' => isset($_GET['wpdocs_restriction']) ? $_GET['wpdocs_restriction'] : '',
					'restriction_container' => isset($_GET['wpdocs_container']) ? $_GET['wpdocs_container'] : '',
					'current_user_id' => get_current_user_id(),
					'current_user_role' => wpdocs_get_current_user_role(),
					
			);
			if($params_array['dir_id'] && $params_array['del_from_front']){
				$params_array['del_from_front'] = wpdocs_can_current_user_upload_file($params_array['dir_id']);
			}

			wp_localize_script(
				$localize_handler,
				'wpdocs',
				$params_array
			);
	

		}
	}
	
	if (is_admin()) {
		add_action('admin_menu', 'wpdocs_menu');
	}
	function wpdocs_menu()
	{
		global $wpdocs_data, $wpdocs_pro;
	
		$title = $wpdocs_data['Name'] . ' ' . ($wpdocs_pro ? ' ' . __('Pro', 'wp-docs') : '');
	
		add_options_page($title, $title, 'publish_pages', 'wpdocs', 'wpdocs_settings');
	}
	function wpdocs_settings()
	{
		global $wpdocs_premium_link, $wpdocs_pro, $wpdocs_url;
		$wpdocs_options = get_option('wpdocs_options', array());
		$wpdocs_options = is_array($wpdocs_options)?$wpdocs_options:array();
		include_once('wpdocs_settings.php');
	}
	
	function wpdocs_list_inner($post_parent='', $recursive=false, $orderby='post_title', $order='ASC', $exclude_post_type=array()){
		
		global $wpdb, $wpdocs_post_types, $wpdocs_post_status;
		
		$ids = (is_array($post_parent)?$post_parent:explode(',', $post_parent));
		$ids = array_map('trim', $ids);
		$ids = array_filter($ids, 'is_numeric');	
		$ids = (empty($ids)?array(0):$ids);
		
		$posts_array = array();
		
		if(!empty($ids)){
			
			$post_types = $wpdocs_post_types;
			
			if(!empty($exclude_post_type)){
				$post_types = array_diff_key($post_types, array_flip($exclude_post_type));
			}
			//pree($exclude_post_type);
			//pree($post_types);//exit;
			
			if($recursive){				
				unset($post_types['shortcut']);				
			}
			

			$query_str = "SELECT ID, post_title, post_type, post_content, post_parent, guid FROM ".$wpdb->prefix."posts WHERE post_status='$wpdocs_post_status' AND post_type IN ('".implode("','", $post_types)."') AND post_parent IN (".implode(',', $ids).") ORDER BY $orderby $order";
			
			//pree($query_str);
			
			$posts_array = $wpdb->get_results($query_str);
			
			if($recursive){
				$post_parent_arr = array();
				if(!empty($posts_array)){
					foreach($posts_array as $posts_item){
						$post_parent_arr[] = $posts_item->ID;
					}
					if(!empty($post_parent)){
						
						$posts_array = array_merge($posts_array, wpdocs_list_inner($post_parent_arr, $recursive, $orderby, $order, $exclude_post_type));
					}
				}
			
			}
			
		}
		
		return $posts_array;
	}
	function wpdocs_list($post_parent = 0, $orderby='post_title', $order='ASC', $return_type='smart', $exclude_post_type=array()){
		
		//pree($post_parent);pree($orderby);pree($order);pree($return_type);pree($exclude_post_type);
		
		global $wpdocs_post_types, $wpdocs_post_status;
		
		$ret = $posts_array = array();
	
		//global $wpdb;
			
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => $orderby,
			'order'            => $order,
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => $wpdocs_post_types,
			'post_mime_type'   => '',				
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => $wpdocs_post_status,
			'suppress_filters' => true
		);
		
		if(is_numeric($post_parent)) {				
			$args['post_parent'] = $post_parent;
			$posts_array = get_posts($args);
			//pree($wpdb->last_query);
		}else{
			
			switch($orderby){
				case 'title':
				case 'post_title':
					$orderby = 'post_title';
				break;
				case 'date':
				case 'post_date':
					$orderby = 'post_date';
				break;
			}
			//pree($exclude_post_type);
			$posts_array = wpdocs_list_inner($post_parent, false, $orderby, $order, $exclude_post_type);
				
			
		}
		
		
		if (!empty($posts_array)) {
			foreach ($posts_array as $posts) { //pree($posts);
				switch($return_type){
					default:
					case 'smart':
						$guid_pos = strpos($posts->guid, 'p='.$posts->ID);
						$posts->guid = ($guid_pos!='' && $guid_pos>=0?'':$posts->guid);
						$ret[] = array('id' => $posts->ID, 'title' => $posts->post_title, 'type' => $posts->post_type, 'content' => $posts->post_content, 'link' => $posts->guid);
					break;
					case 'object':
						$ret[] = $posts;
					break;
				}
			}
		}
	
		//pree($ret);
		
		return $ret;
	}
	
	function wpdocs_create_folder_post($post_parent, $post_title = "New Folder")
	{
	
		$my_post = array(
			'post_title'    => $post_title,
			'post_content'  => '',
			'post_status'   => 'hidden',
			'post_author'   => get_current_user_id(),
			'post_type'   => 'wpdocs_folder',
			'post_parent'      => (($post_parent > 0 && wpdocs_folder_exists($post_parent)) ? $post_parent : 0),
			'post_category' => array()
		);
	
		$dir_id = wp_insert_post($my_post);
		
		return $dir_id;
	}
	
	add_action('wp_ajax_wpdocs_create_folder', 'wpdocs_create_folder');
	
	function wpdocs_create_folder()
	{
		$nonce = sanitize_wpdocs_data(wp_unslash($_POST['nonce']));
		
		if ( ! wp_verify_nonce( $nonce, 'wpdocs_update_options_nonce' ) )
			die (__("Sorry, your nonce did not verify.", 'wp-docs'));
	
		$post_parent = sanitize_wpdocs_data($_POST['parent_dir']);
	
		$dir_id = wpdocs_create_folder_post($post_parent);
		
		$list_obj = get_post($dir_id);
		
		$list = array('id'=>$list_obj->ID, 'content'=>$list_obj->post_content, 'title'=>$list_obj->post_title, 'type'=>$list_obj->post_type, 'guid'=>$list_obj->guid);
		
		$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
		
		echo '<li class="ab-dir ab-new" data-id="'.$dir_id.'" data-resource='.base64_encode($dir_id).'" data-linked="'.$list['content'].'" data-guid="'.($is_shortcut?$list['guid']:'').'"><a class="folder fa fa-folder"></a><a class="dtitle" title="'.__('Click here to rename', 'wp-docs').'">'.__('New Folder', 'wp-docs').'</a><span class="wpd_action_span"><a class="wpd-edit" title="'.__('Click here to edit', 'wp-docs').'"></a><span class="wpd_action_span_inner"><a class="wpd-copy" title="'.__('Click here to copy', 'wp-docs').'"></a><a class="wpd-move" title="'.__('Click here to move', 'wp-docs').'"></a></span><a class="wpd-trash" title="'.__('Click here to delete', 'wp-docs').'"></a></span></li>';
	
		exit;
	}
	
	
	if(!function_exists('wpd_get_icon_file_types')){
	
		function wpd_get_icon_file_types(){
	
			global $icon_sub_path, $wpdocs_dir;
	
			$ext_img_dir = $wpdocs_dir.$icon_sub_path;
	
			$file_types = file_exists($ext_img_dir) && is_dir($ext_img_dir) ? scandir($ext_img_dir) : array();
	
			$file_types = array_map(function ($file) use ($ext_img_dir){
	
				$ignore_array = array('.', '..');
				if(!in_array($file, $ignore_array) && !is_dir($ext_img_dir.$file)){
					return current(explode('.', $file));
				}
	
			}, $file_types);
	
			$file_types = array_filter($file_types);
	
			return $file_types;
	
		}
	}
	
	
	if(!function_exists('wpd_get_item_type_icon_url')){
	
		function wpd_get_item_type_icon_url($item){
			
			//pree($item);
	
			global $wpdocs_url, $icon_sub_path, $wpdocs_options, $wpdocs_pro;
	
			$ext_img_url = $wpdocs_url.$icon_sub_path;
			$file_types = wpd_get_icon_file_types();
			
			$file_url = wp_get_attachment_url($item);
			//pree($file_url);exit;
			
			$encrypted = get_post_meta($item, 'encrypted', true);
			
			$filename = basename($file_url);
			$filename = explode('?', $filename);
			$filename = (is_array($filename)?current($filename):$filename);
			
			
			$ext = explode('.', $filename);
			$ext = end($ext);
			$icon = in_array($ext, $file_types) ? $ext.'.png' : 'unknown.png';
			$icon_url =  $ext_img_url.$icon;
			
			switch($ext){
				case 'svg':
				case 'gif':
				case 'bmp':
				case 'jpg':
				case 'jpeg':
				case 'png':
					$thumb_image = array_key_exists('thumb_image', $wpdocs_options);
					
					if($thumb_image){
						$icon_urls = wp_get_attachment_image_src($item, 'thumbnail', false);
						if(!empty($icon_urls)){
							$icon_url = current($icon_urls);
						}
					}
					//pree($icon_urls);
				break;
			}
				
				
			
	
			return array(
					'file_id' => $item,
					'file_url' => $file_url,
					'ext' => $ext,
					'filename' => $filename,
					'title' => $filename,
					'icon_url' => $icon_url,
					'encrypted' => ($encrypted?true:false)
			);
	
	
		}
	}
	
	add_action('wp_ajax_wpdocs_add_files', 'wpdocs_add_files');
	
	function wpdocs_add_files(){

		$nonce = sanitize_wpdocs_data(wp_unslash($_POST['nonce']));
		
		if ( ! wp_verify_nonce( $nonce, 'wpdocs_update_options_nonce' ) )
		die (__("Sorry, your nonce did not verify.", 'wp-docs'));
		
				
		$dir_id = sanitize_wpdocs_data($_POST['dir_id']);
		$files = sanitize_wpdocs_data($_POST['files']);
		$files = is_array($files) ? $files : array($files);
	
		//delete_post_meta($dir_id, 'wpdocs_items');
		//pree($files);exit;
		$files_list = wpdocs_update_files_meta($dir_id, $files);
		
		
		
		$ret = '';
	
		if(!empty($files)){
			$files_list = wpdocs_list_added_items($dir_id);//, $files_list);
			
			$ret = $files_list;
		}
			
		echo $ret;
		exit;
	}
	function wpdocs_list_added_items($dir, $dir_items=array())
	{
	
		global $wpdocs_options;
	
		$wp_get_upload_dir = wp_get_upload_dir();
		$wp_uploads_path = $wp_get_upload_dir['basedir'];
		$wp_uploads_url = $wp_get_upload_dir['baseurl'];
	
	
		//pree(is_array($dir_items));exit;
		$wpdocs_items = ((is_array($dir_items) && !empty($dir_items))?$dir_items:wpdocs_added_items($dir)); 
		//pree($wpdocs_items);exit;
		
		$files_list = array();
		$pdf_thumb_selected = (array_key_exists('pdf_thumb', $wpdocs_options)?$wpdocs_options['pdf_thumb']:'default');
	
		if (!empty($wpdocs_items)) {
			//pree('$wpdocs_items');
	
			foreach ($wpdocs_items as $item) {
				$class = '';
	
				$item_data = wpd_get_item_type_icon_url($item);
				extract($item_data);
				
				//pree($item_data);
				//pree($wp_uploads_path);exit;
	
				$icon_str = '<img src="'.$icon_url.'" style="">';
				
				switch ($ext) {
					case 'png':
					case 'jpg':
					case 'jpeg':
					case 'gif':
					case 'bmp':
	
						//$class .= 'fa-image';
	
					break;
					
					case 'pdf':
					
						if($pdf_thumb_selected=='first_page_as_thumbnail'){
						
							$pdf_file = str_replace($wp_uploads_url, $wp_uploads_path, $item_data['file_url']);
							
							if(file_exists($pdf_file) && class_exists('imagick')){
									
									//pree($item_data);
									//$icon_str = '<object style="width: 100px; height: 100px" data="'.$item_data['file_url'].'#page=1" type="application/pdf"></object>';
									
									$file_url_thumb = str_replace('.pdf', '.png', strtolower($item_data['file_url']));
									$file_url_thumb_path = str_replace($wp_uploads_url, $wp_uploads_path, $file_url_thumb);
									
									if(!file_exists($file_url_thumb_path)){
									
										$im = new imagick($item_data['file_url']);
										$im->setIteratorIndex(0);
										$im->setImageFormat('png');
										$im->writeImage($file_url_thumb_path);
										
									}
									
									$icon_str = '<img src="'.$file_url_thumb.'" style="">';
				
							}
					
						}
						
					break;
	
					default:
						//$class .= 'fa-file';
					break;
				}
				$class = '';
				$files_list[$title] = '<li data-id="' . $item . '" data-dir="'.$dir. '" title="'.esc_attr($filename).'">
									<a href="' . $file_url . '" target="_blank" class="file  ' . $class . '"> '.$icon_str.' </a>
									<a class="ftitle" title="' . $title . '">' . $title . '</a>
									<span class="wpd_action_span">
									<a href="upload.php?item='.$item.'" target="_blank" class="wpd-edit" title="'.__('Click here to edit', 'wp-docs').'"></a>
									<span class="wpd_action_span_inner">
										<a class="wpd-copy" title="'.__('Click here to copy', 'wp-docs').'"></a>
										<a class="wpd-move" title="'.__('Click here to move', 'wp-docs').'"></a>
									</span>
									
									<a href="upload.php?search='.esc_attr($filename).'" target="_blank" class="wpd-trash" title="'.__('Click here to delete', 'wp-docs').'"></a>
									</span>
								</li>';
			}
		}
	
		ksort($files_list);
	
		return implode('', $files_list);
	}
	
	
	if(!function_exists('wpdocs_get_breadcrumb_array')){
		function wpdocs_get_breadcrumb_array($dir, $breadcrumb=true){
	
			$breadcrumb_array = array();
	
			if ($breadcrumb && $dir) {
				$dir_id = $dir;
	
				$dir_parent = wp_get_post_parent_id($dir_id);
				array_push($breadcrumb_array, $dir_id);
				array_push($breadcrumb_array, $dir_parent);
	
	
				if ($dir_parent != 0) {
					do {
	
						$dir_parent = wp_get_post_parent_id($dir_parent);
						array_push($breadcrumb_array, $dir_parent);
					} while ($dir_parent > 0);
				}
			}
	
	
			return $breadcrumb_array;
	
		}
	}
	
	if(!function_exists('wpdocs_list_population')){
		function wpdocs_list_population($list=array(), $file_data=array(), $file_list_row='', $default_orderby='', $default_order=''){
			$filename = $file_data['filename'];
			$list[$filename] = $file_list_row;
			return $list;
		}
	}
		
	if(!function_exists('wpdocs_sorting')){
		function wpdocs_sorting($list, $order=''){
			ksort($list);
			return $list;
		}
	}
	
	add_shortcode('wpdocs', 'wpdocs_front_list');

	function wpdocs_front_list($atts=array())
	{
		
		if ( 
			!empty($_POST) && !wp_doing_ajax() && 
			(
				! isset( $_POST['wpdocs_front_list_nonce'] ) 
				
				|| 
				
				(isset( $_POST['wpdocs_front_list_nonce'] ) && ! wp_verify_nonce( sanitize_wpdocs_data(wp_unslash($_POST['wpdocs_front_list_nonce_field'])), 'wpdocs_front_list_nonce' ) )
			)
		) {
		
		   print _e('Sorry, your nonce did not verify.', 'wp-docs');
		   exit;
		
		} else {
			
		}
					
		
						
		   				
		//pree($atts);
		ob_start();
		global $wpdocs_url, $wpdocs_options, $wpdocs_pro, $wpdocs_post_types, $pdf_thumb_selected;
		
		$pdf_thumb_selected = (array_key_exists('pdf_thumb', $wpdocs_options)?$wpdocs_options['pdf_thumb']:'default');
		
		$wpdocs_view = get_option('wpdocs_view', array());
		$wpdocs_view = is_array($wpdocs_view) ? $wpdocs_view : array();

		$is_bootstrap = array_key_exists('bootstrap', $wpdocs_options);
		$is_searchbox = array_key_exists('searchbox', $wpdocs_options);
	
		$details_date = array_key_exists('details_date', $wpdocs_options);
        $details_date_created = array_key_exists('details_date_created', $wpdocs_options);
        $details_view_sorting = array_key_exists('details_view_sorting', $wpdocs_options);
		$ajax_based_deep_search = ($wpdocs_pro && array_key_exists('ajax_based_deep_search', $wpdocs_options));
		$details_type = array_key_exists('details_type', $wpdocs_options);
		$details_size = array_key_exists('details_size', $wpdocs_options);			
		
		$customize_icon_size = array_key_exists('icon_size', $wpdocs_options) ? $wpdocs_options['icon_size'] : '';	
		$customize_icon_size = ($customize_icon_size?$customize_icon_size: 'font-size:100%'); 		
		$customize_icon_size = explode(':', $customize_icon_size);
		$customize_icon_size = 'font-size:'.end($customize_icon_size);
		
		$customize_font_size = array_key_exists('font_size', $wpdocs_options) ? $wpdocs_options['font_size'] : '';	 		
		$customize_font_size = ($customize_font_size?$customize_font_size: 'font-size:90%');
		$customize_font_size = explode(':', $customize_font_size);
		$customize_font_size = 'font-size:'.end($customize_font_size);		
		
		$wp_get_upload_dir = wp_get_upload_dir();
		$wp_uploads_path = $wp_get_upload_dir['basedir'];
		
		$wp_uploads_url = home_url('wp-content/uploads');
		
	
		if(isset($_POST['wpd_dir_id_ajax'])){
	
			$dir = ((isset($_POST['wpd_dir_id_ajax']) && wpdocs_folder_exists($_POST['wpd_dir_id_ajax'])) ? $_POST['wpd_dir_id_ajax'] : 0);
			if($_POST['wpd_dir_id_ajax'] == 0 && $_POST['wpd_home_id'] != 0){
				$dir = sanitize_wpdocs_data($_POST['wpd_home_id']);
			}
			$get_permalink = isset($_POST['wpd_get_permalink']) ? $_POST['wpd_get_permalink'] : '' ;
	
		}else{
	
			$dir = ((isset($_GET['dir']) && is_numeric($_GET['dir']) && wpdocs_folder_exists($_GET['dir'])) ? sanitize_wpdocs_data($_GET['dir']) : 0);
			$get_permalink = get_permalink();
		}
		
	
	
	
		$dir = ($dir?$dir:((isset($atts['dir']) && $atts['dir']!='' && is_numeric($atts['dir']) && wpdocs_folder_exists($atts['dir']))?sanitize_wpdocs_data($atts['dir']):$dir));
		$no_breadcrumb = (isset($atts['breadcrumb']) && $atts['breadcrumb']=='false');
		$default_view = (isset($atts['view']) ? $atts['view'] : 'list');
		$default_orderby = (isset($atts['orderby']) ? $atts['orderby'] : 'title');
		$default_order = (isset($atts['order']) ? $atts['order'] : 'ASC');
		
		
		
		switch($default_view){
			default:
			case 'list':
				$default_view = 'list_view';
			break;

			case 'icons':
				$default_view = 'large_icon_view';
			break;

			case 'details':
				$default_view = 'detail_view';
			break;
			
		}


	
		if(isset($_POST['wpd_home_id'])){
	
			$home_id = sanitize_wpdocs_data($_POST['wpd_home_id']);
	
		}elseif(isset($atts['dir'])){
			$home_id = sanitize_wpdocs_data($atts['dir']);
		}else{
			$home_id = 0;
		}
		
		$home_id = preg_replace("/[\'\"\"\"]+/", '', $home_id);
		
		$wpdoc_valid = true;
		
		$wpdocs_security = get_post_meta($dir, 'wpdocs_security', true);
		//pree($wpdocs_security);
		$roles = array();
		if($wpdocs_security!=''){
			if( is_user_logged_in() ) {
				$user = wp_get_current_user();
				$roles = ( array ) $user->roles;			
				//pree($roles);
				$wpdoc_valid = (in_array($wpdocs_security, $roles) || current_user_can('administrator'));
			}else{
				$wpdoc_valid = false;
			}
			
		}
		
		//pree($wpdoc_valid);exit;
		
		$breadcrumb_array = wpdocs_get_breadcrumb_array($dir, !$no_breadcrumb);
		//pree($breadcrumb_array);
		//pree($wpdoc_valid);
		$warning_msg = '';
		if(!$wpdoc_valid){
			$dir = time()*time();
			$warning_msg = '<div class="alert alert-warning fade in alert-dismissible show w-50 mx-auto">
					 <button type="button" class="close" data-dismiss="alert" aria-label="'.__('Close', 'wp-docs').'">
						<span aria-hidden="true" style="font-size:20px">×</span>
					  </button>    <strong>'.__('Sorry', 'wp-docs').'!</strong> '.__('You are not allowed to access this content.', 'wp-docs').'
					</div>';
		}
		
	
		//pree($dir.' ~ '.$no_breadcrumb);
	   
	
	
		if(is_array($breadcrumb_array) && !empty($breadcrumb_array)){
	
	
			$array_search = array_search($home_id , $breadcrumb_array);
	
			if($array_search === 0){
	
				unset($breadcrumb_array);
	
			}else{
	
				$breadcrumb_array[$array_search] = 0;
	
			}
	
			if(!empty($breadcrumb_array)){
	
				foreach ($breadcrumb_array as $index => $bread){
	
					if($index > $array_search){
						unset($breadcrumb_array[$index]);
					}
				}
			}
		}
		
		$wpdocs_list = wpdocs_list($dir, $default_orderby, $default_order);

		$files_list = wpdocs_added_items($dir);
		
		//pree($files_list);
		
		$wpdocs_list_merged_arr = array();
		
		$deep_files_list_arr = array();
		
		
		
		//pree($wpdocs_list);
		//pree($wpdocs_list_merged_arr);
		//pree($deep_files_list_arr);
		//pree($files_list);

		$is_file = wpdocs_dir_options_by_name('file_upload', false, $dir);
		$is_del_from_front = (is_user_logged_in() && wpdocs_dir_options_by_name('del_from_front', false, $dir));
		$is_current_user_files = wpdocs_dir_options_by_name('current_user_files', false, $dir);
		


		if($is_file && $is_current_user_files){
            $files_list = wpdocs_added_items_by_user($dir);
			
			//pree($files_list);
        }
		
		
		if(!$is_current_user_files && $ajax_based_deep_search && function_exists('get_deep_files_list_arr')){
			
			//pree($wpdocs_list);
			
			$deep_list_arr = get_deep_files_list_arr($wpdocs_list);
			$wpdocs_list_merged_arr = $deep_list_arr['wpdocs_list_merged_arr'];
			$deep_files_list_arr = $deep_list_arr['deep_files_list_arr'];
			
		}
	
?>

	
			<div class="container-fluid wpdoc_container" data-dir_restrictions = "<?php echo wpdocs_get_dir_restrictions($dir, 'base64'); ?>" data-del_from_front="<?php echo $is_del_from_front; ?>" data-dir="<?php echo $dir; ?>" data-home="<?php echo $home_id; ?>">
				<?php wp_nonce_field( 'wpdocs_front_list_nonce', 'wpdocs_front_list_nonce_field' ); ?>
				<input type="hidden" class="wpd_home_id" value="<?php echo esc_html($home_id); ?>" />
				<input type="hidden" class="wpd_del_file_id" value="" />
<?php

				$wpdocs_view = array_key_exists($home_id, $wpdocs_view) ? $wpdocs_view[$home_id] : trim($default_view);

?>
	
				<div class="card mt-3">
					<!-- breadcrumb Area -->
					<nav aria-label="breadcrumb" class="wpdocs-nav position-relative">
						<ol class="breadcrumb bg-light" style="border-bottom:1px solid #dee2e6;border-radius: 0; min-height: 40px;">

                            <?php if (!empty($breadcrumb_array)) { ?>

                                <li class="breadcrumb-item bread_home_url"><a class="wpd_bread_item" href="<?php echo $get_permalink ?>" data-id="0"><?php _e('Home', 'wp-docs'); ?></a></li>
							<?php
								
									foreach (array_reverse($breadcrumb_array) as $bread_key => $bread_value) {
										$active = '';
										$page = '';
										$permalink = stripos($get_permalink, '?');
										$permalink_c = ($permalink!='' && is_numeric($permalink) && $permalink>=0);
										$link = '<a class="wpd_bread_item" href="' . $get_permalink . ($permalink_c?'&':'?').'dir=' . $bread_value . '" data-id="'.$bread_value.'" >' . get_the_title($bread_value) . '</a>';
										if ($bread_value == 0) {
											continue;
										}
										if ($bread_value == $dir) {
											$active = 'active';
											$page = 'page';
											$link = get_the_title($bread_value);
										}
	
	
										?>
									<li class="breadcrumb-item <?php echo $active ?>" aria-current="<?php echo $page; ?>"><?php echo $link; ?></li>

                                    <?php } ?>
							<?php
									}
								
								?>
	
						</ol>

                        <?php if($is_del_from_front):?>

                            <i style="opacity: 0.5;" class="fa fa-trash fa-1x position-absolute wp_docs_del_file <?php echo (is_user_logged_in()?'logged_in':'logged_out'); ?>" title="<?php _e('Click here to delete selected files', 'wp-docs'); ?>"></i>

                        <?php endif; ?>

						<?php if($wpdocs_pro && $dir != 0 && wpdocs_can_current_user_upload_file($dir) && $is_file):?>
	
                            <i class="fa fa-upload fa-1x wpdocs-front-add-media position-absolute" id="wpdocs_front_file_add_<?php echo $dir; ?>" title="<?php _e('Click here to add files', 'wp-docs'); ?>"></i>
	
						<?php endif; ?>
					</nav>
<?php
/*
			$current_user_id = get_current_user_id();
		
			pree($current_user_id);
			
			
			$args = array(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'title',
				'order'            => 'ASC',
				'include'          => array($dir),
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'wpdocs_folder',
				'post_mime_type'   => '',
				'post_parent'      => '',//$post_parent,
				'author'	   => $current_user_id,
				'author_name'	   => '',
				'post_status'      => 'hidden',
				'suppress_filters' => true
			);
			$posts_array = get_posts($args);
			pree($posts_array);	
*/					
?>			
					
					<?php echo $warning_msg?'<div class="card-body">'.$warning_msg.'</div>':''; ?>
                    
                    <?php if($is_searchbox || $ajax_based_deep_search): ?>
                    <div class="wpdocs-searchbox">
                    	<input type="text" placeholder="<?php echo ($ajax_based_deep_search?__('Type here to search...', 'wp-docs'):__('Type here to filter...', 'wp-docs')); ?>" />
                    </div>
                    <?php endif; ?>
	
					<div class="card-body <?php echo $warning_msg?'d-none':''; ?>">
	
						<!-- Large Icon View Area -->
						<div class="row folder_view large_icon_view <?php echo $wpdocs_view=='large_icon_view'?'':'d-none'; ?>">
							<?php
								
								//pree($wpdocs_list);
								
								$no_dir_found = false;
								$no_file_found = false;
								if (!empty($wpdocs_list)) {
									foreach ($wpdocs_list as $list) {
										//$wpdocs_child_items = wpdocs_list($list['id']);
										//$wpdocs_child_files_list = wpdocs_added_items($list['id']);
										$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
?>
	
									<div class="col-4 col-md-3 file_wrapper is_dir" style="cursor: pointer;" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['link']:''; ?>">
										<figure class="figure file_view p-0">									
											<span class="fa fa-folder text-warning" style="<?php echo $customize_icon_size; ?>"></span>
											<figcaption class="figure-caption text-center" style="<?php echo $customize_font_size; ?>"><?php echo $list['title']; ?></figcaption>
										</figure>
									</div>
<?php
										}
									} else {
										$no_dir_found = true;
									}
									
									//pree($wpdocs_list_merged_arr);
									
									if (!empty($wpdocs_list_merged_arr)) {
										
										foreach ($wpdocs_list_merged_arr as $wpdocs_merged_list) {
											
											foreach ($wpdocs_merged_list as $list_obj) {
												
												$list = array('id'=>$list_obj->ID, 'content'=>$list_obj->post_content, 'title'=>$list_obj->post_title, 'type'=>$list_obj->post_type, 'guid'=>$list_obj->guid);
												$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
												
?>
									<div class="col-4 col-md-3 file_wrapper is_dir is_deep" style="cursor: pointer;" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['guid']:''; ?>">
										<figure class="figure file_view p-0">									
											<span class="fa fa-folder text-warning" style="<?php echo $customize_icon_size; ?>"></span>
											<figcaption class="figure-caption text-center" style="<?php echo $customize_font_size; ?>"><?php echo $list['title']; ?></figcaption>
										</figure>
									</div>
<?php
											}
										}
									}
									
									//pree($files_list);
	
	
									if (!empty($files_list)) {
										$list = array();
										foreach ($files_list as $file) {
	
											$file_data = wpd_get_item_type_icon_url($file);
											extract($file_data);
											
											//pree($file_data);exit;
											
											if(trim($file_url)){
												
												switch ($ext) {
													case 'png':
													case 'jpg':
													case 'jpeg':
													case 'gif':
													case 'bmp':
									
														//$class .= 'fa-image';
									
													break;
													
													case 'pdf':
													
														if($pdf_thumb_selected=='first_page_as_thumbnail'){
														
															$pdf_file = str_replace($wp_uploads_url, $wp_uploads_path, $file_data['file_url']);
															
															if(file_exists($pdf_file) && class_exists('imagick')){
																	
																	//pree($file_data);
																	//$icon_str = '<object style="width: 100px; height: 100px" data="'.$file_data['file_url'].'#page=1" type="application/pdf"></object>';
																	
																	$file_url_thumb = str_replace('.pdf', '.png', strtolower($file_data['file_url']));
																	$file_url_thumb_path = str_replace($wp_uploads_url, $wp_uploads_path, $file_url_thumb);
																	
																	if(!file_exists($file_url_thumb_path)){
																	
																		$im = new imagick($file_data['file_url']);
																		$im->setIteratorIndex(0);
																		$im->setImageFormat('png');
																		$im->writeImage($file_url_thumb_path);
																		
																	}
																	
																	$icon_url = $file_url_thumb;
												
															}
													
														}
														
													break;
									
													default:
														//$class .= 'fa-file';
													break;
												}
												
												$encrypted_path = wpdocs_encrypted_path($file_id, $encrypted);
												
												$href_attrib = (!$encrypted_path?' href="'.$file_url.'" target="_blank" ':' data-hash="'.$encrypted_path.'" ');
											
												$file_list_row = '
	
																	
															<div title="'.esc_attr($filename).'" class="col-4 col-md-3 is_file text-center is_shallow" style="cursor: pointer;" data-id="'.$file.'">
																<figure class="figure file_view p-1">
																	<a '.$href_attrib.' class="file fv1 '.($encrypted?'encrypted-link':'').'" ><img class="my-3" src="'.$icon_url.'" /></a>
																	<figcaption class="figure-caption text-center">'.$title.'</figcaption>
																</figure>
															</div>
															
															
															';
												$list = wpdocs_list_population($list, $file_data, $file_list_row, $default_orderby);				
									
											}
							
										}
										//pree(array_keys($list));
	
										//ksort($list);
										$list = wpdocs_sorting($list, $default_order);
										//pree(array_keys($list));
										echo implode('', $list);
									} else {
										$no_file_found = true;
									}
									
									if (!empty($deep_files_list_arr)) {
										$list = array();
										foreach ($deep_files_list_arr as $file) {
	
											$file_data = wpd_get_item_type_icon_url($file);
											extract($file_data);
											
											if(trim($file_url)){
												
												
												
												switch ($ext) {
													case 'png':
													case 'jpg':
													case 'jpeg':
													case 'gif':
													case 'bmp':
									
														//$class .= 'fa-image';
									
													break;
													
													case 'pdf':
														
														if($pdf_thumb_selected=='first_page_as_thumbnail'){
															
															$pdf_file = str_replace($wp_uploads_url, $wp_uploads_path, $file_data['file_url']);
															
															if(file_exists($pdf_file) && class_exists('imagick')){
																	
																	//pree($file_data);
																	//$icon_str = '<object style="width: 100px; height: 100px" data="'.$file_data['file_url'].'#page=1" type="application/pdf"></object>';
																	
																	$file_url_thumb = str_replace('.pdf', '.png', strtolower($file_data['file_url']));
																	$file_url_thumb_path = str_replace($wp_uploads_url, $wp_uploads_path, $file_url_thumb);
																	
																	if(!file_exists($file_url_thumb_path)){
																	
																		$im = new imagick($file_data['file_url']);
																		$im->setIteratorIndex(0);
																		$im->setImageFormat('png');
																		$im->writeImage($file_url_thumb_path);
																		
																	}
																	
																	$icon_url = $file_url_thumb;
												
															}
														
														}
														
													break;
									
													default:
														//$class .= 'fa-file';
													break;
												}
												
												$encrypted_path = wpdocs_encrypted_path($file_id, $encrypted);
												
												$href_attrib = (!$encrypted_path?' href="'.$file_url.'" target="_blank" ':' data-hash="'.$encrypted_path.'" ');
											
												$file_list_row = '
	
																	
															<div title="'.esc_attr($filename).'" class="col-4 col-md-3 is_file text-center is_deep" style="cursor: pointer;" data-id="'.$file.'">
																<figure class="figure file_view p-1">
																	<a '.$href_attrib.' class="file fv2 '.($encrypted?'encrypted-link':'').'" ><img class="my-3" src="'.$icon_url.'" /></a>
																	<figcaption class="figure-caption text-center">'.$title.'</figcaption>
																</figure>
															</div>
															
															
															';
												$list = wpdocs_list_population($list, $file_data, $file_list_row, $default_orderby);				
									
											}
							
										}
										//pree(array_keys($list));
	
										//ksort($list);
										$list = wpdocs_sorting($list, $default_order);
										//pree(array_keys($list));
										echo implode('', $list);
									}
									
									
	
									if ($no_dir_found && $no_file_found) {
	
										?>
								<div class="alert alert-info text-center mx-auto empty-dir-files">
									<strong><?php _e('Info!', 'wp-docs'); ?></strong> <?php _e('Empty Directory.', 'wp-docs'); ?>
								</div>
							<?php } ?>
	
	
						</div>
	
						<!-- List View Area -->
						<div class="row folder_view list_view <?php echo $wpdocs_view=='list_view'?'':'d-none'; ?>">
							<?php
								$no_dir_found = false;
								$no_file_found = false;
								if (!empty($wpdocs_list)) {
									foreach ($wpdocs_list as $list) { 
										$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
										?>
	
									<div class="col-12 file_wrapper is_dir" style="cursor: pointer;" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['link']:''; ?>">
										<figure class="figure file_view p-2">
											<span class="fa fa-folder text-warning" style="font-size:25px"></span>
											<small class="text-center"><?php echo $list['title']; ?></small>
										</figure>
									</div>
								<?php
										}
									} else {
										$no_dir_found = true;
									}
									
									if (!empty($wpdocs_list_merged_arr)) {
										
										foreach ($wpdocs_list_merged_arr as $wpdocs_merged_list) {
											
											foreach ($wpdocs_merged_list as $list_obj) {
												
												$list = array('id'=>$list_obj->ID, 'content'=>$list_obj->post_content, 'title'=>$list_obj->post_title, 'type'=>$list_obj->post_type, 'guid'=>$list_obj->guid);
												$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
												
?>
									<div class="col-12 file_wrapper is_dir is_deep" style="cursor: pointer;" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['guid']:''; ?>">
										<figure class="figure file_view p-2">
											<span class="fa fa-folder text-warning" style="font-size:25px"></span>
											<small class="text-center"><?php echo $list['title']; ?></small>
										</figure>
									</div>
<?php
											}
										}
									}
	

									if (!empty($files_list)) {
										$list = array();
										foreach ($files_list as $file) {
	
											$file_data = wpd_get_item_type_icon_url($file);
											extract($file_data);
											
											
											if(trim($file_url)){
												
												$encrypted_path = wpdocs_encrypted_path($file_id, $encrypted);
												
												$href_attrib = (!$encrypted_path?' data-url="'.$file_url.'" target="_blank" ':' data-hash="'.$encrypted_path.'" ');
											
												$file_list_row = '
																<div '.$href_attrib.' title="'.esc_attr($filename).'" class="col-12 file_wrapper is_file '.($encrypted?'encrypted-link':'').'" style="cursor: pointer;" data-id="'.$file.'">
																	<figure class="figure file_view p-3">
																		<a class="file fv3" ><img class="mb-2" src="'.$icon_url.'" style="width: 25px; height: 25px"></a>
																		<small class="text-center">'.$title.'</small>
																	</figure>
																</div>';
																
												$list = wpdocs_list_population($list, $file_data, $file_list_row, $default_orderby);																
									
											}
	
										}
										//pree($list);
										//ksort($list);
										$list = wpdocs_sorting($list, $default_order);
										echo implode('', $list);
									} else {
										$no_file_found = true;
									}
									
									if (!empty($deep_files_list_arr)) {
										$list = array();
										foreach ($deep_files_list_arr as $file) {
	
											$file_data = wpd_get_item_type_icon_url($file);
											extract($file_data);
											
											
											if(trim($file_url)){
												
												$encrypted_path = wpdocs_encrypted_path($file_id, $encrypted);
												
												$href_attrib = (!$encrypted_path?' href="'.$file_url.'" target="_blank" ':' data-hash="'.$encrypted_path.'" ');
											
												$file_list_row = '
																<div title="'.esc_attr($filename).'" class="col-12 file_wrapper is_file is_deep" style="cursor: pointer;" data-id="'.$file.'">
																	<figure class="figure file_view p-3">
																		<a '.$href_attrib.' class="file fv4 '.($encrypted?'encrypted-link':'').'" ><img class="mb-2" src="'.$icon_url.'" style="width: 25px; height: 25px"></a>
																		<small class="text-center">'.$title.'</small>
																	</figure>
																</div>';
																
												$list = wpdocs_list_population($list, $file_data, $file_list_row, $default_orderby);																
									
											}
	
										}
										//pree($list);
										//ksort($list);
										$list = wpdocs_sorting($list, $default_order);
										echo implode('', $list);
									}
									
									
	
									if ($no_dir_found && $no_file_found) {
	
										?>
								<div class="alert alert-info text-center mx-auto empty-dir-files">
									<strong><?php _e('Info!', 'wp-docs'); ?></strong> <?php _e('Empty Directory.', 'wp-docs'); ?>
								</div>
							<?php } ?>
	
	
						</div>
	<?php
	
	?>
                        <?php

                            $d_v_caret = $wpdocs_pro && $details_view_sorting ? '<i class="fa fa-docs-sort" aria-hidden="true"></i>' : '';
							
					


                        ?>    
						<!-- Detail View Area -->
						<div class="row folder_view detail_view mt-0 <?php echo $wpdocs_view=='detail_view'?'':'d-none'; ?>">
							<div class="table-responsive" style="zoom:70%">
								<table class="table">
									<thead class="thead">
										<tr>
											<th><?php _e('Name', 'wp-docs'); ?> <?php echo $d_v_caret; ?></th>
	<?php if($details_date_created): ?>		<th><?php _e('Created Date', 'wp-docs'); ?> <?php echo $d_v_caret; ?></th><?php endif; ?>
	<?php if($details_date): ?>				<th><?php _e('Modified Date', 'wp-docs'); ?> <?php echo $d_v_caret; ?></th><?php endif; ?>
	<?php if($details_type): ?>				<th><?php _e('Type', 'wp-docs'); ?> <?php echo $d_v_caret; ?></th><?php endif; ?>
	<?php if($details_size): ?>				<th><?php _e('Size', 'wp-docs'); ?> <?php echo $d_v_caret; ?></th><?php endif; ?>    
										</tr>
									</thead>
									<?php
										$no_dir_found = false;
										$no_file_found = false;
										if (!empty($wpdocs_list)) {
											foreach ($wpdocs_list as $list) {
												$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
												?>
											<tr title="<?php echo $list['id']; ?>" class="file_wrapper file_view is_dir" style="cursor: pointer;" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['link']:''; ?>">
												<td>
													<figure class="figure ">
														<span class="fa fa-folder text-warning" style="font-size:25px"></span>
														<small class="text-center mb-1"><?php echo $list['title']; ?></small>
													</figure>
												</td>
												
	<?php if($details_date_created): ?>			<td data-time="<?php get_post_time('U', false, $list['id']) ?>"><small><?php echo get_the_date(get_option( 'date_format' ), $list['id']) . ' ' . get_the_time(get_option( 'time_format' ), $list['id']) ?></small></td><?php endif; ?>
	<?php if($details_date): ?>					<td data-time="<?php get_post_modified_time('U', false, $list['id']) ?>"><small><?php echo get_the_modified_date(get_option( 'date_format' ), $list['id']) . ' ' . get_the_modified_time(get_option( 'time_format' ), $list['id']) ?></small></td><?php endif; ?>
	<?php if($details_type): ?>					<td><small><?php 
	
	
	$directory_post_type = get_post_type($list['id']);

	
	if($wpdocs_post_types['shortcut']==$directory_post_type){
		echo '<span style="color: #06C;" class="fa fa-link"></span> '.__('Shortcut', 'wp-docs');
	}elseif($wpdocs_post_types['dir']==$directory_post_type){
		echo __('Directory', 'wp-docs');
	}else{
		echo $directory_post_type;
	}
	
	
	?></small></td><?php endif; ?>
	<?php if($details_size): ?>					<td><small></small></td><?php endif; ?>
											</tr>
										<?php
												}
											} else {
												$no_dir_found = true;
											}
											
											if (!empty($wpdocs_list_merged_arr)) {
										
										foreach ($wpdocs_list_merged_arr as $wpdocs_merged_list) {
											
											foreach ($wpdocs_merged_list as $list_obj) {
												
												$list = array('id'=>$list_obj->ID, 'content'=>$list_obj->post_content, 'title'=>$list_obj->post_title, 'type'=>$list_obj->post_type, 'guid'=>$list_obj->guid);
												$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
												
?>
									<tr title="<?php echo $list['id']; ?>" class="file_wrapper file_view is_dir is_deep" style="cursor: pointer;" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['guid']:''; ?>">
												<td>
													<figure class="figure ">
														<span class="fa fa-folder text-warning" style="font-size:25px"></span>
														<small class="text-center mb-1"><?php echo $list['title']; ?></small>
													</figure>
												</td>
												
	<?php if($details_date_created): ?>			<td data-time="<?php get_post_time('U', false, $list['id']) ?>"><small><?php echo get_the_date(get_option( 'date_format' ), $list['id']) . ' ' . get_the_time(get_option( 'time_format' ), $list['id']) ?></small></td><?php endif; ?>
	<?php if($details_date): ?>					<td data-time="<?php get_post_modified_time('U', false, $list['id']) ?>"><small><?php echo get_the_modified_date(get_option( 'date_format' ), $list['id']) . ' ' . get_the_modified_time(get_option( 'time_format' ), $list['id']) ?></small></td><?php endif; ?>
	<?php if($details_type): ?>					<td><small><?php 
	
	
	$directory_post_type = get_post_type($list['id']);
	
	if($wpdocs_post_types['shortcut']==$directory_post_type){
		echo 'Directory Shortcut';
	}elseif($wpdocs_post_types['dir']==$directory_post_type){
		echo 'Directory';
	}else{
		echo $directory_post_type;
	}
	
	
	
	
	?></small></td><?php endif; ?>
	<?php if($details_size): ?>					<td><small></small></td><?php endif; ?>
											</tr>
<?php
											}
										}
									}
	
	
											if (!empty($files_list)) {
												$list = array();
												foreach ($files_list as $file) {
													
													//pree($file);exit;
	
													$file_data = wpd_get_item_type_icon_url($file);
													extract($file_data);
													//pree($ts);
													
													if(trim($icon_url)){
														
													$encrypted_path = wpdocs_encrypted_path($file_id, $encrypted);
												
													$href_attrib = (!$encrypted_path?' data-url="'.$file_url.'" ':' data-hash="'.$encrypted_path.'" ');	
													
													$files_list_row = '
													<tr '.$href_attrib.' title="'.esc_attr($filename).'" class="file_view file_link is_file '.($encrypted?'encrypted-link':'').'" style="cursor: pointer;" data-id="'.$file.'">

														<td>

															<figure class="figure file_view">
																<span class="file"><img class="mb-2" src="'.$icon_url.'" style="width: 25px; height: 25px"></span>
																<small class="text-center">'.$title.'</small>
															</figure>
														</td>
													';
													
													}
													
													$created_time_u = get_post_time('U', false, $file);
													$modified_time_u = get_post_modified_time('U', false, $file);
													
													$file_path = str_replace($wp_uploads_url, $wp_uploads_path, $file_url);
													
                                                    $file_size = round(@filesize($file_path) / 1024);
													if($details_date_created): $files_list_row .= '<td data-time="'.$created_time_u.'"><small>'.get_the_date(get_option( 'date_format' ), $file) . ' ' . get_the_time(get_option( 'time_format' ), $file).'</small></td>'; endif;
													if($details_date): $files_list_row .= '<td data-time="'.$modified_time_u.'"><small>'.get_the_modified_date(get_option( 'date_format' ), $file) . ' ' . get_the_modified_time(get_option( 'time_format' ), $file).'</small></td>'; endif;
													if($details_type): $files_list_row .= '<td><small>'.get_post_mime_type($file).'</small></td>'; endif;
													if($details_size): $files_list_row .= '<td data-time="'.$file_size.'"><small>'.$file_size. ' KB'.'</small></td>'; endif;
//													data-time is used for sorting purpose on front end it will sort by data-time value if it exist if not exist than it will be sorted by td inner text

													
													$files_list_row .= '</tr>';
													
													$list = wpdocs_list_population($list, $file_data, $files_list_row, $default_orderby);
													
													
	
												}
											
												
												$list = wpdocs_sorting($list, $default_order);
												echo implode('', $list);
												
											} else {
												$no_file_found = true;
											}
											
											if (!empty($deep_files_list_arr)) {
												$list = array();
												
												//pree($wp_get_upload_dir);
												foreach ($deep_files_list_arr as $file) {
													
													//pree($file);exit;
	
													$file_data = wpd_get_item_type_icon_url($file);
													extract($file_data);
													//pree($ts);
													
													if(trim($icon_url)){
														
													$encrypted_path = wpdocs_encrypted_path($file_id, $encrypted);
												
													$href_attrib = (!$encrypted_path?' data-url="'.$file_url.'" ':' data-hash="'.$encrypted_path.'" ');	
													
													$files_list_row = '
													<tr '.$href_attrib.' title="'.esc_attr($filename).'" class="file_view file_link is_file is_deep	'.($encrypted?'encrypted-link':'').'" style="cursor: pointer;" data-id="'.$file.'">

														<td>

															<figure class="figure file_view">
																<span class="file"><img class="mb-2" src="'.$icon_url.'" style="width: 25px; height: 25px"></span>
																<small class="text-center">'.$title.'</small>
															</figure>
														</td>
													';
													
													}
													
													$created_time_u = get_post_time('U', false, $file);
													$modified_time_u = get_post_modified_time('U', false, $file);
													
													$file_path = str_replace($wp_uploads_url, $wp_uploads_path, $file_url);
													
                                                    $file_size = round(@filesize($file_path) / 1024);
													if($details_date_created): $files_list_row .= '<td data-time="'.$created_time_u.'"><small>'.get_the_date(get_option( 'date_format' ), $file) . ' ' . get_the_time(get_option( 'time_format' ), $file).'</small></td>'; endif;
													if($details_date): $files_list_row .= '<td data-time="'.$modified_time_u.'"><small>'.get_the_modified_date(get_option( 'date_format' ), $file) . ' ' . get_the_modified_time(get_option( 'time_format' ), $file).'</small></td>'; endif;
													if($details_type): $files_list_row .= '<td><small>'.get_post_mime_type($file).'</small></td>'; endif;
													if($details_size): $files_list_row .= '<td data-time="'.$file_size.'"><small>'.$file_size. ' KB'.'</small></td>'; endif;
//													data-time is used for sorting purpose on front end it will sort by data-time value if it exist if not exist than it will be sorted by td inner text

													
													$files_list_row .= '</tr>';
													
													$list = wpdocs_list_population($list, $file_data, $files_list_row, $default_orderby);
													
													
	
												}
											
												
												$list = wpdocs_sorting($list, $default_order);
												echo implode('', $list);
												
											}
											
											
	
											if ($no_dir_found && $no_file_found) {
	
												?>
										<tr>
											<td class="alert alert-info text-center mx-auto empty-dir-files" colspan="5">
												<strong><?php _e('Info!', 'wp-docs'); ?></strong> <?php _e('Empty Directory.', 'wp-docs'); ?>
											</td>
										</tr>
									<?php } ?>
								</table>
							</div>
						</div>
	
					</div>
					<div class="card-footer text-right wpdocs-views position-relative">
	
	
	
						<a data-source="large_icon_view" data-toggle="tooltip" data-placement="bottom" title="<?php _e('Thumbnails View', 'wp-docs'); ?>" class="folder_view_btn fa fa-image fa-lg text-danger mr-2"></a>
						<a data-source="list_view" data-toggle="tooltip" data-placement="bottom" title="<?php _e('List View', 'wp-docs'); ?>" class="folder_view_btn fa fa-bars fa-lg text-danger mr-2"></a>
						<a data-source="detail_view" data-toggle="tooltip" data-placement="bottom" title="<?php _e('Details Views', 'wp-docs'); ?>" class="folder_view_btn fa fa-list fa-lg text-danger mr-2"></a>
					</div>
				</div>
	<?php if($is_bootstrap): ?>
				<div class="wpdocs_loader wpd_modal d-none">
					<div class="modal_content">
						<img src="<?php echo $wpdocs_url.'img/loader.gif' ?>" width="50px" height="50px">
					</div>
				</div>
	<?php endif; ?>            
	
			</div>
	
	
	
		<?php
	
	
	
			$out1 = ob_get_contents();
	
			ob_end_clean();
			
			
			
			return $out1;
			
	}


	function wpdocs_parent_folder($id)
	{
		//pree($id);
		$parent_id = 0;
		if (wpdocs_folder_exists($id)) {
			$post_data = get_post($id);
			//pree($post_data);
			$parent_id = $post_data->post_parent;
		}
		return ($parent_id);
	}

    function wpdocs_added_items_by_user($dir_id)
    {
        $current_user_items = array();
        if(!is_user_logged_in()){return array();}



        $current_user = get_current_user_id();



        if (is_numeric($dir_id) && $dir_id > 0 && wpdocs_folder_exists($dir_id)) {
            $wpdocs_items = get_post_meta($dir_id, 'wpdocs_items_by_user', true);
            //pree($wpdocs_items);
            $wpdocs_items = is_array(maybe_unserialize($wpdocs_items)) ? maybe_unserialize($wpdocs_items) : array();

            $current_user_items = array_key_exists($current_user, $wpdocs_items) ? $wpdocs_items[$current_user] : array();
            //pree($wpdocs_items);
            //asort($wpdocs_items);
        }
        return $current_user_items;
    }

	function wpdocs_added_items($dir_id)
	{
		$wpdocs_items = array();
		if (is_numeric($dir_id) && $dir_id > 0 && wpdocs_folder_exists($dir_id)) {
			$wpdocs_items = get_post_meta($dir_id, 'wpdocs_items', true);
			//pree($wpdocs_items);
			$wpdocs_items = is_array(maybe_unserialize($wpdocs_items)) ? maybe_unserialize($wpdocs_items) : array();
			//pree($wpdocs_items);
			//asort($wpdocs_items);
		}
		return $wpdocs_items;
	}

	function wpdocs_folder_exists($ids='', $user_id=0)
	{
		//pree($id);
		//pree($ids);
		$posts_array = array();
		
		$ids = explode(',', $ids);
		$ids = array_map('trim', $ids);
		$ids = array_filter($ids, 'is_numeric');
		
		//pree($ids);exit;
		if (!empty($ids)) {
			global $wpdocs_post_types, $wpdocs_post_status;
			$ids = sanitize_wpdocs_data($ids);
			$args = array(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'title',
				'order'            => 'ASC',
				'include'          => $ids,
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => $wpdocs_post_types,
				'post_mime_type'   => '',
				'post_parent'      => '',//$post_parent,
				'author'	   => ($user_id?$user_id:''),
				'author_name'	   => '',
				'post_status'      => $wpdocs_post_status,
				'suppress_filters' => true
			);
			//pree($args);
			$posts_array = get_posts($args);
		}
		return (count($posts_array) > 0);
	}



	
	add_action('wp_ajax_wpdocs_update_folder', 'wpdocs_update_folder');
	function wpdocs_update_folder()
	{

		$nonce = sanitize_wpdocs_data(wp_unslash($_POST['nonce']));
		
		 if ( ! wp_verify_nonce( $nonce, 'wpdocs_update_options_nonce' ) )
			die (__("Sorry, your nonce did not verify.", 'wp-docs'));
				
		$dir_id = sanitize_wpdocs_data($_POST['dir_id']);
		$dir_id_compare = base64_decode(sanitize_wpdocs_data($_POST['resource_id']));
		//pree($dir_id_compare.'=='.$dir_id.' - '.wpdocs_folder_exists($dir_id));exit;
		
		$ret = array('msg'=>'');
		
		if ($dir_id>0 && $dir_id_compare==$dir_id && wpdocs_folder_exists($dir_id)) {
			
			global $wpdb, $wpdocs_post_types, $wpdocs_post_status;
			
			$my_post = array(
				'post_title'    => htmlspecialchars_decode(sanitize_wpdocs_data($_POST['new_name'])),
				'ID'  => $dir_id,
			);
			//pree($my_post);exit;
			//wp_update_post($my_post);
			$rename_query = "UPDATE $wpdb->posts SET post_title='".esc_sql($my_post['post_title'])."' WHERE ID=$dir_id AND post_type IN ('".implode("','", $wpdocs_post_types)."') AND post_status='$wpdocs_post_status'";
			//pree($rename_query);exit;
			$updated = $wpdb->query($rename_query);
			
			
			if($updated){
				$ret['msg'] = __("Successfully updated.", 'wp-docs');
			}else{
				$ret['msg'] = __("No changes are made, input seems the same as before.", 'wp-docs');
			}
		}

		echo wp_json_encode($ret);
		exit;
	}
	
	add_action('wp_ajax_wpdocs_delete_folder', 'wpdocs_delete_folder');

	function wpdocs_delete_folder()
	{
		$nonce = sanitize_wpdocs_data(wp_unslash($_POST['nonce']));
		
		 if ( ! wp_verify_nonce( $nonce, 'wpdocs_update_options_nonce' ) )
			die (__("Sorry, your nonce did not verify.", 'wp-docs'));
				
		$dir_id = sanitize_wpdocs_data($_POST['dir_id']);
		$resource_id = base64_decode(sanitize_wpdocs_data($_POST['resource_id']));
		
		if($dir_id==$resource_id){
			wpdocs_recursive_delete_folder($dir_id);
		}
		
		exit;
	}	


	
	add_action('wp_ajax_wpdocs_delete_files', 'wpdocs_delete_files');
	
	

	function wpdocs_recursive_delete_folder($dir_id){
		if ($dir_id > 0 && wpdocs_folder_exists($dir_id)) {
			$wpdocs_list = wpdocs_list($dir_id);
			if(!empty($wpdocs_list)){
				foreach($wpdocs_list as $wpdocs_item){
					//pree($wpdocs_item);
					if(is_numeric($wpdocs_item['id'])){
						wpdocs_recursive_delete_folder($wpdocs_item['id']);
					}
				}
			}
			
			if(function_exists('wp_docs_relocate_memphis_meta')){
				wp_docs_relocate_memphis_meta($dir_id);
			}			
			
			wp_delete_post($dir_id, true);
		}
		
	}
	


	if(!function_exists('wpdocs_update_files_meta')){

	    function wpdocs_update_files_meta($dir_id, $files=array()){
			
			//pree($files);
			
			$files_list = array();

            if ($dir_id > 0 && wpdocs_folder_exists($dir_id) && count($files) > 0) {

                //Items for single user
                $current_user = get_current_user_id();
                $wpdocs_items_by_user = get_post_meta($dir_id, 'wpdocs_items_by_user', true);



                $wpdocs_items_by_user = $wpdocs_items_by_user && is_array($wpdocs_items_by_user) ? $wpdocs_items_by_user: array();


                $current_user_items = array_key_exists($current_user, $wpdocs_items_by_user) ? $wpdocs_items_by_user[$current_user] : array();





                $current_user_items = array_merge($current_user_items, $files);




                $current_user_items = array_unique($current_user_items);



                $wpdocs_items_by_user[$current_user] = $current_user_items;




                //overall items

                $wpdocs_items = wpdocs_added_items($dir_id);

                $wpdocs_items = array_merge($wpdocs_items, $files);

                $wpdocs_items = array_unique($wpdocs_items);

                //pree($wpdocs_items);

                update_post_meta($dir_id, 'wpdocs_items_by_user', $wpdocs_items_by_user);
				

               update_post_meta($dir_id, 'wpdocs_items', $wpdocs_items);
			   
				if(function_exists('wpd_update_dir_roles_inner')){
					//pree($dir_id);pree($files);exit;
					$files_list = wpd_update_dir_roles_inner($dir_id, 'existing', $files);
				}
		   
            }
			
			//pree($files_list);exit;
			
			return $files_list;
        }
    }


	
	function wpdocs_delete_files()
	{

		$dir_id = sanitize_wpdocs_data($_POST['dir_id']);
		$files = sanitize_wpdocs_data($_POST['files']);
		$files = is_array($files) ? $files : array($files);
		//pree($dir_id);pree($files);exit;
		if ($dir_id > 0 && wpdocs_folder_exists($dir_id) && count($files) > 0) {

            wpdocs_del_items_by_user($dir_id, $files, get_current_user_id());


			$wpdocs_items = wpdocs_added_items($dir_id);
			//pree($wpdocs_items);
			$wpdocs_items = array_diff($wpdocs_items, $files);
			//pree($wpdocs_items);
			$wpdocs_items = array_unique($wpdocs_items);

			//pree($wpdocs_items);
			
			if(!empty($files)){
				foreach($files as $file_id){			
					update_post_meta($file_id, 'encrypted', false);
				}
			}

			update_post_meta($dir_id, 'wpdocs_items', $wpdocs_items);
		}


		exit;
	}	
	
	function wpd_admin_footer(){
		
?>
<script type="text/javascript" language="javascript">

</script>
	
<?php		
		
	}
	add_action('admin_footer', 'wpd_admin_footer');

add_action('wp_ajax_wpdocs_update_option', 'wpdocs_update_option');

if(!function_exists('wpdocs_update_option')){
    function wpdocs_update_option(){



        if(isset($_POST['wpdocs_update_option_nonce'])){

            $nonce = sanitize_wpdocs_data(wp_unslash($_POST['wpdocs_update_option_nonce']));

            $return = array(

                'option_update' => false,
                'dir_move' => false,
            );

            if ( ! wp_verify_nonce( $nonce, 'wpdocs_update_options_nonce' ) )
                die (__("Sorry, your nonce did not verify.", 'wp-docs'));

            if(isset($_POST['wpdocs_options'])){

                $wpdocs_options = isset($_POST['wpdocs_options']) ? sanitize_wpdocs_data($_POST['wpdocs_options']) : array();

				$wpdocs_dir_id = isset($_POST['wpdocs_dir_id']) ? sanitize_wpdocs_data($_POST['wpdocs_dir_id']) : 0;


                $sanitized_option = sanitize_wpdocs_data($wpdocs_options);
                $sanitized_option['allowed_role'] = $sanitized_option['allowed_role'] !== 'empty' ? $sanitized_option['allowed_role'] : array();


				if($wpdocs_dir_id == 0){

					$update = update_option('wpdocs_options', $sanitized_option);

				}else{

					$update = update_post_meta($wpdocs_dir_id, '_wpdocs_dir_options', $sanitized_option);
					$child_dir_list = wpdoc_get_dir_children($wpdocs_dir_id);
					if(!empty($child_dir_list)){
						foreach ($child_dir_list as $child_dir) {

							$update = update_post_meta($child_dir, '_wpdocs_dir_options', $sanitized_option);

							# code...
						}
					}

					

				}
            }



            if(isset($_POST['wpdocs_move_selected_dir'])){

                $wpdocs_move_selected_dir = sanitize_wpdocs_data($_POST['wpdocs_move_selected_dir']);
				$action_type = $wpdocs_move_selected_dir['action_type'];
				

                $is_file = array_key_exists('is_file', $wpdocs_move_selected_dir) ? $wpdocs_move_selected_dir['is_file']: false;
                $is_file = $is_file == 'false' ? false: true;


                if(!$is_file && array_key_exists('dir_selected', $wpdocs_move_selected_dir) &&
                    array_key_exists('dir_id', $wpdocs_move_selected_dir)){
						
					switch($action_type){
						default:
						case 'move':
						
		
							$update = wp_update_post(
								array(
									'ID' => $wpdocs_move_selected_dir['dir_selected'],
									'post_parent' => $wpdocs_move_selected_dir['dir_id']
								)
							);
							
							if($update == $wpdocs_move_selected_dir['dir_selected']){
								$return['dir_move'] = true;
							}
							
						break;
						
						case 'copy':
							$existing_dir = get_post($wpdocs_move_selected_dir['dir_selected']);
							$existing_dir = (is_object($existing_dir)?(array)$existing_dir:array());
							if(!empty($existing_dir) && array_key_exists('ID', $existing_dir) && function_exists('wpdocs_recursive_copy_folder')){
								
								wpdocs_recursive_copy_folder($wpdocs_move_selected_dir['dir_id'], $existing_dir);
								
								$return['dir_move'] = true;
								
							}
							
							
						break;
					}
                   
					
					
                }
				//exit;

                if($is_file){

                    $file_id = $wpdocs_move_selected_dir['files'];
                    
					$current_dir = $wpdocs_move_selected_dir['file_dir'];
					
                    $new_dir = $wpdocs_move_selected_dir['dir_id'];
					
                    $files =  wpdocs_added_items($current_dir);
					
                    $file_id = is_array($file_id) ? $file_id : array($file_id);
                    $files = array_diff($files, $file_id);
					
					
					switch($action_type){
						default:
						case 'move':
								
							update_post_meta($current_dir, 'wpdocs_items', $files);
							
						break;
						
						case 'copy':
							
						break;
							
					}

					$files_list = wpdocs_update_files_meta($new_dir, $file_id);
					
					if(is_array($files_list) && !empty($files_list)){//$update === true){
						$return['dir_move'] = true;
					}

                }
            }

            echo  wp_json_encode($return);

        }

        wp_die();

    }
}



if(!function_exists('wpdocs_dir_list_complete')){

    function wpdocs_dir_list_complete($dir = 0){

          $wpdocs_list = wpdocs_list($dir);

          if(!empty($wpdocs_list)){

              $wp_dir_child = array();

              foreach ($wpdocs_list as $index => $wp_dir){

                  if(!array_key_exists('id', $wp_dir)) continue;
                  $wpdocs_list_child = wpdocs_list($wp_dir['id']);

                  if(!empty($wpdocs_list_child)){

                      $wp_dir['child_dir'] = wpdocs_dir_list_complete($wp_dir['id']);

                  }

                  $wp_dir_child[] = $wp_dir;


              }

              return $wp_dir_child;

          }else{

              return array();
          }
    }
}

if(!function_exists('wpdocs_dir_list_option')){

    function wpdocs_dir_list_option($dir = 0, $str = ' __ ', $level = 0){


        $wpdocs_list = wpdocs_list($dir);

        $option = '';

        if(!empty($wpdocs_list)){


            foreach ($wpdocs_list as $index => $wp_dir){

                if(!array_key_exists('id', $wp_dir)) continue;
                $wpdocs_list_child = wpdocs_list($wp_dir['id']);

                $option .= '<option value="'.$wp_dir['id'].'" data-parent="'.$dir.'">'.str_repeat(str_replace(' ', '&nbsp;', $str), $level).$wp_dir['title'].'</option>';


                if(!empty($wpdocs_list_child)){


                    $option .= wpdocs_dir_list_option($wp_dir['id'], $str, $level+1);

                }

            }


        }

            return $option;
    }
}

add_action('wpdocs_before_docs_list', 'wpdocs_add_breadcrumb');

if(!function_exists('wpdocs_add_breadcrumb')){

    function wpdocs_add_breadcrumb($dir_id, $breadcrumb=true){



        $breadcrumb_array = wpdocs_get_breadcrumb_array($dir_id, $breadcrumb);
        $get_permalink = admin_url('options-general.php?page=wpdocs');

		if (!empty($breadcrumb_array)) {
        ?>

        <nav aria-label="breadcrumb" class="wpdocs-nav">
            <ol class="breadcrumb bg-light" style="border-bottom:1px solid #dee2e6;border-radius: 0;">

                <li class="breadcrumb-item bread_home_url"><a class="wpd_bread_item" href="<?php echo $get_permalink ?>" data-id="0"><?php _e('Home', 'wp-docs'); ?></a></li>
                <?php
                
                    foreach (array_reverse($breadcrumb_array) as $bread_key => $bread_value) {
                        $active = '';
                        $page = '';

                        $link = '<a class="wpd_bread_item" href="' . $get_permalink .'&dir=' . $bread_value . '" data-id="'.$bread_value.'" >' . get_the_title($bread_value) . '</a>';
                        if ($bread_value == 0) {
                            continue;
                        }
                        if ($bread_value == $dir_id) {
                            $active = 'active';
                            $page = 'page';
                            $link = get_the_title($bread_value);
                        }


                        ?>
                        <li class="breadcrumb-item <?php echo $active ?>" aria-current="<?php echo $page; ?>"><?php echo $link ?></li>

                        <?php
                    }
                
                ?>

            </ol>

        </nav>

        <?php
		
		}
    }
}

	add_action('wp_ajax_wpdocs_update_view', 'wpdocs_update_view');
	add_action('wp_ajax_nopriv_wpdocs_update_view', 'wpdocs_update_view');
		
	if(!function_exists('wpdocs_update_view')){
		function wpdocs_update_view(){
			
			$nonce = sanitize_wpdocs_data(wp_unslash($_POST['nonce']));
			
			if ( ! wp_verify_nonce( $nonce, 'wpdocs_update_options_nonce' ) )
			die (__("Sorry, your nonce did not verify.", 'wp-docs'));			
			
			if(isset($_POST['update_view'])){
			
				$wpdocs_view = get_option('wpdocs_view', array());
				$wpdocs_view = is_array($wpdocs_view) ? $wpdocs_view : array();
				$parent_dir = sanitize_wpdocs_data($_POST['parent_dir']);
				
				$wpdocs_view[$parent_dir] = sanitize_wpdocs_data($_POST['update_view']);
				update_option('wpdocs_view', $wpdocs_view);
			
			}
			exit;
		}
	}
	function wpdocs_init_session() {
		if(!session_id()) {
			session_start();
		}
	}
	
	//add_action('init', 'wpdocs_init_session', 1);

    if(!function_exists('wpdocs_create_dir')){
        function wpdocs_create_dir($dir_id, $parent_path){
			

            if(!file_exists($parent_path)){
                mkdir($parent_path);
            }
            
			$current_dir_name = 'wpdocs';
			
            if($dir_id != 0){

                $current_dir = get_post($dir_id);
                $current_dir_name = $current_dir->post_title;
            }

            $current_dir_temp = $parent_path.'/'.$current_dir_name;

            if(!is_dir($current_dir_temp))
            mkdir($current_dir_temp);

            return $current_dir_temp;
        }
    }

    if(!function_exists('wpdocs_copy_files')){
        function wpdocs_copy_files($wpdocs_items, $current_dir_temp){
			
			//pre($wpdocs_items);

            $upload_dir = wp_upload_dir();

            if(!empty($wpdocs_items)){
                foreach ($wpdocs_items as $item_id){
					//pre($item_id);
					//pre(get_post_meta($item_id));
                    $attached_file = get_post_meta($item_id, '_wp_attached_file', true);
					//pre($attached_file);
					
					$absolute_path = false;
					
					if(!$attached_file){
						$attached_post = get_post($item_id);
						$attached_file = $attached_post->guid;
						$absolute_path = true;
					}
					
					
                    $file_name = basename($attached_file);
                    $file_copy_path = $current_dir_temp.'/'.$file_name;
					
					if($absolute_path){
						$file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $attached_file);
					}else{
	                    $file_path = $upload_dir['basedir'].'/'.$attached_file;
					}
					
					//pre($file_path);
					
					if(!is_dir($file_path) && file_exists($file_path)){						
	                    copy($file_path, $file_copy_path);
					}
                }
            }
			
			//exit;

        }
    }

    if(!function_exists('wpdocs_create')){
        function wpdocs_create($dir_id, $wpdocs_dir){
			
			//pree($dir_id);

            $wpdocs_list = wpdocs_list($dir_id);
			//pre($wpdocs_list);
            $wpdocs_items = wpdocs_added_items($dir_id);
			//pre($wpdocs_items);

            $current_dir_temp = wpdocs_create_dir($dir_id, $wpdocs_dir);
			
			//pree($current_dir_temp);
			//exit;
			
            wpdocs_copy_files($wpdocs_items, $current_dir_temp);

            if(!empty($wpdocs_list)){
                foreach ($wpdocs_list as $single_dir){
					
                    wpdocs_create($single_dir['id'], $current_dir_temp);
                }
            }

        }

    }

    if(!function_exists('wpdocs_generate_zip')){

        function wpdocs_generate_zip($source, $destination)
        {

            // Initialize archive object
            $zip = new ZipArchive();
            $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);


            // Create recursive directory iterator
            /** @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::SELF_FIRST
            );

			//pre($files);

            if (!empty($files)) {
                foreach ($files as $name => $file) {
					
					
					$file_path = $file->getRealPath();
					$relative_path = substr($file_path, strlen($source) + 1);
					/*
					if (!$file->isDir()) {
                        // Get real and relative path for current file
                        
                       

                        // Add current file to archive
                        $zip->addFile($file_path, $relative_path);
                    }
					*/
					
					if (is_dir($file_path) === true) {
						$zip->addEmptyDir(str_replace($source . '/', '', $relative_path . '/'));
					} elseif (is_file($file) === true) {
						$zip->addFromString(str_replace($source . '/', '', $relative_path), file_get_contents($file_path));
					}
                }
            }



            // Zip archive will be created only after closing object
            $zip->close();

            $ret = str_replace('\\', '/', $destination);
			
			$url = str_replace(get_home_path(), get_home_url().'/', $ret);
			
            $resp = array(
                'url' => $url,
                'path' => $destination,
            );
			
			//pree($resp);exit;
			
			return $resp;

        }

    }

    if(!function_exists('wpdocs_download_zip')){
        function wpdocs_download_zip($dir_id){

            $upload_dir = wp_upload_dir();
            $upload_dir = $upload_dir['basedir'];
            $wpdocs_dir = $upload_dir.'/wpdocs';
            $current_dir_temp = wpdocs_create_dir($dir_id, $wpdocs_dir);
            wpdocs_create($dir_id, $wpdocs_dir);

            $dest = $wpdocs_dir.'/'.basename($current_dir_temp).'.zip';
			
			//pre($current_dir_temp);pre($dest);
			//exit;
			
            $ret = wpdocs_generate_zip($current_dir_temp, $dest);
			
			//pree($ret);
			if(isset($_GET['debug'])){
				exit;
			}
			
			return $ret;

        }
    }
	
    add_action('init', 'wpdocs_dir_actions');
    if(!function_exists('wpdocs_dir_actions')){
        function wpdocs_dir_actions(){
			
			if(is_admin() && get_option('wpdocs_memphis_uninstall')){
				if(wp_docs_memphis_folder_preserve('mdocs_2', 'mdocs')){
					update_option('wpdocs_memphis_uninstall', false);
				}	
			}				
			
            if(isset($_GET['wpdocs_dir']) && is_numeric($_GET['wpdocs_dir']) && isset($_GET['wpdocs_wpnonce']) && wp_verify_nonce( sanitize_wpdocs_data(wp_unslash($_GET['wpdocs_wpnonce'])), "wpdocs-{$_GET['wpdocs_dir']}" )){
				
				//pree($_GET);exit;
				
				if(isset($_GET['download'])){
					
					$wpdocs_clear_clutter = get_option('wpdocs_clear_clutter', array());
					$wpdocs_clear_clutter = (is_array($wpdocs_clear_clutter)?$wpdocs_clear_clutter:array());
					if(!empty($wpdocs_clear_clutter)){
						foreach($wpdocs_clear_clutter as $i=>$wpdocs_clear_clutter_iter){
							
							if(is_array($wpdocs_clear_clutter_iter)){
								
								list($rm_dir, $zip_path) = $wpdocs_clear_clutter_iter;
								unlink($zip_path);
								
								if (is_dir($rm_dir)) {
									$dir = new RecursiveDirectoryIterator($rm_dir, RecursiveDirectoryIterator::SKIP_DOTS);
									foreach (new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST ) as $filename => $file) {
										if (is_file($filename))
											unlink($filename);
										else
											rmdir($filename);
									}
									rmdir($rm_dir); // Now remove myfolder
								}
							}
							
							unset($wpdocs_clear_clutter[$i]);
						}
						update_option('wpdocs_clear_clutter', $wpdocs_clear_clutter);
					}
					
						
					
					$wpdocs_dir = sanitize_wpdocs_data($_GET['wpdocs_dir']);
					//pree($wpdocs_dir);exit;
					$zip =  wpdocs_download_zip($wpdocs_dir);
					$rm_dir = str_replace('.zip', '',$zip['path'] );
					//pree($rm_dir);exit;
					//pree($wpdocs_dir);pree($rm_dir);pree($zip);exit;
					if(file_exists($zip['path'])){
						
						$wpdocs_clear_clutter = get_option('wpdocs_clear_clutter', array());
						$wpdocs_clear_clutter = (is_array($wpdocs_clear_clutter)?$wpdocs_clear_clutter:array());
						$wpdocs_clear_clutter[] = array($rm_dir, $zip['path']);
						update_option('wpdocs_clear_clutter', $wpdocs_clear_clutter);
					
						//echo $rm_dir;exit;
		
						
						
						
						//echo $zip['path'];exit;
		
		
						header("Content-type: application/zip");
						header("Content-Disposition: attachment; filename=".basename($zip['path'])."");
						header("Pragma: no-cache");
						header("Expires: 0");
						header("Content-length: " . @filesize($zip['path']));
						//readfile($zip['path']);
						
						wp_redirect($zip['url']);exit;
						
						
						
					}else{
						wp_redirect(admin_url('options-general.php?page=wpdocs'));exit;
					}
					
				}
				
				if(isset($_GET['clear'])){
					global $wpdocs_url, $wpdb, $wpdocs_post_types;
					update_option('wpdocs_options', array());
					$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type IN ('".implode("','", $wpdocs_post_types)."')");
					wp_redirect(admin_url('options-general.php?page=wpdocs'));exit;
				}
				

            }

        }

    }
	
	function wpdocs_plugin_links($links) { 
		global $wpdocs_premium_link, $wpdocs_pro;
		
		$settings_link = '<a href="options-general.php?page=wpdocs">'.__('Settings', 'wp-docs').'</a>';
		
		if($wpdocs_pro){
			array_unshift($links, $settings_link); 
		}else{
			 
			$wpdocs_premium_link = '<a href="'.esc_url($wpdocs_premium_link).'" title="'.__('Go Premium', 'wp-docs').'" target="_blank">'.__('Go Premium', 'wp-docs').'</a>'; 
			array_unshift($links, $settings_link, $wpdocs_premium_link); 
		
		}
		
		
		return $links; 
	}

    if(!function_exists('wpdocs_get_current_user_items')){

        function wpdocs_get_current_user_items($dir_id, $current_user_id){

            $current_user_files = array();

            if ($dir_id > 0 && wpdocs_folder_exists($dir_id)) {

                $wpdocs_items_by_user = get_post_meta($dir_id, 'wpdocs_items_by_user', true);
                $wpdocs_items_by_user = is_array($wpdocs_items_by_user)?$wpdocs_items_by_user:array();
                $current_user_files = array_key_exists($current_user_id, $wpdocs_items_by_user) ? $wpdocs_items_by_user[$current_user_id] : array();

            }

            return $current_user_files;

        }
    }

   
	
	

    if(!function_exists('wpdocs_is_file_belong_to_user')){

        function wpdocs_is_file_belong_to_user($dir_id, $files, $current_user_id){


            if ($dir_id > 0 && wpdocs_folder_exists($dir_id) && count($files) > 0) {


                $current_user_files = wpdocs_get_current_user_items($dir_id, $current_user_id);
                $current_user_updated_items = array_intersect($current_user_files, $files);

                return !empty($current_user_updated_items);


            }else{

                return false;

            }

        }
    }

   




    if(!function_exists('wpdocs_del_items_by_user')){
        function wpdocs_del_items_by_user($dir_id, $files, $current_user_id){


            if ($dir_id > 0 && wpdocs_folder_exists($dir_id) && count($files) > 0) {


                $wpdocs_items_by_user = get_post_meta($dir_id, 'wpdocs_items_by_user', true);
                $current_user_files = wpdocs_get_current_user_items($dir_id, $current_user_id);



                $current_user_updated_items = array_diff($current_user_files, $files);



                $current_user_updated_items = array_unique($current_user_updated_items);




                $wpdocs_items_by_user[$current_user_id] = $current_user_updated_items;



                $wpdocs_items_by_user = array_filter($wpdocs_items_by_user);


                return update_post_meta($dir_id, 'wpdocs_items_by_user', $wpdocs_items_by_user);

            }else{
                return false;
            }

        }
    }
	
	add_action('wp_ajax_wp_docs_import_memphis_docs', 'wp_docs_import_memphis_docs');

	if(!function_exists('wp_docs_import_memphis_docs')){
		function wp_docs_import_memphis_docs(){
			
			$result_array = array(
				'status' => false,
			);
			
			if (!isset($_POST['wp_docs_nonce']) || !wp_verify_nonce( sanitize_wpdocs_data(wp_unslash($_POST['wp_docs_nonce'])), 'wpdocs_update_options_nonce' ) ){
				
				wp_die(__("Sorry, your nonce did not verify.", 'wp-docs'));

			}else{

				$dir_progress = wp_docs_import_memphis_directories();
				$file_progress = wp_docs_memphis_import_files();

				$result_array['status'] = ($dir_progress || $file_progress);

				if($result_array['status']){
					wp_docs_whiteflag_memphis_htaccess();
				}
				
				if(!$dir_progress && !$file_progress){
					
					$result_array['remarks'] = __('No directories and files found.', 'wp-docs');
				}

			}

			wp_send_json($result_array);
		}
	}

	add_action('wp_ajax_wp_docs_import_memphis_rollback', 'wp_docs_import_memphis_rollback');

	if(!function_exists('wp_docs_import_memphis_rollback')){
		function wp_docs_import_memphis_rollback(){
			
			$result_array = array(
				'status' => false,
			);
			
			if (!isset($_POST['wp_docs_nonce']) || !wp_verify_nonce( sanitize_wpdocs_data(wp_unslash($_POST['wp_docs_nonce'])), 'wpdocs_update_options_nonce' ) ){
				
				wp_die(__("Sorry, your nonce did not verify.", 'wp-docs'));

			}else{

				wp_docs_rollback_memphis_import();
				$result_array['status'] = true;
			}

			wp_send_json($result_array);
		}
	}

	if(!function_exists('wp_docs_get_memphis_name')){
		function wp_docs_get_memphis_name(){
			global $wpdb;
			$name = esc_sql('Memphis Documents');

			$query = "SELECT max(ID) FROM $wpdb->posts WHERE post_title LIKE '%$name%'";

			$result = $wpdb->get_var($query);			
			if($result){

				$post = get_post($result);

				$title_array = explode(' ', $post->post_title);
				$last_elment = end($title_array);

				if(is_numeric($last_elment)){
					$last_elment++;
				}else{
					$last_elment = 1;
				}

				$name .= ' '.$last_elment;

			}


			return $name;
		}
	}

	if(!function_exists('wp_docs_get_memphis_dir_id')){
		function wp_docs_get_memphis_dir_id(){
			global $wpdb;

			$name = esc_sql('Memphis Documents');

			$dir_id = 0;

			$query = "SELECT max(ID) FROM $wpdb->posts WHERE post_title LIKE '%$name%'";

			$result = $wpdb->get_var($query);			
			if($result){
				$dir_id = $result;
			}


			return $dir_id;
		}
	}

	if(!function_exists('wp_docs_import_memphis_directories')){

		function wp_docs_import_memphis_directories(){
			global $wp_docs_is_memphis;

			$progress_status = false;

			if(!$wp_docs_is_memphis){
					 
			}else{

				$memphis_folders_array = get_option('mdocs-cats', array());

				if(!empty($memphis_folders_array)){	
					
					extract(wp_docs_count_memphis_folder($memphis_folders_array));

					if($import_folder_count != $total_folder_count){

						$parent_id = wp_docs_get_memphis_dir_id();
						if($parent_id == 0){

							$parent_id = wpdocs_create_folder_post(0, 'Memphis Documents');
						}
						wp_docs_memphis_create_directory($memphis_folders_array, $parent_id);	

						$progress_status = true;

					}				

				}
				
			}

			return $progress_status;
		}

	}

	if(!function_exists('wp_docs_memphis_create_directory')){

		function wp_docs_memphis_create_directory($directory_list, $base_parent_id){
			global $wp_docs_is_memphis, $wpdocs_imported_folder, $memphis_folders_id;
			if(!$wp_docs_is_memphis){
					 
			}else{

				
				if(!empty($directory_list)){

					foreach ($directory_list as $key => $single_directory) {
						# code...

						$slug = $single_directory['slug'];
						$name = $single_directory['name'];
						$child_dir_list = $single_directory['children'];
						$slug_key = $memphis_folders_id.'_'.$slug;

						if(in_array($slug_key, $wpdocs_imported_folder)) continue;
						$current_parent_id = wpdocs_create_folder_post($base_parent_id, $name);
						if($current_parent_id){

							update_post_meta($current_parent_id, '_wpdocs_memphis_slug', $slug_key);
							$wpdocs_imported_folder[] = $slug_key;
							if(!empty($child_dir_list)){
								wp_docs_memphis_create_directory($child_dir_list, $current_parent_id);
							}

						}

					}
					
					update_option('wpdocs_imported_folder', $wpdocs_imported_folder);
				}
				
			}
		}

	}


	if(!function_exists('wp_docs_count_memphis_folder')){
		function wp_docs_count_memphis_folder($memphis_folders, $total_folder_count = 0, $import_folder_count = 0){
			global $memphis_folders_id, $wpdocs_imported_folder;
			
			if(!empty($memphis_folders)){
				foreach ($memphis_folders as $key => $folder) {
					# code...
					$children = $folder['children'];
					$slug = $folder['slug'];
					$slug_key = $memphis_folders_id.'_'.$slug;
					$total_folder_count++;

					if(in_array($slug_key, $wpdocs_imported_folder)){
						$import_folder_count++;
					}

					if(!empty($children)){
						extract(wp_docs_count_memphis_folder($children, $total_folder_count, $import_folder_count));
					}
				}
			}

			return array('total_folder_count' => $total_folder_count, 'import_folder_count' => $import_folder_count);
		
		}
	}

	if(!function_exists('wp_docs_count_memphis_files')){
		function wp_docs_count_memphis_files(){
			
			global $memphis_files_array, $wpdocs_imported_files, $wpdocs_memphis_list;
			
			$total_file_count = (count($memphis_files_array) + count($wpdocs_memphis_list));

			return array('total_file_count' => $total_file_count, 'import_file_count' => count($wpdocs_memphis_list));
		
		}
	}

	if(!function_exists('wp_docs_memphis_statistics')){
		function wp_docs_memphis_statistics(){
			global $memphis_folders_array;
			//pree($memphis_folders_array);
			extract(wp_docs_count_memphis_folder($memphis_folders_array));
			extract(wp_docs_count_memphis_files());

			return array(
				'total_folder' => $total_folder_count,
				'import_folder' => $import_folder_count,
				'total_files' => $total_file_count,
				'import_files' => $import_file_count,
			);

		}
	}

	if(!function_exists('wp_docs_relocate_memphis_meta')){
		function wp_docs_relocate_memphis_meta($dir_id){
			global $wpdocs_imported_files, $wpdocs_imported_folder;
			$wpdocs_items = get_post_meta($dir_id, 'wpdocs_items', true);
			$wpdocs_items = (is_array($wpdocs_items) ? $wpdocs_items : array());
			$dir_slug = get_post_meta($dir_id, '_wpdocs_memphis_slug', true);
			
			$wpdocs_imported_files = (is_array($wpdocs_imported_files)?$wpdocs_imported_files:array());

			if(!empty($wpdocs_items)){

				$wpdocs_imported_files = array_diff($wpdocs_imported_files, $wpdocs_items);
			}

			if($dir_slug){
				$wpdocs_imported_folder = array_diff($wpdocs_imported_folder, array($dir_slug));
			}

			update_option('wpdocs_imported_folder', $wpdocs_imported_folder);
			update_option('wpdocs_imported_files', $wpdocs_imported_files);				

		};
	}

	if(!function_exists('wp_docs_memphis_import_files')){
		function wp_docs_memphis_import_files(){
			global $memphis_folders_id, $memphis_files_array, $wpdocs_imported_files, $wpdocs_memphis_list, $wpdocs_post_types;

				$before_count = count($wpdocs_imported_files);
				if(!empty($memphis_files_array)){

					foreach($memphis_files_array as $file_index => $file_data){
						$attachment_id = $file_data['id'];
						$attachment_folder = $file_data['cat'];
						$slug_key = $memphis_folders_id.'_'.$attachment_folder;
						$files = array($attachment_id);
						$upload_dir = wp_upload_dir();
						$upload_base_url = $upload_dir['baseurl'];

						if(in_array($attachment_id, $wpdocs_imported_files)){continue;}

						$dir_arg = array(
							'post_type' => $wpdocs_post_types,
							'numberposts' => '-1',
							'post_status' => 'any',
							'fields' => 'ids',
							'meta_query' => array(
								array(
									'key' => '_wpdocs_memphis_slug',
									'value' => $slug_key,
									'compare' => '='
								)
							)
						);

						$dir_list = get_posts($dir_arg);

						if(!empty($dir_list)){
							foreach($dir_list as $dir_id){
								update_post_meta($attachment_id, '_wpdocs_memphis_media_file', true);
								$wpdocs_imported_files[] = $attachment_id;
								$files_list = wpdocs_update_files_meta($dir_id, $files);
							}
							unset($memphis_files_array[$file_index]);
							$wpdocs_memphis_list[] = $file_data;
						}
					}

					update_option('mdocs-list', $memphis_files_array);
					update_option('wpdocs_memphis_list', $wpdocs_memphis_list);
					update_option('wpdocs_imported_files', $wpdocs_imported_files);
				}
				$after_count = count($wpdocs_imported_files);

				return ($before_count != $after_count);
		}
	}


	add_filter('pre_delete_attachment', 'wpdocs_delete_attachment_callback', 10, 3);

	if(!function_exists('wpdocs_delete_attachment_callback')){
		function wpdocs_delete_attachment_callback($check, $post, $force_delete){

			$wpdoc_memphis_media_file = get_post_meta($post->ID, '_wpdocs_memphis_media_file', true);
			$is_memphis_media = strpos($post->post_content, 'mdocs_media_attachment');
			
			if($wpdoc_memphis_media_file && $is_memphis_media){
				$post->post_content = '';
				wp_update_post($post);
				return $post;
			}else{
				return $check;
			}

		}
	}

	add_action('pre_uninstall_plugin', 'wp_docs_save_attachments_to_del');

	if(!function_exists('wp_docs_save_attachments_to_del')){
		
		function wp_docs_save_attachments_to_del($plugin){

			global $wpdocs_imported_folder, $wpdocs_imported_files, $wpdocs_memphis_list;

			$wpdocs_memphis_list = get_option('wpdocs_memphis_list', array());

						
			if($plugin == 'memphis-documents-library/memphis-documents.php'){
				if(!empty($wpdocs_memphis_list)){
					if(wp_docs_memphis_folder_preserve('mdocs', 'mdocs_2')){
						update_option('wpdocs_memphis_uninstall', true);
					}	
					
					$wpdocs_imported_folder = array();
					$wpdocs_imported_files = array();
					$wpdocs_memphis_list = array();

					update_option('wpdocs_imported_folder', $wpdocs_imported_folder);
					update_option('wpdocs_imported_files', $wpdocs_imported_files);
					update_option('wpdocs_memphis_list', $wpdocs_memphis_list);
				}


			}
			
			

		}

	}

	if(!function_exists('wp_docs_memphis_folder_preserve')){
		function wp_docs_memphis_folder_preserve($from, $to){

			$upload_dir = wp_upload_dir();
			$upload_basedir = $upload_dir['basedir'];
			$memphis_dir = $upload_basedir.'/'.$from;
			$bak_file = $memphis_dir.'/mdocs-files.bak';

			if(file_exists($bak_file)){

				try {
					unlink($bak_file);
				} catch (\Exception $ex) {
					//throw $th;
				}
			}
			$status = false;

			
			if(file_exists($memphis_dir)){
				$status = rename($memphis_dir, $upload_basedir.'/'.$to);
			}

			return $status;
		}
	}

	if(!function_exists('wp_docs_whiteflag_memphis_htaccess')){
		function wp_docs_whiteflag_memphis_htaccess(){

			$upload_dir = wp_upload_dir();
			$basedir = $upload_dir['basedir'];

			$memphis_dir = $basedir.'/mdocs/';
			
			if(is_dir($memphis_dir)){
					
				$memphis_dir_ht = $memphis_dir.'.htaccess';
				$memphis_dir_index = $memphis_dir.'index.html';
	
				if(file_exists($memphis_dir_ht)){
					if(!file_exists($memphis_dir_index)){
						file_put_contents($memphis_dir_index, '');
					}
					/*file_put_contents($memphis_dir_ht, 'Allow from all
	Options +Indexes
					');*/
					unlink($memphis_dir_ht);
				}
			}

		}
	}

	if(!function_exists('wp_docs_rollback_memphis_import')){
		function wp_docs_rollback_memphis_import(){

			global $memphis_files_array, $wpdocs_imported_folder, $wpdocs_imported_files, $wpdocs_memphis_list, $wpdocs_post_types;
			
			
			$wp_docs_args = array(
				'post_type' => $wpdocs_post_type['dir'],
				'post_status' => 'any',
				'numberposts' => -1,
				'meta_query' => array(
					array(
						'key' => '_wpdocs_memphis_slug',
						'compare' => 'EXIST'
					)
				)
			);

			$all_memphis_folder = get_posts($wp_docs_args);

			if(!empty($all_memphis_folder)){
				foreach($all_memphis_folder as $m_folder){
					wpdocs_recursive_delete_folder($m_folder->ID);
				}
			}

			if(!empty($wpdocs_memphis_list)){
				foreach($wpdocs_memphis_list as $file_index => $file_data){
					delete_post_meta($file_data['id'], '_wpdocs_memphis_media_file');
					unset($wpdocs_memphis_list[$file_index]);
					$memphis_files_array[] = $file_data;

				}
			}

			update_option('mdocs-list', $memphis_files_array);
			update_option('wpdocs_memphis_list', $wpdocs_memphis_list);
			update_option('wpdocs_imported_files', array());
		}
	}
	
	function wpdocs_specific_directory_settings($dir=0, $is_file=false, $is_current_user_files=false, $is_del_from_front=false, $allowed_role=array(), $allowed_ext='', $default_ext=''){
		global $wpdocs_pro;
		$dir_option_class = '';
		if($dir){
			$dir_option_class = 'wpdocs_dir_options';
			$is_file = wpdocs_dir_options_default('file_upload', false, $dir);
			$is_current_user_files = wpdocs_dir_options_default('current_user_files', false, $dir);
			$is_del_from_front = (is_user_logged_in() && wpdocs_dir_options_default('del_from_front', false, $dir));
			$allowed_role = wpdocs_dir_options_default('allowed_role', array(), $dir);
			$allowed_ext = wpdocs_dir_options_default('allowed_ext', '', $dir);
			$default_ext = '';
			
			$breadcrumb = get_the_title($dir);
			?>
			<small class="alert alert-success d-block"><i class="fas fa-chevron-right"></i> <?php echo $breadcrumb; ?></small>
			<?php			
	
		}
		?>
		<label for="wpdocs_options_file">
            <input <?php checked($is_file); ?> type="checkbox" class="<?php echo $dir_option_class; ?>" name="wpdocs_options[file_upload]" value="file_upload" id="wpdocs_options_file"  />
            <?php echo __('File Upload Front-end', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Optional)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small> <i title="<?php echo __('This icon will appear on front-end for users', 'wp-docs'); ?>" class="fa fa-upload" style="color:#ffc107"></i>
            <a href="https://www.youtube.com/embed/flFmqpJCwYk" target="_blank"><?php echo __('Video Tutorial', 'wp-docs'); ?></a>
        </label>
        
        

        <ul class="ml-4 <?php echo $is_file ? '' : 'd-none'?>">
            <li>
                <label for="wpdocs_options_current_user_files">
                    <input class="<?php echo $dir_option_class; ?>" <?php checked($is_file && $is_current_user_files); ?> type="checkbox" name="wpdocs_options[current_user_files]" value="current_user_files" id="wpdocs_options_current_user_files"  />
                    <?php echo __('Do not make files public uploaded by users', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Optional)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small>
                </label>
            </li>

            <li>
                <label for="wpdocs_options_del_from_front">
                    <input class="<?php echo $dir_option_class; ?>" <?php checked($is_file && $is_del_from_front); ?> type="checkbox" name="wpdocs_options[del_from_front]" value="del_from_front" id="wpdocs_options_del_from_front"  />
                    <?php echo __('User can delete the files from front-end?', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Optional)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small> <i class="fas fa-trash-alt" style="color:#ffc107"></i>
                </label>
            </li>

            <li>
                <label for="wpdocs_options_allowed_role">
                    <?php echo __('Allow user roles which can upload files', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Optional)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small> <i class="fas fa-users" style="color:#ffc107"></i>
                </label>



                <select class="wpdocs_options_allowed_role <?php echo $dir_option_class; ?>" name="wpdocs_options[allowed_role]" data-name="allowed_role" id="wpdocs_options_allowed_role" multiple placeholder="<?php echo __('Select roles to allow upload', 'wp-docs'); ?>">

                    <?php echo wpdocs_get_user_roles_options($allowed_role) ?>

                </select>
                

            </li>

            <li>
                <label for="wpdocs_options_allowed_ext">
                    <?php echo __('Allowed File Types', 'wp-docs'); ?> <?php echo ($wpdocs_pro?'':'<small>'.__('(Premium)', 'wp-docs').'</small> '); ?> <i class="fas fa-photo-video" style="color:#ffc107"></i>
                </label>
                <input  type="text" class="form-control <?php echo $dir_option_class; ?>" name="wpdocs_options[allowed_ext]" data-name="allowed_ext" value="<?php echo $allowed_ext; ?>" id="wpdocs_options_allowed_ext" title="<?php _e('Leave blank if you want to allow all type of files', 'wp-docs'); ?>" placeholder="<?php echo $default_ext; ?>" />

            </li>

			</ul>
		<?php		
	}

	if(!function_exists('wpdocs_dir_options_by_name')){
		function wpdocs_dir_options_by_name($option_name, $default_return = false, $dir = 0){
			
			global $wpdocs_options;

			$dir_options = array();

			if($dir > 0){
				$dir_options = get_post_meta($dir, '_wpdocs_dir_options', true);
				$dir_options = (is_array($dir_options) ? $dir_options : array());
			}

			$key_exist = false;
			$search_options = array();

			if(array_key_exists($option_name, $dir_options)){
				$search_options = $dir_options;
				$key_exist = true;

				
			}else if(array_key_exists($option_name, $wpdocs_options)){

				$search_options = $wpdocs_options;
				$key_exist = true;
			}

			if($key_exist){

				$return_value = $search_options[$option_name];
				$return_value = (is_array($default_return) && !is_array($return_value) ? array() : $return_value);
				$default_return = $return_value;

			}

			if($default_return == 'true'){
				$default_return = true;
			}elseif($default_return == 'false'){
				$default_return = false;
			}

			return $default_return;
		}
	}

	if(!function_exists('wpdocs_dir_options_default')){
		function wpdocs_dir_options_default($option_name, $default_return = false, $dir = 0){
			
			global $wpdocs_options;

			$dir_options = array();

			if($dir > 0){
				$dir_options = get_post_meta($dir, '_wpdocs_dir_options', true);
				$dir_options = (is_array($dir_options) ? $dir_options : array());
			}

			$key_exist = false;
			$search_options = array();

			if(array_key_exists($option_name, $dir_options)){
				$search_options = $dir_options;
				$key_exist = true;				
			}

			if($key_exist){

				$return_value = $search_options[$option_name];
				$return_value = (is_array($default_return) && !is_array($return_value) ? array() : $return_value);
				$default_return = $return_value;

			}

			if($default_return == 'true'){
				$default_return = true;
			}elseif($default_return == 'false'){
				$default_return = false;
			}

			return $default_return;
		}
	}

	if(!function_exists('wpdocs_get_dir_restrictions')){
		function wpdocs_get_dir_restrictions($dir = 0, $rest_type = 'array'){
			
			$dir_restrictions = array();

			if($dir > 0){
				$dir_options = get_post_meta($dir, '_wpdocs_dir_options', true);
				$dir_options = (is_array($dir_options) ? $dir_options : array());
				$is_file_upload = array_key_exists('file_upload', $dir_options) && $dir_options['file_upload'] == 'true';
				$dir_restrictions = $dir_options;
			}


			switch ($rest_type) {
				case 'array':
					# code...
					return $dir_restrictions;
				break;

				case 'json':
					# code...
					if(!empty($dir_options)){
						return wp_json_encode($dir_restrictions);

					}else{
						return '';
					}
				break;

				case 'base64':
					# code...

					if(!empty($dir_options)){
						return base64_encode(wp_json_encode($dir_restrictions));
					}else{
						return '';
					}
				break;			

			}
		

			
		}
	}


	add_action('init', function(){

		// return;
		// wpdoc_get_dir_children(8764);
	});

	if(!function_exists('wpdoc_get_dir_children')){
		function wpdoc_get_dir_children($parent_id){
			
			global $wpdocs_post_types;

			$all_folder_query = new WP_Query();
			$all_folder = $all_folder_query->query(array('post_type' => $wpdocs_post_types, 'post_status' => 'any', 'numberposts' => -1));

			$all_folders_array = get_page_children( $parent_id, $all_folder );
			$all_folders_array = array_map(function($single){return $single->ID;}, $all_folders_array);

			return $all_folders_array;

		}
	}

	if(!function_exists('wpdoc_humanize')){
		function wpdoc_humanize($str){
			return ucwords(str_replace(array('-', '_'), ' ', $str));
		}
	}
	
	if(!function_exists('wpdocs_encrypted_path')){
		function wpdocs_encrypted_path($file_id, $encrypted=false){
			return '';
		}
	}