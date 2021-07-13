<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_cf7_mail')){
    function bc_cf7_mail($contact_form = null){
        if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
        }
        if(null === $contact_form){
            return false;
        }
        $skip_mail = ($contact_form->in_demo_mode() or $contact_form->is_true('skip_mail') or !empty($contact_form->skip_mail));
        $skip_mail = apply_filters('wpcf7_skip_mail', $skip_mail, $contact_form);
        if($skip_mail){
        	return true;
        }
        $result = WPCF7_Mail::send($contact_form->prop('mail'), 'mail');
        if(!$result){
            return false;
        }
        $additional_mail = [];
    	if($mail_2 = $contact_form->prop('mail_2') and $mail_2['active']){
    		$additional_mail['mail_2'] = $mail_2;
    	}
    	$additional_mail = apply_filters('wpcf7_additional_mail', $additional_mail, $contact_form);
    	foreach($additional_mail as $name => $template){
    		WPCF7_Mail::send($template, $name);
    	}
    	return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_cf7_type')){
    function bc_cf7_type($contact_form = null){
        if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
        }
        if(null === $contact_form){
            return '';
        }
        $type = $contact_form->pref('bc_type');
        if(null === $type){
            return '';
        }
        return $type;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
