<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lecturer_model extends CI_Model {
    
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
 
    public function getLecturer($lectid){
        
        $ret = ['status' => 'error'];
        
        $this->db->select('l.lname, l.fname, l.lectid, l.email, l.phone, l.profile, d.deptname, c.colname');
        $this->db->from('lecturer l');
        $this->db->join('department d', 'd.deptid = l.deptid');
        $this->db->join('college c', 'c.colid = d.colid');
        $this->db->where('lectid', $lectid);
                
        $result = $this->db->get();
        
        if($result) {

            // Set default return value 
            $ret = ['status' => 'empty'];

            // Check if query is not empty
            if (($r_count = $result->num_rows()) > 0) {
                $result_set = $result->row();
                $ret = ['status' => 'success', 'rs' => $result_set, 'count' => $r_count];                             
            }
        }

        return $ret;        
    }
    
    
} // End class Course_model



