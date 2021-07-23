<?php

if(!class_exists('BC_Response')){
    final class BC_Response {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // private
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        private function from_array($response = []){
            $this->code = (int) $response['code'];
            $this->data = $response['data'];
            $this->message = (string) $response['message'];
            $this->success = (bool) $response['success'];
            if(0 === $this->code or '' === $this->message or $this->success !== $this->seems_successful($this->code)){
                $this->code = 500;
                $this->message = __('Something went wrong.');
                $this->success = false;
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected function maybe_json_decode(){
            $data = json_decode($this->data, true);
            if(JSON_ERROR_NONE === json_last_error()){
                $this->data = $data;
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected function maybe_unserialize(){
            $this->data = maybe_unserialize($this->data);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected function seems_successful($code = 0){
            $code = (int) $code;
            return ($code >= 200 and $code < 300);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected function seems_wp_http_requests_response($response = null){
            return (bc_array_keys_exist(['body', 'cookies', 'filename', 'headers', 'http_response', 'response'], $response) and ($response['http_response'] instanceof WP_HTTP_Requests_Response));
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public $code = 0, $data = '', $message = '', $raw_data = '', $raw_response = null, $success = false;

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function __construct($response = null){
            $code = 500;
            $data = '';
            $message = '';
            $success = false;
            switch(true){
                case is_a($response, 'Requests_Response'):
                    $code = $response->status_code;
                    $data = $response->body;
                    $message = get_status_header_desc($code);
                    $success = $this->seems_successful($code);
                    break;
                case is_wp_error($response):
                    $data = $response->get_error_data();
                    $message = $response->get_error_message();
                    break;
                case $this->seems_wp_http_requests_response($response):
                    $code = wp_remote_retrieve_response_code($response);
                    $data = wp_remote_retrieve_body($response);
                    $message = wp_remote_retrieve_response_message($response);
                    if(!$message){
                        $message = get_status_header_desc($code);
                    }
                    $success = $this->seems_successful($code);
                    break;
                case is_a($response, 'Requests_Exception'):
                    $data = $response->getData();
                    $message = $response->getMessage();
                    break;
                default:
                    $message = __('Invalid object type.');
            }
            $this->raw_data = $data;
            $this->raw_response = $response;
            $this->from_array(compact('code', 'data', 'message', 'success'));
            $this->maybe_json_decode();
            $this->maybe_unserialize();
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function rest_ensure_response(){
            if($this->success){
                return $this->to_wp_rest_response();
            } else {
                return $this->to_wp_error();
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function to_wp_error(){
            if(is_wp_error($this->raw_response)){
                return $this->raw_response;
            } else {
                return bc_error($this->message, [
                    'status' => $this->code,
                ]);
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function to_wp_rest_response(){
            return new WP_REST_Response($this->data, $this->code);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
