<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,11,20";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');

$query_rspros = sprintf("SELECT p.* "
        . "FROM prospective p JOIN accfee_transactions a ON p.jambregid = a.can_no "
        . "WHERE p.jambregid = %s AND a.status='APPROVED' ", 
        GetSQLValueString($jambregid, "text"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$content = 'You have not paid the acceptance fee for this admission!';
$session = getSessionValue("admname");

if($totalRows_rspros > 0) {
    $fullname = $row_rspros['lname'].", ".$row_rspros['fname'];
    $phone = $row_rspros['phone'];
    $content = '
    <div style="text-align: right">Date: ............................</div>
    <div>
        <h4>The Registrar,</h4>
        <h4>Tai Solarin University of Education,</h4>
        <h4>Ijagun,</h4>
        <h4>Ijebu Ode.</h4>
    </div>
    <div>Dear Sir,</div><br/>
    <div>ACCEPTANCE OF OFFER OF PROVISIONAL ADMISSION FOR '.$session.' SESSION</div>
    <p> I write to acknowledge receipt of a letter of provisional admission for the '.$session.' academic session and to 
    inform you that I <span style="padding:0 15px 2px; border-bottom-style:dotted">' . $fullname . '</span></p>
        
    <p>Address: .................................................................................................................................................</p>
    <p>
        <span>UTME Score: ......................................................</span> 
        <span>Post UTME Aggr: ......................................................</span>
    </p>
    <p>JAMB Registration No: ...........................................................................................................................</p>
    <p>Level of Admission: ................................................................................................................................</p>
    <p>Local Government Area: ........................................................................................................................</p>
    <p>State of Origin: ......................................................................................................................................</p>
    <p>Sponsor(<small>as indicated in the online registration form</small>): .....................................................................................</p>
    <p>Phone No: ..............................................................................................................................................</p>
    <p>Have read through the requirement for the offered course and possess the requisite qualifications in 
    either five credits at a sitting or six credits at two sittings and haveaccepted the offer to pursue a 
    full-time Bachelor of Education Degree programme.<br/>I also accepted that my studentship should be terminated at any stage if it is detected that my 
    claim is false.<br/>
    Offered Course : ....................................................................................................................................<br/>
    Department: ..........................................................................................................................................<br/>
    College: .................................................................. of your university and 
    have paid the acceptance fee of Forty Thousand Naira(40,000.00).<br/>
    Attached are the official receipt for payment, referee letter and photocopy of my credentials.<br/>
    Thank you, sir.</p>
    <div>
        <p>Yours Faithfully,</p><br/>
        <p>' . $fullname . '<br/>' . $phone . '</p>
    </div>
';
}

include("../mpdf/mpdf.php");
$mpdf = new mPDF(); 
//$stylesheet = file_get_contents('../css/mpdfstylesheet.css');
//$mpdf->WriteHTML($stylesheet, 1);



$mpdf->WriteHTML(utf8_encode($content));
$mpdf->Output('Acceptance Letter.pdf', 'I');

exit;