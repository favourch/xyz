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
               
        $this->load->library('resourcelib', null, 'res_lib');        
        $this->res_lib->addCorsHeaders();        
    }
    
    
    public function index($id = NULL){
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            $url = 'http://tasued.edu.ng/api/get_recent_posts/';


            //cURL starts
            $crl = curl_init();
            curl_setopt($crl, CURLOPT_URL, $url);
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($crl, CURLOPT_HTTPGET, true);
            $reply = curl_exec($crl);

            if ($reply === false) {

                $this->output->set_status_header(401);
                $this->output->set_output(json_encode(curl_error($crl)));
                return;
            }
            curl_close($crl);

            $this->output->set_output(json_encode($reply));
        }
        
    }
}