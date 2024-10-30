<?php
	add_action('admin_init', 'wpdocs_version_type_update');
	
	function wpdocs_version_type_update(){

				if(isset($_POST['version_type'])){			
					if ( 
						! isset( $_POST['version_type_nonce_field'] ) 
						|| ! wp_verify_nonce( sanitize_wpdocs_data(wp_unslash($_POST['version_type_nonce_field'])), 'version_type_action' ) 
					) {
					
					   print _e('Sorry, your nonce did not verify.', 'wp-docs');
					   exit;
					
					} else {
					
					   // process form data
					
						
							
							update_option('wpdocs_versions_type', sanitize_wpdocs_data($_POST['version_type']));
							
							if($_POST['version_type']=='new')
							wp_redirect('options-general.php?page=wpdocs');
							else
							wp_redirect('admin.php?page=wpdocs-engine.php');
							
							exit;
						
					}
				}
	}
				
	function wpdocs_downward_compatibility(){
		global $wpdocs_data, $wpdocs_versions_type, $wpdocs_pro, $wpdocs_url, $wpdocs_android_settings;
?>		
			<h2><?php echo $wpdocs_data['Name']; ?> <?php echo '('.$wpdocs_data['Version'].($wpdocs_pro?') Pro':')'); ?> - <?php _e('Settings', 'wp-docs'); ?></h2>
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