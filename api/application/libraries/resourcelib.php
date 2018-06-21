<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'third_party/jwt/JWT.php');

Use Firebase\JWT\JWT;

class ResourceLib {
    
    /**
     * Codeigniter instance
     * 
     * @access private
     * @var CI_Controller
     */
    
    private $CI;
    
    /**
     * Header array
     * 
     * @access private
     * @var Array
     */
    private static $headers;
    
    /**
     * Class constructor
     * 
     * @access public
     * @return void
     */
    public function __construct() {	

        // Load CI object
        $this->CI =& get_instance();
        self::$headers = getallheaders();
        $this->secret = $this->CI->config->item('auth_secret');        
                    
    } // End func __construct

    public function addCorsHeaders() {        
        
        if(!isset(self::$headers['Origin'])) {
            self::$headers['Origin'] = '*';
        } 
        
        $age = 60 * 60 * 24 * 30;
        $this->CI->output->set_header("Access-Control-Max-Age: {$age}");
        $this->CI->output->set_header("Access-Control-Allow-Methods: POST, GET");
        $this->CI->output->set_header("Access-Control-Allow-Headers: x-requested-with, Content-Type, origin, authorization");
        $this->CI->output->set_header("Content-type: application/json");
        $this->CI->output->set_header("Access-Control-Allow-Origin: ".self::$headers['Origin']);
        
    }
    
    public function encode($payload) {
        return JWT::encode($payload, $this->secret);
    }
    
    private function decode($token) {
        try{                        
            return JWT::decode($token, $this->secret, ['HS256']);
        }catch(Exception $e) {
            return false;
        }
    }
    
    private function getToken() {

        if (!isset(self::$headers['Authorization']))
            return false;

        $values = explode(' ', self::$headers['Authorization']);
        
        return count($values) > 1? $values[1]: false;
    }
    
    private function getTokenPayload() {
        $status = ['error' => true, 'type' => 'header'];
        
        if($token = $this->getToken()) {            
            if($payload = $this->decode($token)) {
                $status = ['error' => false, 'response' => $payload];
            }else {
                $status['type'] = 'token';
            }
        }
        
        return $status;
    }
    
    public function getUserFromToken() {
        $resp = $this->getTokenPayload();
        
        if(!$resp['error']) {
            $resp['response'] = $resp['response']->sub;            
        }
        
        return $resp;
    }
    
}// END ResourceLib Class
/* End of file ResourceLib.php */
/* Location: ./application/libraries/ResourceLib.php */
