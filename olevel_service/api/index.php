<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');

$url_part = '';

if(isset($_GET['action'])){
    $url_part = $_GET['action'];
}

$post = '';
if(isset($_POST)){
    $post = json_decode(file_get_contents('php://input'), TRUE);
}

$data = array();

switch ($url_part){
    
    case 'yes':
        $date_treated = date('Y-m-d');
        
        $cur_user = $post['stdid'];
        $cur_detais = $post['id'];
        $cur_ordid = $post['ordid'];
        $colid = $post['colid'];
        $session = $post['sesid'];
        

        mysql_query("BEGIN");
        //Get print_counter of college
        $college_print_count_SQL = sprintf("SELECT * "
                                        . "FROM olevel_print "
                                        . "WHERE sesid = %s "
                                        . "AND colid = %s ",
                                        GetSQLValueString($session, 'int'),
                                        GetSQLValueString($colid, 'int'));
        $verify = mysql_query($college_print_count_SQL, $tams) or die(mysql_error());
        $row_college_print_count = mysql_fetch_assoc($verify);
        $found = mysql_num_rows($verify);
        
        $print_counter = $row_college_print_count['counter'] + 1;

        $msg = "<p style='color:green'>Your Olevel Result has been PRINTED by the ICT <br/> "
                . "and it is being forwarded to Admissions Office for Verification .<br/>"
                . "Your Print No is <b> {$date_treated}-{$print_counter}.Code </b><br/> "
                . "Check back after two working days for your Verification </p>";

        
        //Update Olevel with the counter 
        $query = sprintf("UPDATE `olevel_veri_data` "
                        . "SET `treated` = 'Yes', approve = 'Yes', "
                        . "return_msg = %s, date_treated=%s, who=%s, print_no = %s "
                        . "WHERE id = %s", 
                        GetSQLValueString($msg, 'text'), 
                        GetSQLValueString($date_treated, 'date'), 
                        GetSQLValueString($_SESSION['uid'], 'text'),
                        GetSQLValueString($print_counter, 'int'),
                        GetSQLValueString($cur_detais, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        
        //Update print_counter of college
        $update_college_print_count_SQL = sprintf("UPDATE "
                                                . "olevel_print "
                                                . "SET counter = %s "
                                                . "WHERE sesid = %s "
                                                . "AND colid = %s ",
                                                GetSQLValueString($print_counter, 'int'),
                                                GetSQLValueString($session, 'int'),
                                                GetSQLValueString($colid, 'int'));
        $verify2 = mysql_query($update_college_print_count_SQL, $tams) or die(mysql_error());
        

        if(($found > 0) && $verify1 && $verify2 ){
            mysql_query('COMMIT', $tams);
            echo "Operation successful";
        }else{
            mysql_query('ROLLBACK', $tams);
            echo "Unbale to perform this Operation please Contact Administrator";
        }
        
        break;
               
    case 'no':
        $date_treated = date('Y-m-d');
        
        $cur_user = $post['stdid'];
        $cur_detais = $post['id'];
        $cur_ordid = $post['ordid'];
        $colid = $post['colid'];
        $session = $post['sesid'];

        mysql_query("BEGIN");

        $msg = "<p style='color:red'>ICT could NOT PRINT your O'Level Result<br/>"
                . "Your Card details or O'Level result details may be wrong."
                . "Click the Edit button to update the information</p>";
        
        $msgPend = "<p style='color:brown'>"
                . "This result verification is pending "
                . " due to 'Card Error'. "
                . "The Examnation or Card details of your "
                . "other O'Level Result was wrongly submitted. </p>";
        
        $query1 = sprintf("UPDATE `olevel_veri_data` "
                        . "SET treated = 'Yes', approve = 'pending', "
                        . "return_msg = %s, who=%s "
                        . "WHERE stdid = %s " ,
                        GetSQLValueString($msgPend, 'text'),
                        GetSQLValueString($_SESSION['uid'], 'text'),
                        GetSQLValueString($cur_user, 'text') );
        $verify = mysql_query($query1, $tams) or die(mysql_error()); 
        
        $query = sprintf("UPDATE `olevel_veri_data` "
                . "SET `treated` = 'Yes', approve = 'No', "
                . "return_msg = %s, date_treated=%s, who=%s "
                . "WHERE id = %s", 
                GetSQLValueString($msg, 'text'), 
                GetSQLValueString($date_treated, 'date'), 
                GetSQLValueString($_SESSION['uid'], 'text'), 
                GetSQLValueString($cur_detais, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());


        if ($verify && $verify1 ) {
            mysql_query('COMMIT', $tams);
            echo "Operation successful";
        } else {
            mysql_query('ROLLBACK', $tams);
            echo "Unbale to perform this Operation please Contact Administrator";
        }
        break;
        
    case 'pend':
    $date_treated = date('Y-m-d');

    $cur_user = $post['stdid'];
    $cur_detais = $post['id'];
    $cur_ordid = $post['ordid'];
    $colid = $post['colid'];
    $session = $post['sesid'];

    mysql_query("BEGIN");

    $msg = "<p style='color:brown'>"
            . "This result is pending for verification"
            . " due to incorrect result information of "
            . "one of the result sitting </p>";

    mysql_query('BEGIN', $tams);

    $query = sprintf("UPDATE `olevel_veri_data` "
            . "SET `treated` = 'Yes', approve = 'Pending', "
            . "return_msg = %s, date_treated=%s, who=%s "
            . "WHERE id = %s", 
            GetSQLValueString($msg, 'text'), 
            GetSQLValueString($date_treated, 'date'), 
            GetSQLValueString($_SESSION['uid'], 'text'), 
            GetSQLValueString($cur_detais, 'text'));
    $verify1 = mysql_query($query, $tams) or die(mysql_error());


    if ($verify1) {
        mysql_query('COMMIT', $tams);
        echo "Operation successful";
    } else {
        mysql_query('ROLLBACK', $tams);
        echo "Unbale to perform this Operation please Contact Administrator";
    }
    break;
    
    case 'release_code':
        $cur_user = $post['user'];
        $cur_detais = $post['id'];
        $prog_choice = $post['prog_choice'];

        mysql_query("BEGIN");

        $msg = "Congratulations! Your O`level result has been"
                . " verified and it met the requirement of the"
                . " programme you applied for i.e <br/>(<strong>{$prog_choice}</strong>)<br/>. Copy and paste "
                . "the above  verification code in the "
                . "text box below and click verified so that you can"
                . " proceed with your payment ";

        $query = sprintf("UPDATE verification "
                        . "SET status = 'release', msg= %s, "
                        . "released_by = %s, treated_by = %s, "
                        . "date_treated = CURDATE(), date_released = CURDATE() "
                        . "WHERE id = %s ", 
                        GetSQLValueString($msg, 'text'), 
                        GetSQLValueString(getSessionValue('uid'), 'text'), 
                        GetSQLValueString(getSessionValue('uid'), 'text'), 
                        GetSQLValueString($cur_detais, 'text'));
       
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();

        mysql_query("COMMIT");
        echo "Code release successful";
        break;
    
    case 'refer':
        $cur_user = $post['user'];
        $cur_detais = $post['id'];
        $prog_choice = $post['prog_choice'];

        mysql_query("BEGIN");

        $msg = " Your O`level result has been verified But "
                . "did not meet the requirement of the programme applied for i.e "
                . " <br/>(<strong>{$prog_choice}</strong>)<br/>."
                . "However, Kindly wait the Admission Office decision on your application. Check back in 2 working days";

        $query = sprintf("UPDATE verification "
                       . "SET status = 'refer', "
                       . "msg = %s, "
                       . "date_treated = CURDATE(), "
                       . "treated_by = %s "
                       . "WHERE id = %s ", 
                        GetSQLValueString($msg, 'text'), 
                        GetSQLValueString(getSessionValue('uid'), 'text'), 
                        GetSQLValueString($cur_detais, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();
        
        
        mysql_query("COMMIT");
        echo "Refer to Admin Successful";
        break;
    
    case 'refer2' : 
        
        $cur_details = $post['id'];

        $msg = "Sorry, your O'Level Result does not meet the admission requirement.<br/>"
                . "Please contact <strong>".  getSessionValue('lname')." ".getSessionValue('fname'). "</strong> "
                . "at the University Admissions Office for advice, if you have another O'Level Result to present ";
               

        $query = sprintf("UPDATE verification "
                        . "SET  msg= %s, status = 'contact', "
                        . "treated_by = %s, "
                        . "date_treated = CURDATE() "
                        . "WHERE id = %s ", 
                        GetSQLValueString($msg, 'text'),
                        GetSQLValueString(getSessionValue('uid'), 'text'),
                        GetSQLValueString($cur_details, 'int'));
       
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();
        
        if($affected1 > 0){
            echo "Contact Operation succesfull";
        }
                
        break;
        
    case 'extra' : 
        
        $cur_details = $post['id'];
        
       echo  $query = sprintf("UPDATE verification "
                        . "SET "
                        . " extra = %s, "
                        . "treated_by = '', "
                        . "date_treated = CURDATE() "
                        . "WHERE id = %s ",
                        GetSQLValueString($post['cnt'], 'text'),
                        GetSQLValueString($cur_details, 'int'));
       
        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();
        
        if($affected1 > 0){
            echo "Operation succesfull";
        }
                
        break;  
        
    case 'revert' :

        $cur_details = $post['id'];
        $query = sprintf("UPDATE verification "
                        . "SET  msg= '',"
                        . " status = '0',"
                        . "treated_by = '', "
                        . "released_by = '', "
                        . "date_treated = '' "
                        . "WHERE id = %s ", GetSQLValueString($cur_details, 'int'));

        $verify1 = mysql_query($query, $tams) or die(mysql_error());
        $affected1 = mysql_affected_rows();

        if ($affected1 > 0) {
            echo "Operation succesfull";
        }

        break;
      
    case 'fetch_result':
        $cur_user = $post['user'];
       $progid1 = $post['progid1'];
       $sesid = $post['sesid'];
       
        if(getSessionValue('olv_veri_who') == 'pros'){
            $query = sprintf("SELECT * FROM olevel_veri_data "
                        . "WHERE jambregid = %s "
                        . "AND status = 'use' "
                        . "AND approve = 'Yes'",
                        GetSQLValueString($cur_user, 'text'));
                        
            $olevelRS = mysql_query($query, $tams) or die(mysql_error());
            $olevel_row = mysql_fetch_assoc($olevelRS);
            $olevel_num = mysql_num_rows($olevelRS);
            
            $prog_count = sprintf("SELECT count(p.jambregid) AS admitted from prospective p WHERE p.progoffered = %s AND p.sesid = %s AND p.adminstatus='Yes' ", GetSQLValueString($progid1, 'int'),GetSQLValueString($sesid, 'int'));
            $progcountRS = mysql_query($prog_count, $tams) or die(mysql_error());
            $prog_row = mysql_fetch_assoc($progcountRS);
            
            $quotaSQL = sprintf("SELECT quota FROM programme_quota where progid = %s AND sesid = %s ", GetSQLValueString($progid1, 'int'),GetSQLValueString($sesid, 'int'));
            $quotaRS = mysql_query($quotaSQL, $tams) or die(mysql_error());
            $quota_row = mysql_fetch_assoc($quotaRS);
            
        }
        else
        {
            $query = sprintf("SELECT * FROM olevel_veri_data "
                        . "WHERE stdid = %s "
                        . "AND status = 'use' "
                        . "AND approve = 'Yes'",
                        GetSQLValueString($cur_user, 'text'));
            $olevelRS = mysql_query($query, $tams) or die(mysql_error());
            $olevel_row = mysql_fetch_assoc($olevelRS);
            $olevel_num = mysql_num_rows($olevelRS);
        }
        
        
        
        $data = array();
        if($olevel_num > 0){
            do{
                $olevel_row['result_plain'] = json_decode($olevel_row['result_plain'], true);
                $data[] = $olevel_row;
            }while($olevel_row = mysql_fetch_assoc($olevelRS));
        }
        
        $data1['olevel'] = $data;
        
        
        $data1['progcount']= array('admitted'=>$prog_row['admitted'], 'quota'=> $quota_row['quota'] );
        
        echo json_encode($data1);
    default :
        break;
        
       
}
