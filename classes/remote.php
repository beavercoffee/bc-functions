<?php

if(!class_exists('BC_Remote')){
    final class BC_Remote {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // private
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        private $args = [], $url = '';

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        private function request($method = '', $body = []){
            $this->args['method'] = $method;
            if($body){
                if(!is_array($body)){
                    $body = wp_parse_args($body);
                }
                if(array_key_exists('body', $this->args) and $this->args['body']){
                    if(!is_array($this->args['body'])){
                        $this->args['body'] = wp_parse_args($this->args['body']);
                    }
                    $this->args['body'] = array_merge($this->args['body'], $body);
                } else {
                    $this->args['body'] = $body;
                }
            }
            $response = wp_remote_request($this->url, $this->args);
            return bc_response($response);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function __construct($url = '', $args = []){
            $this->args = $args;
            $this->url = $url;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function delete($body = []){
            return $this->request('DELETE', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function get($body = []){
            return $this->request('GET', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function head($body = []){
            return $this->request('HEAD', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function options($body = []){
            return $this->request('OPTIONS', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function patch($body = []){
            return $this->request('PATCH', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function post($body = []){
            return $this->request('POST', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function put($body = []){
            return $this->request('PUT', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function trace($body = []){
            return $this->request('TRACE', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
