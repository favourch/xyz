<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Course_model extends CI_Model {
    
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
 
    public function getRegisteredCourses($user){
        
        $ret = ['status' => 'error'];
        
        $this->db->select('c.csname, c.csid, c.semester, r.tscore + r.escore as score, dc.status as status,'
                . ' dc.unit as unit, c.status as status1, c.unit as unit1');
        $this->db->from('result r');
        $this->db->join('student s', 's.stdid = r.stdid', 'LEFT');
        $this->db->join('programme p', 'p.progid = s.progid', 'LEFT');
        $this->db->join('department d', 'd.deptid = p.deptid', 'LEFT');
        $this->db->join('department_course dc', 'dc.csid = r.csid AND dc.deptid = d.deptid', 'LEFT');        
        $this->db->join('course c', 'c.csid = r.csid', 'LEFT');  
        $this->db->join('session sn', 'sn.sesid = r.sesid', 'LEFT'); 
        $this->db->where('r.stdid', $user);
        $this->db->where('sn.status', 'TRUE');
        
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
    }
    
} // End class Course_model



