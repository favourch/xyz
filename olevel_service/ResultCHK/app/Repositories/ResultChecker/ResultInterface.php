<?php
namespace Repositories\ResultChecker;

/**
 * This defines the how the result response 
 * @author Suleodu
 */
interface ResultInterface {
    
    /**
     * get the Exam Name ie WAEC, NECO, NABTEB
     */
    public function get_exam_name();
    
    /**
     * get Exam year
     */
    public function get_exam_year();
    
    /**
     * Exam type
     */
    public function get_exam_type();
    
    
    public function get_exam_number();
    
    
    public function get_candidate_name();
    
    
    public function get_exam_center();
    
    
    
    /**
     * Return an assocaitive array of subject and score
     */
    public function get_array_scores();
    
    /**
     * Get full result in full text format
     */
    public function get_plain_result();
    
    
    /**
     * Return full HTML Version of the result crawled
     */
    public function get_html_result();
    
    /**
     * Return full HTML Version of the result crawled
     */
    public function get_tabled_result();
    
    
    /**
     * return full Json format of the crawled result 
     */
    public function get_json_result();
    
      
    public function get_result_status();
    
    
    public function get_result_response();
    
    
   
    public function get_card_use();
}
