<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_add_image_size')){
    function bc_add_image_size($name = '', $width = 0, $height = 0, $crop = false){
        if(!array_key_exists('image_sizes', $GLOBALS['bc'])){
            $GLOBALS['bc']['image_sizes'] = [];
        }
		$size = sanitize_title($name);
        if(!array_key_exists($size, $GLOBALS['bc']['image_sizes'])){
            $GLOBALS['bc']['image_sizes'][$size] = $name;
			add_image_size($size, $width, $height, $crop);
        }
        bc_one('image_size_names_choose', function($sizes){
            if(!$GLOBALS['bc']['image_sizes']){
                return $sizes;
            }
			foreach($GLOBALS['bc']['image_sizes'] as $size => $name){
				$sizes[$size] = $name;
			}
            return $sizes;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_add_larger_image_sizes')){
    function bc_add_larger_image_sizes(){
        bc_add_image_size('HD', 1280, 1280);
        bc_add_image_size('Full HD', 1920, 1920);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_attachment_url_to_postid')){
    function bc_attachment_url_to_postid($url = ''){
        $post_id = bc_guid_to_postid($url);
        if($post_id){
            return $post_id;
        }
        preg_match('/^(.+)(\-\d+x\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // resized
        if($matches){
            $url = $matches[1];
            if(isset($matches[3])){
                $url .= $matches[3];
            }
            $post_id = bc_guid_to_postid($url);
            if($post_id){
                return $post_id;
            }
        }
        preg_match('/^(.+)(\-scaled)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // scaled
        if($matches){
            $url = $matches[1];
            if(isset($matches[3])){
                $url .= $matches[3];
            }
            $post_id = bc_guid_to_postid($url);
            if($post_id){
                return $post_id;
            }
        }
        preg_match('/^(.+)(\-e\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // edited
        if($matches){
            $url = $matches[1];
            if(isset($matches[3])){
                $url .= $matches[3];
            }
            $post_id = bc_guid_to_postid($url);
            if($post_id){
                return $post_id;
            }
        }
        return 0;
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

if(!function_exists('bc_fix_audio_video_ext')){
    function bc_fix_audio_video_ext(){
        bc_one('wp_check_filetype_and_ext', function($wp_check_filetype_and_ext, $file, $filename, $mimes, $real_mime){
            if($wp_check_filetype_and_ext['ext'] and $wp_check_filetype_and_ext['type']){
                return $wp_check_filetype_and_ext;
            }
            if(0 === strpos($real_mime, 'audio/') or 0 === strpos($real_mime, 'video/')){
                $filetype = wp_check_filetype($filename);
                if(in_array(substr($filetype['type'], 0, strcspn($filetype['type'], '/')), ['audio', 'video'])){
                    $wp_check_filetype_and_ext['ext'] = $filetype['ext'];
                    $wp_check_filetype_and_ext['type'] = $filetype['type'];
                }
            }
            return $wp_check_filetype_and_ext;
        }, 10, 5);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_guid_to_postid')){
    function bc_guid_to_postid($guid = ''){
        global $wpdb;
        $str = "SELECT ID FROM $wpdb->posts WHERE guid = %s";
        $sql = $wpdb->prepare($str, $guid);
        $post_id = $wpdb->get_var($sql);
        if(null === $post_id){
            return 0;
        }
		return (int) $post_id;
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

if(!function_exists('bc_is_extension_allowed')){
    function bc_is_extension_allowed($extension = ''){
        foreach(wp_get_mime_types() as $exts => $mime){
            if(preg_match('!^(' . $exts . ')$!i', $extension)){
                return true;
            }
        }
        return false;
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
        $to = $download_dir . '/' . bc_md5_to_uuid4($md5);
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
