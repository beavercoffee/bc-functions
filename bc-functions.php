<?php
/*
Author: Beaver Coffee
Author URI: https://beaver.coffee
Description: A collection of useful functions for your WordPress theme's functions.php.
Domain Path:
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network: true
Plugin Name: BC Functions
Plugin URI: https://github.com/beavercoffee/bc-functions
Requires at least: 5.7
Requires PHP: 5.6
Text Domain: bc-functions
Version: 1.7.24.1
*/

if(defined('ABSPATH')){
    foreach(glob(plugin_dir_path(__FILE__) . 'functions/*.php') as $functions){
        require_once($functions);
    }
    unset($functions);
    add_action('plugins_loaded', function(){
        $fs = bc_filesystem();
        if(is_wp_error($fs)){
            bc_add_admin_notice($fs->get_error_message());
        } else {
            $GLOBALS['bc_hooks'] = [];
            bc_build_update_checker('https://github.com/beavercoffee/bc-functions', __FILE__, 'bc-functions');
            define('BC_FUNCTIONS', __FILE__);
            do_action('bc_functions_loaded');
        }
    });
}
