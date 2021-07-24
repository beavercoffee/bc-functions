<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_add_external_rule')){
    function bc_add_external_rule($regex = '', $query = ''){
        if(!array_key_exists('external_rules', $GLOBALS['bc'])){
            $GLOBALS['bc']['external_rules'] = [];
        }
		$rule = [
			'query' => $query,
            'regex' => $regex,
		];
		$md5 = bc_md5($rule);
		if(!array_key_exists($md5, $GLOBALS['bc']['external_rules'])){
			$GLOBALS['bc']['external_rules'][$md5] = $rule;
		}
		bc_one('admin_init', function(){
            if(current_user_can('manage_options')){
				if(!$GLOBALS['bc']['external_rules']){
					return;
				}
				$add_admin_notice = false;
				foreach($GLOBALS['bc']['external_rules'] as $rule){
					$regex = str_replace(home_url('/'), '', $rule['regex']);
					$query = str_replace(home_url('/'), '', $rule['query']);
					if(!bc_external_rule_exists($regex, $query)){
						$add_admin_notice = true;
						break;
					}
				}
				if($add_admin_notice){
					bc_add_admin_notice(sprintf(__('You should update your %s file now.'), '<code>.htaccess</code>') . ' ' . sprintf('<a href="%s">%s</a>', esc_url(admin_url('options-permalink.php')), __('Flush permalinks')) . '.');
				}
            }
		});
		bc_one('generate_rewrite_rules', function($wp_rewrite){
			if(!$GLOBALS['bc']['external_rules']){
				return;
			}
			foreach($GLOBALS['bc']['external_rules'] as $rule){
				$regex = str_replace(home_url('/'), '', $rule['regex']);
				$query = str_replace(home_url('/'), '', $rule['query']);
				$wp_rewrite->add_external_rule($regex, $query);
			}
		});
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_external_rule_exists')){
    function bc_external_rule_exists($regex = '', $query = ''){
        if(!array_key_exists('rewrite_rules', $GLOBALS['bc'])){
            $GLOBALS['bc']['rewrite_rules'] = array_filter(extract_from_markers(get_home_path() . '.htaccess', 'WordPress'));
        }
        $regex = str_replace(home_url('/'), '', $regex);
    	$regex = str_replace('.+?', '.+', $regex);
    	$query = str_replace(home_url('/'), '', $query);
    	$rule = 'RewriteRule ^' . $regex . ' ' . bc_home_root() . $query . ' [QSA,L]';
    	return in_array($rule, $GLOBALS['bc']['rewrite_rules']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_home_root')){
    function bc_home_root(){
        $home_root = parse_url(home_url());
    	if(isset($home_root['path'])){
    		$home_root = trailingslashit($home_root['path']);
    	} else {
    		$home_root = '/';
    	}
    	return $home_root;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
