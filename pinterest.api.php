<?php

    class Pinterest_API {
        
        var $base_url;
        var $access_token;
        
        function __construct($access_token='') {
            $this->base_url = 'https://api.pinterest.com/v2';
            $this->access_token = $access_token;
        }
        
        function fetch_access_token($client_id, $client_secret, $username, $password) {

            $ch=curl_init();
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);                                                

            $post= array(
                "grant_type" => 'password',
                "scope"  => "read_write",
                "redirect_uri" => "http://pinterest.com/about/iphone/"
            );

            $host = "https://api.pinterest.com";
            $endpoint = "/v2/oauth/access_token?client_id=$client_id&client_secret=$client_secret";
            $request_url = $host . $endpoint;
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
            $s=curl_exec($ch);
            $info = curl_getinfo($ch);
            
            curl_close($ch);

            if ($info['http_code'] >= 200 and $info['http_code'] < 300) {
                list($junk, $access_token) = explode('=', $s, 2);
                $this->access_token = $access_token;
            } 

            return $s;
        }
        
        function upload_pin($params) {
            
            $post = self::params_filter($params, array(
                'board' => self::REQUIRED,      // board id #
                'details' => self::REQUIRED,    // description, a string, limit unknown, accepts markups
                'image' => self::REQUIRED,      // currently only accepts path to a file
                'latitude' => 0,
                'longitude' => 0,
                'publish_to_twitter' => 0,
                'publish_to_facebook' => 0    
            ));
        
            return $this->post('/pin/', $post);
        }
        
        function repin($params) {
            
            $params = self::params_filter($params, array(
                'board' => self::REQUIRED,
                'details' => self::REQUIRED,
                'pin' => self::REQUIRED,
            ));
            
            $post = array(
                'board' => $params['board'],
                'details' => $params['details'],
            );
            
            $endpoint = '/repin/' . $params['pin'] . '/';
            
            return $this->post($endpoint, $post);            
        }
        
        function activity($params=array()) {
            return $this->get('/activity/', $params);
        }
        
		function all($params=array()) {
		    $params = self::params_filter($params, array(
                'limit' => 36,
                'page' => 1
            ));
            
            return $this->get('/all/', $params);
		}

        function popular($params=array()) {
            $params = self::params_filter($params, array(
                'limit' => 36,
                'page' => 1
            ));
            
            return $this->get('/popular/', $params);
        }
        
        function newboards($params=array()) {
            return $this->get('/newboards/', $params);
        }
        
        function boards($params=array()) {
            return $this->get('/boards/', $params);    
        }
        
        function categories($params=array()) {
            $params = self::params_filter($params, array(
                'limit' => 36,
                'page' => 1
            ));
            return $this->get('/boards/categories/', $params);
        }
        
        function post($endpoint, $post=array()) {
            $ch=curl_init();

            $request_url = $this->base_url . $endpoint;
            if ($this->access_token) {
                $request_url = "$request_url?access_token=" . $this->access_token;
            }
            
            curl_setopt($ch, CURLOPT_USERAGENT, 'Pinterest For iPhone / 1.4.3');
            
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_POST,1);

            curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
            
#            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
#            curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1");
#            curl_setopt($ch, CURLOPT_PROXYPORT, 8888);
#            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            
        
            
            
            $resp=curl_exec($ch);            
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            return $resp;
        }
        
        function get($endpoint, $params=array()) {
            $ch=curl_init();
            
            if ($this->access_token and !isset($params['access_token'])) {
                $params['access_token'] = $this->access_token;
            }
            
            foreach ($params as $k => $v){
                $encoded_params[] = urlencode($k).'='.urlencode($v);
            }
            
            $request_url = $this->base_url . $endpoint . "?" . implode('&', $encoded_params);
            
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
            $resp=curl_exec($ch);            
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            return $resp;
        }
        
        
        static function params_filter($params, $defaults) {
            
            foreach ($defaults as $k => $v) {
                
                if (!isset($params[$k])) {
                    
                    if ($v === self::REQUIRED) {
                        
                        $trace = debug_backtrace();
                        $function = $trace[1]['function'];
                        $caller = $trace[2]['function'].' in '.$trace[2]['file'].':'.$trace[2]['line'];
                        trigger_error(self::REQUIRED . ": $k (caller was $caller)");
                    
                    } else {
                        $params[$k] = $v;
                    }
                }
            }
            return $params;
        }
        
        const REQUIRED = 'arg is required';
        
    } // end class Pinterest_API