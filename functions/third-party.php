<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_build_update_checker')){
    function bc_build_update_checker(...$args){
        if(!class_exists('Puc_v4_Factory')){
            $dir = bc_use('https://github.com/YahnisElsts/plugin-update-checker/archive/refs/tags/v4.11.zip', 'plugin-update-checker-4.11');
            if(is_wp_error($dir)){
                return $dir;
            }
            require_once($dir . '/plugin-update-checker.php');
        }
        return Puc_v4_Factory::buildUpdateChecker(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_seems_cloudflare')){
    function bc_seems_cloudflare(){
        return isset($_SERVER['HTTP_CF_RAY']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_str_get_html')){
    function bc_str_get_html(...$args){
        if(!function_exists('str_get_html')){
            $dir = bc_use('https://github.com/simplehtmldom/simplehtmldom/archive/refs/tags/1.9.1.zip', 'simplehtmldom-1.9.1');
            if(is_wp_error($dir)){
                return $dir;
            }
            require_once($dir . '/simple_html_dom.php');
        }
        return str_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
