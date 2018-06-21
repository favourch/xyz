<?php if (!defined('BASEPATH'))exit('No direct script access allowed');

class Auth extends CI_Controller {

    /*
     * Class constructor
     * 
     * @access public 
     * @retun void
     */

    public function __construct(){

        parent::__construct();
        $this->load->model('auth_model', 'mdl');
        $this->load->library('ResourceLib', null, 'res_lib');
        $this->res_lib->addCorsHeaders();
    }

    /**
     * Authenticate a user 
     *
     * @return json Authentication token
     * 
     */
    public function login() {
        if($this->input->server('REQUEST_METHOD') == 'POST') {
            $request = json_decode(file_get_contents("php://input"), TRUE);
            $response = '';
            
            $request['username'] = isset($request['username'])? $request['username']: '';
            $request['password'] = isset($request['password'])? $request['password']: '';
                    
            $status = $this->mdl->authenticate($request['username'], $request['password']);
            
            switch($status['status']) {
                case 'success':
                    $user = get_object_vars($status['rs']);
                    $user['image'] = $this->image_encode($user['stdid']);
                    $sessions = [];
                    $session = $this->mdl->get_sessions($user['stdid'], $user['sesid']);
                    
                    switch($session['status']) {
                        case 'success':
                            $sessions = $session['rs'];
                            break;
                    }
                    
                    $required = array_intersect_key($user, 
                                    [
                                        "stdid" => '',
                                        "lname" => '',
                                        "fname" => '',
                                        "mname" => '',
                                        "phone" => '',
                                        "email" => '',
                                        "addr" => '',
                                        "sex" => '',
                                        "typename" => '',
                                        "sesname" => '',
                                        "semester" => '',
                                        "progname" => '',
                                        "deptname" => '',
                                        "colname" => '',
                                        "coltitle" => '',
                                        "level" => '',
                                        "image" => ''
                                    ]
                                );
                    
                    $payload = [
                        'iat' => time(),
                        'sub' => $user['stdid']                          
                    ];
                    
                    $required["sessions"] = $sessions;
                    $token = $this->res_lib->encode($payload);
                    $response = json_encode(['token' => $token, 'user' => $required]);                               
                    break;
                
                case 'invalid':
                    $this->output->set_status_header(401);
                    $response = json_encode(['reason' => 'Incorrect username/password combination!']); 
                    break;
                case 'empty':
                    $this->output->set_status_header(404);
                    $response = json_encode(['reason' => 'Incorrect username/password combination!']);                
                    break;
                
                case 'error':
                    $this->output->set_status_header(500);
                    $response = json_encode(['reason' => 'There was a problem verifying your credentials!']); 
                    break;
            }       
            
            $this->output->set_output($response);
        }
    }
    
    /**
     * Get the users base encoded image
     *
     * @return string encoded image
     * 
     */
    public function image_encode($image_name) {
        
        $base_value = $img = $type = NULL;
        $dir = FCPATH.'/../img/user/student/';
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