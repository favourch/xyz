<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TAMS
 * Exam Model
 * 
 * @category   Model
 * @package    College 
 * @subpackage Admission
 * @author     Suleodu Adedayo <suleodu.adedayo@gmail.com>
 * @copyright  Copyright © 2014 TAMS.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class College_model extends CI_Model {
    
    
    /**
     * Class constructor
     * 
     * @access public
     * @return void
     */
    public function __construct() {
        
            parent::__construct();
            $this->load->database();

    } // End func __construct
 
    
    
    
    public function get_college($id = NULL){
        
        if(!is_null($id) && $id > 0){
//            $this->db->select('colid, colname, coltitle', 'page_up');
            $query = $this->db->get_where('college', array('colid' => $id));
            $result = $query->row_array();
            
        }
        else{
            
            //$this->db->select('colid, colname, coltitle', 'page_up');
            $query = $this->db->get('college');
            $result = $query->result_array();
        }
        
        return $result;
    }
    
    
    /**
     * Create a new prospective student
     * 
     * @access public
     * @param array $param
     * @return int
     */
    public function create($param) {
        
        
    } //End of func create
    
    
    
} // End class addmission_model



