<?php

global $wp_docs_is_memphis, $wpdocs_current_theme;



//pree($wp_docs_is_memphis);exit;
if ($wp_docs_is_memphis) {

    $mp_plugin_data = get_plugin_data(MDOCS_PATH . 'memphis-documents.php', true, false);
	

    $wpdocs_show_data = array(
        'Name', 'Version', 'Author'
    );

?>
    <div class="wpdocs_option_wrapper">
    

    
        <div class="wpdocs_screen_meta">

            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <?php echo __('Import all files from "Memphis Documents Library". On successful import, directories and files will be displayed in WP Docs. You can rollback import action, it is safe.', 'wp-docs').' <a href="https://www.youtube.com/embed/nTFhOcJ2fNk" target="_blank">'.__('Click here for video tutorial.', 'wp-docs').'</a>'; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <ul class="list-group">
                        <?php

                        extract(wp_docs_memphis_statistics());
                        if (!empty($wpdocs_show_data)) {
							//pree($mp_plugin_data);
                            foreach ($wpdocs_show_data as $index => $display_key) {

                                if (array_key_exists($display_key, $mp_plugin_data)) {
									
									$mp_plugin_data[$display_key] = str_replace('<a', '<a target="_blank"', $mp_plugin_data[$display_key]);
									
									if($mp_plugin_data[$display_key]){			
	                                    echo "<li class='list-group-item'>{$display_key}: <strong class='float-right d-inline-block'>{$mp_plugin_data[$display_key]}</strong></li>";
									}
                                }
                            }
                        }

                        $dir_string = __('Memphis Directories', 'wp-docs');
                        $files_string = __('Memphis Files', 'wp-docs');
                        $out_of_string = __('Out of', 'wp-docs');

                        echo "<li class='list-group-item'>{$dir_string}: <strong class='float-right d-inline-block'>{$import_folder} {$out_of_string} {$total_folder}</strong></li>";
                        echo "<li class='list-group-item'>{$files_string}: <strong class='float-right d-inline-block'>{$import_files} {$out_of_string} {$total_files}</strong></li>";

                        ?>

                    </ul>
                </div>
            </div>


            <div class="row mt-4">

                <div class="col-md-12">

                    <div class="text-right">
                        <?php
                        if ($import_folder > 0 || $import_files > 0) {
                        ?>
                            <button data-text="<?php _e('Please wait...', 'wp-docs'); ?>" class="btn btn-info btn-sm wp_docs_import_memphis_rollback"><?php _e('Undo Import', 'wp-docs') ?></button>
                        <?php
                        }
                        ?>
                        <button data-text="<?php _e('Please wait...', 'wp-docs'); ?>" class="btn btn-primary btn-sm wp_docs_import_memphis"><?php _e('Import From Memphis Documents Library', 'wp-docs') ?></button>
                    </div>
                    
                    <div class="alert alert-danger mt-2" style="font-size: 14px;text-align: center;padding: 2px 0 4px;cursor: pointer; margin-bottom:0;" title="<?php _e('Memphis Documents Library .htaccess file in mdocs directory will not let you browse the files on front-end.', 'wp-docs'); ?>"><?php _e('We recommend deactivation of Memphis Documents Library after import.', 'wp-docs'); ?></div>
                </div>

            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="alert alert-info wp_docs_importing_alert"></div>
                    <div class="progress wp_docs_importing mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated pb-1" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                    </div>
                </div>
            </div>


        </div>

        <div class="wpdocs_meta_links">
            <div class="wpdocs_link_wrap">
                <button type="button" class="button wpdocs_show_option" data-control=".wpdocs_screen_meta" data-show="false"><?php _e('Memphis Documents Library', 'wp-docs'); ?> &nbsp;<i class="fa"></i></button>
            </div>
        </div>
    </div>

<?php
}else{
	wp_docs_whiteflag_memphis_htaccess();
}


$all_dirs = wpdocs_list('', 'post_title', 'ASC', 'smart', array('shortcut'));
?>
<div class="wrap wpdocs-wrapper">
<span style="float:right; color:orange; font-size:12px; margin:0 20px 0 0;"><?php echo $wpdocs_current_theme; ?></span>    
<div id="wpdocs_shortcut_dialog">
<div class="wpdocs_dialog_field">
<label><?php _e('Label/Caption', 'wp-docs'); ?>:</label> <input data-dir="" id="wpdocs_shortcut_name" type="text" value="" name="wpdocs_shortcut_name" />
</div>
<div class="wpdocs_dialog_field">
<label><?php _e('Linked Directory', 'wp-docs'); ?>: <span class="fa fa-folder" style="color: #ffc107;"></span></label> <select id="wpdocs_shortcut_to" name="wpdocs_shortcut_to">
<option value="">-</option>
<?php

if(!empty($all_dirs)){
	foreach($all_dirs as $all_dir){
?>
<option value="<?php echo $all_dir['id']; ?>"><?php echo $all_dir['title']; ?> ID: <?php echo $all_dir['id']; ?></option>
<?php		
	}
}
?>			
</select>
<span style="display:block; text-align:center; margin:10px 0 0 0"><?php _e('Or', 'wp-docs'); ?></span>
<label for="wpdocs_shortcut_link"><?php _e('Link', 'wp-docs'); ?> (<?php _e('URL', 'wp-docs'); ?>): <span style="color: #06C;" class="fa fa-link"></span></label> 
<input data-dir="" id="wpdocs_shortcut_link" type="text" value="" name="wpdocs_shortcut_link" />
</div>

</div>
<?php
    global $wpdocs_url, $wp_docs_tabs, $wpdocs_post_types;
	wpdocs_downward_compatibility();
	$dir = ((isset($_GET['dir']) && is_numeric($_GET['dir']) && $_GET['dir']>0)?sanitize_wpdocs_data($_GET['dir']):0);
	$files_list = wpdocs_list_added_items($dir);

    $wpdocs_options = get_option('wpdocs_options', array());

//    pree($wpdocs_options);exit;
    $is_ajax = array_key_exists('ajax', $wpdocs_options);
    $is_ajax_url = array_key_exists('ajax_url', $wpdocs_options);
	$is_bootstrap = array_key_exists('bootstrap', $wpdocs_options);
	$is_bootstrap = empty($wpdocs_options) ? true: $is_bootstrap;
	$is_file = array_key_exists('file_upload', $wpdocs_options);
    $is_current_user_files = array_key_exists('current_user_files', $wpdocs_options);
    $is_del_from_front = array_key_exists('del_from_front', $wpdocs_options);
	$thumb_image = array_key_exists('thumb_image', $wpdocs_options);
	
	$details_date = array_key_exists('details_date', $wpdocs_options);
	$details_date_created = array_key_exists('details_date_created', $wpdocs_options);
	$details_type = array_key_exists('details_type', $wpdocs_options);
	$details_size = array_key_exists('details_size', $wpdocs_options);

	$alt_filename = (array_key_exists('filename_alt', $wpdocs_options)?$wpdocs_options['filename_alt']:'default');
	$pdf_thumb_selected = (array_key_exists('pdf_thumb', $wpdocs_options)?$wpdocs_options['pdf_thumb']:'default');
	
	$details_view_sorting = array_key_exists('details_view_sorting', $wpdocs_options);	
	
	$ajax_based_deep_search = array_key_exists('ajax_based_deep_search', $wpdocs_options);
	
	$is_searchbox = array_key_exists('searchbox', $wpdocs_options);
	$is_borders = array_key_exists('borders', $wpdocs_options);

	$allowed_role = array_key_exists('allowed_role', $wpdocs_options) ? $wpdocs_options['allowed_role'] : array();
	$allowed_role = is_array($allowed_role)?$allowed_role:array();
	$default_ext = 'doc, docx, png, gif, bmp, jpg';
    $allowed_ext = array_key_exists('allowed_ext', $wpdocs_options) ? $wpdocs_options['allowed_ext'] : $default_ext;
	
	$customize_icon_size = array_key_exists('icon_size', $wpdocs_options) ? $wpdocs_options['icon_size'] : '';
	$customize_font_size = array_key_exists('font_size', $wpdocs_options) ? $wpdocs_options['font_size'] : '';


	

	//pree($wpdocs_options);




    $dir_id = $dir;


	
?>

<div class="nav-tab-content">
<div class="wpdocs_in_action">

<?php
	$wpdocs_security = ucwords(get_post_meta($dir_id, 'wpdocs_security', true));
	
	$security_level = __('Security Level:', 'wp-docs').' '.($wpdocs_security?$wpdocs_security:__('None', 'wp-docs'));

	$role_arr = function_exists('wpd_get_roles_select')?wpd_get_roles_select($dir_id):'<a title="'.__('Security Level is a Premium Feature', 'wp-docs').'" class="security_level" href="'.$wpdocs_premium_link.'" target="_blank">'.$security_level.' <i class="fa fa-lock"></i></a>';

	
	$download_nonce = wp_create_nonce( 'wpdocs-'.$dir_id );
	
?>	
	

<div class="wpdocs_folders">
<div class="wpdocs_toolbar">
<ul><li><a class="back-folder fa fa-hand-o-left" title="<?php _e('Click here to go back', 'wp-docs'); ?>" data-parent="<?php echo wpdocs_parent_folder($dir); ?>" data-id="<?php echo ($dir); ?>"></a></li>
<?php if($dir>0): ?>
<li><a class="new-file" data-id="<?php echo $dir; ?>" id="wpcos_new_file"><i class="fa fa-plus-circle"></i><?php _e('Add Files', 'wp-docs'); ?></a></li>
<?php endif; ?>
<li><a title="<?php _e('New folder', 'wp-docs'); ?>" class="new-folder" data-id="<?php echo $dir; ?>"><?php _e('New folder', 'wp-docs'); ?></a></li>
<li><a title="<?php echo ($wpdocs_pro?__('New Shortcut', 'wp-docs'):__('Premium Feature', 'wp-docs')); ?>" class="new-shortcut" data-id="<?php echo $dir; ?>"><?php _e('New Shortcut', 'wp-docs'); ?></a></li>
<li class="wpdocs_move_folder_to" >

    <select title="<?php _e('Move selected folder to..', 'wp-docs'); ?>">
        <option value="-1"><?php _e('Select target directory', 'wp-docs'); ?></option>
        <option value="0"><?php _e('Root', 'wp-docs'); ?></option>
        <?php echo wpdocs_dir_list_option(); ?>
    </select>
    <button><?php _e('Confirm', 'wp-docs'); ?></button>
</li>

<li>
<?php echo $role_arr; ?>
</li>
<li class="wp-docs-multi-select">
<a><i class="far fa-check-circle"></i><i class="fas fa-check-circle"></i> <?php _e('Multiple selection?', 'wp-docs'); ?></a>
</li>
<li style="float:right">
<a style="font-size: 12px;color: red;margin: 8px 0 0 0;display: block;" href="https://www.youtube.com/embed/<?php echo $wpdocs_pro?'cV-u3Iyt8kc':'k5bZqZ5dW30'; ?>" target="_blank"><?php _e('Video Tutorial', 'wp-docs'); ?></a>
</li>

</ul>
</div>
<div class="wpdocs_list">

    <?php do_action('wpdocs_before_docs_list', $dir_id) ?>



<ul>
<?php $wpdocs_list = wpdocs_list($dir); if(!empty($wpdocs_list)){ foreach($wpdocs_list as $list){  
		
		$is_shortcut = ($list['type']==$wpdocs_post_types['shortcut']);
		

?>
	<li title="<?php echo ($is_shortcut?($list['content']?__('Shortcut to', 'wp-docs').' '.'ID: '.$list['content']:''):'ID: '.$list['id']); ?>" class="<?php echo ($is_shortcut?'ab-short':'ab-dir'); ?>" data-id="<?php echo $list['id']; ?>" data-resource="<?php echo base64_encode($list['id']); ?>" data-linked="<?php echo $list['content']; ?>" data-guid="<?php echo $is_shortcut?$list['link']:''; ?>"><a class="folder fa fa-folder"></a><a class="dtitle" title="<?php _e('Click here to rename', 'wp-docs'); ?>"><?php echo ($list['title']?esc_html($list['title']):'&nbsp;'); ?></a><?php echo '<span class="wpd_action_span"><a class="wpd-edit" title="'.__('Click here to edit', 'wp-docs').'"></a><span class="wpd_action_span_inner"><a class="wpd-copy" title="'.__('Click here to copy', 'wp-docs').'"></a><a class="wpd-move" title="'.__('Click here to move', 'wp-docs').'"></a></span><a class="wpd-trash" title="'.__('Click here to delete', 'wp-docs').'"></a></span>'; ?></li>
<?php } }?>    
<?php echo ($files_list!=''?$files_list:''); ?>
</ul>
</div>
</div>
<div class="wpdocs_log">
    <div class="row">
        <div class="col-4 text-center">
            <a class="btn btn-light btn-sm p-1" title="<?php _e('Click here to download current directory', 'wp-docs'); ?>" href="<?php echo admin_url('options-general.php?page=wpdocs&download&wpdocs_dir='.$dir_id.'&wpdocs_wpnonce='.esc_attr($download_nonce)); ?>" data-dir_id="<?php echo $dir_id ?>">
                <i class="fa fa-download"></i>&nbsp;&nbsp;<?php _e('Download', 'wp-docs'); ?>
            </a>
        </div>
        <div class="col-4 text-center"><?php _e('Shortcodes', 'wp-docs'); ?></div>
        <div class="col-4 text-center">
            <a class="btn btn-sm p-1 w-75 btn-danger wpdocs-reset" title="<?php _e('Click here to reset everything', 'wp-docs'); ?>" href="<?php echo admin_url('options-general.php?page=wpdocs&clear&wpdocs_dir='.$dir_id.'&wpdocs_wpnonce='.esc_attr($download_nonce)); ?>">
                <i class="fa fa-times-circle"></i>&nbsp;&nbsp;<?php _e('Reset', 'wp-docs'); ?>
            </a>
		</div>
                    
    </div>
<center></center><br /><br />

[wpdocs<?php echo (isset($_GET['dir']) && is_numeric($_GET['dir']) && $_GET['dir']>0)?' dir="'.esc_attr($_GET['dir']).'"':''; ?> breadcrumb="true" view="list"] <i class="fas fa-code" style="color:#ffc107"></i> <a href="https://www.youtube.com/embed/h5wDMgqT5Ys" target="_blank" class="wpdocs-tutorial"><?php echo __('Video Tutorial', 'wp-docs'); ?></a>

    <br>

    <small class="wp-docs-attribs"><?php echo __('Attribute View', 'wp-docs'); ?>: <b>details</b>, <b>icons</b> and <b>list</b></small><br />




<hr class="bg-warning" />

<div class="row nopadding wpdocs-options">
<?php if(!$wpdocs_pro): ?>
<a class="btn btn-warning btn-sm mx-auto" href="<?php echo esc_url($wpdocs_premium_link); ?>" target="_blank" title="<?php echo __('Click here for Premium Version', 'wp-docs'); ?>"><?php echo __('Go Premium', 'wp-docs'); ?></a>
<?php endif; ?>


<div class="alert alert_main alert-secondary fade in alert-dismissible d-none mx-auto mt-4" style="width: 90%">
 <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo __('Close', 'wp-docs'); ?>">
    <span aria-hidden="true" style="font-size:20px">×</span>
  </button>    <strong><?php echo __('Success!', 'wp-docs'); ?></strong> <?php echo __('Options are updated successfully.', 'wp-docs'); ?>
</div>

<ul class="col col-md-12 mt-4">
    <li>
        <label for="wpdocs_options_bootstrap">
            <input <?php checked($is_bootstrap); ?> type="checkbox" name="wpdocs_options[bootstrap]" value="bootstrap" id="wpdocs_options_bootstrap"  />
            <?php echo __('Bootstrap Based', 'wp-docs'); ?> <small><?php echo __('(Front-end)', 'wp-docs'); ?></small> <i class="fab fa-bootstrap" style="color:#ffc107"></i>
        </label>

    </li>
    
     <li>
        <label for="wpdocs_options_borders">
            <input <?php checked($is_borders); ?> type="checkbox" name="wpdocs_options[borders]" value="borders" id="wpdocs_options_borders"  />
            <?php echo __('Hide Borders & Bars', 'wp-docs'); ?> <i class="fas fa-vector-square"></i>
        </label>

    </li>    
 
     <li>
        <label for="wpdocs_options_searchbox">
            <input <?php checked($is_searchbox); ?> type="checkbox" name="wpdocs_options[searchbox]" value="searchbox" id="wpdocs_options_searchbox"  />
            <?php echo __('Filter Box', 'wp-docs'); ?> <small><?php echo __('(On/Off)', 'wp-docs'); ?></small> <i class="fas fa-search" style="color:#ffc107"></i>
            <a href="https://www.youtube.com/embed/tPiA6T5jk4g" target="_blank"><?php echo __('Video Tutorial', 'wp-docs'); ?></a>
        </label>

    </li>    

    <li>
        <label for="wpdocs_options_thumb">
            <input <?php checked($thumb_image); ?> type="checkbox" name="wpdocs_options[thumb_image]" value="thumb_image" id="wpdocs_options_thumb"  />
            <?php echo __('Image Thumbnails', 'wp-docs'); ?> <small><?php echo __('(Optional)', 'wp-docs'); ?></small> <i class="fas fa-images" style="color:#ffc107"></i>
        </label>

    </li>    

    
    <li>
        <label for="wpdocs_options_details_view">            
            <?php echo __('Details View Columns Settings', 'wp-docs'); ?> <small><?php echo __('(Optional)', 'wp-docs'); ?></small> <i class="fas fa-columns" style="color:#ffc107"></i>
        </label>
        <ul class="ml-4">
        
            <li>
                <label for="wpdocs_options_details_date_created">
                    <input <?php checked($details_date_created); ?> type="checkbox" name="wpdocs_options[details_view]" value="details_date_created" id="wpdocs_options_details_date_created"  />
                    <?php echo __('Date Created', 'wp-docs'); ?> <small>(<?php echo date_i18n( get_option( 'date_format' ) ).' '.date_i18n( get_option( 'time_format' ) ); ?>)</small> <i class="far fa-calendar-alt" style="color:#ffc107"></i>
                </label>
            </li>        
            <li>
                <label for="wpdocs_options_details_date">
                    <input <?php checked($details_date); ?> type="checkbox" name="wpdocs_options[details_view]" value="details_date" id="wpdocs_options_details_date"  />
                    <?php echo __('Date Modified', 'wp-docs'); ?> <small>(<?php echo date_i18n( get_option( 'date_format' ) ).' '.date_i18n( get_option( 'time_format' ) ); ?>)</small> <i class="far fa-calendar-alt" style="color:#ffc107"></i>
                </label>
            </li>
            <li>
                <label for="wpdocs_options_details_type">
                    <input <?php checked($details_type); ?> type="checkbox" name="wpdocs_options[details_view]" value="details_type" id="wpdocs_options_details_type"  />
                    <?php echo __('Item Type', 'wp-docs'); ?> <small></small>
                </label>
            </li>
            <li>
                <label for="wpdocs_options_details_size">
                    <input <?php checked($details_size); ?> type="checkbox" name="wpdocs_options[details_view]" value="details_size" id="wpdocs_options_details_size"  />
                    <?php echo __('Item Size', 'wp-docs'); ?> <small></small>
                </label>
            </li>                            
        </ul>
    </li>    

	<li class="wpdocs-customization">
   		<label for="wpdocs_layout_customization">            
            <?php echo __('Customization', 'wp-docs'); ?> <small><?php echo __('(Optional)', 'wp-docs'); ?></small>
        </label>
    	<ul>
        	<li title="<?php echo __('Icon Size', 'wp-docs'); ?>"><span class="fa fa-folder" style="color: #ffc107;"></span><label for="icon_size"><input id="icon_size" type="text" placeholder="50px" value="<?php echo $customize_icon_size; ?>" data-name="icon_size" name="wpdocs_options[icon_size]" /> <small><?php echo __('Icon Size', 'wp-docs'); ?></small></label></li>
            
            <li title="<?php echo __('Font Size', 'wp-docs'); ?>"><span class="fa fa-font" style="color: #ffc107;"></span><label for="font_size"><input id="font_size" type="text" placeholder="16px" value="<?php echo $customize_font_size; ?>" data-name="font_size" name="wpdocs_options[font_size]" /> <small><?php echo __('Font Size', 'wp-docs'); ?></small></label></li>
        </ul>
    </li>
    
    <?php if(!$wp_docs_tabs){ ?>
    <li class="addon-features"></li>
    
    <li>
    	
		<label for="wpdocs_addon">            
            <?php echo __('Do you need documents inside tabs?', 'wp-docs'); ?> <small><?php echo __('(Optional)', 'wp-docs'); ?></small>
        </label>
        <a href="https://wordpress.org/plugins/wp-responsive-tabs" target="_blank" title="<?php echo __('WP Responsive Tabs', 'wp-docs'); ?>">

        <img height="190" src="<?php echo $wpdocs_url; ?>img/wp-responsive-tabs.gif" />

        </a>
		<label for="wpdocs_addon">            
            <?php echo __('WP Responsive Tabs is a recommended tabs plugin.', 'wp-docs'); ?>
        </label>        
            
    </li>
	<?php } ?>

   	<li class="premium-shortcodes"></li>
   
	<li>
            
        [wpdocs<?php echo (isset($_GET['dir']) && is_numeric($_GET['dir']) && $_GET['dir']>0)?' dir="'.esc_attr($_GET['dir']).'"':''; ?> orderby="date" order="DESC"] <i class="fas fa-code" style="color:#ffc107"></i>
    
        <br>
    
        <small class="wp-docs-attribs"><?php echo __('Attribute Order By', 'wp-docs'); ?>: <b>title</b>, <b>date</b> and <b>modified</b></small><br />        
        <small class="wp-docs-attribs"><?php echo __('Attribute Order', 'wp-docs'); ?>: <b>ASC</b> and <b>DESC</b></small><br /><br />
        
        <br />
        
    	
    </li>     
        
    <li class="premium-features"></li>


	<li <?php echo (extension_loaded('imagick')?'':'style="display:none;"'); ?>>
	
	<?php $pdf_thumb = array('default', 'first_page_as_thumbnail'); ?>    
    	<label for="wpdocs_options_pdf_thumb">

            <?php echo __('Display PDF File Thumbnail?', 'wp-docs'); ?> <br />
            <select name="wpdocs_options[pdf_thumb]" data-name="pdf_thumb" id="wpdocs_options_pdf_thumb">
            	<?php if(!empty($pdf_thumb)){ foreach($pdf_thumb as $alt_type){ ?>
                	<option value="<?php echo $alt_type; ?>" <?php selected($alt_type==$pdf_thumb_selected); ?>><?php echo wpdoc_humanize($alt_type); ?></option>
                <?php } } ?>
            	
            </select>
            <br />
            <small style="float:right;"><?php echo __('(Default: Filename)', 'wp-docs'); ?></small>
        </label>
    </li>
       
    <li>
<?php $filename_alt = array('default', 'filename', 'post_title', 'post_description'); ?>
        <label for="wpdocs_options_filename">

            <?php echo __('Display Filename, Post Title or Post Description?', 'wp-docs'); ?> <br />
            <select name="wpdocs_options[filename_alt]" data-name="filename_alt" id="wpdocs_options_filename">
            	<?php if(!empty($filename_alt)){ foreach($filename_alt as $alt_type){ ?>
                	<option value="<?php echo $alt_type; ?>" <?php selected($alt_type==$alt_filename); ?>><?php echo wpdoc_humanize($alt_type); ?></option>
                <?php } } ?>
            	
            </select>
            <br />
            <small style="float:right;"><?php echo __('(Default: Filename)', 'wp-docs'); ?></small>
        </label>

    </li>   
    
    <li>
        <label for="details_view_sorting">
            <input <?php checked($details_view_sorting); ?> type="checkbox" name="wpdocs_options[details_view_sorting]" value="details_view_sorting" id="details_view_sorting"  />
            <?php echo __('Sortable Columns?', 'wp-docs'); ?> <i class="fas fa-sort" style="color:#ffc107"></i>
        </label>
    </li>
    
    <li>
        <label for="ajax_based_deep_search">
            <input <?php checked($ajax_based_deep_search); ?> type="checkbox" name="wpdocs_options[ajax_based_deep_search]" value="ajax_based_deep_search" id="ajax_based_deep_search"  />
            <?php echo __('Ajax Based Deep Search?', 'wp-docs'); ?> <i title="<?php echo __('All child directories and files can be searched with this option.', 'wp-docs'); ?>" class="fas fa-search" style="color:#ffc107"></i>
        </label>
    </li>
                  
    <li class="<?php echo $dir?'wpdocs-specific-sections':'wpdocs-general-sections'; ?>">

        <button type="button" data-dir_id="<?php echo $dir_id; ?>" class="modal-btn btn-sm btn-danger border rounded-pill shadow-sm mb-2" data-toggle="modal" data-target="#right_modal_sm"><i class="fas fa-folder-plus"></i> <?php echo __('Directory Specific Settings?', 'wp-docs'); ?> <i class="fa fa-angle-right pl-2"></i></button>
        <div class="modal modal-right fade" id="right_modal_sm" tabindex="-1" role="dialog" aria-labelledby="right_modal_sm">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder-plus"></i> <?php echo __('Directory Specific Settings?', 'wp-docs'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
                
                <?php wpdocs_specific_directory_settings($dir_id, $is_file, $is_current_user_files, $is_del_from_front, $allowed_role, $allowed_ext, $default_ext); ?>


            </div>
            <div class="modal-footer modal-footer-fixed">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert_dir alert-secondary fade in alert-dismissible d-none mx-auto mt-4" style="width: 90%">
                            <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo __('Close', 'wp-docs'); ?>">
                                <span aria-hidden="true" style="font-size:20px">×</span>
                            </button>    <strong><?php echo __('Success!', 'wp-docs'); ?></strong> <?php echo __('Options are updated successfully.', 'wp-docs'); ?>
                        </div>
                    </div>

                    <div class="col-md-12 mt-3">
                        <button type="button" class="btn-sm btn-danger float-right save_changes ml-3"><?php echo __('Save changes', 'wp-docs'); ?></button>
                        <button type="button" class="btn-sm btn-light float-right" data-dismiss="modal"><?php echo __('Close', 'wp-docs'); ?></button>
                    </div>
                </div>



            </div>
            </div>
        </div>
        </div>

        <?php wpdocs_specific_directory_settings(false, $is_file, $is_current_user_files, $is_del_from_front, $allowed_role, $allowed_ext, $default_ext); ?>


    </li>    
    <li>
        <label for="wpdocs_options_ajax">
            <input <?php checked($is_ajax); ?> type="checkbox" name="wpdocs_options[ajax]" value="ajax" id="wpdocs_options_ajax"  />
            <?php echo __('Ajax Based Directory Navigation', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Optional)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small>
        </label>
        <ul class="ml-4 <?php echo $is_ajax ? '' : 'd-none'?>">
            <li>
                <label for="wpdocs_options_ajax_url">
                    <input <?php checked($is_ajax && $is_ajax_url); ?> type="checkbox" name="wpdocs_options[ajax_url]" value="ajax_url" id="wpdocs_options_ajax_url"  />
                    <?php echo __('Update URI', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Works well for single instance)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small>
                </label>
            </li>
        </ul>
    </li>
    
    
	<li>
        <label for="wpdocs_options_customization">            
            <?php echo __('Appearance Customization', 'wp-docs'); ?> <small><?php echo $wpdocs_pro?__('(Optional)', 'wp-docs'):__('(Premium)', 'wp-docs'); ?></small>
        </label>
        <ul class="ml-4">
            <li>
                <label for="wpdocs_options_box_bg_color">
                    <input type="color" name="wpdocs_options[box_bg_color]" value="<?php echo  $box_bg_color = array_key_exists('box_bg_color', $wpdocs_options)?$wpdocs_options['box_bg_color']:''; ?>" id="box_bg_color"  />
                    <?php echo __('Directory Background Color', 'wp-docs'); ?> <small></small> <i class="fas fa-palette" style="color:<?php echo ($box_bg_color?''.$box_bg_color.'':'#ffc107'); ?>"></i>
                </label>
            </li>
            <li>
                <label for="wpdocs_options_box_txt_color">
                    <input type="color" name="wpdocs_options[box_txt_color]" value="<?php echo  $box_txt_color = array_key_exists('box_txt_color', $wpdocs_options)?$wpdocs_options['box_txt_color']:''; ?>" id="box_txt_color"  />
                    <?php echo __('Directory Text Color', 'wp-docs'); ?> <small></small> <i class="fas fa-palette" style="color:<?php echo ($box_txt_color?''.$box_txt_color.'':'#ffc107'); ?>"></i>
                </label>
            </li> 
            
            <li>
                <label for="wpdocs_options_box_hbg_color">
                    <input type="color" name="wpdocs_options[box_hbg_color]" value="<?php echo  $box_hbg_color = array_key_exists('box_hbg_color', $wpdocs_options)?$wpdocs_options['box_hbg_color']:''; ?>" id="box_hbg_color"  />
                    <?php echo __('Directory Hover Background Color', 'wp-docs'); ?> <small></small> <i class="fas fa-palette" style="color:<?php echo ($box_hbg_color?''.$box_hbg_color.'':'#ffc107'); ?>"></i>
                </label>
            </li>  
            
            <li>
                <label for="wpdocs_options_box_htxt_color">
                    <input type="color" name="wpdocs_options[box_htxt_color]" value="<?php echo $box_htxt_color = array_key_exists('box_htxt_color', $wpdocs_options)?$wpdocs_options['box_htxt_color']:''; ?>" id="box_htxt_color"  />
                    <?php echo __('Directory Hover Text Color', 'wp-docs'); ?> <small></small> <i class="fas fa-palette" style="color:<?php echo ($box_htxt_color?''.$box_htxt_color.'':'#ffc107'); ?>"></i>
                </label>
            </li>                                    
        </ul>
    </li>    
</ul>



<a class="btn btn-warning btn-sm mx-auto " href="http://demo.androidbubble.com/educational-institution" target="_blank" title="<?php echo __('Click here for demo', 'wp-docs'); ?>"><?php echo __('Click here for demo', 'wp-docs'); ?></a>


<ul class="col col-md-12 mt-4">
	<li class="promotions"></li>
    <li style="text-align:center;">
    <a href="https://wordpress.org/plugins/gulri-slider" target="_blank" title="<?php echo __('Image Slider', 'wp-docs'); ?>"><img src="<?php echo $wpdocs_url; ?>img/gslider.gif" /></a>
    </li>
</ul>
</div>

</div>

	
</div>
</div>
<div class="nav-tab-content hide" data-content="translate">
	<div class="translate_wpdocs_urls">
        <textarea id="translate_wpdocs"></textarea>
        <input id="translate_wpdocs_btn" type="button" value="<?php _e('Translate', 'wp-docs'); ?>" />
        <div class="translate_wpdocs"></div>
    </div>
</div>


<div class="nav-tab-content container-fluid hides tab-help" data-content="help">
    
        <div class="row mt-3">
        
            <ul class="position-relative">
                <li><a class="btn btn-sm btn-info" href="https://wordpress.org/support/plugin/wp-docs/" target="_blank" aria-label="<?php _e('Open a Ticket on Support Forums', 'wp-docs'); ?> (Opens in a new window)"><?php _e('Open a Ticket on Support Forums', 'wp-docs'); ?> &nbsp;<i class="fas fa-tag"></i></a></li>
                <li><a class="btn btn-sm btn-warning" href="http://demo.androidbubble.com/contact/" target="_blank" aria-label="<?php _e('Contact Developer', 'wp-docs'); ?> (Opens in a new window)"><?php _e('Contact Developer', 'wp-docs'); ?> &nbsp;<i class="fas fa-headset"></i></a></li>
                <li><a class="btn btn-sm btn-secondary" href="<?php echo $wpdocs_premium_link; ?>/?help" target="_blank" aria-label="<?php _e('Need Urgent Help?', 'wp-docs'); ?> (Opens in a new window)"><?php _e('Need Urgent Help?', 'wp-docs'); ?> &nbsp;<i class="fas fa-phone"></i></i></a></li>
                
               
                <li><iframe width="560" height="315" src="https://www.youtube.com/embed/k5bZqZ5dW30?t=<?php date('d'); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></li>
            </ul>                
        </div>
    
    </div>
    
</div>	
<style type="text/css">
.woocommerce-message, .update-nag, #message, .notice.notice-error, .error.notice{ display:none; }

</style>