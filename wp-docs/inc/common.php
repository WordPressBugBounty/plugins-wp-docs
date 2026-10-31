<?php
	if ( ! defined( 'ABSPATH' ) ) { exit; }

	/**
	 * Fail-closed request guards (nonce is mandatory, capability is checked first).
	 */
	if(!function_exists('wpdocs_get_request_nonce')){
		function wpdocs_get_request_nonce($keys=array('nonce')){
			foreach((array)$keys as $key){
				if(isset($_POST[$key]) && is_scalar($_POST[$key]) && $_POST[$key]!==''){
					return sanitize_text_field(wp_unslash($_POST[$key]));
				}
			}
			return '';
		}
	}
	if(!function_exists('wpdocs_ajax_guard')){
		function wpdocs_ajax_guard($capability='manage_options', $keys=array('nonce'), $action='wpdocs_update_options_nonce', $require_login=true){
			if($require_login && !is_user_logged_in()){
				wp_send_json_error(array('message'=>__('Please log in to continue.', 'wp-docs')), 401);
			}
			if($capability && !current_user_can($capability)){
				wp_send_json_error(array('message'=>__('Insufficient permissions.', 'wp-docs')), 403);
			}
			$nonce = wpdocs_get_request_nonce($keys);
			if($nonce==='' || !wp_verify_nonce($nonce, $action)){
				wp_send_json_error(array('message'=>__('Sorry, your nonce did not verify.', 'wp-docs')), 403);
			}
			return true;
		}
	}
	if(!function_exists('wpdocs_path_in_uploads')){
		function wpdocs_path_in_uploads($path){
			$uploads = wp_get_upload_dir();
			$base    = realpath($uploads['basedir']);
			$real    = is_string($path) ? realpath($path) : false;
			return ($base && $real && strpos(wp_normalize_path($real), trailingslashit(wp_normalize_path($base))) === 0);
		}
	}
	if(!function_exists('wpdocs_dangerous_inline_ext')){
		function wpdocs_dangerous_inline_ext($path){
			$ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
			return in_array($ext, array('html','htm','xhtml','shtml','svg','svgz','xml','xsl','xslt','js','mjs','php','phtml','swf'), true);
		}
	}

	add_action('admin_init', 'wpdocs_version_type_update');
	
	function wpdocs_version_type_update(){

		if(!isset($_POST['version_type'])){ return; }

		if(!current_user_can('manage_options')){
			return;
		}

		if(
			!isset($_POST['version_type_nonce_field'])
			|| !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['version_type_nonce_field'])), 'version_type_action')
		){
			wp_die(esc_html__('Sorry, your nonce did not verify.', 'wp-docs'), '', array('response'=>403));
		}

		$version_type = sanitize_key(wp_unslash($_POST['version_type']));
		$version_type = ($version_type=='new' ? 'new' : 'old');

		update_option('wpdocs_versions_type', $version_type);

		if($version_type=='new'){
			wp_safe_redirect(admin_url('options-general.php?page=wpdocs'));
		}else{
			wp_safe_redirect(admin_url('admin.php?page=wpdocs-engine.php'));
		}
		exit;
	}
				
	function wpdocs_downward_compatibility(){
		global $wpdocs_data, $wpdocs_versions_type, $wpdocs_pro, $wpdocs_url, $wpdocs_android_settings, $wpdocs_premium_link;
?>		
			<h2><?php echo esc_html($wpdocs_data['Name']); ?> <?php echo esc_html('('.$wpdocs_data['Version'].($wpdocs_pro?') Pro':')')); ?> - <?php _e('Settings', 'wp-docs'); ?></h2>
            <?php if(!$wpdocs_pro): ?>
            <a style="float:right; position:relative; top:-40px; display:none;" href="<?php echo esc_url($wpdocs_premium_link); ?>" target="_blank"><?php _e('Go Premium', 'wp-docs'); ?></a>
            <?php endif; ?>
         
         	<div class="wpdocs_android">
	        <?php if(class_exists('QR_Code_Settings_WPDOCS')){ $wpdocs_android_settings->ab_io_display($wpdocs_url); } ?>
            </div>
            
            <?php 
							

				$wpdocs_versions_type = get_option('wpdocs_versions_type', 'old');
			?>
            
            <style type="text/css">
				.versions_type{
					background-color:#CCC;
					border-radius:4px;
					padding:10px 20px 20px 20px;
					display:none;
					
				}
				.versions_type label{
					font-weight:normal;
					padding:0;
					margin:0;					
				}
				.versions_type input[type="radio"]{
					padding:0;
					margin:0;
				}
				.update-nag{
					display:none;
				}
			</style>
            <script type="text/javascript" language="javascript">
				jQuery(document).ready(function($){
					$('.versions_type input[type="radio"]').on('click', function(){
						$('.versions_type > form').submit();
					});
				});				
			</script>
            
            <div class="versions_type">
            	<form action="" method="post">
                <?php wp_nonce_field( 'version_type_action', 'version_type_nonce_field' ); ?>
                </form>
            </div>
            <div class="wpdocs_help">
            	<h6><?php _e('How it works?', 'wp-docs'); ?></h6>
                <p><?php echo __('A default page is created with title', 'wp-docs').' "WP Docs" '.__('in', 'wp-docs').' <a href="edit.php?post_type=page" target="_blank">'.__('pages', 'wp-docs').'</a>. '.__('You can create more pages with a shortcode', 'wp-docs').' <code>[wpdocs]</code>. '.__('Create directories, sub-directories and add documents to list them with the shortcode.', 'wp-docs').' '.__("That's it.", 'wp-docs'); ?></p>
            </div>
            
            <h2 class="nav-tab-wrapper">

                <a class="nav-tab nav-tab-active" data-tab="directories"><?php _e("Directories",'wp-docs'); ?> <i class="fa fa-folder"></i> / <?php _e("Files",'wp-docs'); ?> <i class="fas fa-file-word"></i> <i class="fas fa-file-pdf"></i> <i class="fas fa-file-image"></i> <i class="fas fa-file-archive"></i></a>
    			<a class="nav-tab" data-tab="tools"><?php _e("Tools",'wp-docs'); ?> <i class="fas fa-tools"></i></a>
                
                <a class="nav-tab float-right" data-tab="help"><?php _e("Help",'wp-docs'); ?> <i class="fas fa-question-circle"></i></a>
                
    
                
    
            </h2>
<?php
	}