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
Version: 1.7.17
*/

if(defined('ABSPATH')){
    define('BC_FUNCTIONS', __FILE__);
    foreach(glob(plugin_dir_path(BC_FUNCTIONS) . 'functions/*.php') as $functions){
        require_once($functions);
    }
    unset($functions);
    add_action('admin_notices', function(){
        $fs = bc_filesystem();
        if(is_wp_error($fs)){
            echo bc_admin_notice($fs->get_error_message());
        }
    });
    add_action('plugins_loaded', function(){
        bc_build_update_checker('https://github.com/beavercoffee/bc-functions', BC_FUNCTIONS, 'bc-functions');
        do_action('bc_functions_loaded');
    });
}
