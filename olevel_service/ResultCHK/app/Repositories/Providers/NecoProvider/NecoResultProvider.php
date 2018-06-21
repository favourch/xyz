<?php

namespace Repositories\Providers\NecoProvider;
use Repositories\ResultChecker\ResultProviderInterface;
//use Repositories\ResultChecker\ResultInterface;
//use Repositories\Library\simple_html_dom;


/**
 * Description of WaecResultProvider
 *
 * @author Suleodu
 */
class NecoResultProvider implements ResultProviderInterface{
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
        $this->necoResult = new NecoResult; 
       
        libxml_use_internal_errors(TRUE);
        //Firstly Grab All the necessary hidden Tokens
        $ch = $this->neco_start();
        
        $viewstate = $_SESSION['viewstate'];
        $viewstategenerator = $_SESSION['viewstategenerator'];
        $eventvalidation = $_SESSION['eventvalidation'];
        $data = array('dlExamType'=>$this->get_exam_type(),
                    'dlyear'=>$this->get_exam_year(),
                    'txtPinNumber'=>$this->get_card_pin(),
                    'txtExamNo'=>$this->get_exam_number(),
                    'btnCheckMyResult' => 'Check My Result',
                    '__VIEWSTATE' => $viewstate,
                    '__VIEWSTATEGENERATOR' => $viewstategenerator,
                    '__EVENTVALIDATION' => $eventvalidation
                );
        $data1 = http_build_query($data);

        // Next Make a curl request 
        //Wanna Query THe Result Checking URL HERE WITH THE DETAILS PASSED ABOVE
        curl_setopt($ch, CURLOPT_URL, "http://www.mynecoexams.com/results/default.aspx");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:53.0) Gecko/20100101 Firefox/53.0');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cache/".$this->get_exam_number() . "session.txt");  //could be empty, but cause problems on some hosts
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
        $answer = curl_exec($ch);
        $answer2 = strip_tags($answer);
        
        if (curl_error($ch)) {
            $this->necoResult->set_result_status(0);
            $this->necoResult->set_exam_name('NECO');
            $this->necoResult->set_html_result(curl_error($ch));
            $this->necoResult->set_result_response(curl_error($ch));  
            
            $this->remove_cache();
            return $this->necoResult;
	}
        
        $fileo = fopen($this->get_exam_number()."-1.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE
			fwrite($fileo,$answer);
			fclose($fileo);
        $fileo = fopen($this->get_exam_number().".txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE WHICH AS BEEN STRIPPED OF ALL TAGS
			fwrite($fileo,$answer2);
			fclose($fileo);
                        
	$file = file($this->get_exam_number().".txt");
	$error = strpos($answer2, "Site Message:");
        
        if ($error==true)
	{
	    $message = trim($file[77]);
            
            $this->necoResult->set_result_status(0);
            $this->necoResult->set_exam_name('NECO');
            $this->necoResult->set_html_result($message);
            $this->necoResult->set_result_response($message);  
            
            $this->remove_cache();
            return $this->necoResult;
	}
	else
        {
            $file = file_get_contents($this->get_exam_number()."-1.txt");
            $html= new \DOMDocument();
            $html->loadHTML("$file");
            $els=$html->getelementsbytagname('input');
            
            foreach ($els as $inp) {
                $name = $inp->getAttribute('name');
                if ($name == 'txtLastName') {
                    //Token Found And Saved Under the variable token
                    $lname = $inp->getAttribute('value');
                }
                if ($name == 'txtFirstname') {
                    $fname = $inp->getAttribute('value');
                }
                if ($name == 'txtCenterNo') {
                    $exam_centre_no = $inp->getAttribute('value');
                }
                if ($name == 'txtExamType') {
                    $exam_t = $inp->getAttribute('value');
                }
                if ($name == 'txtCentername') {
                    $exam_centre = $inp->getAttribute('value');
                }
                if ($name == 'txtCardUsage') {
                    $card_use = $inp->getAttribute('value');
                    break;
                }
            }
            
                $hhtml = $answer;
                $candidate_name = $lname." ". $fname;
                $file = file($this->get_exam_number().".txt");
                $sub1 = trim($file[151]);
                $gsub1 = trim($file[154]);
                $sub2 = trim($file[166]);
                $gsub2 = trim($file[169]);
                $sub3 = trim($file[181]);
                $gsub3 = trim($file[184]);
                $sub4 = trim($file[196]);
                $gsub4 = trim($file[199]);
                $sub5 = trim($file[211]);
                $gsub5 = trim($file[214]);
                $sub6 = trim($file[226]);
                $gsub6 = trim($file[229]);
                $sub7 = trim($file[241]);
                $gsub7 = trim($file[244]);
                $sub8 = trim($file[256]);
                $gsub8 = trim($file[259]);
                $sub9 = trim($file[271]);
                $gsub9 = trim($file[274]);
                
                $this->necoResult->set_result_status(1);
                $this->necoResult->set_exam_name('NECO');
                $this->necoResult->set_html_result($hhtml);
                $this->necoResult->set_exam_type($exam_t);
                $this->necoResult->set_exam_number($this->get_exam_number());
                $this->necoResult->set_candidate_name($candidate_name);
                $this->necoResult->set_exam_year($this->get_exam_year());
                $this->necoResult->set_exam_center($exam_centre);
                $this->necoResult->set_card_use($card_use);

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
                $this->necoResult->set_array_score($result);
                    
                    $table = "<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
                            <table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
                                <thead>
                                    <tr>
                                        <th colspan='2'>Candidate Information</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination Name</td>
                                                        <td align='left' width='60%'>NECO</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination Number</td>
                                                        <td align='left' width='60%'>{$this->get_exam_number()}</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Candidate Name</td>
                                                        <td align='left' width='60%'>{$candidate_name} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination</td>
                                                        <td align='left' width='60%'>{ $exam_t }</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Examination Year</td>
                                                        <td align='left' width='60%'>{$this->get_exam_year()}</td>
                                                    </tr>
                                                    <tr>
                                                        <td align='left' width='40%'>Centre</td>
                                                        <td align='left' width='60%'>{$exam_centre}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>

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
                                            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
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
                
                    $this->necoResult->set_tabled_result($table);
                    
                    $plain = "
                    Candidate Name: $candidate_name
                    <br>
                    Examination: $exam_t . $this->get_exam_year()
                    
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

                    $this->necoResult->set_plain_result($plain);
                    
                    
                    $to_json = array(
                                'exam_name' => 'WAEC',
                                'exam_type' => $exam_t .' '. $this->get_exam_year(),
                                'exam_number' => $this->get_exam_number(),
                                'candidate_name' => $candidate_name,
                                'exam_center' => $exam_centre,
                                'card_use' => $card_use,
                                'result' => $result,
                                'result_html' => $hhtml,
                                'result_table' => $table,
                                'result_plain' => $plain
                            );

                    $this->necoResult->set_json_result(json_encode($to_json)); 
                    
                    $this->remove_cache();
                    return $this->necoResult;
            
        }
    }
    

    public function neco_start() {
        
        libxml_use_internal_errors(TRUE);
        
        //Firstly Initializing Curl
        $this->client = curl_init();
        
        //Will Try to get ViewState,__VIEWSTATEGENERATOR,__EVENTVALIDATION if there's none in the session
        // if(!isset($_SESSION['token'])){
        //Trying To Get tokens generated by NECO so they can think am a normal user and not a bot
        curl_setopt($this->client, CURLOPT_URL, 'http://www.mynecoexams.com/results/default.aspx');
        curl_setopt($this->client, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:53.0) Gecko/20100101 Firefox/53.0');
        curl_setopt($this->client, CURLOPT_POST, false);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "your_email=$email&password=$password");
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->client, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->client, CURLOPT_COOKIEJAR, "cache/".$this->get_exam_number() . "session.txt");  //could be empty, but cause problems on some hosts
        curl_setopt($this->client, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
        
        $answer = curl_exec($this->client);
        
        if (curl_error($this->client)) {
            echo curl_error($this->client);
            $this->necoResult->set_result_status(0);
            $this->necoResult->set_exam_name('NECO');
            $this->necoResult->set_html_result(curl_error($this->client));
            $this->necoResult->set_result_response(curl_error($this->client));  
            
            $this->remove_cache();
            //return $this->necoResult;
        }
        //Now Writing the output to a file
        $fileo = fopen("cache/".$this->get_exam_number() . "token.txt", "w");
        fwrite($fileo, $answer);
        fclose($fileo);
        
        //Now Searching For The token generated in the html file...Wish me luck
        $file = file_get_contents("cache/".$this->get_exam_number() . "token.txt");
        $html = new \DOMDocument();        
        $html->loadHTML("$file");
        $els = $html->getelementsbytagname('input');
        
        foreach ($els as $inp) {
            $name = $inp->getAttribute('name');
            if ($name == '__VIEWSTATE') {
                //Token Found And Saved Under the variable token
                $viewstate = $inp->getAttribute('value');
            }
            if ($name == '__VIEWSTATEGENERATOR') {
                $viewstategenerator = $inp->getAttribute('value');
            }
            if ($name == '__EVENTVALIDATION') {
                $eventvalidation = $inp->getAttribute('value');
                break;
            }
        }
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['viewstate'] = $viewstate;
        $_SESSION['viewstategenerator'] = $viewstategenerator;
        $_SESSION['eventvalidation'] = $eventvalidation;
        // echo $token;
        //unlink("cache/$exam_number"."token.txt");
        // }
        //Next Thing Now is to check the result;
        
        return  $this->client;
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
        return trim(preg_replace('/\s\s+/', ' ', $string));
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
