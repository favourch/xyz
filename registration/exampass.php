<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

if(isset($_GET['sid'])){
    $_SESSION['epass'] = 'TRUE';
}

if(getSessionValue('epass') == 'FALSE'){
    header('Location: ../index.php');
}


$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);
 

if(!checkFees((isset($_GET['sid']))?  $_GET['sid'] : $row_sess['sesid'] , getSessionValue('stid'))) {  //use the $_get session if isset
    header('Location: ../payments/index.php');
    exit;
}

// Define the maximum containing box width & height for each text box as it will appear on the final page (no padding or margin here)
$pw = 290;
$ph = 210;
$minK = 0.7;	// Maximum scaling factor 0.7 = 70%
$inc = 0.01;	// Increment to change scaling factor 0.05 = 5%

$filter = " WHERE status = 'TRUE' ";
$colname_sess = "-1";
if (isset($_GET['sid'])) {
  $colname_sess = $_GET['sid'];
  $filter = sprintf("WHERE sesid = %s",$colname_sess);
}

$deptid = getSessionValue('did');

$query_sess = sprintf("SELECT * FROM `session` %s  ORDER BY sesname DESC LIMIT 1", $filter );
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_stud = "-1";
if ( getSessionValue('stid') != NULL ) {
  $colname_stud = getSessionValue('stid');
}

$query_info = sprintf("SELECT s.stdid, s.jambregid, s.lname, s.fname, s.sex, l.title, l.fname as lfname, l.lname as llname, s.curid, "
                    . "s.email, s.phone, s.dob, s.addr, s.level, s.progid, c.coltitle, p.progname, d.deptname "
                    . "FROM student s "
                    . "JOIN programme p ON s.progid = p.progid "
                    . "JOIN department d ON p.deptid = d.deptid "
                    . "JOIN college c ON d.colid = c.colid "
                    . "LEFT JOIN lecturer l ON l.deptid = d.deptid AND l.access = 3 "
                    . "WHERE stdid = %s", 
                    GetSQLValueString($colname_stud, "text"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$pictureId = $colname_stud;

if($row_info['jambregid'] != NULL) {
    $pictureId = strtoupper($row_info['jambregid']);
}

$img_dir = "../img/user/student";
$image_url = get_pics($colname_stud, $img_dir, FALSE); 

$props = ['email', 'phone', 'sex', 'dob', 'addr', 'image'];
$profileComplete = "true";
foreach($props as $key => $prop) {
    $value = isset($row_info[$prop])? strtolower($row_info[$prop]): '';
    switch($prop) {
        case 'email':
            if(!isset($value) || $value == '' || in_array($value, ['change@youremail.com'])) {
                $profileComplete = 'Email Address';
            }
            break;
        
        case 'phone':
            if(!isset($value) || $value == '') {
                $profileComplete = 'Phone Number';
            }
            break;
        
        case 'sex':
            if(!isset($value) || !in_array($value, ['m', 'f'])) {
                $profileComplete = 'Sex';
            }
            break;
        
        case 'dob':
            if(!isset($value) || $value == '0000-00-00') {
                $profileComplete = 'Date of Birth';
            }
            break;
        
        case 'image':
            if($image_url == '../img/user/student/profile.png') {
                $profileComplete = 'Profile Image';
            }    
            break;
        
        default:
            if(!isset($value) || $value == 'Your Campus or Home Addres') {
                $profileComplete = 'Address';
            }          
    }
    
    if($profileComplete != "true") {
       header('Location: ../student/profile.php');
    }
}

$colname_regStatus1 = "-1"; 
if (isset($row_sess['sesid'])) {
  $colname_regStatus1 = $row_sess['sesid'] ; 
}

$query_reg = sprintf("SELECT * FROM registration r WHERE sesid = %s AND stdid = %s", 
GetSQLValueString($colname_regStatus1, "text"),
GetSQLValueString($colname_stud, "text")); 
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalRows_reg = mysql_num_rows($reg); 

if($row_reg['course'] == 'Registered') { 
    
        $query_course = sprintf("SELECT distinct(r.csid), c.semester, c.csname, dc.status, dc.unit " 
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid AND c.semester= %s "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s "
                        
                        . "UNION "
                        
                        . "SELECT distinct(r.csid), c.semester, c.csname, c.status, c.unit "
                        . "FROM course_reg r, course c "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND c.semester= %s "
                        . "AND r.stdid = %s AND c.curid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.csid NOT IN "
                        
                        . "(SELECT r.csid "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid AND c.semester= %s "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s ) ",
                        
                        GetSQLValueString(($row_sess['semester'] == 'first')?'F':'S', "text"),
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_info['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "int"),
                        GetSQLValueString($colname_regStatus1, "int"),
                        
                        GetSQLValueString(($row_sess['semester'] == 'first')?'F':'S', "text"),
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_info['curid'], "int"),
                        GetSQLValueString($colname_regStatus1, "int"),
                        
                        GetSQLValueString(($row_sess['semester'] == 'first')?'F':'S', "text"),
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString($row_info['curid'], "int"),
                        GetSQLValueString(getSessionValue('pid'), "int"),
                        GetSQLValueString($colname_regStatus1, "int")
                        
                        ); 
                        
    $course = mysql_query($query_course, $tams) or die(mysql_error());
    $row_course = mysql_fetch_assoc($course);
    $totalRows_course = mysql_num_rows($course);

    $curs = '';
    $totalUnit = 0;
    if($totalRows_course > 0) {
        $i = 1;
        do{
            $totalUnit += $row_course['unit'];
            $curs .='
                <tr>
                    <td>'.$i++.'</td>
                    <td>'.$row_course['csid'].'</td>
                    <td>'.$row_course['csname'].'</td>
                    <td>'.substr($row_course['status'], 0, 1).'</td>
                    <td>'.$row_course['unit'].'</td>
                </tr>';
        }while ($row_course = mysql_fetch_assoc($course));

        $curs .= '
                <tr>
                    <td colspan="4" align="right">Total Unit</td>
                    <td>'.$totalUnit.'</td>
                </tr>';
    }else {
        $curs = '<tr>
                    <td align="center" colspan="5">You do not have any course registered for this semester!</td>
                </tr>';
    }

}else {
    header('Location: ../registration/registercourse.php');

}


include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4-L','','',10,10,5,5,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);

function SinglePage($html, $pw, $ph, $minK=1, $inc=0.1) {
// returns height of page
global $mpdf;
	$mpdf->AddPage('','','','','','',($mpdf->w - $pw),'',($mpdf->h - $ph),0,0);
	$k = 1;

	$currpage = $mpdf->page;
	$mpdf->WriteHTML($html);

	$newpage = $mpdf->page;
	while($currpage != $newpage) {
		for($u=0;$u<=($newpage-$currpage);$u++) {
			// DELETE PAGE - the added page
			unset($mpdf->pages[$mpdf->page]);
			if (isset($mpdf->ktAnnots[$mpdf->page])) { unset( $mpdf->ktAnnots[$mpdf->page] ); }
			if (isset($mpdf->tbrot_Annots[$mpdf->page])) { unset( $mpdf->tbrot_Annots[$mpdf->page] ); }
			if (isset($mpdf->kwt_Annots[$mpdf->page])) { unset( $mpdf->kwt_Annots[$mpdf->page] ); }
			if (isset($mpdf->PageAnnots[$mpdf->page])) { unset( $mpdf->PageAnnots[$mpdf->page] ); }
			if (isset($mpdf->ktBlock[$mpdf->page])) { unset( $mpdf->ktBlock[$mpdf->page] ); }
			if (isset($mpdf->PageLinks[$mpdf->page])) { unset( $mpdf->PageLinks[$mpdf->page] ); }
			if (isset($mpdf->pageoutput[$mpdf->page])) { unset( $mpdf->pageoutput[$mpdf->page] ); }
			// Go to page before  - so can addpage
			$mpdf->page--;
		}
		// mPDF 2.4 Float Images
		if (count($mpdf->floatbuffer)) {
			$mpdf->objectbuffer[] = $mpdf->floatbuffer['objattr'];
			$mpdf->printobjectbuffer(false);
			$mpdf->objectbuffer = [];
			$mpdf->floatbuffer = [];
			$mpdf->float = false;
		}


		$k += $inc;
		if ((1/$k) < $minK) { die("Page no. ".$mpdf->page." is too large to fit"); }
		$w = $pw * $k;
		$h = $ph * $k;
		$mpdf->_beginpage('','',($mpdf->w - $w),'',($mpdf->h - $h));
		$currpage = $mpdf->page;

		$mpdf->_out('2 J');
		$mpdf->_out(sprintf('%.2f w',0.1*$mpdf->k));
		$mpdf->SetFont($mpdf->default_font,'',$mpdf->default_font_size ,true,true);	// forces write
		$mpdf->SetDrawColor(0);
		$mpdf->SetFillColor(255);
		$mpdf->SetTextColor(0);
		$mpdf->ColorFlag=false;

		// Start Transformation
		$mpdf->StartTransform();
		$mpdf->transformScale((100/$k), (100/$k), 0, 0);

		$mpdf->WriteHTML($html);

		$newpage = $mpdf->page;

		//Stop Transformation
		$mpdf->StopTransform();
	}
	return ($mpdf->y / $k);
}

$html .= '
<div style="text-align:center; width:100%; font-size: 20pt">
    
    <div style="float:left; width:45%;">
        <table width="90%" 
        style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 12pt; color: #000088;">
            <tr>
                <td width="28%" align="left"><img src="../img/logo/school-logo.png" width="100" height="100" /></td>
                <td width="72%" align="center">
                    <div style="font-weight: bold;">
                        <h2 style="font-size: 13pt">'.$university.'</h2>
                        <h3 style="font-size: 8pt">'.$university_address.'</h3>    
                        <h3>ACADEMIC AFFAIRS DIVISION</h3>
                        <h4>'.$row_sess['sesname'].' '. $row_sess['semester'].' Semester</h4>
                        <h3 style="font-size: 11pt">EXAMINATION CLEARANCE CERTIFICATE</h3>    
                    </div>
                </td>
            </tr>
        </table>
      <table width="532">
            <tr>
                <td width="24%" align ="left">
                    <p>
                                                                <img class="timeline-images" style="width: 150px; height: 150px;" src="'. $image_url.'" />
                                                            </p>
                    <img src="../img/user/watermark.png" 
                    width="150" 
                    height="150" 
                    style="margin-top: -150px">
                </td>
                <td width="76%">
                    <table width="385" valign="bottom" style="font-size: 10pt;">
                        <tr>
                            <td width="100" align ="left"><strong>Matric No :</strong></td>
                            <td width="199" align ="left">'.$row_info['stdid'].'</td>
                            <td valign ="center" rowspan="7">
                                <barcode code="'.$row_sess['sesname'].' '.$row_sess['semester'].' Semester'
                                                .$row_info['stdid'].' '
                                                .$row_info['lname'].' '
                                                .$row_info['fname']
                                                .'" type="QR" class="barcode" size="1.3" error="M" />
                            </td>
                        </tr>
                        <tr>
                            <td width="90" align ="left"><strong>Full Name :</strong> </td>
                            <td width="199" align ="left">'.$row_info['lname'].' '.$row_info['fname'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Sex :</strong></td>
                            <td align ="left">'.$row_info['sex'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Level :</strong></td>
                            <td align ="left">'.$row_info['level'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>College :</strong></td>
                            <td align ="left">'.$row_info['coltitle'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Department :</strong></td>
                            <td align ="left">'.$row_info['deptname'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Programme :</strong></td>
                            <td align ="left">'.$row_info['progname'].'</td>
                        </tr>
                    </table> 
                </td>
            </tr>
            
        </table> 
        <table width="532" align="center" class="table table-bordered table-condensed" style="font-size:8pt">
        <thead>
            <tr>
                <th width="36">S/N</th>
                <th width="80">COURSE CODE</th>
                <th width="278">COURSE NAME</th>
                <th width="52">STATUS</th>
                <th width="30">UNIT</th> 
            </tr>
        </thead>
        <tbody>';
            
        $html .= $curs;
            
        $html .= '</tbody>
        
        </table> 
        <table style="font-size: 10pt;">
            <tr>
                <td colspan="3" >
                    I Certify that the above mention is a Bonafide student and
                    registered for the courses listed above,
                    Please allow him/her for the examination
                </td>
            </tr>
            </tr>
            <tr>
                <td width="40%" align="center">                
                <p>&nbsp;</p>
                        _______________________________<br/>
                        Sign/Date<br/>
                        
                </td>
                <td width="19%">
                  
                </td>
                <td width="40%" align="center">
                <p>&nbsp;</p>
                _______________________________<br/>';
        
        $html .= $row_info['title'].' '.$row_info['llname'].', '.$row_info['lfname']. '<br/>
                        HOD</td>   
            </tr>
              <tr>
                <td colspan="3" align="center">
                (File copy)
                    
                </td>
              </tr>
        </table>
    </div>
    
    <div style="float:right; width:45%;">
        <table width="90%" 
        style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 12pt; color: #000088;">
            <tr>
                <td width="28%" align="left"><img src="../img/logo/school-logo.png" width="100" height="100" /></td>
                <td width="72%" align="center">
                    <div style="font-weight: bold;">
                        <h2 style="font-size: 13pt">'.$university.'</h2>
                        <h3 style="font-size: 8pt">'.$university_address.'</h3>    
                        <h3>ACADEMIC AFFAIRS DIVISION</h3>
                        <h4>'.$row_sess['sesname'].' '. $row_sess['semester'].' Semester</h4>
                        <h3 style="font-size: 11pt">EXAMINATION CLEARANCE CERTIFICATE</h3>    
                    </div>
                </td>
            </tr>
        </table>
      <table width="532">
            <tr>
                <td width="24%" align ="left">
                    <img src="'.$image_url.'" width="150" height="150">
                    <img src="../img/user/watermark.png" 
                    width="150" 
                    height="150" 
                    style="margin-top: -150px">
                </td>
                <td width="76%">
                    <table width="385" valign="bottom" style="font-size: 10pt;">
                        <tr>
                            <td width="100" align ="left"><strong>Matric No :</strong></td>
                            <td width="199" align ="left">'.$row_info['stdid'].'</td>
                            <td valign ="center" rowspan="7">
                                <barcode code="'.$row_sess['sesname'].' '.$row_sess['semester'].' Semester'
                                                .$row_info['stdid'].' '
                                                .$row_info['lname'].' '
                                                .$row_info['fname']
                                                .'" type="QR" class="barcode" size="1.3" error="M" />
                            </td>
                        </tr>
                        <tr>
                            <td width="90" align ="left"><strong>Full Name :</strong> </td>
                            <td width="199" align ="left">'.$row_info['lname'].' '.$row_info['fname'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Sex :</strong></td>
                            <td align ="left">'.$row_info['sex'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Level :</strong></td>
                            <td align ="left">'.$row_info['level'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>College :</strong></td>
                            <td align ="left">'.$row_info['coltitle'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Department :</strong></td>
                            <td align ="left">'.$row_info['deptname'].'</td>
                        </tr>
                        <tr>
                            <td align ="left"><strong>Programme :</strong></td>
                            <td align ="left">'.$row_info['progname'].'</td>
                        </tr>
                    </table> 
                </td>
            </tr>
            
        </table> 
        
        <table width="532" align="center" class="table table-bordered table-condensed" style="font-size:8pt">
        <thead>
            <tr>
                <th width="36">S/N</th>
                <th width="80">COURSE CODE</th>
                <th width="278">COURSE NAME</th>
                <th width="52">STATUS</th>
                <th width="30">UNIT</th> 
            </tr>
        </thead>
        <tbody>';
            
        $html .= $curs;
        
        $html .= '</tbody>
        
        </table> 
        <table style="font-size: 10pt;">
            <tr>
                <td colspan="3" >
                    I Certify that the above mention is a Bonafide student and
                    registered for the courses listed above,
                    Please allow him/her for the examination
                </td>
            </tr>
            <tr>
                <td width="40%" align="center">
                <p>&nbsp;</p>
                        _______________________________<br/>
                        Sign/Date<br/>
                        
                </td>
                <td width="19%">
                  
                </td>
                <td width="40%" align="center">
                <p>&nbsp;</p>
                _______________________________<br/>';
        
        $html .= $row_info['title'].' '.$row_info['llname'].', '.$row_info['lfname']. '<br/>
                        HOD</td>   
            </tr>
              <tr>
                <td colspan="3" align="center">
                (Student\'s copy)
                    
                </td>
              </tr>
        </table>
    </div>
    
    <div style="clear:both"></div>
</div>';

SinglePage($html, $pw, $ph, $minK);
//$mpdf->WriteHTML($html);
$mpdf->Output('Exam Pass.pdf', 'I');
exit;