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

class Erp_model extends CI_Model {
    
    
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
 
    
    
    
    public function get_dept_cs_reg($id = NULL){
        
        if(!is_null($id) && $id > 0){
            //$this->db->select('department.deptid, department.deptname, count(registration.stdid) AS `count`');
            //$this->db->from('registration');
            //$this->db->join('student', 'registration.stdid = student.stdid', 'right');
            //$this->db->join('programme', 'programme.progid = student.progid');
            //$this->db->join('department', 'department.deptid = department.deptid');
            //$this->db->where('registration.course', 'Registered');
            //$this->db->where('student.status', 'Undergrad');
            //$this->db->where('registration.sesid', $id);
            //$this->db->group_by('programme.deptid');
            //$this->db->order_by('department.deptid', 'ASC');
            //$query = $this->db->get();
            
             $sql = sprintf("SELECT d.deptid, d.deptname, count(r.stdid) AS `count` "
                        . "FROM registration r "
                        . "RIGHT JOIN student s ON r.stdid = s.stdid "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "WHERE r.course = 'Registered' "
                        . "AND s.status = 'Undergrad' "
                        . "AND r.sesid = %s "
                        . "GROUP BY p.deptid "
                        . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            $result = $query->result_array();
            
        }
        
        
        return $result;
    }
    
    
    
    public function get_dept_cs_reg_approve($id = NULL){
        
        if(!is_null($id) && $id > 0){
            //$this->db->select('department.deptid, department.deptname, count(registration.stdid) AS `count`');
            //$this->db->from('registration');
            //$this->db->join('student', 'registration.stdid = student.stdid', 'right');
            //$this->db->join('programme', 'programme.progid = student.progid');
            //$this->db->join('department', 'department.deptid = department.deptid');
            //$this->db->where('registration.approved', 'TRUE');
            //$this->db->where('student.status', 'Undergrad');
            //$this->db->where('registration.sesid', $id);
            //$this->db->group_by('programme.deptid');
            //$this->db->order_by('department.deptid', 'ASC');
            //$query = $this->db->get();
            $sql = sprintf("SELECT d.deptid, d.deptname, count(r.stdid) AS `count` "
                            . "FROM registration r "
                            . "RIGHT JOIN student s ON r.stdid = s.stdid "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "WHERE r.approved = 'TRUE' "
                            . "AND s.status = 'Undergrad' "
                            . "AND r.sesid = %s "
                            . "GROUP BY p.deptid "
                            . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            $result = $query->result_array();
            
        }
        
        
        return $result;
    }
    
    
    
    public function dept_student($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT count(r.stdid) as `count`, d.deptname, d.deptid, d.colid "
                        . "FROM student s, department d, programme p, registration r "
                        . "WHERE r.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "GROUP BY d.deptid "
                        . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    
    
    public function dept_student_male($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT distinct(count(s.stdid)) as `count`, d.deptname, d.colid "
                        . "FROM student s, department d, programme p, registration r "
                        . "WHERE r.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "AND s.sex = 'M' "
                        . "GROUP BY d.deptid "
                        . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    
    public function dept_student_female($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT distinct(count(s.stdid)) as `count`, d.deptname, d.colid "
                        . "FROM student s, department d, programme p, registration r "
                        . "WHERE r.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "AND s.sex = 'F' "
                        . "GROUP BY d.deptid "
                        . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    
    public function dept_staff($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT d.deptname, count(lectid) as `count` "
                            . "FROM lecturer l, department d "
                            . "WHERE d.deptid = l.deptid "
                            . "AND l.status = 'Active' "
                            . "GROUP BY l.deptid "
                            . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    
    public function dept_pay($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT d.deptid, d.deptname, count(distinct(st.matric_no)) AS `count` "
                        . "FROM schfee_transactions st "
                        . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid "
                        . "JOIN student s ON st.matric_no = s.stdid "
                        . "JOIN registration r ON r.stdid = s.stdid AND r.stdid = st.matric_no "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "JOIN college c ON c.colid = d.colid "
                        . "WHERE st.status = 'APPROVED' "
                        . "AND ps.sesid = %s "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY d.deptid "
                        . "ORDER BY d.deptid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    public function coll_staff($id = NULL){
        
        $sql = sprintf("SELECT c.colid, c.colname, count(lectid) as `count` "
                            . "FROM lecturer l, department d, college c "
                            . "WHERE d.deptid = l.deptid "
                            . "AND d.colid = c.colid "
                            . "AND l.status = 'Active' "
                            . "GROUP BY c.colid "
                            . "ORDER BY c.colid ASC");
            $query = $this->db->query($sql);
            
            return $result = $query->result_array();
    }
    
    
    public function coll_student($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT distinct(count(s.stdid)) as `count`, c.colname "
                        . "FROM student s, department d, programme p, college c, registration r "
                        . "WHERE s.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "AND d.colid = c.colid "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    
    public function coll_student_male($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT  distinct(count(s.stdid)) as `count`, c.colid, c.colname "
                        . "FROM student s, department d, programme p, college c, registration r "
                        . "WHERE s.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "AND d.colid = c.colid "
                        . "AND s.sex = 'M' "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    public function coll_student_female($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT distinct(count(s.stdid)) as `count`, c.colid, c.colname "
                        . "FROM student s, department d, programme p, college c, registration r "
                        . "WHERE s.progid = p.progid "
                        . "AND d.deptid = p.deptid "
                        . "AND r.stdid = s.stdid "
                        . "AND r.sesid = %s "
                        . "AND d.colid = c.colid "
                        . "AND s.sex = 'F' "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
        }
        return $result;
    }
    
    
    public function get_coll_cs_reg($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT c.colid, c.colname,  count(r.stdid) AS `count` "
                        . "FROM registration r "
                        . "RIGHT JOIN student s ON r.stdid = s.stdid "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "JOIN college c ON c.colid = d.colid "
                        . "WHERE r.course = 'Registered' "
                        . "AND r.sesid = %s "
                        //. "AND s.status = 'Undergrad' "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC", $id);
            $query = $this->db->query($sql);
            $result = $query->result_array();
            
        }
        
        
        return $result;
    }
    
    
    public function get_coll_cs_reg_approve($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT c.colid, d.deptid, d.deptname, count(distinct(r.stdid)) AS `count` "
                                . "FROM registration r "
                                . "RIGHT JOIN student s ON r.stdid = s.stdid "
                                . "JOIN programme p ON p.progid = s.progid "
                                . "JOIN department d ON d.deptid = p.deptid "
                                . "JOIN college c ON c.colid = d.colid "
                                . "WHERE r.approved = 'TRUE' "
                                . "AND r.sesid = %s "
                                //. "AND s.status = 'Undergrad' "
                                . "GROUP BY c.colid "
                                . "ORDER BY c.colid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
            
        }
        
        
        return $result;
    }
    
    
    public function college_pay($id = NULL){
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT c.colid, c.colname, count(distinct(st.matric_no)) AS `count` "
                        . "FROM schfee_transactions st "
                        . "JOIN payschedule ps ON st.scheduleid = ps.scheduleid "
                        . "JOIN student s ON st.matric_no = s.stdid "
                        . "JOIN registration r ON r.stdid = s.stdid AND r.stdid = st.matric_no "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "JOIN department d ON d.deptid = p.deptid "
                        . "JOIN college c ON c.colid = d.colid "
                        . "WHERE st.status = 'APPROVED' "
                        //. "AND s.status = 'Undergrad' "
                        . "AND ps.sesid = %s "
                        . "GROUP BY c.colid "
                        . "ORDER BY c.colid ASC", $id);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
            
            
        }
        
        
        return $result;
    }
    
    
    
    public function college_population($id = NULL){
        $where = "";
        if(isset($_GET['level'])){
            $where .= sprintf("AND r.level =  %s ", $_GET['level']);
        }
        
        if(!is_null($id) && $id > 0){
            $sql = sprintf("SELECT count(distinct(r.stdid)) AS `count`, c.colid, c.coltitle, r.level "
                            . "FROM registration r "
                            . "RIGHT JOIN student s ON r.stdid = s.stdid "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "JOIN college c ON c.colid = d.colid "
                            . "WHERE r.sesid = %s  %s "
                            //. "AND s.status = 'Undergrad' "
                            . "GROUP BY c.colid, r.level "
                            . "ORDER BY c.colid, r.level ASC", $id, $where);
            $query = $this->db->query($sql);
            
            $result = $query->result_array();
            
            
            
        }
        
        
        return $result;
    }
    
} // End class addmission_model



