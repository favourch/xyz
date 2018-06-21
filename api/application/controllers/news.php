<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TAMS
 * News controller
 * 
 * @category   Controller
 * @package    College
 * @subpackage 
 * @author     Sule-odu Adedayo <suleodu.adedayo@gmail.com>
 * @copyright  Copyright © 2014 TAMS.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */
class News extends CI_Controller {

	
   /*
    * Class constructor
    * 
    * @access public 
    * @retun void
    */
    public function __construct() {

        parent::__construct();
        $this->load->model('news_model', 'mdl');
        $this->load->library('ResourceLib', null, 'res_lib');        
        $this->res_lib->addCorsHeaders();        
    }
    
    
    public function index($type = 'list', $id = 1, $limit = 10){
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            $response = ['error' => true];
            $status = null;
            
            if($type == 'list') {
                $status = $this->mdl->getPosts('news', $id, $limit);
            }else {
                $status = $this->mdl->getPostsById($id);
            }
            
            switch($status['status']) {
                case 'success':
                    $response['posts'] = $status['rs'];
                    $response['error'] = false;
                    break;
                case 'empty':
                    $response['posts'] = [];
                    $response['error'] = false;
                    break;
            }
        }
        $this->output->set_output(json_encode($response));
    }
    
    public function announcements($type, $id = 1, $limit = 10){
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            $response = ['error' => true];
            $status = null;
            
            if($type == 'list') {
                $status = $this->mdl->getPosts('announce', $id, $limit);
            }else {
                $status = $this->mdl->getPostsById($id);
            }
            
            switch($status['status']) {
                case 'success':
                    $response['posts'] = $status['rs'];
                    $response['error'] = false;                    
                    break;
                case 'empty':
                    $response['posts'] = [];
                    $response['error'] = false;
                    break;
            }
            $this->output->set_output(json_encode($response));
        }        
    }
}