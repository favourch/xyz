<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TAMS
 * Programme controller
 * 
 * @category   Controller
 * @package    
 * @subpackage 
 * @author     Sule-odu Adedayo <suleodu.adedayo@gmail.com>
 * @copyright  Copyright © 2014 TAMS.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */
class Programme extends CI_Controller {

	
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
        $this->load->model('programme_model','prg_mdl' );
        $this->load->library('resourcelib', null, 'res_lib');
        
        $this->res_lib->addCorsHeaders();
    }
    
    
    public function index($id = NULL){
            
            if(!is_null($id) && $id > 0){
               
                $rs = $this->prg_mdl->get_programme($id);
            }else{
                $rs = $this->prg_mdl->get_programme();
            }
               
            if(empty($rs)){
                $result['status'] = 0;
                $result['rs'] = "No record found";
                
            }
            else{
             
                $result['status'] = 1;
                $result['rs'] = $rs;
            }
            
           echo json_encode($result);
	}
}