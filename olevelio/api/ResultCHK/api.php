<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once ('../../client_area/inc/con.php');
require_once('index.php');
error_reporting(0);

if(isset($_GET['action']))
{
    $actions = $_GET['action'];
}
else
{ 
    echo json_encode(array('status'=> 0,'message'=> 'Action is missing on the url end point'));
    die();
}

if(isset($_POST)){
    $post = json_decode(file_get_contents('php://input'), TRUE);
}

switch ($actions) {
    
    case 'fetch':
        
        $resultChecker = new Result();
        
        
        if($post)
        {    
            $exam_name = $post['exam_name'];
            $exam_type = $post['exam_type'];
            $exam_year = $post['exam_year'];
            $exam_number = $post['exam_num'];
            $card_pin = $post['card_pin'];
            $card_sn = $post['card_sn'];
            
            
            echo  $result = $resultChecker->getResult($exam_name, $exam_type, $exam_year, $exam_number, $card_pin, $card_sn);
   
        }
        else
        {  
            echo json_encode(array('status' => 0, 'message' => 'Required field are missing '));
            die();
        }
        
        break;
        
    case 'save_local':
        if($post){
            
            $insertResultSQL = sprintf("UPDATE  transactions SET result_json = %s, updated_at = NOW(), pay_used = 'yes' WHERE id = %s", 
                      GetSQLValueString(json_encode($post['result']), 'text'), 
                      GetSQLValueString($post['trans_id'], 'text'));
            $result = mysql_query($insertResultSQL, $con) or die(mysql_error()) ;
            $affected_row = mysql_affected_rows();
            
            if($affected_row > 0){
                echo json_encode(array('status' => 1, 'message' => 'Result saved successfully'));
            }else{
                echo json_encode(array('status' => 1, 'message' => 'Nothing changed '));
            }
        }
        break;

    default:
        break;
}


?>