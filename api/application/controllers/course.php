<?php if (!defined('BASEPATH'))exit('No direct script access allowed');
require_once(APPPATH . 'third_party/jwt/JWT.php');

Use Firebase\JWT\JWT;

class Course extends CI_Controller {

    /*
     * Class constructor
     * 
     * @access public 
     * @retun void
     */

    public function __construct(){

        parent::__construct();
        $this->load->model('course_model', 'mdl');
        $this->load->model('department_model','dpt_mdl' );
        $this->load->library('ResourceLib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
    }
    
    
    public function index($id = NULL) {
        
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            
            if (!is_null($id) ) {
                
                $rs = $this->mdl->getCourse($id);
            } else {
                
                $rs = $this->mdl->getCourse();
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
     * Retrieve all courses registered by the active user
     *
     * @return boolean
     * 
     */
    public function getRegisteredCourses($sesid = NULL) {
        
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            
            $response = '';
            $tok_status = $this->res_lib->getUserFromToken();
            
            if(!$tok_status['error']) {
               
                $status = $this->mdl->getRegisteredCourses($tok_status['response'], $sesid);
                
                switch ($status['status']) {
                    case 'success':
                        $response = ['courses' => $status['rs']];
                        break;

                    case 'empty':
                        $response = ['courses' => []];
                        break;

                    case 'error':
                        $this->output->set_status_header(500);
                        $response = ['reason' => 'There was a problem getting your registered courses!'];
                        break;
                }
            }else {
                $tok_status['type'] == 'header'? $this->output->set_status_header(400): 
                    $this->output->set_status_header(401);
            }
            
            $this->output->set_output(json_encode($response));
        }        
    }
    
    /**
     * Retrieve courses for a department
     *
     * @return boolean
     * 
     */
    public function getCoursesForDepartment($deptid = NULL) {
        
        if($this->input->server('REQUEST_METHOD') == 'GET') {           
            $response = [];
            if($deptid) {
                $courses = $this->mdl->getCoursesForDepartment($deptid);
            }else {
                $status = $this->dpt_mdl->get_department($deptid);
                if($status) {
                    $response = ["depts" => $status];
                    $courses = $this->mdl->getCoursesForDepartment($status[0]['deptid']);
                }   
                
            }
            
            switch ($courses['status']) {
                case 'success':
                    $response['crs'] = $courses['rs'];
                    break;

                case 'empty':
                    $response['crs'] = [];
                    break;
                
                case 'error':
                    $response['error'] = 'There was a problem getting courses!';
                    break;
            }
            
            $this->output->set_output(json_encode($response));
        }        
    }
    
    /**
     * Retrieve courses for a department
     *
     * @return boolean
     * 
     */
    public function getCourseInfo($csid) {
        
        if($this->input->server('REQUEST_METHOD') == 'GET') {           
            $response = [];
            
            $content = $this->mdl->getCourseInfo($csid);             
            
            switch ($content['status']) {
                case 'success':
                    $response['content'] = $content['rs'];
                    break;

                case 'empty':
                    $response['content'] = [];
                    break;
                
                case 'error':
                    $response['error'] = 'There was a problem getting content for this course!';
                    break;
            }
            
            $this->output->set_output(json_encode($response));
        }        
    }
    
    
    public function getCourseReg($csid, $sesid){
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            
            $rs = $this->mdl->getStudentCourseReg($csid, $sesid);
            
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

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */