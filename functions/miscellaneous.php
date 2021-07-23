<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_admin_notice')){
    function bc_admin_notice($admin_notice = '', $class = 'warning', $is_dismissible = false){
        if(!in_array($class, ['error', 'info', 'success', 'warning'])){
            $class = 'warning';
        }
        if($is_dismissible){
            $class .= ' is-dismissible';
        }
        return '<div class="notice notice-' . $class . '"><p>' . $admin_notice . '</p></div>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_are_plugins_active')){
    function bc_are_plugins_active($plugins = []){
        if(!is_array($plugins)){
            return false;
        }
        foreach($plugins as $plugin){
            if(!bc_is_plugin_active($plugin)){
                return false;
            }
        }
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_array_keys_exist')){
    function bc_array_keys_exist($keys = [], $array = []){
        if(!is_array($keys) or !is_array($array)){
            return false;
        }
        foreach($keys as $key){
            if(!array_key_exists($key, $array)){
                return false;
            }
        }
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_copy')){
    function bc_copy($source = '', $destination = '', $overwrite = false, $mode = false){
        global $wp_filesystem;
        $fs = bc_filesystem();
        if(is_wp_error($fs)){
            return $fs;
        }
        if(!$wp_filesystem->copy($source, $destination, $overwrite, $mode)){
            return bc_error(sprintf(__('The uploaded file could not be moved to %s.'), $destination));
        }
        return $destination;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_download')){
    function bc_download($url = '', $args = []){
        $args = wp_parse_args($args, [
            'filename' => '',
            'timeout' => 300,
        ]);
        if($args['filename']){
            if(!bc_in_uploads($args['filename'])){
                return bc_error(sprintf(__('Unable to locate needed folder (%s).'), __('The uploads directory')));
            }
        } else {
            $download_dir = bc_download_dir();
            if(is_wp_error($download_dir)){
                return $download_dir;
            }
            $args['filename'] = trailingslashit($download_dir) . uniqid() . '-' . bc_filename($url);
        }
        $args['stream'] = true;
        $args['timeout'] = bc_sanitize_timeout($args['timeout']);
        $response = bc_remote($url, $args)->get();
        if(!$response->success){
            @unlink($args['filename']);
            return $response->to_wp_error();
        }
        return $args['filename'];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_download_dir')){
    function bc_download_dir(){
        $upload_dir = wp_get_upload_dir();
        $dir = $upload_dir['basedir'] . '/bc-downloads';
        if(!wp_mkdir_p($dir)){
            return bc_error(__('Could not create directory.'));
        }
        if(!wp_is_writable($dir)){
            return bc_error(__('Destination directory for file streaming does not exist or is not writable.'));
        }
        return $dir;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_download_url')){
    function bc_download_url($dir = ''){
        $upload_dir = wp_get_upload_dir();
        if('' !== $dir){
            return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $dir);
        } else {
            return $upload_dir['baseurl'] . '/bc-downloads';
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_error')){
    function bc_error($message = '', $data = ''){
        if(!$message){
            $message = __('Something went wrong.');
        }
        return new WP_Error('bc_error', $message, $data);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_filename')){
    function bc_filename($filename = ''){
        return preg_replace('/\?.*/', '', wp_basename($filename));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_filesystem')){
    function bc_filesystem(){
        global $wp_filesystem;
        if($wp_filesystem instanceof WP_Filesystem_Direct){
            return true;
        }
        if(!function_exists('get_filesystem_method')){
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        if('direct' !== get_filesystem_method()){
            return bc_error(__('Could not access filesystem.'));
        }
        if(!WP_Filesystem()){
            return bc_error(__('Filesystem error.'));
        }
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_first_p')){
    function bc_first_p($text = '', $dot = true){
        return bc_one_p($text, $dot, 'first');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_get_post')){
    function bc_get_post($post = null){
        if(is_array($post)){
            $args = array_merge($post, [
                'posts_per_page' => 1,
            ]);
            $posts = get_posts($args);
            if($posts){
                return $posts[0];
            } else {
                return null;
            }
        } elseif(is_string($post) and 1 === preg_match('/^[a-z0-9]{13}$/', $post)){
            return bc_get_post([
                'meta_key' => 'bc_uniqid',
                'meta_value' => $post,
                'post_status' => 'any',
            ]);
        } else {
            return get_post($post);
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_in_uploads')){
    function bc_in_uploads($file = ''){
        $upload_dir = wp_get_upload_dir();
        return strpos($file, $upload_dir['basedir']) === 0;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_is_plugin_active')){
    function bc_is_plugin_active($plugin = ''){
        if(!function_exists('is_plugin_active')){
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        return is_plugin_active($plugin);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_last_p')){
    function bc_last_p($text = '', $dot = true){
        return bc_one_p($text, $dot, 'last');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_maybe_generate_attachment_metadata')){
    function bc_maybe_generate_attachment_metadata($attachment_id = 0){
        $attachment = get_post($attachment_id);
		if(null === $attachment){
			return false;
		}
        if('attachment' !== $attachment->post_type){
			return false;
		}
		wp_raise_memory_limit('image');
        if(!function_exists('wp_generate_attachment_metadata')){
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
		wp_maybe_generate_attachment_metadata($attachment);
		return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_move_uploaded_file')){
    function bc_move_uploaded_file($tmp_name = ''){
        global $wp_filesystem;
        $fs = bc_filesystem();
        if(is_wp_error($fs)){
            return $fs;
        }
        if(!$wp_filesystem->exists($tmp_name)){
            return bc_error(__('File does not exist! Please double check the name and try again.'));
        }
        $upload_dir = wp_upload_dir();
        $original_filename = wp_basename($tmp_name);
        $filename = wp_unique_filename($upload_dir['path'], $original_filename);
        $file = trailingslashit($upload_dir['path']) . $filename;
        $result = bc_copy($tmp_name, $file);
        if(is_wp_error($result)){
            return $result;
        }
        return $file;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_one_p')){
    function bc_one_p($text = '', $dot = true, $p = 'first'){
        if(false === strpos($text, '.')){
            if($dot){
                $text .= '.';
            }
            return $text;
        } else {
            $text = explode('.', $text);
            $text = array_filter($text);
            switch($p){
                case 'first':
                    $text = array_shift($text);
                    break;
                case 'last':
                    $text = array_pop($text);
                    break;
                default:
                    $text = __('Error');
            }
            if($dot){
                $text .= '.';
            }
            return $text;
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_post_type_labels')){
    function bc_post_type_labels($singular = '', $plural = '', $all = true){
        if(!$singular or !$plural){
            return [];
        }
        return [
            'name' => $plural,
            'singular_name' => $singular,
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $singular,
            'edit_item' => 'Edit ' . $singular,
            'new_item' => 'New ' . $singular,
            'view_item' => 'View ' . $singular,
            'view_items' => 'View ' . $plural,
            'search_items' => 'Search ' . $plural,
            'not_found' => 'No ' . strtolower($plural) . ' found.',
            'not_found_in_trash' => 'No ' . strtolower($plural) . ' found in Trash.',
            'parent_item_colon' => 'Parent ' . $singular . ':',
            'all_items' => ($all ? 'All ' : '') . $plural,
            'archives' => $singular . ' Archives',
            'attributes' => $singular . ' Attributes',
            'insert_into_item' => 'Insert into ' . strtolower($singular),
            'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($singular),
            'featured_image' => 'Featured image',
            'set_featured_image' => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image' => 'Use as featured image',
            'filter_items_list' => 'Filter ' . strtolower($plural) . ' list',
            'items_list_navigation' => $plural . ' list navigation',
            'items_list' => $plural . ' list',
            'item_published' => $singular . ' published.',
            'item_published_privately' => $singular . ' published privately.',
            'item_reverted_to_draft' => $singular . ' reverted to draft.',
            'item_scheduled' => $singular . ' scheduled.',
            'item_updated' => $singular . ' updated.',
        ];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_remote')){
    function bc_remote($url = '', $args = []){
        if(!class_exists('BC_Remote')){
            require_once(plugin_dir_path(BC_FUNCTIONS) . 'classes/remote.php');
        }
        return new BC_Remote($url, $args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_response')){
    function bc_response($response = null){
        if(!class_exists('BC_Response')){
            require_once(plugin_dir_path(BC_FUNCTIONS) . 'classes/response.php');
        }
        return new BC_Response($response);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_sanitize_timeout')){
    function bc_sanitize_timeout($timeout = 0){
        $timeout = (int) $timeout;
        $max_execution_time = (int) ini_get('max_execution_time');
        if(0 !== $max_execution_time){
            if(0 === $timeout or $timeout > $max_execution_time){
                $timeout = $max_execution_time - 1; // Prevents error 504
            }
        }
        if(bc_seems_cloudflare()){
            if(0 === $timeout or $timeout > 99){
                $timeout = 99; // Prevents error 524: https://support.cloudflare.com/hc/en-us/articles/115003011431#524error
            }
        }
        return $timeout;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_upload')){
    function bc_upload($file = '', $post_id = 0){
        global $wp_filesystem;
        $fs = bc_filesystem();
        if(is_wp_error($fs)){
            return $fs;
        }
        if(!$wp_filesystem->exists($file)){
            return bc_error(__('File does not exist! Please double check the name and try again.'));
        }
        if(!bc_in_uploads($file)){
            return bc_error(sprintf(__('Unable to locate needed folder (%s).'), __('The uploads directory')));
        }
        $filename = wp_basename($file);
        $filetype_and_ext = wp_check_filetype_and_ext($file, $filename);
        if(!$filetype_and_ext['type']){
            return bc_error(__('Sorry, this file type is not permitted for security reasons.'));
        }
        $upload_dir = wp_get_upload_dir();
        $attachment_id = wp_insert_attachment([
            'guid' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file),
            'post_mime_type' => $filetype_and_ext['type'],
            'post_status' => 'inherit',
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
        ], $file, $post_id, true);
        if(is_wp_error($attachment_id)){
            return $attachment_id;
        }
        bc_maybe_generate_attachment_metadata($attachment_id);
        return $attachment_id;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_upload_file')){
    function bc_upload_file($tmp_name = '', $post_id = 0){
        $file = bc_move_uploaded_file($tmp_name);
        if(is_wp_error($file)){
            return $file;
        }
        return bc_upload($file, $post_id);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use')){
    function bc_use($url = '', $dir = ''){
        global $wp_filesystem;
        $md5 = md5($url);
        $option = 'bc_downloaded_library_' . $md5;
        $value = get_option($option, '');
        if('' !== $value){
            return $value;
        }
        $download_dir = bc_download_dir();
        if(is_wp_error($download_dir)){
            return $download_dir;
        }
        $to = $download_dir . '/' . $md5;
        if($dir){
            $dir = ltrim($dir, '/');
            $dir = untrailingslashit($dir);
            $dir = trailingslashit($to) . $dir;
        } else {
            $dir = $to;
        }
        $fs = bc_filesystem();
        if(is_wp_error($fs)){
            return $fs;
        }
        if(!$wp_filesystem->dirlist($dir, false)){
            $file = bc_download($url);
            if(is_wp_error($file)){
                return $file;
            }
            $result = unzip_file($file, $to);
            if(is_wp_error($result)){
                @unlink($file);
                $wp_filesystem->rmdir($to, true);
                return $result;
            }
            @unlink($file);
            if(!$wp_filesystem->dirlist($dir, false)){
                return bc_error(__('Destination directory for file streaming does not exist or is not writable.'));
            }
            update_option($option, $dir);
        }
        return $dir;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
