<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

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

class Student_model  extends CI_Model {
    /**
     * Class constructor
     * 
     * @access public
     * @return void
     */
    public function __construct() {

        parent::__construct();
        $this->load->database();
    }

// End func __construct
    
    public function get_student($id = NULL) {

        if (!is_null($id) ) {
            $this->db->select('stdid AS matric, progid, lname AS Last_name, fname AS First_name, mname AS Middle_name, phone, email,sex, addr AS Address, dob, level, maritalstatus');
            $query = $this->db->get_where('student', array('stdid' => $id, 'disciplinary' => 'False', 'status' => 'Undergrad'));
            $result = $query->row();
        } else {

            $this->db->select('stdid AS matric, progid, lname AS Last_name, fname AS First_name, mname AS Middle_name, phone, email,sex, addr AS Address, dob, level, maritalstatus');
            $query = $this->db->get_where('student', array('disciplinary' => 'False', 'status' => 'Undergrad'));
            $result = $query->result_array();
        }

        return $result;
    }
    
    
    public function get_student_not_in($userArray){
        $this->db->select('stdid, fname,lname,mname,progid,email');
        $this->db->from('student');
        $this->db->where_not_in('crm.user_id', $userArray);
        $result = $this->db->get();
        
        return $result = $query->result_array();
        
    }

}
