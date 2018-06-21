<?php
if (!isset($_SESSION)) {
   session_start();
}

require_once('../../path.php');

//DEMO STUDENT 

$stdid = getSessionValue('uid');

if(isset($_GET['uid'])){
   $stdid =  $_GET['uid'];
}


$ses_folder = explode('/', $_SESSION['admname']); 

$image_url = get_pics($stdid, "../../img/user/prospective/{$ses_folder[0]}", FALSE);

$array_clinicq = []; 
$query_clinicq = sprintf("SELECT *, cr.response as ans_response "
                        ." FROM clinic_questions cq "
                        ." LEFT JOIN clinic_response cr on cr.queid = cq.id  JOIN prospective p ON p.jambregid = cr.stdid "
                        ." AND cr.stdid = '$stdid'");
$clinicq = mysql_query($query_clinicq, $tams) or die(mysql_error());
$totalRows_clinicq = mysql_num_rows($clinicq);       
if($totalRows_clinicq > 0){
    for(; $row_clinicq = mysql_fetch_assoc($clinicq); ){
      $array_clinicq1[] = $row_clinicq;
      $array_clinicq[$row_clinicq['cat']][] = $row_clinicq;  
    }
}



include("../../mpdf/mpdf.php");
$mpdf = new mPDF('c','A4','','',15,15,40,15,10,10); 
$stylesheet = file_get_contents('../../css/mpdfstylesheet.css');
$mpdf->WriteHTML($stylesheet, 1);

$html = '';

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
            <tr>
                <td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
                <td width="85%" align="center">
                    <div style="font-weight: bold;">
                        <h2 style="font-size: 25pt">'.$university.'</h2>
                        <h5 style="font-size: 15pt">MEDICAL CENTRE</h5>
                        <h5 style="font-size: 12pt">STUDENTS’ HEALTH INFORMATION DATA (CONFIDENTIAL)</h5>
                    </div>
                </td>
            </tr>
            </table>';
            
$mpdf->SetHTMLHeader($header);

$html .=' <table>
<tr>
    <td width="80%" align="left">Please complete this carefully as information therein will form part of your health record during your stay in the University.
        Note that the Clinic will not attend to student who is not duly registered with it.
    </td>
    <td width="20%">
       <img width="140" height="160" align="top" name="placeholder" alt="Image" src="'.$image_url .'" style="alignment-adjust: central">
    </td>
</tr>
</table> 

<h3 style="font-size: 15pt; margin-top: 20px">SECTION I </h3>
JAMB REG.NUMBER:....'.$array_clinicq1[0]['jambregid'].'....... MATRIC NUMBER:..........................................</p>

<h3>A.  BIODATA</h3>
<p>Surname:...'.ucwords($array_clinicq1[0]['lname']).'.................. Other Names:.....'.ucwords($array_clinicq1[0]['fname']).' '.ucwords($array_clinicq1[0]['mname']).'...........</p>

<p>Date of Birth...'.$array_clinicq1[0]['DoB'].'................ ';

if($array_clinicq1[0]['Sex'] == 'male'){
    $html .= 'Gender : Male ( * )  Female ( )';
}else{
    $html .= 'Gender : Male (  )  Female ( * )';
}
$html .= 'Marital  Status:    Single ( * ) Married (    )   ';   

$html .= '<p>Programme: Degree ( * ). Pre Degree(  ) Deg Foundation(  ) Part Time studies(CEPEP)(  ) PG(  ). Others (Specify)........................ 

<p>Home Address:...........'.$array_clinicq1[0]['address'].'.......................</p>

<p>Parent/Guardian/Spouse....'.$array_clinicq1[0]['sponsorname'].'....... Relationship.....'.$array_clinicq1[0]['sponsorrelation'].'.........</p>

<p> Address : ...'.$array_clinicq1[0]['sponsoradrs'].'.... Telephone Number:....'.$array_clinicq1[0]['sponsorphn'].'.....</p>

        <table class="table table-hover">
          <thead>
             <tr>
                <th>#</th>
                <th>Questions</th>
                <th>Option</th>
                <th>Responds</th>
             </tr>
          </thead>
          <tbody>';

           foreach ($array_clinicq as $key => $cqs){ 
                $html .= '<tr>
                            <th colspan="4"></th>
                        </tr>
                        <tr>
                            <th colspan="4">'.$key.'</th>
                        </tr>';
             $cqno = 0; 
             foreach ($cqs as $key => $cq){
                $html .= '<tr>
                            <th scope="row">'.++$cqno.'</th>
                            <td>'.ucfirst($cq['question']).'</td>
                            <td>'.(ucfirst($cq['options'])).'</td>
                            <td>'.(ucfirst($cq['ans_response'])).'</td>
                        </tr>';
              } 
            } 
            
         $html .= '

                </tbody>
       </table>
       <span style="page-break-after: always;"></span>
        <pagebreak />
       <div style="font-size: 15pt; margin-top: 20px"> SECTION II</div>
       <table class="table table-hover">
            
            <tbody>
              <tr>
                    <th colspan="3"> Body Mass</th>
                    </tr>
                    <tr>
                        <th> 1. </th>
                        <td> What is your height? (in metres) </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 2.</th>
                        <td> What is your current weight? (in Kg) </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                            <th colspan="3"></th>
                    </tr>
                     <tr>
                        <th colspan="3"> TO BE COMPLETED BY REGISTERED MEDICAL PRACTITIONAL </th>
                    </tr>
                    <tr>
                        <th> 1.</th>
                        <td> Eyes (WITH/WITHOUT GLASSES) (L-6)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 2.</th>
                        <td> Eyes (WITH/WITHOUT GLASSES) (R-6)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 3.</th>
                        <td> EAR (R-6)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 4.</th>
                        <td> EAR (R-6)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 5.</th>
                        <td> NOSE</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 6.</th>
                        <td> MOUTH</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 7.</th>
                        <td> CHEST (X-Ray Number and Report Please)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 8.</th>
                        <td> CHEST (Lung Field)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 9.</th>
                        <td> CHEST (Heart Field)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 10.</th>
                        <td> ABDOMEN (Distensions)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 11.</th>
                        <td> ABDOMEN (Liver)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 12.</th>
                        <td> ABDOMEN (Spleen)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 13.</th>
                        <td> ABDOMEN (Kidneys)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 14.</th>
                        <td> ABDOMEN (Hernia Orifices)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 15.</th>
                        <td> BLOOD PRESSURE</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 16.</th>
                        <td> PULSE RATE</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 17.</th>
                        <td> State other physical findings of significance</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                            <th colspan="3"></th>
                        </tr>

                    <tr>
                        <th colspan="3"> LABORATORY TESTS </th>
                    </tr>
                    <tr>
                        <th> 1.</th>
                        <td> Urine Analysis </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 2.</th>
                        <td> Stool Analysis</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 3.</th>
                        <td> P.C.V. </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 4.</th>
                        <td> Blood Group</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 5.</th>
                        <td> Genotype </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 6.</th>
                        <td> H.I.V. </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> 7.</th>
                        <td> Lab. Technologist Signature </td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th></th>
                        <td></td>
                        <td></td>
                    </tr>
            </tbody>
        </table>

        <div style="font-size: 15pt; margin-top: 5px"> SECTION III</div>
        <table class="table table-hover">
            <tbody>

                    <tr>
                        <th colspan="3"> FOR UNIVERSITY MEDICAL CENTRE ONLY </th>
                    </tr>
                    <tr>
                        <th> 1.</th>
                        <td> Questionnaire checked and passed </td>
                        <td> Yes _____  No______</td>
                    </tr>
                    <tr>
                        <th> 2.</th>
                        <td> Comments and other actions on (1) above</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> a.</th>
                        <td> Further Test (Please Specify)</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <th> b.</th>
                        <td> Treatment ordered as regards (a) above</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <td> <br /><br /><br /><br /><br />
                            <p text-align: right>......................................................</p> <br />
                                   <p>University Medical Officer</p>
                        </td>
                        <td >_________________ <br /><br /></td>
                    </tr>
            </tbody>
        </table>';


 //echo utf8_encode($html);
 $mpdf->WriteHTML(utf8_encode($html));
 $mpdf->Output('clinic_response.pdf', 'I');
 exit();
?>