<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_build_update_checker')){
    function bc_build_update_checker(...$args){
        $r = bc_use_plugin_update_checker();
        if(is_wp_error($r)){
            return $r;
        }
        return Puc_v4_Factory::buildUpdateChecker(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_file_get_html')){
    function bc_file_get_html(...$args){
        $r = bc_use_simplehtmldom();
        if(is_wp_error($r)){
            return $r;
        }
        return file_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_seems_cloudflare')){
    function bc_seems_cloudflare(){
        return isset($_SERVER['HTTP_CF_RAY']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_serializable_closure')){
    function bc_serializable_closure(...$args){
        $r = bc_use_closure();
        if(is_wp_error($r)){
            return $r;
        }
        return new Opis\Closure\SerializableClosure(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_str_get_html')){
    function bc_str_get_html(...$args){
        $r = bc_use_simplehtmldom();
        if(is_wp_error($r)){
            return $r;
        }
        return str_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_tgmpa')){
    function bc_tgmpa(...$args){
        $r = bc_use_tgm_plugin_activation();
        if(is_wp_error($r)){
            return $r;
        }
        return tgmpa(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use_closure')){
    function bc_use_closure(){
        if(class_exists('Opis\Closure\SerializableClosure')){
            return true;
        }
        $dir = bc_use('https://github.com/opis/closure/archive/3.6.2.zip', 'closure-3.6.2');
        if(is_wp_error($dir)){
            return $dir;
        }
        require_once($dir . '/autoload.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use_php_jwt')){
    function bc_use_php_jwt(){
        if(class_exists('Firebase\JWT\JWT')){
            return true;
        }
        $dir = bc_use('https://github.com/firebase/php-jwt/archive/refs/tags/v5.2.1.zip', 'php-jwt-5.2.1');
        if(is_wp_error($dir)){
            return $dir;
        }
        require_once($dir . '/src/BeforeValidException.php');
        require_once($dir . '/src/ExpiredException.php');
        require_once($dir . '/src/JWK.php');
        require_once($dir . '/src/JWT.php');
        require_once($dir . '/src/SignatureInvalidException.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use_php_xlsxwriter')){
    function bc_use_php_xlsxwriter(){
        if(class_exists('XLSXWriter')){
            return true;
        }
        $dir = bc_use('https://github.com/mk-j/PHP_XLSXWriter/archive/refs/tags/0.38.zip', 'PHP_XLSXWriter-0.38');
        if(is_wp_error($dir)){
            return $dir;
        }
        require_once($dir . '/xlsxwriter.class.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use_plugin_update_checker')){
    function bc_use_plugin_update_checker(){
        if(class_exists('Puc_v4_Factory')){
            return true;
        }
        $dir = bc_use('https://github.com/YahnisElsts/plugin-update-checker/archive/refs/tags/v4.11.zip', 'plugin-update-checker-4.11');
        if(is_wp_error($dir)){
            return $dir;
        }
        require_once($dir . '/plugin-update-checker.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use_simplehtmldom')){
    function bc_use_simplehtmldom(){
        if(class_exists('simple_html_dom')){
            return true;
        }
        $dir = bc_use('https://github.com/simplehtmldom/simplehtmldom/archive/refs/tags/1.9.1.zip', 'simplehtmldom-1.9.1');
        if(is_wp_error($dir)){
            return $dir;
        }
        require_once($dir . '/simple_html_dom.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_use_tgm_plugin_activation')){
    function bc_use_tgm_plugin_activation(){
        if(class_exists('TGM_Plugin_Activation')){
            return true;
        }
        $dir = bc_use('https://github.com/TGMPA/TGM-Plugin-Activation/archive/refs/tags/2.6.1.zip', 'TGM-Plugin-Activation-2.6.1');
        if(is_wp_error($dir)){
            return $dir;
        }
        require_once($dir . '/class-tgm-plugin-activation.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_xlsx')){
    function bc_xlsx(...$args){
        $r = bc_use_php_xlsxwriter();
        if(is_wp_error($r)){
            return $r;
        }
        return new XLSXWriter(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_zoom_jwt')){
	function bc_zoom_jwt($api_key = '', $api_secret = ''){
        $r = bc_use_php_jwt();
        if(is_wp_error($r)){
            return $r;
        }
        $payload = [
            'exp' => time() + DAY_IN_SECONDS,
            'iss' => $api_key,
        ];
        return Firebase\JWT\JWT::encode($payload, $api_secret);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
