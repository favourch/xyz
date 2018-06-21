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

class Department_model extends CI_Model {
    
    
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
 
    public function get_department($id = NULL){
        
        $this->db->select('deptid, deptname, colid, page_up, page_down');

        if(!is_null($id) && $id > 0){
            $query = $this->db->get_where('department', array('deptid' => $id));
            $result = $query->row_array();
            
        }else{
            $query = $this->db->get('department');
            $result = $query->result_array();
        }
        
        return $result;
    }
    
    public function getDeptLecturers($deptid) {
        $ret = ['status' => 'error'];
        
        $this->db->select('fname, lname, mname, lectid');
        $this->db->from('lecturer');
        $this->db->where('deptid', $deptid);
        
        $result = $this->db->get();
        
        if($result) {

            // Set default return value 
            $ret = ['status' => 'empty'];

            // Check if query is not empty
            if (($r_count = $result->num_rows()) > 0) {
                $result_set = $result->result();
                $ret = ['status' => 'success', 'rs' => $result_set, 'count' => $r_count];
                             
            }
        }

        return $ret;     
        
    } //End of func create
    
    
    
} // End class addmission_model



