<?php

require_once('../../path.php');

$url_part = '';

if (isset($_GET['action'])) {
    $url_part = $_GET['action'];
}

$post = '';
if (isset($_POST)) {
    $post = json_decode(file_get_contents('php://input'), TRUE);
    
}

$data = array();

switch ($url_part) {

    case 'result' :
        $query = sprintf("SELECT * FROM result "
                . "WHERE stdid = %s "
                . "AND csid = %s "
                . "AND sesid = %s", 
                GetSQLValueString($post['student'], 'text'),
                GetSQLValueString($post['course'], 'text'), 
                GetSQLValueString($post['session'], 'int'));
        $result = mysql_query($query, $tams) or die(mysql_error());
        $row_result = mysql_fetch_assoc($result);
        $totalRows_result = mysql_num_rows($result);

        if ($totalRows_result > 0) {
            $data['status'] = 'success';
            
            $data['rs'] = $row_result;
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
        echo json_encode($data);

        break;

    case 'cs_reg':
        $query = sprintf("SELECT * FROM course_reg cr "
                . "JOIN session s ON cr.sesid = s.sesid "
                . "WHERE cr.stdid = %s AND cr.csid = %s ",
                GetSQLValueString($post['stdid'], 'text'), 
                GetSQLValueString($post['csid'], 'text'));
        $result = mysql_query($query, $tams) or die(mysql_error());
        $row_result = mysql_fetch_assoc($result);
        $totalRows_result = mysql_num_rows($result);

        if ($totalRows_result > 0) {
            $data['status'] = 'success';
            do{
                $data['rs'][] = $row_result;
            }while($row_result = mysql_fetch_assoc($result));
            
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
        echo json_encode($data);
        break;

    default :
        break;
}