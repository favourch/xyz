<?php
namespace Repositories\Providers\WaecProvider;

use Repositories\ResultChecker\ResultProviderInterface;
//use Repositories\ResultChecker\ResultInterface;
use Exception;


/**
 * Description of WaecResultProvider
 *
 * @author Suleodu
 */
class WaecResultProvider implements ResultProviderInterface{
    private $card_pin;
    private $card_sn;
    private $exam_number;
    private $exam_type;
    private $exam_year;
    private $client; // Service used to fetch and crawl the result;
    private $waec_url;
    private $result_json;
    private $waecResult;
    
    private $exam_result;
    
    public function __construct($client) {
        $this->client = $client;
        
    }

    public function fetch_result(){
        $this->waecResult = new WaecResult; 
       
        //Wanna Query THe Result Checking URL HERE WITH THE DETAILS PASSED ABOVE
        $waec_url_query_string = sprintf("?ExamNumber=%s"
                                        . "&ExamYear=%s"
                                        . "&serial=%s"
                                        . "&pin=%s"
                                        . "&ExamType=%s",
                                        $this->get_exam_number(),
                                        $this->get_exam_year(), 
                                        $this->get_card_sn(), 
                                        $this->get_card_pin(), 
                                        $this->get_exam_type());
        $this->waec_url = "https://www.waecdirect.org/DisplayResult.aspx".$waec_url_query_string;
        
        $this->client = curl_init();
        curl_setopt($this->client, CURLOPT_URL, $this->waec_url);
        curl_setopt($this->client, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->client, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->client, CURLOPT_COOKIEJAR, "cache/".$this->get_exam_number(). "session.txt");  //could be empty, but cause problems on some hosts
        curl_setopt($this->client, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
        
        $answer = curl_exec($this->client);
        $answer2 = strip_tags($answer);
        
        if (curl_error($this->client)) 
        {
            $this->waecResult->set_result_status(0);
            $this->waecResult->set_exam_name('WAEC');
            $this->waecResult->set_html_result(curl_error($this->client));
            $this->waecResult->set_result_response(curl_error($this->client));  
            
            $to_json = array(
                    'status' => 0,
                    'exam_name' => 'WAEC',
                    'message' => curl_error($this->client),
                    
                );
            $this->waecResult->set_json_result(json_encode($to_json)); 
                    
            $this->remove_cache();
            return $this->waecResult;
        }
        else
        {
            $fileo = fopen($this->get_exam_number() . "-1.txt", "w"); //STORING THE RESULT OF OUR QUERY IN A FILE
            fwrite($fileo, $answer);
            fclose($fileo);
            $fileo = fopen($this->get_exam_number() . ".txt", "w"); //STORING THE RESULT OF OUR QUERY IN A FILE WHICH AS BEEN STRIPPED OF ALL TAGS
            fwrite($fileo, $answer2);
            fclose($fileo);

            $pageData = file_get_contents($this->get_exam_number() . "-1.txt"); //READING THE CONTENT OF THE FILE TO KNOW IF THERE'S AN ERROR OR NOT
        
            if (preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $pageData, $links, PREG_PATTERN_ORDER)) 
            {
               //IF THERE'S AN ERROR ONLY ONE LINK WILL BE AVAILABLE ON THE PAGE SO LET'S CHECK WHAT THE FIRST LINK CONTAINS
                $all_hrefs = array_unique($links[1]);
                $subjects = $all_hrefs[0];
                $querystring = parse_url($subjects, PHP_URL_QUERY);
                parse_str($querystring, $vars);
                
                //NOW CHECK IF THE SELECTED LINK CONTAINS THE errTitle KEYWORD IF NO THEN WE GOT A RESULT IF YES THEN ECHO ERRO OR DO ANY OTHER STUFF HERE
                if (isset($vars['errTitle'])) {
                    //insert_error("WAEC", $vars['errTitle']);
                    $message = $vars['errTitle'];
                    $this->waecResult->set_result_status(0);
                    $this->waecResult->set_exam_name('WAEC');
                    $this->waecResult->set_html_result($message);
                    $this->waecResult->set_result_response($message);  
                    
                    $to_json = array(
                            'status' => 0,
                            'exam_name' => 'WAEC',
                            'message' => $message,
                            
                        );

                    $this->waecResult->set_json_result(json_encode($to_json));
                    $this->remove_cache();
            
                    return $this->waecResult;
                }
                else
                {
                    $html = new \DOMDocument();
                    //IF WE PASS THAT BLOCK CODE THAT MEANS WE HAVE A RESULT SO LET'S CONTINUE OUR ADVENTURE
                    //LETS READ THE PAGE INTO AN ARRAY ALTHOUGH THIS METHOD MIGHT LOOK CRUDE IT WORKS AS LONG AS THE STRUCTURE OF THE SITE DOESN'T CHANGE
                    
                    $html->loadHTML("$pageData");
                    $tables = $html->getelementsbytagname('table');
                    $candidate_info = $tables->item(2)->getElementsByTagName('td');
                    $result_info = $tables->item(4)->getElementsByTagName('td');
                    $card_info = $tables->item(6)->getElementsByTagName('td');
                    // print_r($result_info->item(13)->textContent);die();
                    $rows = $tables->item(0)->getElementsByTagName('tr');
                    $cols = $rows->item(1)->getElementsByTagName('td');
                    $file = file($this->get_exam_number() . ".txt");
                    $hhtml = $answer;
                    $candidate_name = $candidate_info->item(4)->textContent;
                    $exam_t = $candidate_info->item(6)->textContent;
                    $exam_centre = $candidate_info->item(8)->textContent;
                    $card_use = $card_info->item(0)->textContent;
                    $sub1 = $result_info->item(1)->textContent;
                    $gsub1 = $result_info->item(2)->textContent;
                    $sub2 = $result_info->item(3)->textContent;
                    $gsub2 = $result_info->item(4)->textContent;
                    $sub3 = $result_info->item(5)->textContent;
                    $gsub3 = $result_info->item(6)->textContent;
                    $sub4 = $result_info->item(7)->textContent;
                    $gsub4 = $result_info->item(8)->textContent;
                    $sub5 = $result_info->item(9)->textContent;
                    $gsub5 = $result_info->item(10)->textContent;
                    $sub6 = $result_info->item(11)->textContent;
                    $gsub6 = $result_info->item(12)->textContent;
                    $sub7 = $result_info->item(13)->textContent;
                    $gsub7 = $result_info->item(14)->textContent;
                    $sub8 = $result_info->item(15)->textContent;
                    $gsub8 = $result_info->item(16)->textContent;
                    $sub9 = $result_info->item(17)->textContent;
                    $gsub9 = $result_info->item(18)->textContent;

                    $this->waecResult->set_result_status(1);
                    $this->waecResult->set_exam_name('WAEC');
                    $this->waecResult->set_html_result($hhtml);
                    $this->waecResult->set_exam_type($exam_t);
                    $this->waecResult->set_exam_number($this->get_exam_number());
                    $this->waecResult->set_candidate_name($candidate_name);
                    $this->waecResult->set_exam_year($this->get_exam_year());
                    $this->waecResult->set_exam_center($exam_centre);
                    $this->waecResult->set_card_use($card_use);

                    $result = array(
                        array('subject' => $sub1, 'score' => $gsub1),
                        array('subject' => $sub2, 'score' => $gsub2),
                        array('subject' => $sub3, 'score' => $gsub3),
                        array('subject' => $sub4, 'score' => $gsub4),
                        array('subject' => $sub5, 'score' => $gsub5),
                        array('subject' => $sub6, 'score' => $gsub6),
                        array('subject' => $sub7, 'score' => $gsub7),
                        array('subject' => $sub8, 'score' => $gsub8),
                        array('subject' => $sub9, 'score' => $gsub9)
                    );
                    $this->waecResult->set_array_score($result);
                    
                    $table = "<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>
                            <table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>Candidate Information</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination Name</td>
                                                        <td align='left' width='60%'>WAEC</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination Number</td>
                                                        <td align='left' width='60%'>".$this->get_exam_number()."</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Candidate Name</td>
                                                        <td align='left' width='60%'>$candidate_name &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination</td>
                                                        <td align='left' width='60%'> $exam_t</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Centre</td>
                                                        <td align='left' width='60%'>$exam_centre</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;&nbsp;
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>

                            <table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Subject Grades</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub1</td>
                                                        <td align='left' width='60%'>$gsub1</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub2</td>
                                                        <td align='left' width='60%'>$gsub2</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub3</td>
                                                        <td align='left' width='60%'>$gsub3</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub4</td>
                                                        <td align='left' width='60%'>$gsub4</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub5</td>
                                                        <td align='left' width='60%'>$gsub5</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub6</td>
                                                        <td align='left' width='60%'>$gsub6</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub7</td>
                                                        <td align='left' width='60%'>$gsub7</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub8</td>
                                                        <td align='left' width='60%'>$gsub8</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>$sub9</td>
                                                        <td align='left' width='60%'>$gsub9</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;&nbsp;</p>
                            <table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Card Information</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td align='left' width='40%'>Card Use</td>
                                                        <td align='left' style='' width='60%'>$card_use</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>";
                
                    $this->waecResult->set_tabled_result($table);
                    
                    $plain = "
                    Candidate Name: $candidate_name
                    <br>
                    Examination: $exam_t
                    <br>
                    Centre: $exam_centre
                    <br>
                    Card Use: $card_use
                    <br>
                    $sub1 - $gsub1
                    <br>
                    $sub2 - $gsub2
                    <br>
                    $sub3 - $gsub3
                    <br>
                    $sub4 - $gsub4
                    <br>
                    $sub5 - $gsub5
                    <br>
                    $sub6 - $gsub6
                    <br>
                    $sub7 - $gsub7
                    <br>
                    $sub8 - $gsub8
                    <br>
                    $sub9 - $gsub9
                    <br>
                    ";

                    $this->waecResult->set_plain_result($plain);
                    
                    
                    $to_json = array(
                                'status' => 1,
                                'card_pin'=>$this->get_card_pin(),
                                'card_sn'=>$this->get_card_sn(),
                                'exam_name' => 'WAEC',
                                'exam_year' => $this->exam_year,
                                'exam_type' => $exam_t,
                                'exam_number' => $this->get_exam_number(),
                                'candidate_name' => $this->sanitize($candidate_name),
                                'exam_center' => $this->sanitize($exam_centre),
                                'card_use' => $card_use,
                                'result' => $result,
                                
                            );

                    $this->waecResult->set_json_result(json_encode($to_json)); 
                    
                    $this->remove_cache();
                    return $this->waecResult;
                }
            }
            
        }
    }
    


    public function get_card_pin() {
        return $this->card_pin;
    }

    public function get_card_sn() {
        return $this->card_sn;
    }

    public function get_exam_number() {
        return $this->exam_number;
    }

    public function get_exam_type() {
        return $this->exam_type;
    }

    public function get_exam_year() {
        return $this->exam_year;
    }

    public function set_card_pin($pin) {
        $this->card_pin = $pin;
    }

    public function set_card_sn($sn) {
        $this->card_sn = $sn;
    }

    public function set_exam_number($ex_num) {
        $this->exam_number = $ex_num;
    }

    public function set_exam_type($ex_type) {
        $this->exam_type = $ex_type;
    }
    
    public function get_json_result(){
        
    }

    public function set_exam_year($ex_year) {
        $this->exam_year = $ex_year;
    }

    
    public function get_all_exam_types(){
       return array(
                'WAEC' => 'MAY/JUNE', 
                'WAEC-PRIVATE' => 'NOV/DEC'
                );
                   
                
    }
    
    private function sanitize($string) {
        return trim(preg_replace('/\s\s+/', ' ', str_replace("'", "`", $string)));
    }

    
    private function remove_cache() {
        
        if (file_exists($this->get_exam_number().".txt")) {
            unlink($this->get_exam_number().".txt");
        }
        if (file_exists($this->get_exam_number()."-1.txt")) {
            unlink($this->get_exam_number(). "-1.txt");
        }
        if (file_exists("cache/".$this->get_exam_number(). "session.txt")) {
            unlink("cache/".$this->get_exam_number() . "session.txt");
        }
        if (file_exists("cache/".$this->get_exam_number() . "token.txt")) {
            unlink("cache/".$this->get_exam_number() . "token.txt");
        }
    }

}
?>