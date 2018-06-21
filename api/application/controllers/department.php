<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TAMS
 * College controller
 * 
 * @category   Controller
 * @package    College
 * @subpackage 
 * @author     Sule-odu Adedayo <suleodu.adedayo@gmail.com>
 * @copyright  Copyright © 2014 TAMS.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */
class Department extends CI_Controller {

	
   /*
    * Class constructor
    * 
    * @access public 
    * @retun void
    */
    public function __construct() {

        parent::__construct();
        
        /*
         * Load payment model 
         */
        $this->load->model('department_model','dpt_mdl' );
        $this->load->library('ResourceLib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
        
    }
    
    public function index($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {               
            if(!is_null($id) && $id > 0){

                $rs = $this->dpt_mdl->get_department($id);
                
                $status = $this->dpt_mdl->getDeptLecturers($id);
                switch ($status['status']) {
                    case 'success':
                        $result['lects'] = $status['rs'];
                        break;

                    case 'empty':
                        $result['lects'] = [];
                        break;

                    case 'error':
                        $result['lects'] = [];
                }
            }else{
                $rs = $this->dpt_mdl->get_department();
            }
            
            if(empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{
                $result['status'] = 1;
                $result['rs'] = $rs;                
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    public function getLecturers($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {               
            if(!is_null($id) && $id > 0){

                $rs = $this->dpt_mdl->get_department($id);
            }else{
                $rs = $this->dpt_mdl->get_getAllLecturers();
            }
            
            if(empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
}