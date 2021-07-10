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

if(!function_exists('bc_build_update_checker')){
    function bc_build_update_checker(...$args){
        //return Puc_v4_Factory::buildUpdateChecker(...$args);
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
            return bc_error(sprintf(__('Unable to locate needed folder (%s).'), 'uploads'));
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
