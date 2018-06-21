<?php
require("db.php"); 

$exam = $mode;
if($exam == 'siteinfo'){
exit();
}	
if(empty($_REQUEST['exam_number'])){
	die('Exam Number Cannot be Empty');
}
if(empty($_REQUEST['exam_year'])){
	die("Exam Year Cannot be Empty");
}
if(empty($_REQUEST['exam_month'])){
	die("Exam Month Cannot be Empty");
}
if(empty($_REQUEST['pin'])){
	die("Card Pin Cannot Be Empty");
}
if(empty($_REQUEST['serial'])){
	die("Serial No Cannot Be Empty");
}

$exam_number = strip_tags($_REQUEST['exam_number']);
$exam_year = strip_tags($_REQUEST['exam_year']);
$exam_month = strip_tags($_REQUEST['exam_month']);
$exm = strip_tags($_REQUEST['exam_month']);
$pin = strip_tags($_REQUEST['pin']);
$serial = strip_tags($_REQUEST['serial']);
$exam = strtolower($exam);


if($exam == 'waec'){
	waec_check($exam_number,$exam_year,$pin,$exam_month,$serial);
}elseif($exam == 'neco'){
	if($exam_month == 'JUN/JUL'){
		$exam_month = "1";
	}elseif($exam_month == 'NOV/DEC'){
		$exam_month = "2";
	}elseif($exam_month == 'JSCE'){
		$exam_month = "2";
	}elseif($exam_month == 'NCEE'){
		$exam_month = "2";
	}

	neco_check($exam_number,$exam_year,$pin,$exam_month);
}elseif($exam == 'nabteb'){
	if($exam_month == 'MAY/JUN'){
		$exam_month = "01";
	}elseif($exam_month == 'NOV/DEC'){
		$exam_month = "02";
	}elseif($exam_month == 'MODULAR(MARCH)'){
		$exam_month = "03";
	}elseif($exam_month == 'MODULAR(DECEMBER)'){
		$exam_month = "04";
	}elseif($exam_month == "MODULAR(JULY)"){
		$exam_month = "05";
	}



	nabteb_check($exam_number,$exam_year,$pin,$exam_month,$serial);
}

//$reply = json_decode($json);
if(empty($message)){
	if(!empty($routes[4])){
		$format = strip_tags($routes[4]);
	}else{
		$format = 'json';
	}
	if($format == 'json'){
		$reply = $json;
	}elseif($format == 'table'){
		$reply = table_result();
	}elseif($format == 'plain'){
		$reply = plain_result();
	}elseif($format == 'html'){
		$reply = $hhtml;
	}else{
		$reply = $json;
	}
// $reply->type = array(
// 	'json' => $json,
// 	'table' => table_result(),
// 	'plain' => plain_result(),
// 	'html' => $hhtml
// 	);
// $reply = json_encode($reply);
}else{
	$reply = $message;
}
echo $reply;

function waec_check($exam_number,$exam_year,$pin,$exam_month,$serial){
	global $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$exam,$message;
	if(check() === FALSE){

	$ch = curl_init();
	 	//Wanna Query THe Result Checking URL HERE WITH THE DETAILS PASSED ABOVE
	curl_setopt($ch, CURLOPT_URL, "https://www.waecdirect.org/DisplayResult.aspx?ExamNumber=$exam_number&ExamYear=$exam_year&serial=$serial&pin=$pin&ExamType=$exam_month");
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cache/$exam_number"."session.txt");  //could be empty, but cause problems on some hosts
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
 	$answer = curl_exec($ch);
 	$answer2 = strip_tags($answer);
	if (curl_error($ch)) {
     echo curl_error($ch);
	}
	$fileo = fopen("$exam_number"."1.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE
			fwrite($fileo,$answer);
			fclose($fileo);
    $fileo = fopen("$exam_number.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE WHICH AS BEEN STRIPPED OF ALL TAGS
			fwrite($fileo,$answer2);
			fclose($fileo);
	$pageData = file_get_contents("$exam_number"."1.txt"); //READING THE CONTENT OF THE FILE TO KNOW IF THERE'S AN ERROR OR NOT
	if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $pageData, $links, PREG_PATTERN_ORDER)){
	//IF THERE'S AN ERROR ONLY ONE LINK WILL BE AVAILABLE ON THE PAGE SO LET'S CHECK WHAT THE FIRST LINK CONTAINS
	$all_hrefs = array_unique($links[1]);
	$subjects = $all_hrefs[0];
	$querystring = parse_url($subjects, PHP_URL_QUERY);
	parse_str($querystring, $vars);
	}
	//NOW CHECK IF THE SELECTED LINK CONTAINS THE errTitle KEYWORD IF NO THEN WE GOT A RESULT IF YES THEN ECHO ERRO OR DO ANY OTHER STUFF HERE
	if(isset($vars['errTitle'])){
		insert_error("WAEC", $vars['errTitle']);
		$message =  $vars['errTitle'];	
	}
	else{
		libxml_use_internal_errors(TRUE);
	//IF WE PASS THAT BLOCK CODE THAT MEANS WE HAVE A RESULT SO LET'S CONTINUE OUR ADVENTURE
	//LETS READ THE PAGE INTO AN ARRAY ALTHOUGH THIS METHOD MIGHT LOOK CRUDE IT WORKS AS LONG AS THE STRUCTURE OF THE SITE DOESN'T CHANGE
	$html =new DOMDocument();
	$html->loadHTML("$pageData");
	$tables = $html->getelementsbytagname('table');
	$candidate_info = $tables->item(2)->getElementsByTagName('td');
	$result_info = $tables->item(4)->getElementsByTagName('td');
	$card_info = $tables->item(6)->getElementsByTagName('td');
	// print_r($result_info->item(13)->textContent);die();
	$rows = $tables->item(0)->getElementsByTagName('tr');
	$cols = $rows->item(1)->getElementsByTagName('td');
	$file = file("$exam_number.txt");
	$hhtml = $answer;
	$candidate_name = $candidate_info->item(3)->textContent;
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
	$json = jsonify();
	insert_record("WAEC");
	}
	}
//remove_cache();
}
function neco_start(){
libxml_use_internal_errors(TRUE);
	global $exam_number;
	//Firstly Initializing Curl
	$ch = curl_init();
	//Will Try to get ViewState,__VIEWSTATEGENERATOR,__EVENTVALIDATION if there's none in the session
	// if(!isset($_SESSION['token'])){
	//Trying To Get tokens generated by NECO so they can think am a normal user and not a bot
	curl_setopt($ch, CURLOPT_URL, 'http://www.mynecoexams.com/results/default.aspx');
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:53.0) Gecko/20100101 Firefox/53.0');
	curl_setopt($ch, CURLOPT_POST, false);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, "your_email=$email&password=$password");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cache/$exam_number"."session.txt");  //could be empty, but cause problems on some hosts
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
	$answer = curl_exec($ch);
	if (curl_error($ch)) {
	     echo curl_error($ch);
	}
	//Now Writing the output to a file
	$fileo = fopen("cache/$exam_number"."token.txt","w");
			fwrite($fileo,$answer);
			fclose($fileo);
	//Now Searching For The token generated in the html file...Wish me luck
	$file = file_get_contents("cache/$exam_number"."token.txt");
	$html=new DOMDocument();
	$html->loadHTML("$file");
	$els=$html->getelementsbytagname('input');
	foreach($els as $inp)
  		{
  	$name=$inp->getAttribute('name');
  	if($name=='__VIEWSTATE'){
  	//Token Found And Saved Under the variable token
     $viewstate=$inp->getAttribute('value');
    }
    if($name=='__VIEWSTATEGENERATOR'){
     $viewstategenerator =$inp->getAttribute('value');
    }
    if($name=='__EVENTVALIDATION'){
     $eventvalidation = $inp->getAttribute('value');
    break;
    }
  }
	$_SESSION['viewstate'] = $viewstate;
	$_SESSION['viewstategenerator'] =  $viewstategenerator;
	$_SESSION['eventvalidation'] = $eventvalidation;
	// echo $token;
	//unlink("cache/$exam_number"."token.txt");
	// }
	//Next Thing Now is to check the result;
	return $ch;

	}


	function neco_check($exam_number,$exam_year,$pin,$exam_month){
global $email, $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$exam,$message;
		if(check() === FALSE){
		libxml_use_internal_errors(TRUE);
		//Firstly Grab All the necessary hidden Tokens
		$ch = neco_start();
		$viewstate = $_SESSION['viewstate'];
		$viewstategenerator = $_SESSION['viewstategenerator'];
		$eventvalidation = $_SESSION['eventvalidation'];
		$data = array('dlExamType'=>$exam_month,
              'dlyear'=>$exam_year,
              'txtPinNumber'=>$pin,
              'txtExamNo'=>$exam_number,
              'btnCheckMyResult' => 'Check My Result',
              '__VIEWSTATE' => $viewstate,
              '__VIEWSTATEGENERATOR' => $viewstategenerator,
              '__EVENTVALIDATION' => $eventvalidation
              );
		$data1 = http_build_query($data);
		// Next Make a curl request 
			//Wanna Query THe Result Checking URL HERE WITH THE DETAILS PASSED ABOVE
	curl_setopt($ch, CURLOPT_URL, "http://www.mynecoexams.com/results/default.aspx");
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:53.0) Gecko/20100101 Firefox/53.0');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cache/$exam_number"."session.txt");  //could be empty, but cause problems on some hosts
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
 	$answer = curl_exec($ch);
 	$answer2 = strip_tags($answer);
	if (curl_error($ch)) {
     echo curl_error($ch);
	}
	$fileo = fopen("$exam_number"."1.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE
			fwrite($fileo,$answer);
			fclose($fileo);
    $fileo = fopen("$exam_number.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE WHICH AS BEEN STRIPPED OF ALL TAGS
			fwrite($fileo,$answer2);
			fclose($fileo);
	$file = file("$exam_number.txt");
	$error = strpos($answer2, "Site Message:");
	if ($error==true)
	{
		insert_error("NECO",trim($file[77]));
	    $message = trim($file[77]);
	}
	else
	{
	   //Firstly we need to grab the info of the candidate so lets ZAGADAT

	$file = file_get_contents("$exam_number"."1.txt");
	$html=new DOMDocument();
	$html->loadHTML("$file");
	$els=$html->getelementsbytagname('input');
	foreach($els as $inp)
  		{
  	$name=$inp->getAttribute('name');
  	if($name=='txtLastName'){
  	//Token Found And Saved Under the variable token
     $lname=$inp->getAttribute('value');
    }
    if($name=='txtFirstname'){
     $fname =$inp->getAttribute('value');
    }
    if($name=='txtCenterNo'){
     $exam_centre_no = $inp->getAttribute('value');
    
    }
    if($name=='txtExamType'){
     $exam_t = $inp->getAttribute('value');
    
    }
    if($name=='txtCentername'){
     $exam_centre = $inp->getAttribute('value');
    
    }
    if($name=='txtCardUsage'){
     $card_use = $inp->getAttribute('value');
    break;

    }
	
	}
    $hhtml = $answer;
    $candidate_name = "$lname $fname";
	$file = file("$exam_number.txt");
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
    $gub7 = trim($file[244]);
    $sub8 = trim($file[256]);
    $gsub8 = trim($file[259]);
    $sub9 = trim($file[271]);
	$gsub9 = trim($file[274]);
	$json = jsonify();
	insert_record("NECO");	

      }
  }
  remove_cache();
	}

	function nabteb_check($exam_number,$exam_year,$pin,$exam_month,$serial){
		global $email, $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$exam,$message;
		if(check() === FALSE){
libxml_use_internal_errors(TRUE);
		// candid=29043001&examtype=01&examyear=2013&serial=NBRC13191708&pin=188947699759&flag=false&email=&Submit=Submit
			$data = array('examtype'=>$exam_month,
              'examyear'=>$exam_year,
              'pin'=>$pin,
              'candid'=>$exam_number,
              'Submit' => 'Submit',
              'serial' => $serial,
              'flag' => 'false'
              );
			if(!empty($email)){
				$data['email'] = $email;
			}
		$data1 = http_build_query($data);
$ch = curl_init();
	 	//Wanna Query THe Result Checking URL HERE WITH THE DETAILS PASSED ABOVE
	curl_setopt($ch, CURLOPT_URL, "https://eworld.nabtebnigeria.org/results.asp");
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cache/$exam_number"."session.txt");  //could be empty, but cause problems on some hosts
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');  //could be empty, but cause problems on some hosts
 	$answer = curl_exec($ch);
 	$answer2 = strip_tags($answer);
	if (curl_error($ch)) {
     echo curl_error($ch);
	}
	$fileo = fopen("$exam_number"."1.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE
			fwrite($fileo,$answer);
			fclose($fileo);
    $fileo = fopen("$exam_number.txt","w"); //STORING THE RESULT OF OUR QUERY IN A FILE WHICH AS BEEN STRIPPED OF ALL TAGS
			fwrite($fileo,$answer2);
			fclose($fileo);
	$error = strpos($answer, "Invalid");
	if ($error==true)
	{
	$file = file_get_contents("$exam_number"."1.txt");
	$html = new DOMDocument();
	$html->loadHTML("$file");
	$els = $html->getelementsbytagname('p');
	insert_error("NABTEB",$els->item(0)->textContent);
	$message = $els->item(0)->textContent;	
	}else{
	
	// $p=$doc->getElementById('content')->getElementsByTagName('p')->item(0);
	// echo $p->nodeValue;

	$file = file_get_contents("$exam_number"."1.txt");
	$html=new DOMDocument();
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
	$json = jsonify();
	insert_record("NABTEB");

	}
	}
	remove_cache();
}






 function plain_result(){
 	global $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$exam;
 	return  $reply = "
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
 }

 function table_result(){
 	global $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$exam;
 	 return $reply = "<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>

<table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
	<thead>
		<tr>
			<th colspan='2'>Candidate Information</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;

				<table>
					<tbody>
						<tr>
							<td align='left' width='40%'>Examination Number</td>
							<td align='left' width='60%'>$exam_number</td>
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
				</table>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>
		</tr>
	</tbody>
</table>

<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>

<table align='center' border='0' cellpadding='4' cellspacing='1' height='10%' width='94%'>
	<thead>
		<tr>
			<th colspan='2'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Subject Grades</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;

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
				</table>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>
		</tr>
	</tbody>
</table>

<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>

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
</table>
";
 }


 

function remove_cache(){
	global $exam_number;
	// die("Sai BABA");
  if(file_exists("$exam_number.txt")){
  unlink("$exam_number.txt");
}
 if(file_exists("$exam_number"."1.txt")){
   unlink("$exam_number"."1.txt");
 }
  if(file_exists("cache/$exam_number"."session.txt")){
  unlink("cache/$exam_number"."session.txt");
}
if(file_exists("cache/$exam_number"."token.txt")){
	unlink("cache/$exam_number"."token.txt");
}

}

function insert_record($exam_mode){
	global $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$dbc2,$site_token;
	$data = array(
	"candidate_name" => $candidate_name,
	"exam_t" => $exam_t,
	"exam_centre" => $exam_centre,
	"card_use" => $card_use,
	"sub1" => $sub1,
	"gsub1" => $gsub1,
    "sub2" => $sub2,
    "gsub2" => $gsub2,
    "sub3" => $sub3,
    "gsub3" => $gsub3,
    "sub4" => $sub4,
    "gsub4" => $gsub4,
	"sub5" => $sub5,
    "gsub5" => $gsub5,
    "sub6" => $sub6,
    "gsub6" => $gsub6,
    "sub7" => $sub7,
    "gsub7" => $gsub7,
    "sub8" => $sub8,
    "gsub8" => $gsub8,
    "sub9" => $sub9,
	"gsub9" => $gsub9,
	"serial_no" => $serial,
	"pin" => $pin,
	"exam_number" => $exam_number,
	"exam_month" => $exam_month,
	"exam_year" => $exam_year,
	"html" => $hhtml,
	"json" => json_encode($json),
	"status" => 'active',
	'exam' => $exam_mode,
	'site_token' => $site_token,
	'message' => ''
		) ;
	if(insert("result",$data,$dbc2)){
		return TRUE;
	}else{
		return FALSE;
	}
}

function insert_error($exam_mode,$message){
	global $exam_number,$exam_year,$exam_month,$dbc2,$site_token;
	$data = array(
		"exam_number" => $exam_number,
		"exam_year" => $exam_year,
		"exam_month" => $exam_month,
		"exam" => $exam_mode,
		"message" => $message,
		"site_token" => $site_token
		);
	if(insert("result",$data,$dbc2)){
		return TRUE;
	}else{
		return FALSE;
	}
}

function jsonify(){
	global $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json;
	$json = array(
		'details' => array(
			"candidate_name" => $candidate_name,
			"exam_t" => $exam_t,
			"exam_centre" => $exam_centre,
			"card_use" => $card_use,
			"exam_number" => $exam_number,
			"exam_month" => $exam_month,
			"exam_year" => $exam_year
			),
		'result' => array(
			"sub1" => $sub1,
			"gsub1" => $gsub1,
		    "sub2" => $sub2,
		    "gsub2" => $gsub2,
		    "sub3" => $sub3,
		    "gsub3" => $gsub3,
		    "sub4" => $sub4,
		    "gsub4" => $gsub4,
			"sub5" => $sub5,
		    "gsub5" => $gsub5,
		    "sub6" => $sub6,
		    "gsub6" => $gsub6,
		    "sub7" => $sub7,
		    "gsub7" => $gsub7,
		    "sub8" => $sub8,
		    "gsub8" => $gsub8,
		    "sub9" => $sub9,
			"gsub9" => $gsub9
			)

		);
	return $json;
}

function check(){
	global $email, $candidate_name, $exam_t,$exam_centre,$card_use,$sub1,$sub2,$sub3,$sub4,$sub5,$sub6,$sub7,$sub8,$sub9,$gsub1,$gsub2,$gsub3,$gsub4,$gsub5,$gsub6,$gsub7,$gsub8,$gsub9,$serial,$pin,$exam_number,$exam_month,$exam_year,$hhtml,$json,$message,$exam,$check_time,$dbc2;	
	$check = get_stats('result',"exam_number = '$exam_number' and exam_year = '$exam_year' and exam_month = '$exam_month' and message = ''",$dbc2);
	if($check < 1){
		return FALSE;
	}else{
	$data = run_query("SELECT * FROM result where exam_number = '$exam_number' and exam_year = '$exam_year' and exam_month = '$exam_month' and message = ''",$dbc2);
	if(!empty($data)){
		$candidate_name = $data[0]['candidate_name'];
		$exam_t = $data[0]['exam_t'];
		$exam_centre = $data[0]['exam_centre'];
		$card_use = $data[0]['card_use'];
		$sub1 = $data[0]['sub1'];
		$gsub1 = $data[0]['gsub1'];
	    $sub2 = $data[0]['sub2'];
	    $gsub2 = $data[0]['gsub2'];
	    $sub3 = $data[0]['sub3'];
	    $gsub3 = $data[0]['gsub3'];
	    $sub4 = $data[0]['sub4'];
	    $gsub4 = $data[0]['gsub4'];
		$sub5 = $data[0]['sub5'];
	    $gsub5 = $data[0]['gsub5'];
	    $sub6 = $data[0]['sub6'];
	    $gsub6 = $data[0]['gsub6'];
	    $sub7 = $data[0]['sub7'];
	    $gsub7 = $data[0]['gsub7'];
	    $sub8 = $data[0]['sub8'];
	    $gsub8 = $data[0]['gsub8'];
	    $sub9 = $data[0]['sub9'];
		$gsub9 = $data[0]['gsub9'];
		$serial = $data[0]['serial_no'];
		$pin = $data[0]['pin'];
		$exam_number = $data[0]['exam_number'];
		$exam_month = $data[0]['exam_month'];
		$$exam_year = $data[0]['exam_year'];
		$hhtml = $data[0]['html'];
		$json = $data[0]['json'];
		$status = $data[0]['status'];
		$exam = $data[0]['exam'];
		$message = $data[0]['message'];
		$check_time = $data[0]['check_time'];
		return TRUE;
	}
	}

	
}















