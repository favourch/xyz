<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class News_model extends CI_Model {
    
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
 
    public function getPosts($type, $page, $limit){
        
        $ret = ['status' => 'error'];
        
        $this->db->select('post_id, title, text, date');
        $this->db->from('post p');
        $this->db->join('post_cat pc', 'pc.cat_id = p.cat_id'); 
        
        if($type == 'news')
            $this->db->where('pc.cat_id', 1);
        else
            $this->db->where('pc.cat_id', 2);
        
        $this->db->order_by('post_id', 'DESC');
        
        if(is_int($limit)) {
            $this->db->limit($limit);
        }
        
        if(is_int($page) && $page > 2) {
            $this->db->limit($limit, $page*$limit);
        }
        
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
    
    public function getPostsById($postId) {
        $ret = ['status' => 'error'];
       
        $this->db->select('post_id, title, text, date');
        $this->db->from('post');      
        $this->db->where('post_id', $postId);
        
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
} // End class Auth_model



