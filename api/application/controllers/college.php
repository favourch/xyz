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
class College extends CI_Controller {

	
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
        $this->load->model('college_model','clg_mdl' );
        $this->load->library('resourcelib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
        
    }
    
    public function index($id = NULL) {
        if($this->input->server('REQUEST_METHOD') == 'GET') {
            if (!is_null($id) && $id > 0) {

                $rs = $this->clg_mdl->get_college($id);
            } else {
                $rs = $this->clg_mdl->get_college();
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