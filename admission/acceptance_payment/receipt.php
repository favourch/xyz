<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}



require_once('../../path.php');

$MM_authorizedUsers = "11";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$order_no = "-1";
if(isset($_GET['no'])){
    $order_no = $_GET['no'];
}

mysql_select_db($database_tams, $tams);

$query_history = sprintf('SELECT p.fname, p.lname, p.mname, at.can_no, at.can_name, at.ordid, at.pcount, '
                        . 'at.status, at.reference, at.amt, at.date_time, pr.progname '
                        . "FROM accfee_transactions at "
                        . "JOIN prospective p ON p.jambregid = at.can_no "
                         . "JOIN programme pr ON pr.progid = p.progoffered "
                        . "WHERE at.can_no = %s "
                        . "AND at.status = 'APPROVED' "
                        . "AND at.ordid = %s ", 
        GetSQLValueString($_SESSION['MM_Username'], "text"),
        GetSQLValueString($order_no, "int"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);



include("../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,50,15,5,5); 
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

if($totalRows_history > 0) {
    $html ='<p style="text-align:center; font-size: 20pt; margin-bottom: 30px"><strong> POST UTME/DE ACCEPTANCE RECEIPT</strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table align="center" style="width: 60%;" class="table table-bordered table-striped">
            <tr>
                <td width="150">Name:</td>
                <td width="400">'.$row_history['lname'].', '.$row_history['fname'].' '.$row_history['mname'].'</td>
                <td rowspan="4">
                    <barcode code="'
                                                    .$row_history['can_no'].' '
                                                    .$row_history['lname'].', '.$row_history['fname'].' '.$row_history['mname'].' '
                                                    .$row_history['amt'].'  '
                                                    .'Acceptance Fees'. '  '
                                                    .$row_history['date_time'].'  '
                                                    .'" type="QR" class="barcode" size="1.5" error="M" />
                </td>
            </tr>

            <tr>
                <td>UTME Reg No :</td>
                <td>'.$row_history['can_no'].'</td>
            </tr>
            <tr>
                <td>Description:</td>
                <td>POST UTME/DE ACCEPTANCE PAYMENT</td>
                <td></td>
            </tr>
            <tr>
                <td>Programme Offered:</td>
                <td>'.$row_history['progname'].'</td>
                <td></td>
            </tr>

            <tr>
                <td>Transaction Reference:</td>
                <td>'.$row_history['reference'].'</td>
                <td></td>
            </tr>

            <tr>
                <td>Date:</td>
                <td>'.$row_history['date_time'].'</td>
                <td></td>
            </tr>

            <tr>
                <td>Amount:</td>
                <td>'.$row_history['amt'].'</td>
                <td></td>
            </tr>
        </table>
         <div >
            <img src="../../img/payment/bursary-stamp.jpg" />
            <div style="font-size: 11px">ODUSANYA I.O</div>
            <div style="font-size: 9px">'.$row_history['date_time'].'</div>
        </div>

    </div>';
}

$mpdf->WriteHTML($html);
$printCountSQL = sprintf("UPDATE accfee_transactions SET pcount = pcount + 1 "
                        . "WHERE can_no = %s "
                        . "AND status = 'APPROVED' "
                        . "AND ordid = %s ",
                        GetSQLValueString($_SESSION['MM_Username'], "text"), 
                        GetSQLValueString($row_history['ordid'], "int"));
$printCountRS = mysql_query($printCountSQL, $tams) or die(mysql_error());

$mpdf->Output('receipt.pdf', 'I');

exit;