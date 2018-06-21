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
class Erp extends CI_Controller {

	
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
        $this->load->model('erp_model','clg_mdl' );
        $this->load->library('resourcelib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
        
    }
    
    //Get all course reg by department
    public function dept_cs_reg($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->get_dept_cs_reg($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    public function dept_cs_reg_approve($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->get_dept_cs_reg_approve($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function dept_std($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->dept_student($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function dept_staff($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->dept_staff($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function dept_student_male($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->dept_student_male($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function dept_student_female($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->dept_student_female($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function dept_pay($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->dept_pay($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_staff($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            $rs = $this->clg_mdl->coll_staff();
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_student($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {
                $rs = $this->clg_mdl->coll_student($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{
                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_student_male($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {
                $rs = $this->clg_mdl->coll_student_male($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{
                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_student_female($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {
                $rs = $this->clg_mdl->coll_student_female($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{
                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_cs_reg($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {
                $rs = $this->clg_mdl->get_coll_cs_reg($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{
                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_cs_reg_approve($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {
                $rs = $this->clg_mdl->get_coll_cs_reg($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                $result['rs'] = "No record found";

            }else{
                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_pay($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->college_pay($id);
            } 
            
            if (empty($rs)) {
                $result['status'] = 0;
                    $result['rs'] = "No record found";

            }else{

                $result['status'] = 1;
                $result['rs'] = $rs;
            }

            $this->output->set_output(json_encode($result));
        }
    }
    
    
    
    /**
     * Student in department 
     * @param type $id
     */
    public function college_pop($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->college_population($id);
            } 
            
            if (empty($rs)) {
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
