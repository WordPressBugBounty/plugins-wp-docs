<?php defined( 'ABSPATH' ) or die( 'No fankari bachay!' );
/*
Plugin Name: WP Docs
Plugin URI: http://androidbubble.com/blog/wp-docs
Description: A documents management tool for education portals.
Author: Fahad Mahmood
Version: 2.1.8
Text Domain: wp-docs
Domain Path: /languages
Author URI: https://profiles.wordpress.org/fahadmahmood/
License: GPL2
	
This WordPress Plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This free software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.	
*/

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	if(isset($_GET['page']) && $_GET['page']=='wpdocs'){
		//require_once __DIR__ . '/spatie/vendor/autoload.php';
	}

	if(!function_exists('pre')){
	function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 	
	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 
	
	global $wpdocs_data, $wpdocs_pro, $wpdocs_premium_link, $wpdocs_dir, $wpdocs_levels, $wpdocs_versions_type, $wpdocs_url, $icon_sub_path, $wpdocs_options, $wpdocs_android_settings, $wp_docs_tabs, $wp_docs_is_memphis, $wpdocs_post_types, $wpdocs_post_status, $wpdocs_current_theme;
	
	$wp_docs_is_memphis = (defined('MDOC_PATH') || defined('MDOCS_PATH'));
	$wpdocs_data = get_plugin_data(__FILE__, true, false);
	$wpdocs_dir = plugin_dir_path( __FILE__ );
    $wpdocs_url = plugin_dir_url( __FILE__ );
	$wpdocs_versions_type = get_option('wpdocs_versions_type', 'old');
    $icon_sub_path = 'img/filetype-icons/';
    $wpdocs_options = get_option('wpdocs_options', array());
	$wpdocs_post_types = array('dir'=>'wpdocs_folder', 'shortcut'=>'wpdocs_shortcut');
	$wpdocs_post_status = 'hidden';
	$wpdocs_current_theme = str_replace(array('-', ' '), '_', strtolower(wp_get_theme()));
	
	$wp_docs_tabs = in_array( 'wp-responsive-tabs/index.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )	;


    $wpdocs_premium_link = 'https://shop.androidbubbles.com/product/wp-docs-pro';//https://shop.androidbubble.com/products/wordpress-plugin?variant=36439508320411';//
	
	
	$wpdocs_pro_file = $wpdocs_dir.'pro/wp-docs-pro.php';

		
	include_once 'inc/common.php';
	
    $wpdocs_pro = file_exists($wpdocs_pro_file);
    if($wpdocs_pro){
        include($wpdocs_pro_file);
    }	
	
	include_once('inc/functions.php');

	if(is_admin()){
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'wpdocs_plugin_links' );	
	}
	
	if($wp_docs_is_memphis){

		global $memphis_list_id, $memphis_folders_id, $memphis_folders_array, $memphis_files_array, $wpdocs_imported_folder, $wpdocs_imported_files, $wpdocs_memphis_list;



		$memphis_list_id = wp_docs_get_option_id('mdocs-list');
		$memphis_folders_id = wp_docs_get_option_id('mdocs-cats');

		$wpdocs_memphis_list = get_option('wpdocs_memphis_list', array());
		$wpdocs_memphis_list = (is_array($wpdocs_memphis_list) ? $wpdocs_memphis_list : array());

		$memphis_folders_array = get_option('mdocs-cats', array());
		$memphis_folders_array = (is_array($memphis_folders_array) ? $memphis_folders_array : array());

		$memphis_files_array = get_option('mdocs-list', array());
		$memphis_files_array = (is_array($memphis_files_array) ? $memphis_files_array : array());

		$wpdocs_imported_folder = get_option('wpdocs_imported_folder', array());
		$wpdocs_imported_folder = (is_array($wpdocs_imported_folder) ? $wpdocs_imported_folder : array());

		$wpdocs_imported_files = get_option('wpdocs_imported_files', array());	
		$wpdocs_imported_files = (is_array($wpdocs_imported_files) ? $wpdocs_imported_files : array());
		

	}