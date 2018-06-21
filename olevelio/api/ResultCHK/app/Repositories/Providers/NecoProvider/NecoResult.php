<?php
namespace Repositories\Providers\NecoProvider;
use Repositories\ResultChecker\ResultInterface;
/**
 * Description of WaecResult
 *
 * @author Suleodu
 */
class NecoResult implements ResultInterface{
    private $exam_name;
    private $exam_year;
    private $exam_type;
    private $exam_number;
    private $exam_center;
    private $candidate_name;
    private $score_array = array();
    private $plain_result;
    private $html_result;
    private $json_result;
    private $tabled_result;
    private $array_result = array();
    private $status;
    private $response;
    private $card_use;
    

    
    function get_exam_name() {
        return $this->exam_name;
    }

    
    function get_exam_year() {
        return $this->exam_year;
    }

    
    function get_exam_type() {
        return $this->exam_type;
    }

    
    function get_exam_number() {
        return $this->exam_number;
    }

    
    function get_candidate_name() {
        return $this->candidate_name;
    }

    
    function get_exam_center() {
        return $this->exam_center;
    }

   
    function get_plain_result(){
        return $this->plain_result;
    }
    
    
    function get_html_result(){
        return $this->html_result;
    }
    
    
    function get_tabled_result(){
        return $this->tabled_result;
    }
    
    
    function get_array_scores(){
        return $this->array_result;
    }
    
    
    function get_json_result(){
        return $this->json_result;
    }
    
    
    function get_result_status() {
        return $this->status;
    }
    
    
    function get_result_response() {
        return $this->response;
    }

    
    function get_card_use(){
        return $this->card_use;
    }
    
    
    
//    This Are setterer
    function set_exam_name($exam) {
        $this->exam_name = $exam;
    }

    function set_exam_year($exam_year) {
        $this->exam_year = $exam_year;
    }

    function set_exam_type($exam_type) {
        $this->exam_type = $exam_type;
    }

    function set_exam_number($exam_number) {
        $this->exam_number = $exam_number;
    }

    function set_candidate_name($candidate_name) {
        $this->candidate_name = $candidate_name;
    }

    function set_html_result($html) {
        $this->html_result = $html;
    }

    function set_exam_center($exam_center) {
        $this->exam_center = $exam_center;
    }
    
    
    function set_array_score(array $score_array){
        $this->score_array = $score_array;
    }

    function set_result_status($status) {
        $this->status = $status;
    }

    function set_result_response($response) {
        $this->response = $response;
    }
    
    function set_result_card_use($count){
        $this->card_use = $count;
    }
    
    function set_array_result($rst){
        $this->array_result = $rst;
    }

    function set_plain_result($plain_result) {
        $this->plain_result = $plain_result;
    }
    
    function set_tabled_result($table_result) {
        $this->tabled_result = $table_result;
    }
    
    function set_card_use($card){
        $this->card_use = $card;
    }
    
    function set_json_result( $json){
        return $this->json_result = $json;
    }
}
