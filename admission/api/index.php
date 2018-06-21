<?php
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
    
    case 'lga' :
        
        $query = sprintf("SELECT * FROM state_lga WHERE stid = %s", GetSQLValueString($post, 'text'));
        $lga = mysql_query($query, $tams) or die(mysql_error());
        $totalRows_lga = mysql_num_rows($lga);
        $local = array();
        if($totalRows_lga > 0 ){
            $data['status'] = 'success';
            for(;$row_lga = mysql_fetch_assoc($lga);){
                array_push($local, $row_lga);
            }
            $data['rs'] = $local;
            
        }
        else{
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
         echo json_encode($data);

        break;
        
    case 'applicant1':
       
        $query = sprintf("SELECT * FROM prospective "
                        . "WHERE jambregid = %s "
                        . "AND lname = %s ", 
                        GetSQLValueString($post['jambregid'], 'text'),
                        GetSQLValueString($post['lname'], 'text'));
        $pros = mysql_query($query, $tams) or die(mysql_error());
        $row_pros = mysql_fetch_assoc($pros);
        $totalRows_pros = mysql_num_rows($pros);
        
        if($totalRows_pros <  1){
            $data = array(
                'status' => 000, 
                'msg' => "No Record found with this credential"
            );
        }
        else
        {
            if ($row_pros['activate'] == 'true') {
                $data = array(
                    'status' => 001,
                    'msg' => "Account has alredy been activated please contact the Helpdesk for assistance "
                );
            } else {
                $data = array(
                    'status' => '002',
                    'rs' => $row_pros
                );
            }
        }
        echo json_encode($data);
        
        break;
        
        case 'applicant':
       
        $query = sprintf("SELECT * FROM prospective "
                        . "WHERE email = %s "
                        . "AND lname = %s ", 
                        GetSQLValueString($post['email'], 'text'),
                        GetSQLValueString($post['lname'], 'text'));
        $pros = mysql_query($query, $tams) or die(mysql_error());
        $row_pros = mysql_fetch_assoc($pros);
        $totalRows_pros = mysql_num_rows($pros);
        
        if($totalRows_pros <  1){
            $data = array(
                'status' => 000, 
                'msg' => "No Record found with this credential"
            );
        }
        else
        {
            if ($row_pros['activate'] == 'true') {
                $data = array(
                    'status' => 001,
                    'msg' => "Account has alredy been activated please contact the Helpdesk for assistance "
                );
            } else {
                $data = array(
                    'status' => '002',
                    'rs' => $row_pros
                );
            }
        }
        echo json_encode($data);
        
        break;
    
    default :
        break;
        
       
}