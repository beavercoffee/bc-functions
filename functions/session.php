<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_destroy_other_sessions')){
    function bc_destroy_other_sessions(){
        bc_one('init', 'wp_destroy_other_sessions');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_session_destroy')){
    function bc_session_destroy(){
        if(session_id()){
            session_destroy();
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_session_start')){
    function bc_session_start(){
        if(!session_id()){
            session_start();
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_support_sessions')){
    function bc_support_sessions(){
        bc_one('init', 'bc_session_start');
        bc_one('wp_login', 'bc_session_destroy');
        bc_one('wp_logout', 'bc_session_destroy');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
