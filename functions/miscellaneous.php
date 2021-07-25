<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_add_admin_notice')){
    function bc_add_admin_notice($admin_notice = '', $class = 'error', $is_dismissible = false){
        if(!array_key_exists('admin_notices', $GLOBALS['bc'])){
            $GLOBALS['bc']['admin_notices'] = [];
        }
        $admin_notice = bc_admin_notice($admin_notice, $class, $is_dismissible);
        $md5 = md5($admin_notice);
        if(!array_key_exists($md5, $GLOBALS['bc']['admin_notices'])){
            $GLOBALS['bc']['admin_notices'][$md5] = $admin_notice;
        }
        bc_one('admin_notices', function(){
            if(!$GLOBALS['bc']['admin_notices']){
                return;
            }
            foreach($GLOBALS['bc']['admin_notices'] as $admin_notice){
                echo $admin_notice;
            }
        });
    }
}

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

if(!function_exists('bc_base64_urldecode')){
    function bc_base64_urldecode($data = '', $strict = false){
        return base64_decode(strtr($data, '-_', '+/'), $strict);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_base64_urlencode')){
    function bc_base64_urlencode($data = ''){
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_clone_role')){
    function bc_clone_role($source = '', $destination = '', $display_name = ''){
        $role = get_role($source);
        if(is_null($role)){
            return null;

        }
        return add_role(sanitize_title($destination), $display_name, $role->capabilities);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_current_screen_in')){
    function bc_current_screen_in($ids = []){
        global $current_screen;
        if(!is_array($ids)){
            return false;
        }
        if(!isset($current_screen)){
            return false;
        }
        return in_array($current_screen->id, $ids);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_current_screen_is')){
    function bc_current_screen_is($id = ''){
        global $current_screen;
        if(!is_string($id)){
            return false;
        }
        if(!isset($current_screen)){
            return false;
        }
        return ($current_screen->id === $id);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_custom_login_logo')){
    function bc_custom_login_logo($attachment_id = 0){
        if(!wp_attachment_is_image($attachment_id)){
            return;
        }
        bc_one('login_enqueue_scripts', function() use($attachment_id){
            $custom_logo = wp_get_attachment_image_src($attachment_id, 'medium'); ?>
            <style type="text/css">
                #login h1 a,
                .login h1 a {
                    background-image: url(<?php echo $custom_logo[0]; ?>);
                    background-size: <?php echo $custom_logo[1] / 2; ?>px <?php echo $custom_logo[2] / 2; ?>px;
                    height: <?php echo $custom_logo[2] / 2; ?>px;
                    width: <?php echo $custom_logo[1] / 2; ?>px;
                }
            </style><?php
		});
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_enqueue_floating_labels')){
    function bc_enqueue_floating_labels(){
        bc_one('wp_enqueue_scripts', function(){
            $src = plugin_dir_url(BC_FUNCTIONS) . 'assets/bc-floating-labels.js';
            $ver = filemtime(plugin_dir_path(BC_FUNCTIONS) . 'assets/bc-floating-labels.js');
            wp_enqueue_script('bc-floating-labels', $src, ['jquery'], $ver, true);
            wp_add_inline_script('bc-floating-labels', 'bc_floating_labels.init();');
            $src = plugin_dir_url(BC_FUNCTIONS) . 'assets/bc-floating-labels.css';
            $ver = filemtime(plugin_dir_path(BC_FUNCTIONS) . 'assets/bc-floating-labels.css');
            wp_enqueue_style('bc-floating-labels', $src, [], $ver);
        });
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

if(!function_exists('bc_first_p')){
    function bc_first_p($text = '', $dot = true){
        return bc_one_p($text, $dot, 'first');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_get_memory_size')){
    function bc_get_memory_size(){
        if(!function_exists('exec')){
            return 0;
        }
        exec('free -b', $output);
        $output = explode(' ', trim(preg_replace('/\s+/', ' ', $output[1])));
        return (int) $output[1];
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

if(!function_exists('bc_is_array_assoc')){
    function bc_is_array_assoc($array = []){
        if(!is_array($array)){
            return false;
        }
        return (array_keys($array) !== range(0, count($array) - 1));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_is_doing_heartbeat')){
    function bc_is_doing_heartbeat(){
        return (defined('DOING_AJAX') and DOING_AJAX and isset($_POST['action']) and $_POST['action'] == 'heartbeat');
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

if(!function_exists('bc_is_plugin_deactivating')){
    function bc_is_plugin_deactivating($file = ''){
        global $pagenow;
        if(!is_file($file)){
            return false;
        }
        return (is_admin() and 'plugins.php' === $pagenow and isset($_GET['action'], $_GET['plugin']) and 'deactivate' === $_GET['action'] and plugin_basename($file) === $_GET['plugin']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_is_post_revision_or_auto_draft')){
    function bc_is_post_revision_or_auto_draft($post = null){
        return (wp_is_post_revision($post) or 'auto-draft' === get_post_status($post));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_ksort_deep')){
    function bc_ksort_deep($data = []){
        if(bc_is_array_assoc($data)){
            ksort($data);
            foreach($data as $index => $item){
                $data[$index] = bc_ksort_deep($item);
            }
        }
        return $data;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_last_p')){
    function bc_last_p($text = '', $dot = true){
        return bc_one_p($text, $dot, 'last');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_local_login_header')){
    function bc_local_login_header(){
        bc_one('login_headertext', function($login_headertext){
			return get_option('blogname');
		});
		bc_one('login_headerurl', function($login_headerurl){
			return home_url();
		});
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_one')){
    function bc_one($hook_name, $callback, $priority = 10, $accepted_args = 1){
        if(!array_key_exists('hooks', $GLOBALS['bc'])){
            $GLOBALS['bc']['hooks'] = [];
        }
        if(!array_key_exists($hook_name, $GLOBALS['bc']['hooks'])){
            $GLOBALS['bc']['hooks'][$hook_name] = [];
        }
        $idx = _wp_filter_build_unique_id($hook_name, $callback, $priority);
        $md5 = md5($idx);
        if($callback instanceof Closure){
            $md5_closure = bc_md5_closure($callback);
            if(!is_wp_error($md5_closure)){
                $md5 = $md5_closure;
            }
        }
        if(array_key_exists($md5, $GLOBALS['bc']['hooks'][$hook_name])){
            return $GLOBALS['bc']['hooks'][$hook_name][$md5];
        } else {
            $GLOBALS['bc']['hooks'][$hook_name][$md5] = $idx;
            add_filter($hook_name, $callback, $priority, $accepted_args);
            return $idx;
        }
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

if(!function_exists('bc_prepare')){
    function bc_prepare($str = '', ...$args){
        global $wpdb;
        if(!$args){
            return $str;
        }
        if(false === strpos($str, '%')){
            return $str;
        } else {
            return str_replace("'", '', $wpdb->remove_placeholder_escape($wpdb->prepare(...$args)));
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_read_file_chunk')){
    function bc_read_file_chunk($handle = null, $chunk_size = 0){
        $giant_chunk = '';
    	if(is_resource($handle) and is_int($chunk_size)){
    		$byte_count = 0;
    		while(!feof($handle)){
                $length = apply_filters('bc_file_chunk_lenght', (KB_IN_BYTES * 8));
    			$chunk = fread($handle, $length);
    			$byte_count += strlen($chunk);
    			$giant_chunk .= $chunk;
    			if($byte_count >= $chunk_size){
    				return $giant_chunk;
    			}
    		}
    	}
        return $giant_chunk;
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

if(!function_exists('bc_remove_whitespaces')){
    function bc_remove_whitespaces($str = ''){
        return trim(preg_replace('/[\r\n\t\s]+/', ' ', $str));
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

if(!function_exists('bc_seems_false')){
    function bc_seems_false($data = ''){
        return in_array((string) $data, ['0', '', 'false', 'off'], true);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_seems_mysql_date')){
	function bc_seems_mysql_date($pattern = ''){
        return preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $pattern);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_seems_true')){
    function bc_seems_true($data = ''){
        return in_array((string) $data, ['1', 'on', 'true'], true);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_signon_without_password')){
    function bc_signon_without_password($username_or_email = '', $remember = false){
        if(is_user_logged_in()){
            return wp_get_current_user();
        } else {
            $idx = bc_one('authenticate', function($user, $username_or_email){
                if(is_null($user)){
                    if(is_email($username_or_email)){
                        $user = get_user_by('email', $username_or_email);
                    }
                    if(is_null($user)){
                        $user = get_user_by('login', $username_or_email);
                        if(is_null($user)){
                            return bc_error(__('The requested user does not exist.'));
                        }
                    }
                }
                return $user;
            }, 10, 2);
            $user = wp_signon([
                'remember' => $remember,
                'user_login' => $username_or_email,
                'user_password' => '',
            ]);
            remove_filter('authenticate', $idx);
            return $user;
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
