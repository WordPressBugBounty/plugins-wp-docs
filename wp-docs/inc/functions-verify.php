<?php
/**
 * Memphis Documents Verification Functions
 * Corrected to match your existing statistics counting method
 */

add_action('wp_ajax_wp_docs_verify_memphis_docs_start', 'wp_docs_verify_memphis_docs_start');
add_action('wp_ajax_wp_docs_verify_memphis_docs_batch', 'wp_docs_verify_memphis_docs_batch');
add_action('wp_ajax_wp_docs_verify_memphis_docs_clear', 'wp_docs_verify_memphis_docs_clear');

/**
 * Start verification - Build queue from Memphis data
 */
function wp_docs_verify_memphis_docs_start() {
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_wpdocs_data(wp_unslash($_POST['nonce'])), 'wpdocs_verify_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    global $memphis_folders_id;
    
    // Clear any existing session
    delete_option('wpdocs_memphis_verify_session');
    
    $folders = get_option('mdocs-cats', array());
    $files = get_option('mdocs-list', array());
    
    // Get already imported items from your existing options
    $wpdocs_imported_folder = get_option('wpdocs_imported_folder', array());
    $wpdocs_imported_files = get_option('wpdocs_imported_files', array());
    
    $queue = array();
    
    wp_docs_build_verify_queue($folders, $files, $queue, 0, '');
    
    $session = array(
        'queue' => $queue,
        'stats' => array(
            'folders_total'   => 0,
            'folders_found'   => 0,
            'folders_missing' => 0,
            'files_total'     => 0,
            'files_found'     => 0,
            'files_missing'   => 0,
        ),
        'tree' => array(),
        'missing_folders' => array(),
        'missing_files'   => array(),
        'pointer' => 0,
        'imported_folders' => $wpdocs_imported_folder,
        'imported_files' => $wpdocs_imported_files
    );
    
    update_option('wpdocs_memphis_verify_session', $session);
    
    wp_send_json_success(array(
        'total_items' => count($queue)
    ));
}

/**
 * Build verification queue recursively
 */
function wp_docs_build_verify_queue($folders, $files, &$queue, $depth = 0, $parent_path = '') {
    
    if (empty($folders)) {
        return;
    }
    
    foreach ($folders as $folder) {
        
        $current_path = $parent_path ? $parent_path . ' > ' . $folder['name'] : $folder['name'];
        
        $queue[] = array(
            'type' => 'folder',
            'depth' => $depth,
            'path' => $current_path,
            'data' => $folder,
        );
        
        // Add files belonging to this folder
        if (!empty($files)) {
            foreach ($files as $file) {
                if (isset($file['cat']) && $file['cat'] == $folder['slug']) {
                    $queue[] = array(
                        'type' => 'file',
                        'depth' => ($depth + 1),
                        'path' => $current_path,
                        'data' => $file,
                    );
                }
            }
        }
        
        // Process children recursively
        if (!empty($folder['children'])) {
            wp_docs_build_verify_queue($folder['children'], $files, $queue, ($depth + 1), $current_path);
        }
    }
}

/**
 * Process verification batch
 */
function wp_docs_verify_memphis_docs_batch() {
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_wpdocs_data(wp_unslash($_POST['nonce'])), 'wpdocs_verify_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    global $memphis_folders_id;
    
    $session = get_option('wpdocs_memphis_verify_session', array());
    
    if (empty($session)) {
        wp_send_json_error('Session expired. Please restart verification.');
    }
    
    $batch_size = 100;
    $queue = $session['queue'];
    $pointer = intval($session['pointer']);
    $end = min($pointer + $batch_size, count($queue));
    
    for ($i = $pointer; $i < $end; $i++) {
        
        $item = $queue[$i];
        
        if ($item['type'] == 'folder') {
			
			$folder = $item['data'];
			
			// Build the same slug key used during import
			$slug_key = $memphis_folders_id . '_' . $folder['slug'];
			
			// Check using your existing imported folders array FIRST (for speed)
			$status = in_array($slug_key, $session['imported_folders']) ? 'found' : 'missing';
			
			// Double-check with database for accuracy
			$args = array(
				'post_type' => 'wpdocs_folder',
				'posts_per_page' => 1,
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
			
			$found_posts = get_posts($args);
			
			$folder_id = 0; // Initialize folder ID
			
			if (!empty($found_posts)) {
				$status = 'found';
				$folder_id = $found_posts[0]; // Get the WP Docs post ID
			} else {
				$status = 'missing';
			}
			
			$session['stats']['folders_total']++;
			
			if ($status == 'found') {
				$session['stats']['folders_found']++;
			} else {
				$session['stats']['folders_missing']++;
				$session['missing_folders'][] = array(
					'slug' => $folder['slug'],
					'name' => $folder['name'],
					'path' => $item['path'],
					'parent' => $folder['parent']
				);
			}
			
			$session['tree'][] = array(
				'type' => 'folder',
				'name' => stripslashes($folder['name']),
				'slug' => $folder['slug'],
				'id' 	=> $folder_id,  // ADD THIS LINE - WP Docs folder ID for clickable link
				'depth' => $item['depth'],
				'path' => $item['path'],
				'status' => $status
			);
			
		} else {
			
			$file = $item['data'];
						
			$session['stats']['files_total']++;
						
			// METHOD 1: Check using your existing imported_files option
			$status = in_array($file['id'], $session['imported_files']) ? 'found' : 'missing';
			
			// First, find the destination folder ID
			$slug_key = $memphis_folders_id . '_' . $file['cat'];
			$folder_args = array(
				'post_type' => 'wpdocs_folder',
				'posts_per_page' => 1,
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
			
			$folder_posts = get_posts($folder_args);
			$expected_folder_id = !empty($folder_posts) ? $folder_posts[0] : 0;
			
			// Search for attachment with OR logic for backward compatibility
			$file_exists = false;
			
			if ($expected_folder_id) {
				$args = array(
					'post_type' => 'attachment',
					'posts_per_page' => -1,
					'post_status' => 'any',
					'post_parent' => $expected_folder_id,
					'fields' => 'ids',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key' => '_wpdocs_memphis_media_file',
							'value' => 'true',
							'compare' => '='
						),
						array(
							'key' => '_wpdocs_memphis_file_id',
							'value' => $file['id'],
							'compare' => '='
						)
					)
				);
				
				$found_attachments = get_posts($args);
				
				if (!empty($found_attachments)) {
					$file_exists = true;
				}
			}
			
			// Also check globally (in case parent is different) - with OR logic
			if (!$file_exists) {
				$args = array(
					'post_type' => 'attachment',
					'posts_per_page' => -1,
					'post_status' => 'any',
					'fields' => 'ids',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key' => '_wpdocs_memphis_media_file',
							'value' => 'true',
							'compare' => '='
						),
						array(
							'key' => '_wpdocs_memphis_file_id',
							'value' => $file['id'],
							'compare' => '='
						)
					)
				);
				
				$found_attachments = get_posts($args);
				
				if (!empty($found_attachments)) {
					// Verify it's the correct file by filename
					foreach ($found_attachments as $attachment_id) {
						$attached_file = get_post_meta($attachment_id, '_wp_attached_file', true);
						$filename = isset($file['filename']) ? $file['filename'] : '';
						
						if ($attached_file && $filename && strpos($attached_file, $filename) !== false) {
							$file_exists = true;
							break;
						}
					}
				}
			}
			
			if ($file_exists) {
				$status = 'found';
			} else {
				$status = 'missing';
			}
			
			// Update status based on actual file existence
			if ($status == 'found') {
				$session['stats']['files_found']++;
			} else {
				$session['stats']['files_missing']++;
				$session['missing_files'][] = array(
					'id' => $file['id'],
					'name' => isset($file['name']) && !empty($file['name']) ? $file['name'] : basename($file['filename'], '.pdf'),
					'filename' => $file['filename'],
					'cat' => $file['cat'],
					'path' => $item['path']
				);
			}
			
			$file_name = isset($file['name']) && !empty($file['name']) 
				? $file['name'] 
				: (isset($file['filename']) ? basename($file['filename'], '.pdf') : 'Unknown File');
			
			$session['tree'][] = array(
				'type' => 'file',
				'name' => $file_name,
				'id' => $file['id'],
				'depth' => $item['depth'],
				'path' => $item['path'],
				'status' => $status
			);
		}
    }
    
    $session['pointer'] = $end;
    update_option('wpdocs_memphis_verify_session', $session);
    
    $completed = ($end >= count($queue));
    
    // Prepare current item for display
    $current_item = array();
    if (isset($queue[$end - 1])) {
        $current_item = $queue[$end - 1];
        if ($current_item['type'] == 'folder') {
            $current_item['display_name'] = $current_item['data']['name'];
        } else {
            $current_item['display_name'] = isset($current_item['data']['name']) && !empty($current_item['data']['name'])
                ? $current_item['data']['name']
                : basename($current_item['data']['filename'], '.pdf');
        }
    }
    
    wp_send_json_success(array(
        'completed' => $completed,
        'processed' => $end,
        'total' => count($queue),
        'percent' => (count($queue) ? round(($end / count($queue)) * 100, 2) : 100),
        'current_item' => $current_item,
        'stats' => $session['stats'],
        'tree' => $session['tree']
    ));
}

/**
 * Clear verification session
 */
function wp_docs_verify_memphis_docs_clear() {
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_wpdocs_data(wp_unslash($_POST['nonce'])), 'wpdocs_verify_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    delete_option('wpdocs_memphis_verify_session');
    
    wp_send_json_success();
}

/**
 * Missing Items Migration Functions
 * Add to functions-verify.php
 */

add_action('wp_ajax_wp_docs_import_missing_memphis_start', 'wp_docs_import_missing_memphis_start');
add_action('wp_ajax_wp_docs_import_missing_memphis_batch', 'wp_docs_import_missing_memphis_batch');

/**
 * Start missing items import - Build queue from missing items
 */
function wp_docs_import_missing_memphis_start() {
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_wpdocs_data(wp_unslash($_POST['nonce'])), 'wpdocs_verify_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Get verification session which contains missing items
    $verify_session = get_option('wpdocs_memphis_verify_session', array());
    
    if (empty($verify_session)) {
        wp_send_json_error('No verification session found. Please run verification first.');
    }
    
    global $memphis_folders_id;
    
    // Build import queue from missing items
    $queue = array();
    $all_folders = get_option('mdocs-cats', array());
    $all_files = get_option('mdocs-list', array());
    
    // First, add all missing folders (need to preserve hierarchy)
    $missing_folders = $verify_session['missing_folders'];
    
    // Build a map of all folders by slug for quick lookup
    $folders_by_slug = array();
    wp_docs_build_folders_map($all_folders, $folders_by_slug);
    
    // Sort missing folders by depth (parents first)
    $missing_folders = wp_docs_sort_folders_by_depth($missing_folders, $folders_by_slug);
    
    foreach ($missing_folders as $folder) {
        $queue[] = array(
            'type' => 'folder',
            'slug' => $folder['slug'],
            'name' => $folder['name'],
            'parent' => $folder['parent'],
            'path' => $folder['path']
        );
    }
    
    // Add all missing files
    foreach ($verify_session['missing_files'] as $file) {
        $queue[] = array(
            'type' => 'file',
            'id' => $file['id'],
            'name' => $file['name'],
            'filename' => $file['filename'],
            'cat' => $file['cat'],
            'path' => $file['path']
        );
    }
    
    $import_session = array(
        'queue' => $queue,
        'pointer' => 0,
        'stats' => array(
            'folders_total' => count($missing_folders),
            'folders_imported' => 0,
            'folders_failed' => 0,
            'files_total' => count($verify_session['missing_files']),
            'files_imported' => 0,
            'files_failed' => 0
        ),
        'failed_items' => array()
    );
    
    update_option('wpdocs_memphis_import_session', $import_session);
    
    wp_send_json_success(array(
        'total_items' => count($queue),
        'folders_total' => count($missing_folders),
        'files_total' => count($verify_session['missing_files'])
    ));
}

/**
 * Build a map of folders by slug for parent lookup
 */
function wp_docs_build_folders_map($folders, &$map, $parent = '0') {
    foreach ($folders as $folder) {
        $map[$folder['slug']] = array(
            'name' => $folder['name'],
            'parent' => $folder['parent'],
            'children' => $folder['children']
        );
        if (!empty($folder['children'])) {
            wp_docs_build_folders_map($folder['children'], $map, $folder['slug']);
        }
    }
}

/**
 * Sort folders by depth (parents before children)
 */
function wp_docs_sort_folders_by_depth($folders, $folders_map) {
    // Simple approach: order by path length (more slashes = deeper)
    usort($folders, function($a, $b) {
        $depth_a = substr_count($a['path'], ' > ');
        $depth_b = substr_count($b['path'], ' > ');
        return $depth_a - $depth_b;
    });
    return $folders;
}

/**
 * Process missing items import batch
 */
function wp_docs_import_missing_memphis_batch() {
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_wpdocs_data(wp_unslash($_POST['nonce'])), 'wpdocs_verify_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    global $memphis_folders_id;
    
    $session = get_option('wpdocs_memphis_import_session', array());
    
    if (empty($session)) {
        wp_send_json_error('Import session expired. Please restart.');
    }
    
    $batch_size = 10; // Smaller batch for reliability
    $queue = $session['queue'];
    $pointer = intval($session['pointer']);
    $end = min($pointer + $batch_size, count($queue));
    
    for ($i = $pointer; $i < $end; $i++) {
        
        $item = $queue[$i];
        
        if ($item['type'] == 'folder') {
            $result = wp_docs_import_single_folder($item);
            if ($result) {
                $session['stats']['folders_imported']++;
            } else {
                $session['stats']['folders_failed']++;
                $session['failed_items'][] = $item;
            }
        } else {
            $result = wp_docs_import_single_file($item);
            if ($result) {
                $session['stats']['files_imported']++;
            } else {
                $session['stats']['files_failed']++;
                $session['failed_items'][] = $item;
            }
        }
    }
    
    $session['pointer'] = $end;
    update_option('wpdocs_memphis_import_session', $session);
    
    $completed = ($end >= count($queue));
    
    // Prepare current item for display
    $current_item = array();
    if (isset($queue[$end - 1])) {
        $current_item = $queue[$end - 1];
        if ($current_item['type'] == 'folder') {
            $current_item['display_name'] = $current_item['name'];
        } else {
            $current_item['display_name'] = $current_item['name'];
        }
    }
    
    wp_send_json_success(array(
        'completed' => $completed,
        'processed' => $end,
        'total' => count($queue),
        'percent' => (count($queue) ? round(($end / count($queue)) * 100, 2) : 100),
        'current_item' => $current_item,
        'stats' => $session['stats']
    ));
}

/**
 * Import a single missing folder
 */
function wp_docs_import_single_folder($folder) {
    global $memphis_folders_id;
    
    // Check if folder already exists (double-check)
    $slug_key = $memphis_folders_id . '_' . $folder['slug'];
    $args = array(
        'post_type' => 'wpdocs_folder',
        'posts_per_page' => 1,
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
    
    $existing = get_posts($args);
    if (!empty($existing)) {
        return true; // Already exists
    }
    
    // Find parent folder ID
    $parent_id = 0;
    if ($folder['parent'] != '0') {
        $parent_slug_key = $memphis_folders_id . '_' . $folder['parent'];
        $parent_args = array(
            'post_type' => 'wpdocs_folder',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_wpdocs_memphis_slug',
                    'value' => $parent_slug_key,
                    'compare' => '='
                )
            )
        );
        $parent_posts = get_posts($parent_args);
        if (!empty($parent_posts)) {
            $parent_id = $parent_posts[0];
        } else {
            // Parent not found - try to find by name as fallback
            $parent_args = array(
                'post_type' => 'wpdocs_folder',
                'posts_per_page' => 1,
                'post_status' => 'any',
                'title' => $folder['parent_name'] ?? '',
                'fields' => 'ids'
            );
            $parent_posts = get_posts($parent_args);
            if (!empty($parent_posts)) {
                $parent_id = $parent_posts[0];
            }
        }
    }
    
    // Create the folder
    $post_data = array(
        'post_title' => $folder['name'],
        'post_type' => 'wpdocs_folder',
        'post_status' => 'hidden',
        'post_author' => get_current_user_id(),
        'post_parent' => $parent_id
    );
    
    $folder_id = wp_insert_post($post_data);
    
    if ($folder_id && !is_wp_error($folder_id)) {
        update_post_meta($folder_id, '_wpdocs_memphis_slug', $slug_key);
        return true;
    }
    
    return false;
}

/**
 * Import a single missing file
 */
function wp_docs_import_single_file($file) {
    global $memphis_folders_id;
    
    $attachment_id = $file['id'];  // The attachment already exists in WordPress
    
    // Check if file already has the Memphis meta key
    $already_imported = get_post_meta($attachment_id, '_wpdocs_memphis_media_file', true);
    if ($already_imported) {
        return true; // Already imported
    }
    
    // Find the destination folder
    $slug_key = $memphis_folders_id . '_' . $file['cat'];
    $folder_args = array(
        'post_type' => 'wpdocs_folder',
        'posts_per_page' => 1,
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
    
    $folder_posts = get_posts($folder_args);
    if (empty($folder_posts)) {
        error_log("WP Docs: Cannot find destination folder for file ID: " . $attachment_id);
        return false;
    }
    
    $folder_id = $folder_posts[0];
    
    // Add Memphis meta keys to existing attachment (matching original migration)
    update_post_meta($attachment_id, '_wpdocs_memphis_media_file', 'true');
    update_post_meta($attachment_id, '_wpdocs_memphis_file_id', $attachment_id);
    
    // Add to folder's items list (matching original migration)
    $wpdocs_items = get_post_meta($folder_id, 'wpdocs_items', true);
    $wpdocs_items = is_array($wpdocs_items) ? $wpdocs_items : array();
    
    if (!in_array($attachment_id, $wpdocs_items)) {
        $wpdocs_items[] = $attachment_id;
        update_post_meta($folder_id, 'wpdocs_items', $wpdocs_items);
    }
    
    // Also update user-specific items if needed (matching original migration)
    $current_user = get_current_user_id();
    $wpdocs_items_by_user = get_post_meta($folder_id, 'wpdocs_items_by_user', true);
    $wpdocs_items_by_user = is_array($wpdocs_items_by_user) ? $wpdocs_items_by_user : array();
    
    if (!isset($wpdocs_items_by_user[$current_user])) {
        $wpdocs_items_by_user[$current_user] = array();
    }
    
    if (!in_array($attachment_id, $wpdocs_items_by_user[$current_user])) {
        $wpdocs_items_by_user[$current_user][] = $attachment_id;
        update_post_meta($folder_id, 'wpdocs_items_by_user', $wpdocs_items_by_user);
    }
    
    return true;
}

/**
 * Clear import session
 */
add_action('wp_ajax_wp_docs_import_missing_memphis_clear', 'wp_docs_import_missing_memphis_clear');

function wp_docs_import_missing_memphis_clear() {
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_wpdocs_data(wp_unslash($_POST['nonce'])), 'wpdocs_verify_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    delete_option('wpdocs_memphis_import_session');
    
    wp_send_json_success();
}