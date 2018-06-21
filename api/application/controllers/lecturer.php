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
                    $status['rs']->image = $this->image_encode($status['rs']->lectid);
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
    
    public function image_encode($image_name) {
        
        $base_value = $img = $type = NULL;
        $dir = FCPATH.'/../img/user/staff/';
        $image = [
            "{$dir}{$image_name}.jpg",
            "{$dir}{$image_name}.JPG",
            "{$dir}{$image_name}.png",
            "{$dir}{$image_name}.PNG",
        ];

        for ($idx = 0; $idx < count($image); $idx++) {
            if (realpath($image[$idx])) {
                $image_url = $image[$idx];
                $type = pathinfo($image_url, PATHINFO_EXTENSION);
                $img = base64_encode(file_get_contents($image_url));
                break;
            }
        }
        
        if($img) {
            $base_value = "data:image/{$type};base64,{$img}";
        }
                
        return $base_value;
    }
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */