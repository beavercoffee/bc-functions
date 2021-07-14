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
        $skip_mail = bc_cf7_skip_mail($contact_form);
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

if(!function_exists('bc_cf7_meta_data')){
    function bc_cf7_meta_data($contact_form = null, $submission = null){
        if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
        }
        if(null === $contact_form){
            return [];
        }
        if(null === $submission){
            $submission = WPCF7_Submission::get_instance();
        }
        if(null === $submission){
            return [];
        }
        $meta_data = [
            'bc_contact_form_id' => $contact_form->id(),
            'bc_contact_form_locale' => $contact_form->locale(),
            'bc_contact_form_name' => $contact_form->name(),
            'bc_contact_form_title' => $contact_form->title(),
            'bc_submission_container_post_id' => $submission->get_meta('container_post_id'),
            'bc_submission_current_user_id' => $submission->get_meta('current_user_id'),
            'bc_submission_remote_ip' => $submission->get_meta('remote_ip'),
            'bc_submission_remote_port' => $submission->get_meta('remote_port'),
            'bc_submission_response' => $submission->get_response(),
            'bc_submission_status' => $submission->get_status(),
            'bc_submission_timestamp' => $submission->get_meta('timestamp'),
            'bc_submission_unit_tag' => $submission->get_meta('unit_tag'),
            'bc_submission_url' => $submission->get_meta('url'),
            'bc_submission_user_agent' => $submission->get_meta('user_agent'),
        ];
        $meta_data = apply_filters('bc_cf7_meta_data', $meta_data);
        return $meta_data;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_cf7_skip_mail')){
    function bc_cf7_skip_mail($contact_form = null){
        if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
        }
        if(null === $contact_form){
            return false;
        }
        $skip_mail = ($contact_form->in_demo_mode() or $contact_form->is_true('skip_mail') or !empty($contact_form->skip_mail));
        $skip_mail = apply_filters('wpcf7_skip_mail', $skip_mail, $contact_form);
        return $skip_mail;
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

if(!function_exists('bc_cf7_update_meta_data')){
    function bc_cf7_update_meta_data($meta_data = [], $object_id = 0, $meta_type = 'post'){
        if(!$meta_data){
            return true;
        }
        foreach($meta_data as $key => $value){
            update_metadata($meta_type, $object_id, $key, $value);
        }
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_cf7_update_posted_data')){
    function bc_cf7_update_posted_data($posted_data = [], $object_id = 0, $meta_type = 'post'){
        if(!$posted_data){
            return true;
        }
        foreach($posted_data as $key => $value){
            if(is_array($value)){
                delete_metadata($meta_type, $object_id, $key);
                foreach($value as $single){
                    add_metadata($meta_type, $object_id, $key, $single);
                }
            } else {
                update_metadata($meta_type, $object_id, $key, $value);
            }
        }
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_cf7_update_uploaded_files')){
    function bc_cf7_update_uploaded_files($uploaded_files = [], $object_id = 0, $meta_type = 'post'){
        if(!$uploaded_files){
            return true;
        }
        $success = true;
        foreach($uploaded_files as $key => $value){
            delete_metadata($meta_type, $object_id, $key . '_id');
            delete_metadata($meta_type, $object_id, $key . '_filename');
            foreach((array) $value as $single){
                if('post' === $meta_type){
                    $attachment_id = bc_upload_file($single, $object_id);
                } else {
                    $attachment_id = bc_upload_file($single);
                }
                if(is_wp_error($attachment_id)){
                    add_metadata($meta_type, $object_id, $key . '_id', 0);
                    add_metadata($meta_type, $object_id, $key . '_filename', $attachment_id->get_error_message());
                    $success = false;
                } else {
                    add_metadata($meta_type, $object_id, $key . '_id', $attachment_id);
                    add_metadata($meta_type, $object_id, $key . '_filename', wp_basename($single));
                }
            }
        }
        return $success;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
