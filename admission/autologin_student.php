<?php 
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "11";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');


//get prospective matric number from school fee tramsaction table 
$paymentSQL = sprintf("SELECT * FROM schfee_transactions st JOIN prospective p ON st.can_no = p.jambregid WHERE st.can_no = %s AND st.status = 'APPROVED' LIMIT 1",
                    GetSQLValueString($jambregid, "text"));
$payRS = mysql_query($paymentSQL, $tams) or die(mysql_error());
$row_pay = mysql_fetch_assoc($payRS);
$totalRows_pay = mysql_num_rows($payRS);


if($totalRows_pay > 1){
    header("Location:pogress.php");
    exit();
}



$loginUsername = $row_pay['matric_no'];
$password = strtolower($row_pay['lname']);

//var_dump($password);

//unset($_SESSION);
//session_destroy();

$response = doLogin(2, $loginUsername, $password, "../registration/registercourse.php");



?>