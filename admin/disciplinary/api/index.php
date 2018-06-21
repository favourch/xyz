<?php
require_once('../../../path.php');

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
    
    case 'students' :
        
        $query = sprintf("SELECT s.stdid, s.fname,s.level, "
                        . "s.lname, s.mname, "
                        . "p.progname, dpt.deptname, "
                        . "c.colname "
                        . "FROM student s "
                        . "JOIN programme p "
                        . "ON p.progid = s.progid "
                        . "JOIN department dpt "
                        . "ON dpt.deptid = p.deptid "
                        . "JOIN college c "
                        . "ON c.colid = dpt.colid"
                        . " WHERE s.stdid = %s LIMIT 1", GetSQLValueString($post, 'text'));
        $student = mysql_query($query, $tams) or die(mysql_error());
        $row_stud = mysql_fetch_assoc($student);
        $totalRows_stud = mysql_num_rows($student);
        
        if($totalRows_stud > 0 ){
            $data['status'] = 'success';
            $data['rs'] = $row_stud;
            
        }
        else{
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
         echo json_encode($data);

        break;
        
    case 'session_to_pay':
        $record = array();
        $query = sprintf("SELECT sesid, sesname "
                        . "FROM `session` "
                        . "WHERE sesid "
                        . "BETWEEN %s AND %s ", 
                        GetSQLValueString($_GET['from'], 'int'),
                        GetSQLValueString($_GET['to'], 'int'));
        $session = mysql_query($query, $tams) or die(mysql_error());
        $row_ses = mysql_fetch_assoc($session);
        $totalRows_ses = mysql_num_rows($session);
        
        do{
           $record[] =  $row_ses;
        }while($row_ses = mysql_fetch_assoc($session));

        if ($totalRows_ses > 0) {
            $data['status'] = 'success';
            $data['rs'] = $record;
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
        echo json_encode($data);
        break;
    
    
        case 'registration':
        $record = array();
        $query = sprintf("SELECT sesid, sesname "
                        . "FROM `session` "
                        . "WHERE status = 'TRUE' ");
        $session = mysql_query($query, $tams) or die(mysql_error());
        $row_ses = mysql_fetch_assoc($session);
        $totalRows_ses = mysql_num_rows($session);
        
       if ($totalRows_ses > 0) {
            
            $query1 = sprintf("SELECT * "
                        . "FROM schfee_transactions "
                        . "WHERE matric_no =  %s AND sesid = %s AND status = 'APPROVED'", 
                        GetSQLValueString($post['stdid'], 'text'), 
                        GetSQLValueString($row_ses['sesid'], 'text'));
            $reg_rs = mysql_query($query1, $tams) or die(mysql_error());
            $row_reg = mysql_fetch_assoc($reg_rs);
            $totalRows_reg = mysql_num_rows($reg_rs);
            
            if($totalRows_reg > 0){
                $data['status'] = 'success';
                $data['rs'] = true;
            }
            else{
                $data['status'] = 'error';
                $data['msg'] = 'This student have not paid school fee for '.$row_ses['sesname']. ' academic session';
            }
            
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Active Academic Session Found';
        }
        
        

        
        echo json_encode($data);
        break;
    default :
        break;
        
       
}