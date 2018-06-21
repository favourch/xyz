<?php

namespace Repositories\Providers\NabtebProvider;
use Repositories\ResultChecker\ResultProviderInterface;
//use Repositories\ResultChecker\ResultInterface;
//use Repositories\Library\simple_html_dom;


/**
 * Description of WaecResultProvider
 *
 * @author Suleodu
 */
class NabtebResultProvider implements ResultProviderInterface{
    private $card_pin;
    private $card_sn;
    private $exam_number;
    private $exam_type;
    private $exam_year;
    private $client; // Service used to fetch and crawl the result;
    private $neco_url;
    private $necoResult;
    
    private $exam_result;
    
    public function __construct($client) {
        $this->client = $client;
        
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function fetch_result(){
        $this->nabtebResult = new NabtebResult;
        libxml_use_internal_errors(TRUE);
		
        $data = array(
                'examtype'=>$this->get_exam_type(),
                'examyear'=>$this->get_exam_year(),
                'pin'=>$this->get_card_pin(),
                'candid'=>$this->get_exam_number(),
                'Submit' => 'Submit',
                'serial' => $this->get_card_sn(),
                'flag' => 'false'
                );
        if(!empty($email)){
                $data['email'] = $email;
        }
        $data1 = http_build_query($data);
        
        $this->client = curl_init();
	 	//Wanna Query THe Result Checking URL HERE WITH THE DETAILS PASSED ABOVE
	curl_setopt($this->client, CURLOPT_URL, "https://eworld.nabtebnigeria.org/results.asp");
	curl_setopt($this->client, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
	curl_setopt($this->client, CURLOPT_POST, true);
	curl_setopt($this->client, CURLOPT_POSTFIELDS, $data1);
	curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($this->client, CURLOPT_COOKIESESSION, true);
	curl_setopt($this->client, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($this->client, CURLOPT_COOKIEJAR, "cache/".$this->get_exam_number()."session.txt");  //could be empty, but cause problems on some hosts
	curl_setopt($this->client, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
 	
        $answer = curl_exec($this->client);
 	$answer2 = strip_tags($answer);
        
	if (curl_error($this->client)) {
            
            $this->nabtebResult->set_result_status(0);
            $this->nabtebResult->set_exam_name('NABTEB');
            $this->nabtebResult->set_html_result(curl_error($this->client));
            $this->nabtebResult->set_result_response(curl_error($this->client));  
            
            $to_json = array(
                    'status' => 0,
                    'exam_name' => 'NABTEB',
                    'message' => curl_error($this->client),
                   
                );
            
            $this->nabtebResult->set_json_result(json_encode($to_json)); 
                    
            $this->remove_cache();
            return $this->nabtebResult;
            
	}
        
        $fileo = fopen($this->get_exam_number()."-1.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE
			fwrite($fileo,$answer);
			fclose($fileo);
        $fileo = fopen($this->get_exam_number().".txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE WHICH AS BEEN STRIPPED OF ALL TAGS
			fwrite($fileo,$answer2);
			fclose($fileo);
        $error = strpos($answer, "Invalid");
        
        if ($error==true)
        {
            $file = file_get_contents($this->get_exam_number() . "-1.txt");
            $html = new \DOMDocument();
            $html->loadHTML("$file");
            $els = $html->getelementsbytagname('p');
            
            $message = $els->item(0)->textContent;
            $this->nabtebResult->set_result_status(0);
            $this->nabtebResult->set_exam_name('NABTEB');
            $this->nabtebResult->set_html_result($message);
            $this->nabtebResult->set_result_response($message); 
            
            $to_json = array(
                    'status' => 0,
                    'exam_name' => 'NABTEB',
                    'message' => $message,
                    
                );
            
            $this->nabtebResult->set_json_result(json_encode($to_json)); 
                    
            $this->remove_cache();
            return $this->nabtebResult;
        }
        else
        {
            $file = file_get_contents($this->get_exam_number() . "-1.txt");
            $html = new \DOMDocument();
            $html->loadHTML("$file");
            $th = $html->getelementsbytagname('th');
            $td = $html->getelementsbytagname('td');
            // print_r($td->item(2)->nodeValue);
            $hhtml = $answer;
            $candidate_name = $td->item(3)->nodeValue;
            $exam_t = $td->item(4)->nodeValue;
            $exam_centre = $td->item(6)->nodeValue;
            $card_use = $td->item(7)->nodeValue;
            $sub1 = $th->item(11)->nodeValue;
            $gsub1 = $td->item(8)->nodeValue;
            $sub2 = $th->item(12)->nodeValue;
            $gsub2 = $td->item(9)->nodeValue;
            $sub3 = $th->item(13)->nodeValue;
            $gsub3 = $td->item(10)->nodeValue;
            $sub4 = $th->item(15)->nodeValue;
            $gsub4 = $td->item(11)->nodeValue;
            $sub5 = $th->item(17)->nodeValue;
            $gsub5 = $td->item(12)->nodeValue;
            $sub6 = $th->item(18)->nodeValue;
            $gsub6 = $td->item(13)->nodeValue;
            $sub7 = $th->item(19)->nodeValue;
            $gsub7 = $td->item(14)->nodeValue;
            $sub8 = $th->item(20)->nodeValue;
            $gsub8 = $td->item(15)->nodeValue;
            $sub9 = $th->item(21)->nodeValue;
            $gsub9 = $td->item(16)->nodeValue;
            
            
            $this->nabtebResult->set_result_status(1);
            $this->nabtebResult->set_exam_name('NABTEB');
            $this->nabtebResult->set_html_result($hhtml);
            $this->nabtebResult->set_exam_type($exam_t);
            $this->nabtebResult->set_exam_number($this->get_exam_number());
            $this->nabtebResult->set_candidate_name($candidate_name);
            $this->nabtebResult->set_exam_year($this->get_exam_year());
            $this->nabtebResult->set_exam_center($exam_centre);
            $this->nabtebResult->set_card_use($card_use);

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
            $this->nabtebResult->set_array_score($result);
            
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
                                                        <td align='left' width='40%'>Examination </td>
                                                        <td align='left' width='60%'>NABTEB</td>
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
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>

                            <table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Subject Grades</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
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
                            <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
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
                
                    $this->nabtebResult->set_tabled_result($table);
                    
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

                    $this->nabtebResult->set_plain_result($plain);
                    
                    
                    $to_json = array(
                                'status' => 1,
                                'card_pin' => $this->get_card_pin(),
                                'card_sn' => $this->get_card_sn(),
                                'exam_name' => 'NABTEB',
                                'exam_type' => $exam_t,
                                'exam_year' => $this->get_exam_year(),
                                'exam_number' => $this->get_exam_number(),
                                'candidate_name' => $this->sanitize($candidate_name),
                                'exam_center' => $this->sanitize($exam_centre),
                                'card_use' => $card_use,
                                'result' => $result,
                                
                            );

                    $this->nabtebResult->set_json_result(json_encode($to_json)); 
                    
                    $this->remove_cache();
                    return $this->nabtebResult;
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
        if (file_exists($this->get_exam_number() . ".txt")) {
            unlink($this->get_exam_number() . ".txt");
        }
        if (file_exists($this->get_exam_number() . "-1.txt")) {
            unlink($this->get_exam_number() . "-1.txt");
        }
        if (file_exists("cache/" . $this->get_exam_number() . "session.txt")) {
            unlink("cache/" . $this->get_exam_number() . "session.txt");
        }
        if (file_exists("cache/" . $this->get_exam_number() . "token.txt")) {
            unlink("cache/" . $this->get_exam_number() . "token.txt");
        }
    }

}
