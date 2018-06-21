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
class students extends CI_Controller{
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
        $this->load->model('student_model','std_mdl' );
        $this->load->library('ResourceLib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
        
    }
    
    
    public function index($id = NULL) {
        if ($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id)) {

                $rs = $this->std_mdl->get_student($id);
            } else {
                $rs = $this->std_mdl->get_student();
            }

            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";
            } else {

                $result['status'] = 1;
                $result['rs'] = $rs;
            }


            $this->output->set_output(json_encode($result));
        }
    }
    
    
    public function studentNotIn($userArray){
        if($this->input->server('REQUEST_METHOD') == 'POST') {
            $request = json_decode(file_get_contents("php://input"), TRUE);
            dd($request);
            $rs = $this->get_student_not_in($request);
            
            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";
            } else {

                $result['status'] = 1;
                $result['rs'] = $rs;
            }


            $this->output->set_output(json_encode($result));
        }
    }

}
