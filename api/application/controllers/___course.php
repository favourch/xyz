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
        $this->load->library('resourcelib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
    }
    
    /**
     * Retrieve all courses registered by the active user
     *
     * @return boolean
     * 
     */
    public function getRegisteredCourses() {
        
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            
            $response = null;
            $tok_status = $this->res_lib->getUserFromToken();
            
            if(!$tok_status['error']) {
               
                $status = $this->mdl->getRegisteredCourses($tok_status['response']);
                
                switch ($status['status']) {
                    case 'success':
                        $response = json_encode(['courses' => $status['rs']]);
                        break;

                    case 'empty':
                        $response = json_encode(['courses' => []]);
                        break;

                    case 'error':
                        $this->output->set_status_header(500);
                        $response = json_encode(['reason' => 'There was a problem getting your registered courses!']);
                        break;
                }
            }else {
                $tok_status['type'] == 'header'? $this->output->set_status_header(400): 
                    $this->output->set_status_header(401);
            }
            
            $this->output->set_output($response);
        }        
    }
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */