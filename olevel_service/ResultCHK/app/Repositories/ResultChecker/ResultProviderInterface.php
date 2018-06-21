<?php
namespace Repositories\ResultChecker;

/**
 * Result Checker Library 
 * 
 * 
 * @category   Provider Interface
 * @subpackage 
 * @author     Adedayo Sule-odu  <suleodu.adedayo@gmail.com>
 * @copyright  Copyright Â© 2014 Result Checker Library.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */
interface ResultProviderInterface {
    
    public function get_exam_year();
    
    public function set_exam_year($yr);
    
    public function get_exam_type();
    
    public function set_exam_type($typ);
    
    public function get_exam_number();
    
    public function set_exam_number($num);
    
    public function get_card_pin();
    
    public function set_card_pin($pin);
    
    public function get_all_exam_types();
    
    public function get_card_sn();
    
    public function set_card_sn($sn);
    
    public function fetch_result();
    
    
}
