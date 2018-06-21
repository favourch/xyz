<?php if (!defined('BASEPATH'))exit('No direct script access allowed');
require_once(APPPATH . 'third_party/jwt/JWT.php');

Use Firebase\JWT\JWT;

class Lecturer extends CI_Controller {

    /*
     * Class constructor
     * 
     * @access public 
     * @retun void
     */

    public function __construct(){

        parent::__construct();
        $this->load->model('lecturer_model', 'mdl');
        $this->load->library('ResourceLib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
    }
    
    /**
     * Retrieve all courses registered by the active user
     *
     * @return boolean
     * 
     */
    public function getLecturer($lectid) {
        
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            
            $response = [];
            
            $status = $this->mdl->getLecturer($lectid);

            switch ($status['status']) {
                case 'success':
                    $response = ['info' => $status['rs']];
                    break;

                case 'empty':
                    $response = ['info' => []];
                    break;

                case 'error':
                    $this->output->set_status_header(500);
                    $response = ['reason' => 'There was a problem getting information for this lecturer!'];
                    break;
            }
            
            
            $this->output->set_output(json_encode($response));
        }        
    }
    
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */