<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model {
    
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
 
    public function authenticate($username, $password){
        
        $ret = ['status' => 'error'];
        
        $this->db->select('*');
        $this->db->from('student s');
        //$this->db->join('admissions a', 'a.admid = s.admid', 'LEFT');
        $this->db->join('admission_type at', 's.admid = at.typeid', 'LEFT');
        $this->db->join('session sn', 's.sesid = sn.sesid', 'LEFT');
        $this->db->join('programme p', 's.progid = p.progid', 'LEFT');
        $this->db->join('department d', 'p.deptid = d.deptid', 'LEFT');
        $this->db->join('college c', 'c.colid = d.colid', 'LEFT');        
        $this->db->where('stdid', $username);
        $this->db->or_where('s.phone', $username);
        $this->db->or_where('s.email', $username);
        
        $result = $this->db->get();
        
        if($result) {

            // Set default return value 
            $ret = ['status' => 'empty'];

            // Check if query is not empty
            if (($r_count = $result->num_rows()) > 0) {
                $result_set = $result->row();
                
                if($result_set->password == md5($password)) {
                    unset($result_set->password);
                    $ret = ['status' => 'success', 'rs' => $result_set, 'count' => $r_count];
                }else {
                    $ret = ['status' => 'invalid'];
                }               
            }
        }

        return $ret;        
    }
    
    public function get_sessions($userid, $adses) {
        $ret = ['status' => 'error'];
//        
//        $this->db->select('sesid, sesname');
//        $this->db->from('session s');      
//        $this->db->where('sesid', $username);
//        $this->db->query('phone', $username);
        $sql = "SELECT sesid, sesname FROM session WHERE sesid >= ?";

        
        $result = $this->db->query($sql, array($adses));
        
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
} // End class Auth_model



