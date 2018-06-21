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
    
    
    public function getCourse($csid = null){
        
        if(!is_null($csid) ){
            $query = $this->db->get_where('course', array('csid' => $csid));
            $result = $query->result_array();
            
        }
        else{
            $query = $this->db->get('course');
            
            $result = $query->result_array();
        }
        
        return $result;
        
    }
 
    public function getRegisteredCourses($user, $sesid){
        
        $ret = ['status' => 'error'];
        
        $this->db->select('c.csname, c.csid, c.semester, r.tscore + r.escore as score, dc.status as status,'
                . ' dc.unit as unit, c.status as status1, c.unit as unit1, t.released as approve');
        $this->db->from('course_reg cr');
        $this->db->join('result r', 'cr.stdid = r.stdid AND cr.csid = r.csid AND cr.sesid = r.sesid', 'LEFT');  
        $this->db->join('student s', 's.stdid = r.stdid', 'LEFT');     
        $this->db->join('course c', 'c.csid = r.csid');      
        $this->db->join('teaching t', 't.csid = r.csid AND t.sesid = r.sesid', 'LEFT');  
        $this->db->join('session sn', 'sn.sesid = r.sesid'); 
        $this->db->join('department_course dc', 'dc.csid = r.csid AND dc.progid = s.progid', 'LEFT'); 
        $this->db->where('r.stdid', $user);
        
        if($sesid)
            $this->db->where('sn.sesid', $sesid);
        else 
           $this->db->where('sn.status', 'TRUE');
           
        $result = $this->db->get();
        
        if($result) {

            // Set default return value 
            $ret = ['status' => 'empty'];

            // Check if query is not empty
            if (($r_count = $result->num_rows()) > 0) {                
                $result_set = $result->result();
                foreach ($result_set as $row) {
                    if($row->approve != 'yes')
                        $row->score = null;
                }
                $ret = ['status' => 'success', 'rs' => $result_set, 'count' => $r_count];
                             
            }
        }

        return $ret;        
    }
    
    public function getCoursesForDepartment($deptid){
        
        $ret = ['status' => 'error'];
        
        $this->db->select('csname, csid');
        $this->db->from('course');
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
    }
    
    public function getCourseInfo($csid) {
        $ret = ['status' => 'error'];
        
        $this->db->select('csname, c.csid, cscont, lname, fname, lectid');
        $this->db->from('course c');
        $this->db->join('teaching t', 't.csid = c.csid', 'LEFT');
        $this->db->join('lecturer l', 'l.lectid = t.lectid1', 'LEFT');
        $this->db->join('session s', 's.sesid = t.sesid', 'LEFT');
        $this->db->where('c.csid', $csid);
        $this->db->where('s.status', 'TRUE');
        
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
    
    
    public function getStudentCourseReg($csid, $sesid){
        
        $this->db->select('stdid');
        $query = $this->db->get_where('course_reg', array('csid' => $csid, 'sesid' => $sesid));
        $result = $query->result_array();
        //die($this->db->last_query());
        
        return $result;
    }
    
} // End class Course_model
